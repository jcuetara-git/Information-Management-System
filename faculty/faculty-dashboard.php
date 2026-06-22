<?php
session_start();
include("../config/db.php");

// Protect page access - ensure user is logged in and is a faculty member
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../auth/login.php");
    exit();
}

$first_name = $_SESSION['first_name'] ?? 'Faculty';
$faculty_id = $_SESSION['student_no'];

// Check if portfolio already exists to dynamically change to a checkmark (just like the student card)
$portfolio_exists = false;
$stmt = $conn->prepare("SELECT id FROM faculty_profile WHERE faculty_no = ?");
$stmt->bind_param("s", $faculty_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
    $portfolio_exists = true;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>faculty dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/faculty-dashboard.css?v=1.4">
</head>
<body>

    <header class="logo-section">
        <div class="logo-left">
            <div class="logo-circle">
                <img src="../assets/logo.png" alt="Logo">
            </div>
            <div class="logo-text">
                <h2>College of Criminal Justice</h2>
                <p>Center of Development in Criminology</p>
            </div>
        </div>
        <div class="profile-menu">
            <a href="../auth/logout.php" title="Logout">
                <i class="fa-solid fa-sign-out-alt"></i>
                <span class="logout-text">Logout</span>
            </a>
        </div>
    </header>

    <main>
        
        <section class="card welcome-card">
            <h1>Welcome, <?= htmlspecialchars($first_name) ?>!</h1>
            <p>Manage your professional portfolio information and view your teaching record.</p>
        </section>

        <?php if ($portfolio_exists): ?>
            <section class="card add-info-card disabled">
                <div class="check-icon"></div>
                <p>Faculty Portfolio Added</p>
            </section>
        <?php else: ?>
            <a href="faculty-add-portfolio.php" class="add-info-link">
                <section class="card add-info-card">
                    <div class="plus-icon"></div>
                    <p>Add Faculty Portfolio</p>
                </section>
            </a>
        <?php endif; ?>

        <div class="button-container">
            <button class="view-btn" onclick="window.location.href='view-record.php'">
                View Record
            </button>
        </div>

    </main>

</body>
</html>