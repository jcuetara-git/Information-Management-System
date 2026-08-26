<?php
session_start();
include("../config/db.php");

// Ensure user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// 1. Handle Status Updates
if (isset($_POST['update_status'])) {
    $batch_id     = intval($_POST['batch_id'] ?? 0);
    $new_status   = trim($_POST['status'] ?? 'Pending');
    $role_tab     = $_POST['active_tab'] ?? 'All';
    $current_page = intval($_POST['current_page'] ?? 1);
    $current_limit= intval($_POST['current_limit'] ?? 5);

    if ($batch_id > 0) {
        $update_stmt = $conn->prepare("UPDATE internship_batches SET status = ? WHERE id = ?");
        if ($update_stmt) {
            $update_stmt->bind_param("si", $new_status, $batch_id);
            $update_stmt->execute();
            $update_stmt->close();
            
            header("Location: admin-internship.php?tab=" . urlencode($role_tab) . "&page=" . $current_page . "&limit=" . $current_limit . "&success=" . urlencode("Batch report status updated successfully!"));
            exit();
        }
    }
    
    header("Location: admin-internship.php?tab=" . urlencode($role_tab) . "&error=" . urlencode("Failed to update batch status."));
    exit();
}

// 2. Handle File Upload & Batch Creation
if (isset($_POST['upload_batch'])) {
    $batch_name    = trim($_POST['batch_name'] ?? '');
    $academic_yr   = trim($_POST['academic_year'] ?? '');
    $semester      = trim($_POST['semester'] ?? '');
    $report_title  = trim($_POST['report_title'] ?? '');
    $total_interns = intval($_POST['total_interns'] ?? 0);
    $role_tab      = $_POST['active_tab'] ?? 'All';

    if (!empty($batch_name) && !empty($academic_yr) && !empty($semester) && !empty($report_title) && isset($_FILES['report_file']) && $_FILES['report_file']['error'] === UPLOAD_ERR_OK) {
        $file_name  = $_FILES['report_file']['name'];
        $file_tmp   = $_FILES['report_file']['tmp_name'];
        $upload_dir = '../uploads/internship_reports/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $extension   = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_filename= time() . '_' . uniqid() . '.' . $extension;
        $target_file = $upload_dir . $new_filename;

        if (move_uploaded_file($file_tmp, $target_file)) {
            $insert_stmt = $conn->prepare("INSERT INTO internship_batches (batch_name, academic_year, semester, report_title, total_interns, file_path, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'Pending', NOW())");
            if ($insert_stmt) {
                $insert_stmt->bind_param("ssssis", $batch_name, $academic_yr, $semester, $report_title, $total_interns, $new_filename);
                $insert_stmt->execute();
                $insert_stmt->close();
                
                header("Location: admin-internship.php?tab=" . urlencode($role_tab) . "&success=" . urlencode("Accomplishment report batch uploaded successfully!"));
                exit();
            }
        }
        
        header("Location: admin-internship.php?tab=" . urlencode($role_tab) . "&error=" . urlencode("Failed to move uploaded file to destination server."));
        exit();
    } else {
        header("Location: admin-internship.php?tab=" . urlencode($role_tab) . "&error=" . urlencode("Please complete all required fields and upload a valid report file."));
        exit();
    }
}

// 3. Determine Active Tab Filter
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'All';

// 4. Set Pagination Limits
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
if (!in_array($limit, [5, 20, 50])) {
    $limit = 5;
}
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) { $page = 1; }
$offset = ($page - 1) * $limit;

// 5. Dynamic Query Generation based on Active Tab
$whereClause = "";
$paramType   = "";
$paramValue  = "";

if ($active_tab === '1st Semester') {
    $whereClause = "WHERE semester = ?";
    $paramType   = "s";
    $paramValue  = "1st Semester";
} elseif ($active_tab === '2nd Semester') {
    $whereClause = "WHERE semester = ?";
    $paramType   = "s";
    $paramValue  = "2nd Semester";
} elseif ($active_tab === 'Summer') {
    $whereClause = "WHERE semester LIKE ?";
    $paramType   = "s";
    $paramValue  = "Summer%";
}

