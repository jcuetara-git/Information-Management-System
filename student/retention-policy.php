<?php
session_start();
include("../config/db.php");

// Ensure the student is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] != "student") {
    header("Location: ../auth/login.php");
    exit();
}

$student_no = $_SESSION['student_no'] ?? '';
$first_name = $_SESSION['firstname'] ?? $_SESSION['first_name'] ?? 'John';
$last_name = $_SESSION['lastname'] ?? $_SESSION['last_name'] ?? 'Doe';

// Fetch submissions for this specific student from the database
$submissions = [];
if (!empty($student_no)) {
    $stmt = $conn->prepare("SELECT * FROM retention_records WHERE student_no = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $student_no);
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
    <title>retention-policy</title>
    <!-- Include your global stylesheets -->
    <link rel="stylesheet" href="../assets/css/retention-policy.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
</head>
<body>

    <!-- WRAP THE ENTIRE APP IN THIS FLEX CONTAINER -->
    <div class="app-layout">
        
        <!-- Include sidebar -->
        <?php include("../includes/student-sidebar.php"); ?>

        <!-- MAIN PAGE CONTENT -->
        <div class="main-content">
            <div class="content-wrapper">
                
                <!-- Display Success/Error Alerts -->
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

                <!-- Header & Add Button -->
                <div class="page-header">
                    <div>
                        <h2 class="header-title"><i class="fa-solid fa-list-check"></i> Retention Submissions</h2>
                        <p class="header-desc">View the approval status of your submitted Letters of Undertaking.</p>
                    </div>
                    
                    <button type="button" class="btn-add" onclick="openRetentionModal()">
                        <i class="fa-solid fa-plus"></i> Add New LOU
                    </button>
                </div>

                <!-- Status Table -->
                <div class="table-responsive">
                    <table class="status-table">
                        <thead>
                            <tr>
                                <th>Date Submitted</th>
                                <th>Date Memo was Issued</th>
                                <th>Failed Subjects</th>
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
                                    ?>
                                    <tr>
                                        <td><?= date('M d, Y', strtotime($row['created_at'])); ?></td>
                                        <td><?= !empty($row['memo_issued_date']) ? date('M d, Y', strtotime($row['memo_issued_date'])) : 'N/A'; ?></td>
                                        <td><?= htmlspecialchars($row['failed_subjects_count']); ?></td>
                                        <td><span class="badge <?= $badge_class; ?>"><?= htmlspecialchars($status); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: #64748b; padding: 25px;">No retention submissions found. Click "Add New LOU" to submit one.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- END MAIN CONTENT -->

    </div> 
    <!-- END APP LAYOUT -->

    <!-- RETENTION LOU MODAL -->
    <div class="modal-overlay" id="retentionModal">
        <div class="personal-modal">
            <span class="close-btn" onclick="closeRetentionModal()">&times;</span>
            
            <form class="personal-form" method="POST" action="save-retention.php" enctype="multipart/form-data">
                <h3 class="form-title"><i class="fa-solid fa-calendar-days"></i> Submit Retention LOU</h3>
                
                <p class="form-desc">
                    Submit your Letter of Undertaking if you have accumulated three (3) or more failed professional subjects.
                </p>

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

    <!-- JAVASCRIPT FOR MODAL TOGGLE & ALERTS -->
    <script>
        function openRetentionModal() {
            document.getElementById('retentionModal').style.display = 'flex';
        }

        function closeRetentionModal() {
            document.getElementById('retentionModal').style.display = 'none';
        }

        window.onclick = function(event) {
            var modal = document.getElementById('retentionModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Auto-dismiss alert box after 4 seconds
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