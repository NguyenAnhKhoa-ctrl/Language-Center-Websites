<?php
/**
 * EDUCENTER - HỆ THỐNG WEBSITE QUẢN LÝ TRUNG TÂM NGOẠI NGỮ & GIA SƯ
 * File: admin/grades.php
 * Phụ trách (Tuần 4 - Lý Hạo Danh - 52400175):
 * Quản lý Điểm số 4 kỹ năng (Nghe, Nói, Đọc, Viết), Tự động tính Overall & Xếp loại học lực
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

$admin_title = 'Quản Lý & Nhập Điểm Số';
$active_menu = 'grades';

// Danh sách lớp học và cấu hình thang điểm tương ứng
$classConfigs = [
    'IELTS Intensive K42' => [
        'label'   => 'IELTS Intensive K42 (Giảng viên: Cô Thảo My)',
        'teacher' => 'Cô Nguyễn Thảo My',
        'scale'   => 'ielts',
        'step'    => '0.5',
        'max'     => '9.0'
    ],
    'TOEIC 750+ Cấp Tốc' => [
        'label'   => 'TOEIC 750+ Cấp Tốc (Giảng viên: Thầy Đình Khôi)',
        'teacher' => 'Thầy Trần Đình Khôi',
        'scale'   => 'toeic',
        'step'    => '5',
        'max'     => '495'
    ],
    'Gia Sư 1-1 Thi Đại Học' => [
        'label'   => 'Gia Sư 1-1 Thi Đại Học (Gia sư: Cô Bảo Trâm)',
        'teacher' => 'Cô Lê Hoàng Bảo Trâm',
        'scale'   => 'gpa10',
        'step'    => '0.25',
        'max'     => '10.0'
    ]
];

// Dữ liệu bảng điểm mặc định chuẩn theo Báo cáo (Hình 4.5)
$defaultGradesByClass = [
    'IELTS Intensive K42' => [
        [
            'student_id'   => 1,
            'student_code' => 'HV001',
            'full_name'    => 'Nguyễn Văn An',
            'listening'    => 7.5,
            'speaking'     => 7.5,
            'reading'      => 8.0,
            'writing'      => 7.0,
            'overall'      => 7.5,
            'rank'         => 'Giỏi',
            'comment'      => 'Rất vững Nghe & Đọc, cần luyện Task 2'
        ],
        [
            'student_id'   => 4,
            'student_code' => 'HV004',
            'full_name'    => 'Hoàng Minh Quân',
            'listening'    => 6.0,
            'speaking'     => 6.5,
            'reading'      => 6.5,
            'writing'      => 6.0,
            'overall'      => 6.5,
            'rank'         => 'Khá',
            'comment'      => 'Phát âm cải thiện tốt, từ vựng phong phú'
        ],
        [
            'student_id'   => 5,
            'student_code' => 'HV005',
            'full_name'    => 'Đỗ Phương Linh',
            'listening'    => 7.0,
            'speaking'     => 7.0,
            'reading'      => 7.5,
            'writing'      => 6.5,
            'overall'      => 7.0,
            'rank'         => 'Giỏi',
            'comment'      => 'Tiếp thu nhanh, tư duy làm bài mạch lạc'
        ]
    ],
    'TOEIC 750+ Cấp Tốc' => [
        [
            'student_id'   => 2,
            'student_code' => 'HV002',
            'full_name'    => 'Trần Thị Bích Ngọc',
            'listening'    => 430,
            'speaking'     => 0,
            'reading'      => 410,
            'writing'      => 0,
            'overall'      => 840,
            'rank'         => 'Giỏi',
            'comment'      => 'Khả năng bắt từ khóa Part 3, 4 chuẩn xác'
        ]
    ],
    'Gia Sư 1-1 Thi Đại Học' => [
        [
            'student_id'   => 3,
            'student_code' => 'HV003',
            'full_name'    => 'Lê Gia Huy',
            'listening'    => 8.5,
            'speaking'     => 8.5,
            'reading'      => 9.0,
            'writing'      => 8.5,
            'overall'      => 8.6,
            'rank'         => 'Giỏi',
            'comment'      => 'Tiến bộ vượt bậc sau 1 tháng gia sư riêng'
        ]
    ]
];

if (!isset($_SESSION['admin_grades_by_class'])) {
    $_SESSION['admin_grades_by_class'] = $defaultGradesByClass;
}

/**
 * Hàm tính điểm Overall và phân loại học lực tự động theo thang điểm
 */
