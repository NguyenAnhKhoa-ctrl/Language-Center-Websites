<?php
/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: admin/finance.php
 * Phụ trách (Bổ sung Gói B2 - Lý Hạo Danh - 52400175):
 * Quản lý Tài chính, Hóa đơn Học phí, Tự động tính Lương Giảng viên/Gia sư & Báo cáo Doanh thu
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

$admin_title = 'Quản Lý Tài Chính, Học Phí & Lương Giảng Viên';
$active_menu = 'finance';

// 1. Khởi tạo Danh sách Hóa đơn Học phí mẫu
$defaultInvoices = [
    [
        'invoice_code'   => 'HD202601',
        'student_code'   => 'HV001',
        'student_name'   => 'Nguyễn Văn An',
        'class_name'     => 'IELTS Intensive K42',
        'amount'         => 8900000,
        'payment_method' => 'VietQR Ngân hàng',
        'due_date'       => '15/09/2026',
        'status'         => 'Đã thanh toán'
    ],
    [
        'invoice_code'   => 'HD202602',
        'student_code'   => 'HV002',
        'student_name'   => 'Trần Thị Bích Ngọc',
        'class_name'     => 'TOEIC 750+ Cấp Tốc',
        'amount'         => 4500000,
        'payment_method' => 'Ví MoMo',
        'due_date'       => '20/09/2026',
        'status'         => 'Đã thanh toán'
    ],
    [
        'invoice_code'   => 'HD202603',
        'student_code'   => 'HV003',
        'student_name'   => 'Lê Gia Huy',
        'class_name'     => 'Gia sư 1-1 Luyện thi THPT',
        'amount'         => 6500000,
        'payment_method' => 'VNPay QR',
        'due_date'       => '05/10/2026',
        'status'         => 'Đã thanh toán'
    ],
    [
        'invoice_code'   => 'HD202604',
        'student_code'   => 'HV004',
        'student_name'   => 'Hoàng Minh Quân',
        'class_name'     => 'Tiếng Anh Giao Tiếp VIP',
        'amount'         => 5200000,
        'payment_method' => 'Chờ thanh toán QR',
        'due_date'       => '25/11/2026',
        'status'         => 'Chưa đóng'
    ],
    [
        'invoice_code'   => 'HD202605',
        'student_code'   => 'HV005',
        'student_name'   => 'Đỗ Phương Linh',
        'class_name'     => 'IELTS Intensive K42',
        'amount'         => 8900000,
        'payment_method' => 'VietQR Ngân hàng',
        'due_date'       => '20/11/2026',
        'status'         => 'Chờ duyệt QR'
    ]
];

if (!isset($_SESSION['admin_tuition_invoices'])) {
    $_SESSION['admin_tuition_invoices'] = $defaultInvoices;
}

// 2. Khởi tạo Bảng lương Giảng viên & Gia sư (Tính tự động theo số ca dạy & điểm Rating 5 sao)
$defaultPayrolls = [
    [
        'payroll_id'     => 1,
        'teacher_name'   => 'Thầy Michael Roberts',
        'specialization' => 'Trưởng khoa Giao tiếp & Thuyết trình',
        'sessions_count' => 26,
        'rate_per_shift' => 750000,
        'rating'         => 4.9,
        'bonus'          => 2000000,
        'status'         => 'Đã quyết toán'
    ],
    [
        'payroll_id'     => 2,
        'teacher_name'   => 'Cô Nguyễn Thảo My',
        'specialization' => 'Chuyên gia Luyện thi IELTS 8.5+',
        'sessions_count' => 30,
        'rate_per_shift' => 700000,
        'rating'         => 5.0,
        'bonus'          => 2500000,
        'status'         => 'Đã quyết toán'
    ],
    [
        'payroll_id'     => 3,
        'teacher_name'   => 'Thầy Trần Đình Khôi',
        'specialization' => 'Chuyên gia Luyện thi TOEIC & Ngữ Pháp',
        'sessions_count' => 24,
        'rate_per_shift' => 550000,
        'rating'         => 4.8,
        'bonus'          => 1500000,
        'status'         => 'Chờ chuyển khoản'
    ],
    [
        'payroll_id'     => 4,
        'teacher_name'   => 'Cô Lê Hoàng Bảo Trâm',
        'specialization' => 'Gia sư Cao cấp 1 Kèm 1 & Tiếng Anh Thiếu Nhi',
        'sessions_count' => 28,
        'rate_per_shift' => 500000,
        'rating'         => 4.9,
        'bonus'          => 1500000,
        'status'         => 'Chờ chuyển khoản'
    ]
];

