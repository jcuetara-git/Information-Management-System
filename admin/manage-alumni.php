<?php
include("../config/db.php");
include("../config/auth.php");

if($_SESSION['role'] != "admin"){
    header("Location: ../auth/login.php");
    exit();
}

// Handle Delete Row Data (Updated to use student_no for the users table target for alumni)
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE student_no = ? AND role = 'alumni'");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    
    $stmt2 = $conn->prepare("DELETE FROM alumni_profile WHERE alumni_no = ?");
    $stmt2->bind_param("s", $id);
    $stmt2->execute();
    
    header("Location: manage-alumni.php?message=Alumni Record Deleted");
    exit();
}

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$year_filter = isset($_GET['year_graduated']) ? trim($_GET['year_graduated']) : '';

$query = "SELECT u.student_no AS alumni_no, u.first_name, u.last_name, u.email, 
                 p.contact_number, p.year_graduated, p.current_job
          FROM users u 
          LEFT JOIN alumni_profile p ON u.student_no = p.alumni_no 
          WHERE u.role = 'alumni'";

$params = [];
$types = '';

// Add search filter parameters
if(!empty($search)){
    $query .= " AND (u.student_no LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
    $search_param = '%' . $search . '%';
    array_push($params, $search_param, $search_param, $search_param, $search_param);
    $types .= 'ssss';
}

// Add year graduated filter
if(!empty($year_filter)){
    $query .= " AND p.year_graduated = ?";
    $params[] = $year_filter;
    $types .= 's';
}

$query .= " ORDER BY u.first_name ASC";

$stmt = $conn->prepare($query);

// Safety checkpoint: catch any alternative syntax mismatches instantly
if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

if(!empty($params)){
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$total_results = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="Manage Alumni Records - College of Criminal Justice">
    <meta name="theme-color" content="#f4b42c">
    <title>manage-alumni</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="../assets/css/manage-students.css"> 
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

    <?php 
    include("../includes/sidebar.php");
    ?>

    <main class="dashboard-container" id="mainContent" role="main">

        <section class="card welcome-card" aria-label="Welcome Section">
            <div class="welcome-content">
                <h2>Manage Alumni Records</h2>
                <p>Filter, view, edit, and manage alumni profiles.</p>
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
                    <label for="search">Search Alumni</label>
                    <input type="text" id="search" name="search" placeholder="ID, Name, or Email" value="<?= htmlspecialchars($search) ?>">
                </div>

                <div class="filter-group">
                    <label for="year_graduated">Year Graduated</label>
                    <input type="text" id="year_graduated" name="year_graduated" placeholder="e.g. 2023" value="<?= htmlspecialchars($year_filter) ?>">
                </div>

                <div class="filter-buttons">
                    <button type="submit" class="filter-btn"><i class="fa-solid fa-search"></i> Search</button>
                    <a href="manage-alumni.php" class="reset-btn"><i class="fa-solid fa-redo"></i> Reset</a>
                </div>
            </form>
        </section>

        <div class="result-count">
            Showing <strong><?= $total_results ?></strong> alumni member<?= $total_results !== 1 ? 's' : '' ?>
        </div>

        <section class="card table-container" aria-label="Alumni Records List">
            <div class="table-wrapper">
                <?php if($total_results > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Alumni No</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Contact Number</th>
                            <th>Year Graduated</th>
                            <th>Current Job</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['alumni_no']) ?></td>
                            <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['contact_number'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($row['year_graduated'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($row['current_job'] ?? 'N/A') ?></td>
                            <td data-label="Actions" class="action-btns">
                                <a href="admin-view-alumni.php?id=<?= urlencode($row['alumni_no']) ?>" class="view-btn-table" title="View Alumni" aria-label="View alumni <?= htmlspecialchars($row['first_name'] . " " . $row['last_name']) ?>">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="admin-edit-alumni.php?id=<?= urlencode($row['alumni_no']) ?>" class="edit-btn-table" title="Edit Alumni" aria-label="Edit alumni <?= htmlspecialchars($row['first_name'] . " " . $row['last_name']) ?>">
                                    <i class="fa-solid fa-edit"></i>
                                </a>
                                <a href="manage-alumni.php?delete=<?= urlencode($row['alumni_no']) ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this alumni member record permanently?')" title="Delete Alumni" aria-label="Delete alumni <?= htmlspecialchars($row['first_name'] . " " . $row['last_name']) ?>">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="no-results">
                    <div class="no-results-icon"><i class="fa-solid fa-search"></i></div>
                    <p>No alumni records found. Try adjusting your search filters.</p>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <div class="button-container">
            <button class="view-btn" onclick="window.location.href='admin-dashboard.php'">
                <i class="fa-solid fa-arrow-left"></i> Back 
            </button>
        </div>

    </main>

</div>

<script>
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