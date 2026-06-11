<?php
include("../config/db.php");
include("../config/auth.php");

if($_SESSION['role'] != "admin"){
    header("Location: ../auth/login.php");
    exit();
}

// Handle Delete
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE student_no = ? AND role = 'student'");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    
    $stmt2 = $conn->prepare("DELETE FROM student_profile WHERE student_no = ?");
    $stmt2->bind_param("s", $id);
    $stmt2->execute();
    
    header("Location: manage-students.php?message=Student Deleted");
    exit();
}

$query = "SELECT u.student_no, u.first_name, u.last_name, u.email, p.contact_number, u.year_level
          FROM users u 
          LEFT JOIN student_profile p ON u.student_no = p.student_no 
          WHERE u.role = 'student'";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="Manage Students - College of Criminal Justice">
    <meta name="theme-color" content="#f4b42c">
    <title>manage-students</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="../assets/css/manage-students.css">
</head>

<body>

<div class="main-container">

    <!-- ================= SIDEBAR ================= -->
     <nav class="sidebar" id="sidebar" role="navigation" aria-label="Main Navigation">
        <div class="sidebar-header">
            <button id="toggleSidebar" class="hamburger-btn" aria-label="Toggle Sidebar">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

        <ul>
            <li role="menuitem" onclick="window.location.href='admin-dashboard.php'">
                <i class="fa-solid fa-chart-line"></i> 
                <span>Dashboard</span>
            </li>
            <li class="active" role="menuitem" onclick="window.location.href='admin-students.php'" >
                <i class="fa-solid fa-users"></i> 
                <span>Students</span>
            </li>
            <li role="menuitem">
                <i class="fa-solid fa-user-tie"></i> 
                <span>Faculty</span>
            </li>
            <li role="menuitem">
                <i class="fa-solid fa-briefcase"></i> 
                <span>Internship</span>
            </li>
            <li role="menuitem">
                <i class="fa-solid fa-folder"></i> 
                <span>Organizations</span>
            </li>
            <li role="menuitem">
                <i class="fa-solid fa-house"></i> 
                <span>Community Extension</span>
            </li>
            <li role="menuitem">
                <i class="fa-solid fa-file"></i> 
                <span>Indiana Jones</span>
            </li>
        </ul>

        <div class="profile-menu">
            <a href="../auth/logout.php" role="menuitem">
                <i class="fa-solid fa-sign-out-alt"></i> 
                <span>Logout</span>
            </a>
        </div>
    </nav>


    <!-- ================= MAIN CONTENT ================= -->
    <main class="dashboard-container" role="main">

        <!-- HEADER -->
        <header class="logo-section">
            <div class="logo-left">
                <div class="logo-circle">
                    <img src="../assets/logo.png" alt="College of Criminal Justice Logo">
                </div>

                <div class="logo-text">
                    <h1>College of Criminal Justice</h1>
                    <p>Center of Development in Criminology</p>
                </div>
            </div>
        </header>

        <!-- Welcome Section -->
        <section class="card welcome-card" aria-label="Welcome Section">
            <h2>Manage Students</h2>
            <p>View, edit, and manage student information.</p>
        </section>

        <!-- Students Table -->
        <section class="card table-container" aria-label="Students List">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID Number</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Contact Number</th>
                            <th>Year Level</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td data-label="ID Number"><?= htmlspecialchars($row['student_no']) ?></td>
                            <td data-label="Name"><?= htmlspecialchars($row['first_name'] . " " . $row['last_name']) ?></td>
                            <td data-label="Email"><?= htmlspecialchars($row['email']) ?></td>
                            <td data-label="Contact Number"><?= htmlspecialchars($row['contact_number'] ?? 'N/A') ?></td>
                            <td data-label="Year Level"><?= htmlspecialchars($row['year_level'] ?? 'N/A') ?></td>
                            <td data-label="Actions" class="action-btns">
                                <a href="manage-students.php?delete=<?= urlencode($row['student_no']) ?>" class="delete-btn" onclick="return confirm('Are you sure to delete this student?')" title="Delete Student" aria-label="Delete student <?= htmlspecialchars($row['first_name'] . " " . $row['last_name']) ?>">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Action Buttons -->
        <div class="button-container">
            <button class="view-btn" onclick="window.location.href='admin-dashboard.php'">
                <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
            </button>
        </div>

    </main>

</div>

</body>
</html>