function calculateOverallAndRank($l, $s, $r, $w, $scale = 'ielts') {
    $l = (float)$l;
    $s = (float)$s;
    $r = (float)$r;
    $w = (float)$w;

    if ($scale === 'toeic') {
        $overall = round($l + $r);
        if ($overall >= 750) {
            $rank = 'Giỏi';
        } elseif ($overall >= 550) {
            $rank = 'Khá';
        } else {
            $rank = 'Trung bình';
        }
        return ['overall' => $overall, 'rank' => $rank];
    }

    if ($scale === 'gpa10') {
        $overall = round(($l + $s + $r + $w) / 4, 1);
        if ($overall >= 8.0) {
            $rank = 'Giỏi';
        } elseif ($overall >= 6.5) {
            $rank = 'Khá';
        } else {
            $rank = 'Trung bình';
        }
        return ['overall' => $overall, 'rank' => $rank];
    }

    // Mặc định: Thang điểm chuẩn IELTS (0.0 - 9.0), làm tròn đến 0.5 gần nhất
    $avg     = ($l + $s + $r + $w) / 4;
    $overall = round($avg * 2) / 2;

    if ($overall >= 7.0) {
        $rank = 'Giỏi';
    } elseif ($overall >= 5.5) {
        $rank = 'Khá';
    } else {
        $rank = 'Trung bình';
    }

    return ['overall' => $overall, 'rank' => $rank];
}

/**
 * Hàm trả về class màu badge theo xếp loại
 */
function getGradeBadgeClasses($rank) {
    if ($rank === 'Giỏi') {
        return [
            'overall_badge' => 'bg-success',
            'rank_badge'    => 'bg-success-subtle text-success'
        ];
    }
    if ($rank === 'Khá') {
        return [
            'overall_badge' => 'bg-primary',
            'rank_badge'    => 'bg-primary-subtle text-primary'
        ];
    }
    return [
        'overall_badge' => 'bg-warning text-dark',
        'rank_badge'    => 'bg-warning-subtle text-warning'
    ];
}

$selectedClass = isset($_GET['class_name']) ? trim($_GET['class_name']) : 'IELTS Intensive K42';
if (!isset($classConfigs[$selectedClass])) {
    $selectedClass = 'IELTS Intensive K42';
}

$examType   = isset($_GET['exam_type']) ? trim($_GET['exam_type']) : 'Kỳ thi Giữa Khóa (Mid-term Test)';
$currentCfg = $classConfigs[$selectedClass];
$scaleMode  = isset($_GET['scale']) ? trim($_GET['scale']) : $currentCfg['scale'];

$flashMessage = '';

// Xử lý lưu và tính điểm Overall khi giáo viên nhấn Cập Nhật Tất Cả Điểm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_grades') {
    $selectedClass = trim($_POST['class_name'] ?? 'IELTS Intensive K42');
    if (!isset($classConfigs[$selectedClass])) {
        $selectedClass = 'IELTS Intensive K42';
    }
    $currentCfg = $classConfigs[$selectedClass];
    $scaleMode  = trim($_POST['scale'] ?? $currentCfg['scale']);
    $examType   = trim($_POST['exam_type'] ?? 'Kỳ thi Giữa Khóa (Mid-term Test)');

    $listeningScores = $_POST['listening'] ?? [];
    $speakingScores  = $_POST['speaking'] ?? [];
    $readingScores   = $_POST['reading'] ?? [];
    $writingScores   = $_POST['writing'] ?? [];

    if (isset($_SESSION['admin_grades_by_class'][$selectedClass])) {
        foreach ($_SESSION['admin_grades_by_class'][$selectedClass] as &$row) {
            $sid = (int)$row['student_id'];
            $l   = isset($listeningScores[$sid]) ? (float)$listeningScores[$sid] : (float)$row['listening'];
            $s   = isset($speakingScores[$sid])  ? (float)$speakingScores[$sid]  : (float)$row['speaking'];
            $r   = isset($readingScores[$sid])   ? (float)$readingScores[$sid]   : (float)$row['reading'];
            $w   = isset($writingScores[$sid])   ? (float)$writingScores[$sid]   : (float)$row['writing'];

            $calc = calculateOverallAndRank($l, $s, $r, $w, $scaleMode);

            $row['listening'] = $l;
            $row['speaking']  = $s;
            $row['reading']   = $r;
            $row['writing']   = $w;
            $row['overall']   = $calc['overall'];
            $row['rank']      = $calc['rank'];
        }
        unset($row);
    }

    $flashMessage = "Đã tính toán điểm Overall và cập nhật bảng điểm lớp <strong>" . htmlspecialchars($selectedClass) . "</strong> thành công!";
}

$gradesList = $_SESSION['admin_grades_by_class'][$selectedClass] ?? [];

require_once __DIR__ . '/includes/admin_header.php';
?>