if (!isset($_SESSION['admin_teacher_payrolls'])) {
    $_SESSION['admin_teacher_payrolls'] = $defaultPayrolls;
}

$flashMessage = '';
$activeTab    = isset($_GET['tab']) ? trim($_GET['tab']) : 'invoices';

// ============================================================================
// XỬ LÝ THAO TÁC QUẢN TRỊ TÀI CHÍNH (DUYỆT HÓA ĐƠN, TẠO HÓA ĐƠN, QUYẾT TOÁN LƯƠNG)
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // 1. Duyệt xác nhận đã thu học phí
    if ($action === 'approve_invoice') {
        $activeTab   = 'invoices';
        $invoiceCode = trim($_POST['invoice_code'] ?? '');
        foreach ($_SESSION['admin_tuition_invoices'] as &$inv) {
            if ($inv['invoice_code'] === $invoiceCode) {
                $inv['status'] = 'Đã thanh toán';
                if ($inv['payment_method'] === 'Chờ thanh toán QR') {
                    $inv['payment_method'] = 'VietQR Ngân hàng';
                }
                break;
            }
        }
        unset($inv);
        $flashMessage = "Đã duyệt xác nhận thu học phí cho hóa đơn <strong>" . htmlspecialchars($invoiceCode) . "</strong> thành công!";
    }

    // 2. Tạo hóa đơn học phí mới
    elseif ($action === 'create_invoice') {
        $activeTab     = 'invoices';
        $studentCode   = strtoupper(trim($_POST['student_code'] ?? 'HV006'));
        $studentName   = trim($_POST['student_name'] ?? '');
        $className     = trim($_POST['class_name'] ?? 'IELTS Intensive K42');
        $amount        = (float)($_POST['amount'] ?? 8900000);
        $paymentMethod = trim($_POST['payment_method'] ?? 'VietQR Ngân hàng');
        $status        = trim($_POST['status'] ?? 'Chưa đóng');

        if ($studentName !== '' && $amount > 0) {
            $newInvCode = 'HD' . random_int(202606, 202999);
            array_unshift($_SESSION['admin_tuition_invoices'], [
                'invoice_code'   => $newInvCode,
                'student_code'   => $studentCode,
                'student_name'   => $studentName,
                'class_name'     => $className,
                'amount'         => $amount,
                'payment_method' => $paymentMethod,
                'due_date'       => date('d/m/Y', strtotime('+7 days')),
                'status'         => $status
            ]);
            $flashMessage = "Đã phát hành hóa đơn học phí <strong>" . htmlspecialchars($newInvCode) . "</strong> cho học viên <strong>" . htmlspecialchars($studentName) . "</strong>!";
        }
    }

    // 3. Quyết toán lương giảng viên
    elseif ($action === 'settle_payroll') {
        $activeTab = 'payroll';
        $payrollId = (int)($_POST['payroll_id'] ?? 0);
        $tName     = '';
        foreach ($_SESSION['admin_teacher_payrolls'] as &$pr) {
            if ((int)$pr['payroll_id'] === $payrollId) {
                $pr['status'] = 'Đã quyết toán';
                $tName        = $pr['teacher_name'];
                break;
            }
        }
        unset($pr);
        $flashMessage = "Đã phê duyệt quyết toán lương & thù lao tháng cho <strong>" . htmlspecialchars($tName) . "</strong>!";
    }
}

$invoicesList = $_SESSION['admin_tuition_invoices'];
$payrollsList = $_SESSION['admin_teacher_payrolls'];

// Tính toán tổng số liệu tài chính động
$paidTotal    = 143500000;
$pendingTotal = 9400000;
foreach ($invoicesList as $inv) {
    if ($inv['status'] === 'Đã thanh toán') {
        $paidTotal += (float)$inv['amount'];
    } else {
        $pendingTotal += (float)$inv['amount'];
    }
}
$totalRevenue = $paidTotal + $pendingTotal;

$totalPayrollFund = 0;
foreach ($payrollsList as $pr) {
    $totalPayrollFund += ($pr['sessions_count'] * $pr['rate_per_shift']) + $pr['bonus'];
}

require_once __DIR__ . '/includes/admin_header.php';
?>

<!-- TIÊU ĐỀ TRANG & NÚT PHÁT HÀNH HÓA ĐƠN -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2 no-print">
    <div>
        <h3 class="fw-bold mb-1">Quản Lý Tài Chính, Học Phí &amp; Lương Giảng Viên</h3>
        <p class="text-muted small mb-0">Theo dõi hóa đơn học phí, đối soát cổng thanh toán QR và quyết toán thù lao giảng dạy</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary rounded-pill px-3" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i>In Báo Cáo
        </button>
        <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#createInvoiceModal">
            <i class="fa-solid fa-file-invoice-dollar me-2"></i>Tạo Hóa Đơn Học Phí
        </button>
    </div>
