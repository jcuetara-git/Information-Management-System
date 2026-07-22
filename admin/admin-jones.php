<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch all Submissions directly since there is no program_type column
$query = "SELECT * FROM indiana_jones_records ORDER BY date_recorded DESC";
$result = $conn->query($query);
$submissions = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Fetch Stats directly from the table
$stat_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected
    FROM program_submissions";
$stat_result = $conn->query($stat_query);
$stats = $stat_result ? $stat_result->fetch_assoc() : ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="Indiana Jones Submissions Management - College of Criminal Justice">
    <meta name="theme-color" content="#f4b42c">
    <title>Indiana Jones Submissions</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="../assets/css/manage-students.css">
    <link rel="stylesheet" href="../assets/css/admin-jones.css">
</head>

<body>
    <!-- Alerts integration for success/error messages upon saving a review -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success" id="alertBox">
            <i class="fa-solid fa-circle-check"></i>
            <?= htmlspecialchars($_GET['success']) ?>
            <button class="close-btn" onclick="document.getElementById('alertBox').style.display='none'">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger" id="alertBox">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= htmlspecialchars($_GET['error']) ?>
            <button class="close-btn" onclick="document.getElementById('alertBox').style.display='none'">&times;</button>
        </div>
    <?php endif; ?>

    <div class="main-container">

        <?php include("../includes/sidebar.php"); ?>

        <!-- Main Content Wrapped exactly like manage-students.php -->
        <main class="dashboard-container" id="mainContent" role="main">

            <!-- Welcome / Header Card -->
            <section class="card welcome-card" aria-label="Welcome Section">
                <div class="welcome-content">
                    <h2>Indiana Jones Submissions</h2>
                    <p>Review Letters of Undertaking for students with 3 or more consecutive absences.</p>
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
                    <p>Approved</p>
                    <h3><?= $stats['approved'] ?? 0; ?></h3>
                </div>
                <div class="stat-card">
                    <p>Rejected</p>
                    <h3><?= $stats['rejected'] ?? 0; ?></h3>
                </div>
            </div>

            <!-- Table Container -->
            <section class="card table-container" aria-label="Submissions List">
                <div class="table-wrapper">
                    <?php if (count($submissions) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Year Level</th>
                                    <th>Absences</th>
                                    <th>Date Recorded</th>
                                    <th>Document</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($submissions as $row): ?>
                                    <tr>
                                        <td data-label="Student ID"><?= htmlspecialchars($row['student_no']); ?></td>
                                        <td data-label="Name">
                                            <?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?>
                                        </td>
                                        <td data-label="Year Level"><?= htmlspecialchars($row['year_level']); ?></td>
                                        <td data-label="Absences"><?= htmlspecialchars($row['number_of_absences']); ?></td>
                                        <td data-label="Date Recorded">
                                            <?= htmlspecialchars($row['date_recorded']); ?>
                                        </td>
                                        <td data-label="Document">
                                            <a href="../uploads/jones/<?= htmlspecialchars($row['undertaking_file_path']); ?>" target="_blank"
                                                class="document-link">
                                                <i class="fa-solid fa-file-pdf"></i> View PDF
                                            </a>
                                        </td>
                                        <td data-label="Status">
                                            <?= htmlspecialchars($row['status']); ?>
                                        </td>
                                        <td data-label="Action" class="action-btns">
                                            <button class="review-btn-table"
                                                onclick='openReviewModal(<?= json_encode($row); ?>)' title="Review Submission">
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
                            <p>No Indiana Jones submissions found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

        </main>
    </div>

    <!-- Modal Code (Review Modal) -->
    <div id="reviewModal" class="modal-overlay">
        <div class="modal-content-box">
            <h3 class="modal-title"><i class="fa-solid fa-edit"></i> Review Submission</h3>
            <form method="POST" action="update-program-status.php">
                <input type="hidden" name="submission_id" id="modalSubmissionId">
                <!-- Redirects back to this exact page after update -->
                <input type="hidden" name="return_url" value="admin-jones.php">

                <div class="modal-form-group">
                    <label class="modal-label">Update Status</label>
                    <select name="status" class="modal-select">
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>

                <div class="modal-actions">
                    <button type="button" class="modal-btn-cancel"
                        onclick="document.getElementById('reviewModal').style.display='none'">Cancel</button>
                    <button type="submit" class="modal-btn-save">Save Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openReviewModal(data) {
            document.getElementById('modalSubmissionId').value = data.id;
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