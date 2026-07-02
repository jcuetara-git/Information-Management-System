<?php
session_start();
include("../config/db.php");

// Protect page access - ensure user is logged in and is an alumni
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'alumni') {
    header("Location: ../auth/login.php");
    exit();
}

$first_name = $_SESSION['first_name'] ?? 'Alumni';
$student_no = $_SESSION['student_no'] ?? '';

// Check if portfolio already exists to dynamically toggle view state
$portfolio_exists = false;
$stmt = $conn->prepare("SELECT id FROM alumni_profile WHERE student_no = ?");
$stmt->bind_param("s", $student_no);
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
    <title>alumni-dashboard</title>
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

    <main style="max-width: 1200px; margin: 0 auto; padding: 0 20px; box-sizing: border-box;">

        <!-- ================= MATCHING FACULTY SUCCESS BANNER ================= -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div id="successAlertBanner" style="background-color: #d1fae5; color: #065f46; padding: 15px 20px; border-radius: 12px; margin: 20px 0 0 0; border: 1px solid #a7f3d0; font-weight: 500; display: flex; align-items: center; justify-content: space-between; gap: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); font-family: sans-serif; transition: opacity 0.5s ease, margin 0.5s ease, padding 0.5s ease, height 0.5s ease; overflow: hidden; opacity: 1;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-circle-check" style="color: #10b981; font-size: 1.2rem;"></i>
                    <span><?= htmlspecialchars($_SESSION['success_message']); ?></span>
                </div>
                <button type="button" onclick="dismissAlertBanner()" style="background: none; border: none; color: #065f46; font-size: 1.4rem; cursor: pointer; line-height: 1; padding: 0 5px; opacity: 0.7; transition: opacity 0.2s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.7;">&times;</button>
            </div>
        <?php 
            unset($_SESSION['success_message']);
        endif; 
        ?>
        
        <section class="card welcome-card" style="margin-top: 20px;">
            <h1>Welcome, <?= htmlspecialchars($first_name) ?>!</h1>
            <p>Keep your alumni records updated and stay connected.</p>
        </section>

        <?php if ($portfolio_exists): ?>
            <section class="card add-info-card disabled">
                <div class="check-icon"></div>
                <p>Alumni Profile Completed</p>
            </section>
        <?php else: ?>
            <a href="alumni-add-portfolio.php" class="add-info-link">
                <section class="card add-info-card">
                    <div class="plus-icon"></div>
                    <p>Add Alumni Information</p>
                </section>
            </a>
        <?php endif; ?>

        <div class="button-container">
            <button class="view-btn" onclick="window.location.href='alumni-view-record.php'">
                <i class="fa-solid fa-file-lines"></i> View My Record
            </button>
        </div>

    </main>

    <!-- ================= MATCHING FACULTY ALERTS DISMISSAL SCRIPT ================= -->
    <script>
        function dismissAlertBanner() {
            const banner = document.getElementById('successAlertBanner');
            if (banner) {
                banner.style.opacity = '0';
                setTimeout(() => {
                    banner.style.padding = '0px';
                    banner.style.margin = '0px';
                    banner.style.height = '0px';
                    banner.style.border = 'none';
                    setTimeout(() => banner.remove(), 500);
                }, 400);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const banner = document.getElementById('successAlertBanner');
            if (banner) {
                setTimeout(() => {
                    dismissAlertBanner();
                }, 5000); // Automatically slide up after 5 seconds
            }
        });
    </script>

</body>
</html>