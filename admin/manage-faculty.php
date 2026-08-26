<?php
include("../config/db.php");
include("../config/auth.php");

if($_SESSION['role'] != "admin"){
    header("Location: ../auth/login.php");
    exit();
}

// Handle Delete Row Data (Updated to use student_no for the users table target)
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE student_no = ? AND role = 'faculty'");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    
    $stmt2 = $conn->prepare("DELETE FROM faculty_profile WHERE faculty_no = ?");
    $stmt2->bind_param("s", $id);
    $stmt2->execute();
    
    header("Location: manage-faculty.php?message=Faculty Record Deleted");
    exit();
}

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

// Pagination variables
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;
if (!in_array($limit, [5, 20, 50])) {
    $limit = 5; // Default fallback
}
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) { 
    $page = 1; 
}
$offset = ($page - 1) * $limit;

// 1. Build Count Query for Pagination
$countQuery = "SELECT COUNT(*) as total
               FROM users u 
               LEFT JOIN faculty_profile p ON u.student_no = p.faculty_no 
               WHERE u.role = 'faculty'";

$countParams = [];
$countTypes = '';

if(!empty($search)){
    $countQuery .= " AND (u.student_no LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
    $search_param = '%' . $search . '%';
    array_push($countParams, $search_param, $search_param, $search_param, $search_param);
    $countTypes .= 'ssss';
}

if(!empty($status_filter)){
    $countQuery .= " AND p.status = ?";
    $countParams[] = $status_filter;
    $countTypes .= 's';
}

$stmtCount = $conn->prepare($countQuery);
if (!$stmtCount) {
    die("SQL Error: " . $conn->error);
}
if(!empty($countParams)){
    $stmtCount->bind_param($countTypes, ...$countParams);
}
$stmtCount->execute();
$countResult = $stmtCount->get_result();
$totalRows = $countResult ? $countResult->fetch_assoc()['total'] : 0;
$stmtCount->close();

$totalPages = ceil($totalRows / $limit);

// 2. Build Main Data Query with LIMIT and OFFSET
$query = "SELECT u.student_no AS faculty_no, u.first_name, u.last_name, u.email, 
                 p.contact_no, p.status
          FROM users u 
          LEFT JOIN faculty_profile p ON u.student_no = p.faculty_no 
          WHERE u.role = 'faculty'";

$params = [];
$types = '';

if(!empty($search)){
    $query .= " AND (u.student_no LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
    $search_param = '%' . $search . '%';
    array_push($params, $search_param, $search_param, $search_param, $search_param);
    $types .= 'ssss';
}

if(!empty($status_filter)){
    $query .= " AND p.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$query .= " ORDER BY u.first_name ASC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);

if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

if(!empty($params)){
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="Manage Faculty Records - College of Criminal Justice">
    <meta name="theme-color" content="#f4b42c">
    <title>manage-faculty</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="../assets/css/manage-students.css"> 
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
                <h2>Manage Faculty Records</h2>
                <p>Filter, view, edit, and manage faculty profile.</p>
            </div>
        </section>

        <?php if(isset($_GET['message'])): ?>
            <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px;">
                <i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($_GET['message']) ?>
            </div>
        <?php endif; ?>

        <section class="card filters-container" aria-label="Search and Filter">
            <div class="filters-header">
                <h3><i class="fa-solid fa-filter"></i> Search & Filter</h3>
            </div>
            
            <form method="GET" class="filters-grid">
                <div class="filter-group search-input">
                    <label for="search">Search Faculty</label>
                    <input type="text" id="search" name="search" placeholder="ID, Name, or Email" value="<?= htmlspecialchars($search) ?>">
                </div>

                <div class="filter-group">
                    <label for="status">Employment Status</label>
                    <select id="status" name="status">
                        <option value="">All Employment Statuses</option>
                        <option value="Full-Time Regular" <?= $status_filter === 'Full-Time Regular' ? 'selected' : '' ?>>Full-Time Regular</option>
                        <option value="Full-Time Probationary" <?= $status_filter === 'Full-Time Probationary' ? 'selected' : '' ?>>Full-Time Probationary</option>
                        <option value="Part-Time Lawyer" <?= $status_filter === 'Part-Time Lawyer' ? 'selected' : '' ?>>Part-Time Lawyer</option>
                        <option value="Part-Time Instructor" <?= $status_filter === 'Part-Time Instructor' ? 'selected' : '' ?>>Part-Time Instructor</option>
                    </select>
                </div>

                <div class="filter-buttons">
                    <button type="submit" class="filter-btn"><i class="fa-solid fa-search"></i> Search</button>
                    <a href="manage-faculty.php" class="reset-btn"><i class="fa-solid fa-redo"></i> Reset</a>
                </div>
            </form>
        </section>

        <div class="result-count">
            Showing <strong><?= $totalRows ?></strong> faculty member<?= $totalRows !== 1 ? 's' : '' ?>
        </div>

        <section class="card table-container" aria-label="Faculty Records List">
            <div class="table-wrapper">
                <?php if($totalRows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID Number</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Contact Number</th>
                            <th>Employment Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['faculty_no']) ?></td>
                            <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['contact_no'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($row['status'] ?? 'N/A') ?></td>
                            <td data-label="Actions" class="action-btns">
                                <a href="admin-view-faculty.php?id=<?= urlencode($row['faculty_no']) ?>" class="view-btn-table" title="View Faculty" aria-label="View faculty <?= htmlspecialchars($row['first_name'] . " " . $row['last_name']) ?>">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="admin-edit-faculty.php?id=<?= urlencode($row['faculty_no']) ?>" class="edit-btn-table" title="Edit Faculty" aria-label="Edit faculty <?= htmlspecialchars($row['first_name'] . " " . $row['last_name']) ?>">
                                    <i class="fa-solid fa-edit"></i>
                                </a>
                                <a href="manage-faculty.php?delete=<?= urlencode($row['faculty_no']) ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this faculty member record permanently?')" title="Delete Faculty" aria-label="Delete faculty <?= htmlspecialchars($row['first_name'] . " " . $row['last_name']) ?>">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
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
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => 1, 'limit' => $limit])) ?>"><i class="fa-solid fa-angle-double-left"></i></a>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1, 'limit' => $limit])) ?>"><i class="fa-solid fa-angle-left"></i></a>
                        <?php endif; ?>

                        <span class="current">Page <?= $page ?> of <?= max(1, $totalPages) ?></span>

                        <?php if ($page < $totalPages): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1, 'limit' => $limit])) ?>"><i class="fa-solid fa-angle-right"></i></a>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages, 'limit' => $limit])) ?>"><i class="fa-solid fa-angle-double-right"></i></a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php else: ?>
                <div class="no-results">
                    <div class="no-results-icon"><i class="fa-solid fa-search"></i></div>
                    <p>No faculty records found. Try adjusting your search filters.</p>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <div class="button-container">
            <button class="view-btn" onclick="window.location.href='admin-faculty.php'">
                <i class="fa-solid fa-arrow-left"></i> Back 
            </button>
        </div>

    </main>

</div>

<script>
    function changeRowsPerPage(val) {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('limit', val);
        urlParams.set('page', 1); // Reset to page 1 on limit change
        window.location.search = urlParams.toString();
    }

    // Automatically fade alert blocks smoothly after 4 seconds
    setTimeout(function() {
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