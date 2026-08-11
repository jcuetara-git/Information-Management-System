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

$concerns = [];
if (!empty($student_no)) {
    $stmt = $conn->prepare("SELECT * FROM student_concerns WHERE student_no = ? ORDER BY id DESC");
    if ($stmt) {
        $stmt->bind_param("s", $student_no);
        $stmt->execute();
        $result = $stmt->get_result();
        $concerns = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Concern</title>
    <link rel="stylesheet" href="../assets/css/student-dashboard.css">
    <link rel="stylesheet" href="../assets/css/retention-policy.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="dashboard-container">
    <?php include("../includes/header.php"); ?>

    <div class="dashboard-layout">
        <?php include("../includes/student-sidebar.php"); ?>

        <main class="main-content">
            <div class="content-wrapper">
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

                <div class="page-header">
                    <div>
                        <h2 class="header-title"><i class="fa-solid fa-envelope"></i> Student Concerns</h2>
                        <p class="header-desc">Track status updates for concerns or inquiries submitted to the administration.</p>
                    </div>
                    <button type="button" class="btn-add" onclick="openConcernForm()">
                        <i class="fa-solid fa-plus"></i> Submit Concern
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="status-table">
                        <thead>
                            <tr>
                                <th>Year Level</th>
                                <th>Attachment File</th>
                                <th>Date Submitted</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($concerns) > 0): ?>
                                <?php foreach ($concerns as $row): ?>
                                    <?php 
                                        $status = $row['status'] ?? 'Pending';
                                        $badge_class = 'badge-pending';
                                        if (strtolower($status) === 'resolved') {
                                            $badge_class = 'badge-resolved';
                                        } elseif (strtolower($status) === 'rejected') {
                                            $badge_class = 'badge-rejected';
                                        }
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['year_level'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php if (!empty($row['file_path'])): ?>
                                                <a href="../uploads/concerns/<?= htmlspecialchars($row['file_path']); ?>" target="_blank" style="color: #2563eb; text-decoration: underline;">View File</a>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td><?= !empty($row['created_at']) ? date('M d, Y', strtotime($row['created_at'])) : 'N/A'; ?></td>
                                        <td><span class="badge <?= $badge_class; ?>"><?= htmlspecialchars($status); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: #64748b; padding: 25px;">No concern records found. Click "Submit Concern" to raise one.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div> 
</div>

<div class="modal-overlay" id="concernModal">
    <div class="personal-modal">
        <span class="close-btn" onclick="closeConcernForm()">&times;</span>
        <form class="personal-form" method="POST" action="save-concern.php" enctype="multipart/form-data">
            <h3 class="form-title">Submit a Concern</h3>
            
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

            <div class="form-group">
                <label class="form-label">Year Level</label>
                <select name="year_level" class="form-input" required>
                    <option value="" disabled selected>Select</option>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year">2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                    <option value="4th Year">4th Year</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Upload Concern (Document/Image)</label>
                <input type="file" name="concern_file" class="form-input form-file" required>
            </div>

            <div class="modal-buttons">
                <button type="button" class="cancel-btn" onclick="closeConcernForm()">Cancel</button>
                <button type="submit" class="save-btn">Submit Concern</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openConcernForm() {
        document.getElementById('concernModal').style.display = 'flex';
    }

    function closeConcernForm() {
        document.getElementById('concernModal').style.display = 'none';
    }

    window.onclick = function(event) {
        var modal = document.getElementById('concernModal');
        if (event.target == modal) {
            closeConcernForm();
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