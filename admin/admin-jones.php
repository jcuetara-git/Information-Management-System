<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: ../auth/login.php");
    exit();
}

// Pagination variables
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
if (!in_array($limit, [5, 20, 50])) {
    $limit = 5; // Fallback default if tampered
}
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) { $page = 1; }
$offset = ($page - 1) * $limit;

// Fetch total records count for pagination
$countQuery = "SELECT COUNT(*) as total FROM indiana_jones_records";
$countResult = $conn->query($countQuery);
$totalRows = $countResult ? $countResult->fetch_assoc()['total'] : 0;
$totalPages = ceil($totalRows / $limit);

// Fetch paginated records
$query = "SELECT * FROM indiana_jones_records ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
$submissions = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

// Fetch Stats directly from indiana_jones_records table
$stat_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected
    FROM indiana_jones_records";
$stat_result = $conn->query($stat_query);
$stats = $stat_result ? $stat_result->fetch_assoc() : ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="Indiana Jones Records Management">
    <meta name="theme-color" content="#f4b42c">
    <title>indiana-jones</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="../assets/css/manage-students.css">
    <link rel="stylesheet" href="../assets/css/admin-jones.css">
    <link rel="stylesheet" href="../assets/css/admin-announcement.css">
</head>

<body>
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

        <main class="dashboard-container" id="mainContent" role="main">
            <section class="card welcome-card" aria-label="Welcome Section">
                <div class="welcome-content">
                    <h2>Indiana Jones Submissions</h2>
                    <p>Review records and undertakings for Indiana Jones module.</p>
                </div>
            </section>

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

            <section class="card table-container" aria-label="Submissions List" style="padding: 0; overflow: hidden;">
                <div class="table-wrapper">
                    <?php if (count($submissions) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Name</th>
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
                                            <?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></td>
                                        <td data-label="Date Recorded"><?= date('M d, Y', strtotime($row['date_recorded'])); ?>
                                        </td>
                                        <td data-label="Document">
                                            <a href="../uploads/lou/<?= htmlspecialchars($row['undertaking_file_path']); ?>" target="_blank"
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
                                    <a href="?page=1&limit=<?= $limit ?>"><i class="fa-solid fa-angle-double-left"></i></a>
                                    <a href="?page=<?= $page - 1 ?>&limit=<?= $limit ?>"><i class="fa-solid fa-angle-left"></i></a>
                                <?php endif; ?>

                                <span class="current">Page <?= $page ?> of <?= max(1, $totalPages) ?></span>

                                <?php if ($page < $totalPages): ?>
                                    <a href="?page=<?= $page + 1 ?>&limit=<?= $limit ?>"><i class="fa-solid fa-angle-right"></i></a>
                                    <a href="?page=<?= $totalPages ?>&limit=<?= $limit ?>"><i class="fa-solid fa-angle-double-right"></i></a>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="no-results" style="padding: 40px; text-align: center;">
                            <div class="no-results-icon"><i class="fa-solid fa-folder-open"></i></div>
                            <p>No submissions found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

    <!-- Modal Code -->
    <div id="reviewModal" class="modal-overlay">
        <div class="modal-content-box">
            <h3 class="modal-title"><i class="fa-solid fa-edit"></i> Review Submission</h3>
            <form method="POST" action="update-indiana-status.php">
                <input type="hidden" name="submission_id" id="modalSubmissionId">
                <input type="hidden" name="return_url" value="admin-jones.php?page=<?= $page ?>&limit=<?= $limit ?>">
                <input type="hidden" name="table_name" value="indiana_jones_records">

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
        function changeRowsPerPage(val) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('limit', val);
            urlParams.set('page', 1); // Reset to page 1 on limit change
            window.location.search = urlParams.toString();
        }

        function openReviewModal(data) {
            document.getElementById('modalSubmissionId').value = data.id;
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