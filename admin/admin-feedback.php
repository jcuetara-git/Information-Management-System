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
    $source_table = $_POST['source_table'] ?? 'student_concerns';
    $role_tab = $_POST['active_tab'] ?? 'All';
    $current_page = $_POST['current_page'] ?? 1;
    $current_limit = $_POST['current_limit'] ?? 5;
    
    // Ensure we update the correct source table
    $allowed_tables = ['student_concerns', 'faculty_concerns', 'alumni_concerns'];
    if (in_array($source_table, $allowed_tables)) {
        $update_stmt = $conn->prepare("UPDATE {$source_table} SET status = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_status, $concern_id);
        $update_stmt->execute();
        $update_stmt->close();
    }
    
    header("Location: admin-feedback.php?tab=" . urlencode($role_tab) . "&page=" . $current_page . "&limit=" . $current_limit . "&success=Concern status updated successfully!");
    exit();
}

// Determine active role tab from GET parameter
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'All';

// Pagination variables
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
if (!in_array($limit, [5, 20, 50])) {
    $limit = 5; // Fallback default if tampered
}
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) { $page = 1; }
$offset = ($page - 1) * $limit;

// 1. Get total records count for pagination based on the active tab
if ($active_tab === 'Students') {
    $countQuery = "SELECT COUNT(*) as total FROM student_concerns WHERE role LIKE 'Student%'";
} elseif ($active_tab === 'Faculty') {
    $countQuery = "SELECT COUNT(*) as total FROM faculty_concerns";
} elseif ($active_tab === 'Alumni') {
    $countQuery = "SELECT COUNT(*) as total FROM alumni_concerns";
} else {
    $countQuery = "SELECT (SELECT COUNT(*) FROM student_concerns) + (SELECT COUNT(*) FROM faculty_concerns) + (SELECT COUNT(*) FROM alumni_concerns) as total";
}

$countResult = $conn->query($countQuery);
$totalRows = $countResult ? $countResult->fetch_assoc()['total'] : 0;
$totalPages = ceil($totalRows / $limit);

// 2. Build paginated queries combining student_concerns, faculty_concerns, and alumni_concerns tables
if ($active_tab === 'Students') {
    $query = "SELECT id, created_at, student_no AS id_number, first_name, last_name, 'Student' AS role, year_level, file_path, status, 'student_concerns' AS source_table FROM student_concerns WHERE role LIKE 'Student%' ORDER BY created_at DESC LIMIT ? OFFSET ?";
} elseif ($active_tab === 'Faculty') {
    $query = "SELECT id, created_at, faculty_no AS id_number, first_name, last_name, 'Faculty' AS role, NULL AS year_level, file_path, status, 'faculty_concerns' AS source_table FROM faculty_concerns ORDER BY created_at DESC LIMIT ? OFFSET ?";
} elseif ($active_tab === 'Alumni') {
    $query = "SELECT id, created_at, alumni_no AS id_number, first_name, last_name, 'Alumni' AS role, NULL AS year_level, file_path, status, 'alumni_concerns' AS source_table FROM alumni_concerns ORDER BY created_at DESC LIMIT ? OFFSET ?";
} else {
    // 'All' tab pulls from all three tables using UNION ALL wrapped in a wrapper for global sorting & pagination
    $query = "SELECT * FROM (
                (SELECT id, created_at, student_no AS id_number, first_name, last_name, role, year_level, file_path, status, 'student_concerns' AS source_table FROM student_concerns)
                UNION ALL
                (SELECT id, created_at, faculty_no AS id_number, first_name, last_name, 'Faculty' AS role, NULL AS year_level, file_path, status, 'faculty_concerns' AS source_table FROM faculty_concerns)
                UNION ALL
                (SELECT id, created_at, alumni_no AS id_number, first_name, last_name, 'Alumni' AS role, NULL AS year_level, file_path, status, 'alumni_concerns' AS source_table FROM alumni_concerns)
              ) AS combined_concerns 
              ORDER BY created_at DESC LIMIT ? OFFSET ?";
}

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
if (!$result) {
    die("Database Query Error: " . $conn->error);
}
$submissions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch overall stats counting across student, faculty, and alumni tables
$stat_query = "SELECT 
    (SELECT COUNT(*) FROM student_concerns) + (SELECT COUNT(*) FROM faculty_concerns) + (SELECT COUNT(*) FROM alumni_concerns) as total,
    
    (SELECT SUM(CASE WHEN status = 'Pending' OR status IS NULL OR status = '' THEN 1 ELSE 0 END) FROM student_concerns) + 
    (SELECT SUM(CASE WHEN status = 'Pending' OR status IS NULL OR status = '' THEN 1 ELSE 0 END) FROM faculty_concerns) +
    (SELECT SUM(CASE WHEN status = 'Pending' OR status IS NULL OR status = '' THEN 1 ELSE 0 END) FROM alumni_concerns) as pending,
    
    (SELECT SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) FROM student_concerns) + 
    (SELECT SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) FROM faculty_concerns) +
    (SELECT SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) FROM alumni_concerns) as in_progress,
    
    (SELECT SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) FROM student_concerns) + 
    (SELECT SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) FROM faculty_concerns) +
    (SELECT SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) FROM alumni_concerns) as resolved";

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
    <link rel="stylesheet" href="../assets/css/admin-feedback.css">
    <link rel="stylesheet" href="../assets/css/admin-announcement.css">