</div>

<?php if ($flashMessage !== ''): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4 no-print" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i><?php echo $flashMessage; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- 1. BỐN THẺ KPI TÀI CHÍNH TỔNG QUAN -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white admin-kpi-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">TỔNG DOANH THU THÁNG</span>
                    <h4 class="fw-bold my-1 text-primary"><?php echo number_format($totalRevenue, 0, ',', '.'); ?>đ</h4>
                    <small class="text-success"><i class="fa-solid fa-arrow-trend-up me-1"></i>+18.5% so với tháng trước</small>
                </div>
                <div class="bg-primary-subtle text-primary admin-stat-icon">
                    <i class="fa-solid fa-chart-line fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white admin-kpi-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">HỌC PHÍ ĐÃ THU</span>
                    <h4 class="fw-bold my-1 text-success"><?php echo number_format($paidTotal, 0, ',', '.'); ?>đ</h4>
                    <small class="text-muted">Đã đối soát VietQR / MoMo / VNPay</small>
                </div>
                <div class="bg-success-subtle text-success admin-stat-icon">
                    <i class="fa-solid fa-sack-dollar fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white admin-kpi-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">CÔNG NỢ CHỜ THU</span>
                    <h4 class="fw-bold my-1 text-warning"><?php echo number_format($pendingTotal, 0, ',', '.'); ?>đ</h4>
                    <small class="text-danger fw-semibold">Học viên chờ duyệt QR / chưa đóng</small>
                </div>
                <div class="bg-warning-subtle text-warning admin-stat-icon">
                    <i class="fa-solid fa-clock-rotate-left fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white admin-kpi-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">QUỸ LƯƠNG GIẢNG VIÊN</span>
                    <h4 class="fw-bold my-1 text-info"><?php echo number_format($totalPayrollFund, 0, ',', '.'); ?>đ</h4>
                    <small class="text-muted">Tính theo số ca dạy &amp; thưởng 5 sao</small>
                </div>
                <div class="bg-info-subtle text-info admin-stat-icon">
                    <i class="fa-solid fa-money-check-dollar fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 2. THANH ĐIỀU HƯỚNG 3 PHÂN HỆ TÀI CHÍNH -->
<ul class="nav nav-pills mb-4 bg-white p-2 rounded-4 shadow-sm d-inline-flex gap-1 no-print" id="financeTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link <?php echo ($activeTab === 'invoices') ? 'active' : ''; ?> px-4 py-2 rounded-pill fw-bold"
                data-bs-toggle="pill"
                data-bs-target="#tab-invoices"
                type="button"
                role="tab">
            <i class="fa-solid fa-file-invoice me-2"></i>1. Hóa Đơn Thu Học Phí
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?php echo ($activeTab === 'payroll') ? 'active' : ''; ?> px-4 py-2 rounded-pill fw-bold"
                data-bs-toggle="pill"
                data-bs-target="#tab-payroll"
                type="button"
                role="tab">
            <i class="fa-solid fa-user-check me-2"></i>2. Bảng Lương Giảng Viên &amp; Gia Sư
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?php echo ($activeTab === 'revenue') ? 'active' : ''; ?> px-4 py-2 rounded-pill fw-bold"
                data-bs-toggle="pill"
                data-bs-target="#tab-revenue"
                type="button"
                role="tab">
            <i class="fa-solid fa-chart-pie me-2"></i>3. Báo Cáo Doanh Thu Theo Khóa
        </button>
    </li>
</ul>