<!-- TIÊU ĐỀ TRANG & NÚT THAO TÁC NHANH -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="fw-bold mb-1">Quản Lý Điểm Số &amp; Đánh Giá Học Viên</h3>
        <p class="text-muted small mb-0">Nhập điểm 4 kỹ năng định kỳ, tính toán điểm tổng kết và ghi chú đánh giá học thuật</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary rounded-pill px-3" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i>In Bảng Điểm
        </button>
        <button type="submit" form="gradesForm" class="btn btn-primary rounded-pill px-4 shadow-sm fw-semibold">
            <i class="fa-solid fa-floppy-disk me-2"></i>Lưu Bảng Điểm
        </button>
    </div>
</div>

<?php if ($flashMessage !== ''): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i><?php echo $flashMessage; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- BỘ LỌC LỚP HỌC, ĐỢT KIỂM TRA & THANG ĐIỂM -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form action="grades.php" method="GET" class="row g-3 align-items-center">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted mb-1">Chọn lớp học:</label>
                <select name="class_name" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($classConfigs as $clsKey => $cfg): ?>
                        <option value="<?php echo htmlspecialchars($clsKey); ?>"
                            <?php echo ($selectedClass === $clsKey) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cfg['label']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Đợt kiểm tra:</label>
                <?php
                    $examTypes = [
                        'Kỳ thi Giữa Khóa (Mid-term Test)',
                        'Kỳ thi Cuối Khóa (Final Test)',
                        'Kiểm tra Mini-test Tuần 4'
                    ];
                ?>
                <select name="exam_type" class="form-select">
                    <?php foreach ($examTypes as $et): ?>
                        <option value="<?php echo htmlspecialchars($et); ?>"
                            <?php echo ($examType === $et) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($et); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Thang điểm:</label>
                <select name="scale" id="gradeScaleSelect" class="form-select">
                    <option value="ielts" <?php echo ($scaleMode === 'ielts') ? 'selected' : ''; ?>>Thang điểm IELTS (0.0 - 9.0)</option>
                    <option value="toeic" <?php echo ($scaleMode === 'toeic') ? 'selected' : ''; ?>>Thang điểm TOEIC (0 - 990)</option>
                    <option value="gpa10" <?php echo ($scaleMode === 'gpa10') ? 'selected' : ''; ?>>Thang điểm 10 (0.0 - 10.0)</option>
                </select>
            </div>

            <div class="col-md-2 mt-auto">
                <button type="submit" class="btn btn-outline-primary w-100 rounded-pill fw-semibold">
                    <i class="fa-solid fa-filter me-1"></i>Tải bảng
                </button>
            </div>
        </form>
    </div>
</div>