</head>
<body>

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
        <?php include("../includes/sidebar.php"); ?>

        <main class="dashboard-container" id="mainContent" role="main">
            
            <section class="card welcome-card" aria-label="Welcome Section">
                <div class="welcome-content">
                    <h2>Feedback & Concerns Management</h2>
                    <p>Review and manage academic or personal concerns submitted by students, faculty, and alumni.</p>
                </div>
            </section>

            <div class="stats-grid">
                <div class="stat-card">
                    <p>TOTAL</p>
                    <h3><?= $stats['total'] ?? 0; ?></h3>
                </div>
                <div class="stat-card">
                    <p>PENDING</p>
                    <h3><?= $stats['pending'] ?? 0; ?></h3>
                </div>
                <div class="stat-card">
                    <p>IN PROGRESS</p>
                    <h3><?= $stats['in_progress'] ?? 0; ?></h3>
                </div>
                <div class="stat-card">
                    <p>RESOLVED</p>
                    <h3><?= $stats['resolved'] ?? 0; ?></h3>
                </div>
            </div>

            <div class="concerns-tabs-container">
                <a href="admin-feedback.php?tab=All&limit=<?= $limit ?>" class="concern-tab <?= $active_tab === 'All' ? 'active' : ''; ?>">All</a>
                <a href="admin-feedback.php?tab=Students&limit=<?= $limit ?>" class="concern-tab <?= $active_tab === 'Students' ? 'active' : ''; ?>">Students</a>
                <a href="admin-feedback.php?tab=Faculty&limit=<?= $limit ?>" class="concern-tab <?= $active_tab === 'Faculty' ? 'active' : ''; ?>">Faculty</a>
                <a href="admin-feedback.php?tab=Alumni&limit=<?= $limit ?>" class="concern-tab <?= $active_tab === 'Alumni' ? 'active' : ''; ?>">Alumni</a>
            </div>

            <section class="card table-container" aria-label="Submissions List" style="padding: 0; overflow: hidden;">
                <div class="table-wrapper">
                    <?php if (count($submissions) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Date Submitted</th>
                                    <th>ID Number</th>
                                    <th>Full Name</th>
                                    <th>Role / Details</th>
                                    <th>Attachment</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($submissions as $row): ?>
                                    <?php 
                                        // Dynamically route file paths based on source table folder structure
                                        $file_folder = 'concerns';
                                        if (($row['source_table'] ?? '') === 'faculty_concerns') {
                                            $file_folder = 'faculty_concerns';
                                        } elseif (($row['source_table'] ?? '') === 'alumni_concerns') {
                                            $file_folder = 'alumni_concerns';
                                        }
                                    ?>
                                    <tr>
                                        <td data-label="Date Submitted"><?= !empty($row['created_at']) ? date("M d, Y h:i A", strtotime($row['created_at'])) : 'N/A'; ?></td>
                                        <td data-label="ID Number"><?= htmlspecialchars($row['id_number'] ?? 'N/A'); ?></td>
                                        <td data-label="Full Name"><?= htmlspecialchars(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: 'N/A'); ?></td>
                                        <td data-label="Role / Details">
                                            <span class="role-badge <?= strtolower($row['role'] ?? 'student'); ?>"><?= htmlspecialchars($row['role'] ?? 'Student'); ?></span>
                                            <?php if (!empty($row['year_level'])): ?>
                                                <br><small><?= htmlspecialchars($row['year_level']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td data-label="Attachment">
                                            <?php if (!empty($row['file_path'])): ?>
                                                <a href="../uploads/<?= $file_folder; ?>/<?= htmlspecialchars($row['file_path']); ?>" target="_blank" class="document-link" title="View Uploaded Document">
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

                        <!-- Pagination Control Bar -->
                        <div class="pagination-container">
                            <div class="rows-per-page">
                                <label for="limitSelect">Show:</label>
                                <select id="limitSelect" onchange="changeRowsPerPage(this.value)">
                                    <option value="5" <?= $limit == 5 ? 'selected' : '' ?>>5</option>
                                    <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>20</option>
                                    <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                                </select>
                                <span>entries (Total: <?= $totalRows ?>)</span>
                            </div>

                            <div class="pagination-links">
                                <?php if ($page > 1): ?>
                                    <a href="?tab=<?= urlencode($active_tab) ?>&page=1&limit=<?= $limit ?>"><i class="fa-solid fa-angle-double-left"></i></a>
                                    <a href="?tab=<?= urlencode($active_tab) ?>&page=<?= $page - 1 ?>&limit=<?= $limit ?>"><i class="fa-solid fa-angle-left"></i></a>
                                <?php endif; ?>

                                <span class="current">Page <?= $page ?> of <?= max(1, $totalPages) ?></span>

                                <?php if ($page < $totalPages): ?>
                                    <a href="?tab=<?= urlencode($active_tab) ?>&page=<?= $page + 1 ?>&limit=<?= $limit ?>"><i class="fa-solid fa-angle-right"></i></a>
                                    <a href="?tab=<?= urlencode($active_tab) ?>&page=<?= $totalPages ?>&limit=<?= $limit ?>"><i class="fa-solid fa-angle-double-right"></i></a>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="no-results" style="padding: 40px; text-align: center;">
                            <div class="no-results-icon"><i class="fa-solid fa-folder-open"></i></div>
                            <p>No concerns found under <?= htmlspecialchars($active_tab); ?>.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

        </main>
    </div>

    <div id="reviewModal" class="modal-overlay">
        <div class="modal-content-box">
            <h3 class="modal-title"><i class="fa-solid fa-edit"></i> Review Concern Status</h3>
            <form action="admin-feedback.php" method="POST">
                <input type="hidden" name="concern_id" id="modalConcernId">
                <input type="hidden" name="source_table" id="modalSourceTable">
                <input type="hidden" name="active_tab" value="<?= htmlspecialchars($active_tab); ?>">
                <input type="hidden" name="current_page" value="<?= $page; ?>">
                <input type="hidden" name="current_limit" value="<?= $limit; ?>">

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
        function changeRowsPerPage(val) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('limit', val);
            urlParams.set('page', 1); // Reset to page 1 on limit change
            window.location.search = urlParams.toString();
        }

        function openReviewModal(data) {
            document.getElementById('modalConcernId').value = data.id;
            document.getElementById('modalSourceTable').value = data.source_table || 'student_concerns';
            const statusSelect = document.getElementById('modalStatusSelect');
            if (data.status) {
                statusSelect.value = data.status;
            } else {
                statusSelect.value = 'Pending';
            }
            document.getElementById('reviewModal').style.display = 'flex';
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