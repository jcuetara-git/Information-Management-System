<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != "student") {
    header("Location: ../auth/login.php");
    exit();
}

$student_no = $_SESSION['student_no'] ?? '';
$first_name = $_SESSION['firstname'] ?? $_SESSION['first_name'] ?? 'John';
$last_name = $_SESSION['lastname'] ?? $_SESSION['last_name'] ?? 'Doe';

// Handle pagination/limit configuration
$allowed_limits = [5, 20, 50];
$limit = isset($_GET['limit']) && in_array((int)$_GET['limit'], $allowed_limits) ? (int)$_GET['limit'] : 5;

// Fetch announcements for header dropdown
$announcements = [];
$conn->query("SET time_zone = '+08:00'");
$query = "SELECT title, message, created_at, 
          (created_at >= NOW() - INTERVAL 1 DAY) AS is_new 
          FROM announcements 
          WHERE status = 'published' 
          AND (target_audience = 'all' OR target_audience = 'students' OR (target_audience = 'specific_user' AND target_user_id = ?))
          ORDER BY created_at DESC LIMIT 10";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $student_no);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
}
$stmt->close();

$submissions = [];
if (!empty($student_no)) {
    $stmt = $conn->prepare("SELECT * FROM retention_records WHERE student_no = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("si", $student_no, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $submissions = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retention Policy</title>
    <link rel="stylesheet" href="../assets/css/student-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="dashboard-container">
    <?php include("../includes/header.php"); ?>

    <div class="dashboard-layout">
        <?php include("../includes/student-sidebar.php"); ?>

        <main class="main-content">
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success" id="alertBox">
                    <span><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($_GET['success']); ?></span>
                    <button class="close-btn" onclick="document.getElementById('alertBox').style.display='none'">&times;</button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger" id="alertBox">
                    <span><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($_GET['error']); ?></span>
                    <button class="close-btn" onclick="document.getElementById('alertBox').style.display='none'">&times;</button>
                </div>
            <?php endif; ?>

            <div class="card welcome-card">
                <div class="page-header" style="margin-bottom: 0; width: 100%;">
                    <div>
                        <h2 class="header-title"><i class="fa-solid fa-list-check"></i> Retention Submissions</h2>
                        <p class="header-desc">View the approval status of your submitted Letters of Undertaking.</p>
                    </div>
                    <button type="button" class="btn-add" onclick="openRetentionModal()">
                        <i class="fa-solid fa-plus"></i> Add New LOU
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="table-controls">
                    <div>
                        Show 
                        <select id="entriesLimit" onchange="changeLimit(this.value)">
                            <option value="5" <?= $limit == 5 ? 'selected' : ''; ?>>5</option>
                            <option value="20" <?= $limit == 20 ? 'selected' : ''; ?>>20</option>
                            <option value="50" <?= $limit == 50 ? 'selected' : ''; ?>>50</option>
                        </select> 
                        entries
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="status-table">
                        <thead>
                            <tr>
                                <th>Student Number</th>
                                <th>Year Level</th>
                                <th>Date Submitted</th>
                                <th>Date Memo was Issued</th>
                                <th>Failed Subjects</th>
                                <th>Attachment File</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($submissions) > 0): ?>
                                <?php foreach ($submissions as $row): ?>
                                    <?php 
                                        $status = $row['status'] ?? 'Pending';
                                        $badge_class = 'badge-pending';
                                        if (strtolower($status) === 'approved') {
                                            $badge_class = 'badge-approved';
                                        } elseif (strtolower($status) === 'rejected') {
                                            $badge_class = 'badge-rejected';
                                        }

                                        // Match the exact column name from your save handler
                                        $file_col = $row['undertaking_file_path'] ?? $row['undertaking_file'] ?? $row['file_path'] ?? '';
                                    ?>
                                    <tr>
                                        <td data-label="Student Number"><?= htmlspecialchars($row['student_no'] ?? $student_no); ?></td>
                                        <td data-label="Year Level"><?= htmlspecialchars($row['year_level'] ?? 'N/A'); ?></td>
                                        <td data-label="Date Submitted"><?= date('M d, Y', strtotime($row['created_at'])); ?></td>
                                        <td data-label="Date Memo was Issued"><?= !empty($row['memo_issued_date']) ? date('M d, Y', strtotime($row['memo_issued_date'])) : 'N/A'; ?></td>
                                        <td data-label="Failed Subjects"><?= htmlspecialchars($row['failed_subjects_count']); ?></td>
                                        <td data-label="Attachment File">
                                            <?php if (!empty($file_col)): ?>
                                                <a href="../uploads/lou/<?= htmlspecialchars($file_col); ?>" target="_blank" style="color: #2563eb; text-decoration: underline;">View File</a>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td data-label="Status"><span class="badge <?= $badge_class; ?>"><?= htmlspecialchars($status); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; color: #64748b; padding: 25px;">No retention submissions found. Click "Add New LOU" to submit one.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<div class="modal-overlay" id="retentionModal">
    <div class="personal-modal">
        <span class="close-btn" onclick="closeRetentionModal()">&times;</span>
        <form class="personal-form" method="POST" action="save-retention.php" enctype="multipart/form-data">
            <h3 class="form-title"><i class="fa-solid fa-calendar-days"></i> Submit Retention LOU</h3>
            <p class="form-desc">Submit your Letter of Undertaking if you have accumulated three (3) or more failed professional subjects.</p>

            <div class="form-grid">
                <div class="form-group no-margin">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-input form-input-readonly" value="<?= htmlspecialchars($first_name); ?>" readonly>
                </div>
                <div class="form-group no-margin">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-input form-input-readonly" value="<?= htmlspecialchars($last_name); ?>" readonly>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group no-margin">
                    <label class="form-label">Year Level</label>
                    <select name="year_level" class="form-input" required>
                        <option value="" disabled selected>Select</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                    </select>
                </div>
                <div class="form-group no-margin">
                    <label class="form-label">Number of Failed Subjects</label>
                    <input type="number" name="failed_subjects_count" class="form-input" min="3" placeholder="e.g. 3" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Date Memo was Issued</label>
                <input type="date" name="memo_issued_date" class="form-input" value="<?= date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Upload Letter of Undertaking (PDF only)</label>
                <input type="file" name="undertaking_file" class="form-input form-file" accept=".pdf" required>
            </div>

            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="closeRetentionModal()">Cancel</button>
                <button type="submit" class="btn-submit">Submit Document</button>
            </div>
        </form>
    </div>
</div>

<script>
    function changeLimit(value) {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('limit', value);
        window.location.search = urlParams.toString();
    }

    function openRetentionModal() {
        document.getElementById('retentionModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeRetentionModal() {
        document.getElementById('retentionModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    window.onclick = function(event) {
        var retentionModal = document.getElementById('retentionModal');
        if (event.target == retentionModal) {
            closeRetentionModal();
        }
    }

    setTimeout(function () {
        const alertBox = document.getElementById('alertBox');
        if (alertBox) {
            alertBox.style.transition = "opacity 0.5s ease";
            alertBox.style.opacity = "0";
            setTimeout(() => alertBox.style.display = "none", 500);
        }
    }, 4000);
</script>
</body>
</html>