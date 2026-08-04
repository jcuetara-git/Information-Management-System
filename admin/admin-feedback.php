<?php
session_start();
include("../config/db.php");

// Ensure user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Handle status updates or resolution actions if posted
if (isset($_POST['update_status'])) {
    $concern_id = $_POST['concern_id'];
    $new_status = $_POST['status'];
    
    $update_stmt = $conn->prepare("UPDATE student_concerns SET status = ? WHERE id = ?");
    $update_stmt->bind_param("si", $new_status, $concern_id);
    $update_stmt->execute();
    $update_stmt->close();
    
    header("Location: admin-feedback.php?success=Concern status updated successfully!");
    exit();
}

// Fetch all submitted student concerns
$query = "SELECT * FROM student_concerns ORDER BY created_at DESC";
$result = $conn->query($query);
$submissions = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Fetch Stats directly from student_concerns table
$stat_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Pending' OR status IS NULL OR status = '' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved
    FROM student_concerns";
$stat_result = $conn->query($stat_query);
$stats = $stat_result ? $stat_result->fetch_assoc() : ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'resolved' => 0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="Feedback & Concerns Management - College of Criminal Justice">
    <meta name="theme-color" content="#f4b42c">
    <title>feedback-concerns</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="../assets/css/manage-students.css">
    <link rel="stylesheet" href="../assets/css/admin-retention.css">
</head>
<body>

    <!-- Alerts integration for success/error messages -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success" id="alertBox">
            <i class="fa-solid fa-circle-check"></i>
            <?= htmlspecialchars($_GET['success']); ?>
            <button class="close-btn" onclick="document.getElementById('alertBox').style.display='none'">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger" id="alertBox">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= htmlspecialchars($_GET['error']); ?>
            <button class="close-btn" onclick="document.getElementById('alertBox').style.display='none'">&times;</button>
        </div>
    <?php endif; ?>

    <div class="main-container">

        <!-- Include the sidebar -->
        <?php include("../includes/sidebar.php"); ?>

        <!-- Main Content Wrapped exactly like manage-students.php and admin-retention.php -->
        <main class="dashboard-container" id="mainContent" role="main">
            
            <!-- Welcome / Header Card -->
            <section class="card welcome-card" aria-label="Welcome Section">
                <div class="welcome-content">
                    <h2>Feedback & Concerns Management</h2>
                    <p>Review and manage academic or personal concerns submitted by students.</p>
                </div>
            </section>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <p>Total</p>
                    <h3><?= $stats['total'] ?? 0; ?></h3>
                </div>
                <div class="stat-card">
                    <p>Pending</p>
                    <h3><?= $stats['pending'] ?? 0; ?></h3>
                </div>
                <div class="stat-card">
                    <p>In Progress</p>
                    <h3><?= $stats['in_progress'] ?? 0; ?></h3>
                </div>
                <div class="stat-card">
                    <p>Resolved</p>
                    <h3><?= $stats['resolved'] ?? 0; ?></h3>
                </div>
            </div>

            <!-- Table Container -->
            <section class="card table-container" aria-label="Submissions List">
                <div class="table-wrapper">
                    <?php if (count($submissions) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Date Submitted</th>
                                    <th>Student ID</th>
                                    <th>Student Name</th>
                                    <th>Year Level</th>
                                    <th>Attachment</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($submissions as $row): ?>
                                    <tr>
                                        <td data-label="Date Submitted"><?= date("M d, Y h:i A", strtotime($row['created_at'])); ?></td>
                                        <td data-label="Student ID"><?= htmlspecialchars($row['student_no']); ?></td>
                                        <td data-label="Student Name"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                        <td data-label="Year Level"><?= htmlspecialchars($row['year_level']); ?></td>
                                        <td data-label="Attachment">
                                            <?php if (!empty($row['file_path'])): ?>
                                                <a href="../uploads/concerns/<?= htmlspecialchars($row['file_path']); ?>" target="_blank" class="document-link" title="View Uploaded Document">
                                                    <i class="fa-solid fa-file-arrow-down"></i> View File
                                                </a>
                                            <?php else: ?>
                                                <span class="no-file" style="color: #adb5bd; font-style: italic;">No file</span>
                                            <?php endif; ?>
                                        </td>
                                        <td data-label="Status">
                                            <?= htmlspecialchars($row['status'] ?? 'Pending'); ?>
                                        </td>
                                        <td data-label="Action" class="action-btns">
                                            <button class="review-btn-table" onclick='openReviewModal(<?= json_encode($row); ?>)' title="Update Status">
                                                <i class="fa-solid fa-file-signature"></i> Review
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-results">
                            <div class="no-results-icon"><i class="fa-solid fa-folder-open"></i></div>
                            <p>No student concerns submitted yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

        </main>
    </div>

    <!-- Modal Code (Review Modal) -->
    <div id="reviewModal" class="modal-overlay">
        <div class="modal-content-box">
            <h3 class="modal-title"><i class="fa-solid fa-edit"></i> Review Concern Status</h3>
            <form action="admin-feedback.php" method="POST">
                <input type="hidden" name="concern_id" id="modalConcernId">

                <div class="modal-form-group">
                    <label class="modal-label">Update Status</label>
                    <select name="status" class="modal-select" id="modalStatusSelect">
                        <option value="Pending">Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Resolved">Resolved</option>
                    </select>
                </div>

                <div class="modal-actions">
                    <button type="button" class="modal-btn-cancel" onclick="document.getElementById('reviewModal').style.display='none'">Cancel</button>
                    <button type="submit" name="update_status" value="1" class="modal-btn-save">Save Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openReviewModal(data) {
            document.getElementById('modalConcernId').value = data.id;
            const statusSelect = document.getElementById('modalStatusSelect');
            if (data.status) {
                statusSelect.value = data.status;
            } else {
                statusSelect.value = 'Pending';
            }
            document.getElementById('reviewModal').style.display = 'flex';
        }

        // Automatically hide the alert box after 4 seconds
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