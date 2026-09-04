<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != "alumni") {
    header("Location: ../auth/login.php");
    exit();
}

$alumni_no  = $_SESSION['alumni_no'] ?? $_SESSION['student_no'] ?? '';
$first_name = $_SESSION['firstname'] ?? $_SESSION['first_name'] ?? 'Alumni';
$last_name  = $_SESSION['lastname'] ?? $_SESSION['last_name'] ?? '';

// Handle pagination/limit configuration
$allowed_limits = [5, 20, 50];
$limit = isset($_GET['limit']) && in_array((int)$_GET['limit'], $allowed_limits) ? (int)$_GET['limit'] : 5;

// Fetch announcements for header dropdown
$announcements = [];
if (isset($conn)) {
    $conn->query("SET time_zone = '+08:00'");
    $query = "SELECT title, message, created_at, 
              (created_at >= NOW() - INTERVAL 1 DAY) AS is_new 
              FROM announcements 
              WHERE status = 'published' 
              AND (target_audience = 'all' OR target_audience = 'alumni' OR (target_audience = 'specific_user' AND target_user_id = ?))
              ORDER BY created_at DESC LIMIT 10";

    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("s", $alumni_no);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $announcements[] = $row;
            }
        }
        $stmt->close();
    }
}

$concerns = [];
if (!empty($alumni_no)) {
    $stmt = $conn->prepare("SELECT * FROM alumni_concerns WHERE alumni_no = ? ORDER BY id DESC LIMIT ?");
    if ($stmt) {
        $stmt->bind_param("si", $alumni_no, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $concerns = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Concerns</title>
    <link rel="stylesheet" href="../assets/css/student-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="dashboard-container">
    <?php include("../includes/header.php"); ?>

    <div class="dashboard-layout">
        <?php include("../includes/alumni-sidebar.php"); ?>

        <main class="main-content">
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

            <div class="card welcome-card">
                <div class="page-header" style="margin-bottom: 0; width: 100%;">
                    <div>
                        <h2 class="header-title"><i class="fa-solid fa-envelope"></i> Alumni Concerns</h2>
                        <p class="header-desc">Track status updates for concerns or inquiries submitted to the administration.</p>
                    </div>
                    <button type="button" class="btn-add" onclick="openConcernForm()">
                        <i class="fa-solid fa-plus"></i> Submit Concern
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="table-controls">
                    <div>
                        Show 
                        <select id="entriesLimit" onchange="changeLimit(this.value)">
                            <option value="5" <?= $limit == 5 ? 'selected' : ''; ?>>5</option>
                            <option value="20" <?= $limit == 20 ? 'selected' : ''; ?>>20</option>
                            <option value="50" <?= $limit == 50 ? 'selected' : ''; ?>>50</option>
                        </select> 
                        entries
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="status-table">
                        <thead>
                            <tr>
                                <th>Alumni Number</th>
                                <th>Attachment File</th>
                                <th>Date Submitted</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($concerns) > 0): ?>
                                <?php foreach ($concerns as $row): ?>
                                    <?php 
                                        $status = $row['status'] ?? 'Pending';
                                        $badge_class = 'badge-pending';
                                        if (strtolower($status) === 'resolved') {
                                            $badge_class = 'badge-resolved';
                                        } elseif (strtolower($status) === 'rejected') {
                                            $badge_class = 'badge-rejected';
                                        }

                                        $filename = $row['file_path'] ?? $row['concern_file'] ?? '';
                                    ?>
                                    <tr>
                                        <td data-label="Alumni Number"><?= htmlspecialchars($row['alumni_no'] ?? $alumni_no); ?></td>
                                        <td data-label="Attachment File">
                                            <?php if (!empty($filename)): ?>
                                                <a href="../uploads/alumni_concerns/<?= htmlspecialchars($filename); ?>" target="_blank" style="color: #2563eb; text-decoration: underline;">View File</a>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td data-label="Date Submitted"><?= !empty($row['created_at']) ? date('M d, Y', strtotime($row['created_at'])) : 'N/A'; ?></td>
                                        <td data-label="Status"><span class="badge <?= $badge_class; ?>"><?= htmlspecialchars($status); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: #64748b; padding: 25px;">No concern records found. Click "Submit Concern" to raise one.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div> 
</div>

<div class="modal-overlay" id="concernModal">
    <div class="personal-modal">
        <span class="close-btn" onclick="closeConcernForm()">&times;</span>
        <form class="personal-form" method="POST" action="save-alumni-concern.php" enctype="multipart/form-data">
            <h3 class="form-title">Submit a Concern</h3>
            <p class="form-desc">Submit a Letter of Concern regarding career updates, records, or other inquiries.</p>
            
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

            <div class="form-group">
                <label class="form-label">Upload Concern (Document/Image)</label>
                <input type="file" name="concern_file" class="form-input form-file" required>
            </div>

            <div class="modal-buttons">
                <button type="button" class="cancel-btn" onclick="closeConcernForm()">Cancel</button>
                <button type="submit" class="save-btn">Submit Concern</button>
            </div>
        </form>
    </div>
</div>

<script>
    function changeLimit(value) {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('limit', value);
        window.location.search = urlParams.toString();
    }

    function openConcernForm() {
        document.getElementById('concernModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeConcernForm() {
        document.getElementById('concernModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    window.onclick = function(event) {
        var modal = document.getElementById('concernModal');
        if (event.target == modal) {
            closeConcernForm();
        }
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