<div class="tab-content">
    <!-- TAB 1: QUẢN LÝ HÓA ĐƠN THU HỌC PHÍ -->
    <div class="tab-pane fade <?php echo ($activeTab === 'invoices') ? 'show active' : ''; ?>" id="tab-invoices" role="tabpanel">
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-receipt text-primary me-2"></i>Danh Sách Hóa Đơn &amp; Trạng Thái Đóng Học Phí
                </h5>
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                    Tổng số: <?php echo count($invoicesList); ?> hóa đơn
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Mã HĐ</th>
                            <th>Mã HV / Họ Tên</th>
                            <th>Lớp / Chương trình</th>
                            <th>Học phí</th>
                            <th>Phương thức</th>
                            <th>Hạn đóng</th>
                            <th>Trạng thái</th>
                            <th class="text-end pe-4 no-print">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoicesList as $inv): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-primary"><?php echo htmlspecialchars($inv['invoice_code']); ?></td>
                                <td>
                                    <strong class="d-block text-dark"><?php echo htmlspecialchars($inv['student_name']); ?></strong>
                                    <small class="text-muted">Mã HV: <?php echo htmlspecialchars($inv['student_code']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($inv['class_name']); ?></td>
                                <td class="fw-bold text-danger"><?php echo number_format((float)$inv['amount'], 0, ',', '.'); ?>đ</td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="fa-solid fa-qrcode text-primary me-1"></i><?php echo htmlspecialchars($inv['payment_method']); ?>
                                    </span>
                                </td>
                                <td class="small text-muted"><?php echo htmlspecialchars($inv['due_date']); ?></td>
                                <td>
                                    <?php if ($inv['status'] === 'Đã thanh toán'): ?>
                                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                                            <i class="fa-solid fa-circle-check me-1"></i>Đã thanh toán
                                        </span>
                                    <?php elseif ($inv['status'] === 'Chờ duyệt QR'): ?>
                                        <span class="badge bg-info-subtle text-info px-3 py-2 rounded-pill">
                                            <i class="fa-solid fa-spinner me-1"></i>Chờ duyệt QR
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i>Chưa đóng
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4 no-print">
                                    <?php if ($inv['status'] !== 'Đã thanh toán'): ?>
                                        <form action="finance.php?tab=invoices" method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="approve_invoice">
                                            <input type="hidden" name="invoice_code" value="<?php echo htmlspecialchars($inv['invoice_code']); ?>">
                                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold">
                                                <i class="fa-solid fa-check me-1"></i>Duyệt thu
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <a href="../lookup.php?student_id=<?php echo urlencode($inv['student_code']); ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                            <i class="fa-solid fa-eye me-1"></i>Xem hồ sơ
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 2: BẢNG TÍNH LƯƠNG & THÙ LAO GIẢNG VIÊN / GIA SƯ -->
    <div class="tab-pane fade <?php echo ($activeTab === 'payroll') ? 'show active' : ''; ?>" id="tab-payroll" role="tabpanel">
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-money-bill-transfer text-success me-2"></i>Bảng Tính Lương &amp; Thù Lao Giảng Viên / Gia Sư Trong Tháng
                </h5>
                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2">
                    Công thức: (Số ca dạy &times; Đơn giá ca) + Thưởng Rating 5 Sao
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Giảng viên / Gia sư</th>
                            <th class="text-center">Số ca dạy</th>
                            <th>Đơn giá / Ca</th>
                            <th class="text-center">Rating</th>
                            <th>Thưởng chất lượng</th>
                            <th>Tổng thực lĩnh</th>
                            <th>Trạng thái</th>
                            <th class="text-end pe-4 no-print">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payrollsList as $pr): ?>
                            <?php $totalSalary = ($pr['sessions_count'] * $pr['rate_per_shift']) + $pr['bonus']; ?>
                            <tr>
                                <td class="ps-4">
                                    <strong class="d-block text-dark"><?php echo htmlspecialchars($pr['teacher_name']); ?></strong>
                                    <small class="text-muted"><?php echo htmlspecialchars($pr['specialization']); ?></small>
                                </td>
                                <td class="text-center fw-bold"><?php echo (int)$pr['sessions_count']; ?> ca</td>
                                <td><?php echo number_format((float)$pr['rate_per_shift'], 0, ',', '.'); ?>đ</td>
                                <td class="text-center">
                                    <span class="badge bg-warning-subtle text-dark">
                                        <i class="fa-solid fa-star text-warning me-1"></i><?php echo htmlspecialchars((string)$pr['rating']); ?>/5.0
                                    </span>
                                </td>
                                <td class="text-success fw-semibold">+<?php echo number_format((float)$pr['bonus'], 0, ',', '.'); ?>đ</td>
                                <td class="fw-bold text-primary fs-6"><?php echo number_format((float)$totalSalary, 0, ',', '.'); ?>đ</td>
                                <td>
                                    <?php if ($pr['status'] === 'Đã quyết toán'): ?>
                                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                                            <i class="fa-solid fa-check-double me-1"></i>Đã quyết toán
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill">
                                            <i class="fa-solid fa-clock me-1"></i>Chờ chuyển khoản
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4 no-print">
                                    <?php if ($pr['status'] !== 'Đã quyết toán'): ?>
                                        <form action="finance.php?tab=payroll" method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="settle_payroll">
                                            <input type="hidden" name="payroll_id" value="<?php echo (int)$pr['payroll_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold">
                                                <i class="fa-solid fa-paper-plane me-1"></i>Quyết toán
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="small text-muted"><i class="fa-solid fa-lock me-1"></i>Đã hoàn tất</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 3: BÁO CÁO DOANH THU THEO KHÓA HỌC -->
    <div class="tab-pane fade <?php echo ($activeTab === 'revenue') ? 'show active' : ''; ?>" id="tab-revenue" role="tabpanel">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h5 class="fw-bold text-dark mb-4">
                <i class="fa-solid fa-chart-column text-primary me-2"></i>Cơ Cấu Doanh Thu Theo Nhóm Chương Trình Đào Tạo
            </h5>

            <?php
                $revenueItems = [
                    ['name' => 'Luyện thi IELTS Intensive & Foundation', 'amount' => '78.400.000đ', 'percent' => 42, 'color' => 'bg-primary'],
                    ['name' => 'Dịch vụ Gia sư Tiếng Anh VIP 1 Kèm 1', 'amount' => '46.500.000đ', 'percent' => 25, 'color' => 'bg-warning'],
                    ['name' => 'Luyện thi TOEIC 4 Kỹ năng Cấp Tốc', 'amount' => '31.500.000đ', 'percent' => 17, 'color' => 'bg-success'],
                    ['name' => 'Tiếng Anh Giao Tiếp Phản Xạ Thực Chiến', 'amount' => '18.200.000đ', 'percent' => 10, 'color' => 'bg-info'],
                    ['name' => 'Tiếng Anh Thiếu Nhi Chuẩn Cambridge', 'amount' => '11.800.000đ', 'percent' => 6, 'color' => 'bg-secondary']
                ];
            ?>

            <?php foreach ($revenueItems as $item): ?>
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="text-dark"><?php echo htmlspecialchars($item['name']); ?></strong>
                        <span class="fw-bold text-primary">
                            <?php echo htmlspecialchars($item['amount']); ?> (<?php echo (int)$item['percent']; ?>%)
                        </span>
                    </div>
                    <div class="progress" style="height: 12px;">
                        <div class="progress-bar <?php echo htmlspecialchars($item['color']); ?>"
                             role="progressbar"
                             style="width: <?php echo (int)$item['percent']; ?>%;"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- MODAL TẠO HÓA ĐƠN HỌC PHÍ MỚI -->