<!-- BẢNG NHẬP ĐIỂM 4 KỸ NĂNG & TỰ ĐỘNG TÍNH OVERALL (HÌNH 4.5) -->
<div class="card border-0 shadow-sm rounded-4">
    <form action="grades.php" method="POST" id="gradesForm">
        <input type="hidden" name="action" value="save_grades">
        <input type="hidden" name="class_name" value="<?php echo htmlspecialchars($selectedClass); ?>">
        <input type="hidden" name="exam_type" value="<?php echo htmlspecialchars($examType); ?>">
        <input type="hidden" name="scale" id="hiddenScaleInput" value="<?php echo htmlspecialchars($scaleMode); ?>">

        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0 text-center" id="gradesTable">
                <thead class="table-dark">
                    <tr>
                        <th class="text-start ps-4">Mã HV</th>
                        <th class="text-start">Họ và Tên</th>
                        <th style="width: 105px;">Nghe</th>
                        <th style="width: 105px;">Nói</th>
                        <th style="width: 105px;">Đọc</th>
                        <th style="width: 105px;">Viết</th>
                        <th style="width: 120px;">Overall</th>
                        <th style="width: 115px;">Xếp Loại</th>
                        <th class="text-start pe-4" style="min-width: 250px;">Nhận xét giáo viên</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($gradesList as $gr): ?>
                        <?php
                            $sid    = (int)$gr['student_id'];
                            $badges = getGradeBadgeClasses($gr['rank']);
                            $fmt    = ($scaleMode === 'toeic') ? 0 : 1;
                        ?>
                        <tr class="grade-row" data-student-id="<?php echo $sid; ?>">
                            <td class="text-start ps-4">
                                <strong class="text-primary"><?php echo htmlspecialchars($gr['student_code']); ?></strong>
                            </td>
                            <td class="text-start fw-bold text-dark">
                                <?php echo htmlspecialchars($gr['full_name']); ?>
                            </td>
                            <td>
                                <input type="number"
                                       step="<?php echo htmlspecialchars($currentCfg['step']); ?>"
                                       min="0"
                                       max="<?php echo htmlspecialchars($currentCfg['max']); ?>"
                                       name="listening[<?php echo $sid; ?>]"
                                       class="form-control form-control-sm text-center skill-input skill-l"
                                       value="<?php echo htmlspecialchars(number_format((float)$gr['listening'], $fmt, '.', '')); ?>">
                            </td>
                            <td>
                                <input type="number"
                                       step="<?php echo htmlspecialchars($currentCfg['step']); ?>"
                                       min="0"
                                       max="<?php echo htmlspecialchars($currentCfg['max']); ?>"
                                       name="speaking[<?php echo $sid; ?>]"
                                       class="form-control form-control-sm text-center skill-input skill-s"
                                       value="<?php echo htmlspecialchars(number_format((float)$gr['speaking'], $fmt, '.', '')); ?>">
                            </td>
                            <td>
                                <input type="number"
                                       step="<?php echo htmlspecialchars($currentCfg['step']); ?>"
                                       min="0"
                                       max="<?php echo htmlspecialchars($currentCfg['max']); ?>"
                                       name="reading[<?php echo $sid; ?>]"
                                       class="form-control form-control-sm text-center skill-input skill-r"
                                       value="<?php echo htmlspecialchars(number_format((float)$gr['reading'], $fmt, '.', '')); ?>">
                            </td>
                            <td>
                                <input type="number"
                                       step="<?php echo htmlspecialchars($currentCfg['step']); ?>"
                                       min="0"
                                       max="<?php echo htmlspecialchars($currentCfg['max']); ?>"
                                       name="writing[<?php echo $sid; ?>]"
                                       class="form-control form-control-sm text-center skill-input skill-w"
                                       value="<?php echo htmlspecialchars(number_format((float)$gr['writing'], $fmt, '.', '')); ?>">
                            </td>
                            <td>
                                <span class="badge <?php echo $badges['overall_badge']; ?> fs-6 overall-badge">
                                    <?php echo htmlspecialchars(number_format((float)$gr['overall'], $fmt, '.', '')); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo $badges['rank_badge']; ?> rank-badge">
                                    <?php echo htmlspecialchars($gr['rank']); ?>
                                </span>
                            </td>
                            <td class="text-start pe-4">
                                <small class="text-muted"><?php echo htmlspecialchars($gr['comment']); ?></small>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white py-3 border-0 text-end">
            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-semibold">
                <i class="fa-solid fa-check me-1"></i>Cập Nhật Tất Cả Điểm
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var scaleSelect = document.getElementById('gradeScaleSelect');
    var hiddenScale = document.getElementById('hiddenScaleInput');

    function recalculateRow(row) {
        var scale = scaleSelect ? scaleSelect.value : 'ielts';
        var l = parseFloat(row.querySelector('.skill-l').value) || 0;
        var s = parseFloat(row.querySelector('.skill-s').value) || 0;
        var r = parseFloat(row.querySelector('.skill-r').value) || 0;
        var w = parseFloat(row.querySelector('.skill-w').value) || 0;

        var overall = 0;
        var rank    = 'Trung bình';

        if (scale === 'toeic') {
            overall = Math.round(l + r);
            if (overall >= 750) rank = 'Giỏi';
            else if (overall >= 550) rank = 'Khá';
        } else if (scale === 'gpa10') {
            overall = (Math.round(((l + s + r + w) / 4) * 10) / 10).toFixed(1);
            if (overall >= 8.0) rank = 'Giỏi';
            else if (overall >= 6.5) rank = 'Khá';
        } else {
            var avg = (l + s + r + w) / 4;
            overall = (Math.round(avg * 2) / 2).toFixed(1);
            if (overall >= 7.0) rank = 'Giỏi';
            else if (overall >= 5.5) rank = 'Khá';
        }

        var overallBadge = row.querySelector('.overall-badge');
        var rankBadge    = row.querySelector('.rank-badge');

        if (overallBadge) {
            overallBadge.textContent = overall;
            overallBadge.className = 'badge fs-6 overall-badge ' + (
                rank === 'Giỏi' ? 'bg-success' : (rank === 'Khá' ? 'bg-primary' : 'bg-warning text-dark')
            );
        }

        if (rankBadge) {
            rankBadge.textContent = rank;
            rankBadge.className = 'badge rank-badge ' + (
                rank === 'Giỏi' ? 'bg-success-subtle text-success' : (rank === 'Khá' ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-warning')
            );
        }
    }

    document.querySelectorAll('.grade-row').forEach(function (row) {
        row.querySelectorAll('.skill-input').forEach(function (input) {
            input.addEventListener('input', function () {
                recalculateRow(row);
            });
        });
    });

    if (scaleSelect) {
        scaleSelect.addEventListener('change', function () {
            if (hiddenScale) {
                hiddenScale.value = this.value;
            }
            document.querySelectorAll('.grade-row').forEach(recalculateRow);
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