// Fetch Total Rows Count for Active Tab
$countQuery = "SELECT COUNT(*) as total FROM internship_batches {$whereClause}";
if (!empty($whereClause)) {
    $countStmt = $conn->prepare($countQuery);
    $countStmt->bind_param($paramType, $paramValue);
    $countStmt->execute();
    $totalRows = $countStmt->get_result()->fetch_assoc()['total'];
    $countStmt->close();
} else {
    $countResult = $conn->query($countQuery);
    $totalRows = $countResult ? $countResult->fetch_assoc()['total'] : 0;
}

$totalPages = ceil($totalRows / $limit);

// Fetch Data Rows for Active Tab with Pagination
$dataQuery = "SELECT * FROM internship_batches {$whereClause} ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($dataQuery);

if (!empty($whereClause)) {
    $stmt->bind_param($paramType . "ii", $paramValue, $limit, $offset);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();
$submissions = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

// 6. Dynamic Overall Database Stats Query
$stat_query = "SELECT 
    COUNT(*) as total,
    COALESCE(SUM(total_interns), 0) as total_interns,
    SUM(CASE WHEN status = 'Pending' OR status IS NULL OR status = '' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved
    FROM internship_batches";

$stat_result = $conn->query($stat_query);
$stats = $stat_result ? $stat_result->fetch_assoc() : ['total' => 0, 'total_interns' => 0, 'pending' => 0, 'approved' => 0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="Internship Management - College of Criminal Justice">
    <meta name="theme-color" content="#f4b42c">
    <title>internship-management</title>
    
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
                    <h2>Internship Management</h2>
                    <p>Upload and manage batch accomplishment reports and student deployment records.</p>
                </div>
            </section>

            <div class="stats-grid">
                <div class="stat-card">
                    <p>TOTAL BATCHES</p>
                    <h3><?= intval($stats['total'] ?? 0); ?></h3>
                </div>
                <div class="stat-card">
                    <p>TOTAL INTERNS</p>
                    <h3><?= intval($stats['total_interns'] ?? 0); ?></h3>
                </div>
                <div class="stat-card">
                    <p>PENDING REVIEWS</p>
                    <h3><?= intval($stats['pending'] ?? 0); ?></h3>
                </div>
                <div class="stat-card">
                    <p>APPROVED REPORTS</p>
                    <h3><?= intval($stats['approved'] ?? 0); ?></h3>
                </div>
            </div>

            <div class="concerns-tabs-container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div class="tabs-group">
                    <a href="admin-internship.php?tab=All&limit=<?= $limit ?>" class="concern-tab <?= $active_tab === 'All' ? 'active' : ''; ?>">All</a>
                    <a href="admin-internship.php?tab=1st+Semester&limit=<?= $limit ?>" class="concern-tab <?= $active_tab === '1st Semester' ? 'active' : ''; ?>">1st Semester</a>
                    <a href="admin-internship.php?tab=2nd+Semester&limit=<?= $limit ?>" class="concern-tab <?= $active_tab === '2nd Semester' ? 'active' : ''; ?>">2nd Semester</a>
                    <a href="admin-internship.php?tab=Summer&limit=<?= $limit ?>" class="concern-tab <?= $active_tab === 'Summer' ? 'active' : ''; ?>">Summer</a>
                </div>
                <button class="modal-btn-save" onclick="openUploadModal()" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; text-decoration: none; cursor: pointer;">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Upload Batch Report
                </button>
            </div>

            <section class="card table-container" aria-label="Internship Batches List" style="padding: 0; overflow: hidden;">
                <div class="table-wrapper">
                    <?php if (count($submissions) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Batch Name</th>
                                    <th>A.Y. & Semester</th>
                                    <th>Report Title</th>
                                    <th>Interns Count</th>
                                    <th>Date Uploaded</th>
                                    <th>Attachment</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($submissions as $row): ?>
                                    <tr>
                                        <td data-label="Batch Name"><strong><?= htmlspecialchars($row['batch_name'] ?? 'N/A'); ?></strong></td>
                                        <td data-label="A.Y. & Semester"><?= htmlspecialchars(($row['academic_year'] ?? '') . ' (' . ($row['semester'] ?? '') . ')'); ?></td>
                                        <td data-label="Report Title"><?= htmlspecialchars($row['report_title'] ?? 'N/A'); ?></td>
                                        <td data-label="Interns Count"><?= htmlspecialchars($row['total_interns'] ?? 0); ?> Students</td>
                                        <td data-label="Date Uploaded"><?= !empty($row['created_at']) ? date("M d, Y h:i A", strtotime($row['created_at'])) : 'N/A'; ?></td>
                                        <td data-label="Attachment">
                                            <?php if (!empty($row['file_path'])): ?>
                                                <a href="../uploads/internship_reports/<?= htmlspecialchars($row['file_path']); ?>" target="_blank" class="document-link" title="View Document">
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
                                            <button class="review-btn-table" onclick='openReviewModal(<?= json_encode($row); ?>)' title="Review Status">
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
                            <p>No internship reports found under <?= htmlspecialchars($active_tab); ?>.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

        </main>
    </div>

    <!-- Upload Batch Accomplishment Report Modal -->
    <div id="uploadModal" class="modal-overlay">
        <div class="modal-content-box" style="max-width: 550px;">
            <h3 class="modal-title"><i class="fa-solid fa-cloud-arrow-up"></i> Upload Accomplishment Report</h3>
            <form action="admin-internship.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="upload_batch" value="1">
                <input type="hidden" name="active_tab" value="<?= htmlspecialchars($active_tab); ?>">

                <div class="modal-form-group">
                    <label class="modal-label">Batch Section / Name <span style="color:red;">*</span></label>
                    <input type="text" name="batch_name" class="modal-select" placeholder="e.g. Batch 2025-2026 Alpha" required style="width: 100%; box-sizing: border-box;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="modal-form-group">
                        <label class="modal-label">Academic Year <span style="color:red;">*</span></label>
                        <select name="academic_year" class="modal-select" required>
                            <option value="2025-2026">2025-2026</option>
                            <option value="2026-2027">2026-2027</option>
                        </select>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label">Semester <span style="color:red;">*</span></label>
                        <select name="semester" class="modal-select" required>
                            <option value="1st Semester">1st Semester</option>
                            <option value="2nd Semester">2nd Semester</option>
                            <option value="Summer">Summer</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 12px;">
                    <div class="modal-form-group">
                        <label class="modal-label">Report Document Title <span style="color:red;">*</span></label>
                        <input type="text" name="report_title" class="modal-select" placeholder="e.g. Midterm Report" required style="width: 100%; box-sizing: border-box;">
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label">Total Interns <span style="color:red;">*</span></label>
                        <input type="number" name="total_interns" class="modal-select" placeholder="e.g. 40" required style="width: 100%; box-sizing: border-box;">
                    </div>
                </div>

                <div class="modal-form-group">
                    <label class="modal-label">Report File (PDF / ZIP) <span style="color:red;">*</span></label>
                    <input type="file" name="report_file" class="modal-select" accept=".pdf,.zip,.rar" required style="width: 100%; box-sizing: border-box;">
                </div>

                <div class="modal-actions">
                    <button type="button" class="modal-btn-cancel" onclick="document.getElementById('uploadModal').style.display='none'">Cancel</button>
                    <button type="submit" class="modal-btn-save">Submit Upload</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Review Status Modal -->
    <div id="reviewModal" class="modal-overlay">
        <div class="modal-content-box">
            <h3 class="modal-title"><i class="fa-solid fa-edit"></i> Review Report Status</h3>
            <form action="admin-internship.php" method="POST">
                <input type="hidden" name="batch_id" id="modalBatchId">
                <input type="hidden" name="active_tab" value="<?= htmlspecialchars($active_tab); ?>">
                <input type="hidden" name="current_page" value="<?= $page; ?>">
                <input type="hidden" name="current_limit" value="<?= $limit; ?>">

                <div class="modal-form-group">
                    <label class="modal-label">Update Status</label>
                    <select name="status" class="modal-select" id="modalStatusSelect">
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
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
            urlParams.set('page', 1);
            window.location.search = urlParams.toString();
        }

        function openUploadModal() {
            document.getElementById('uploadModal').style.display = 'flex';
        }

        function openReviewModal(data) {
            document.getElementById('modalBatchId').value = data.id;
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