<div class="modal fade" id="createInvoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="finance.php?tab=invoices" method="POST">
                <input type="hidden" name="action" value="create_invoice">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="fa-solid fa-file-invoice-dollar text-primary me-2"></i>Phát Hành Hóa Đơn Học Phí Mới
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-sm-4">
                            <label class="form-label fw-semibold small">Mã Học viên <span class="text-danger">*</span></label>
                            <input type="text" name="student_code" class="form-control" placeholder="HV006" required>
                        </div>
                        <div class="col-sm-8">
                            <label class="form-label fw-semibold small">Họ và tên Học viên <span class="text-danger">*</span></label>
                            <input type="text" name="student_name" class="form-control" placeholder="Nguyễn Hoàng Nam" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Lớp / Khóa học đăng ký</label>
                        <select name="class_name" class="form-select">
                            <option value="IELTS Intensive K42">IELTS Intensive K42</option>
                            <option value="TOEIC 750+ Cấp Tốc">TOEIC 750+ Cấp Tốc</option>
                            <option value="Tiếng Anh Giao Tiếp VIP">Tiếng Anh Giao Tiếp VIP</option>
                            <option value="Gia sư 1-1 Luyện thi THPT">Gia sư 1-1 Luyện thi THPT</option>
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold small">Số tiền học phí (VNĐ) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" value="8900000" min="500000" step="100000" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold small">Phương thức thanh toán</label>
                            <select name="payment_method" class="form-select">
                                <option value="VietQR Ngân hàng">VietQR Ngân hàng</option>
                                <option value="Ví MoMo">Ví MoMo</option>
                                <option value="VNPay QR">VNPay QR</option>
                                <option value="Tiền mặt tại quầy">Tiền mặt tại quầy</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Trạng thái hóa đơn</label>
                        <select name="status" class="form-select">
                            <option value="Chưa đóng">Chưa đóng (Gửi thông báo QR)</option>
                            <option value="Chờ duyệt QR">Chờ duyệt QR</option>
                            <option value="Đã thanh toán">Đã thanh toán</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                        <i class="fa-solid fa-check me-1"></i>Phát Hành Hóa Đơn
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
