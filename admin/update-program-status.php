<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission_id = intval($_POST['submission_id'] ?? 0);
    $status = $_POST['status'] ?? 'Pending';
    $remarks = trim($_POST['admin_remarks'] ?? '');
    
    // Determine where to redirect back to
    $return_url = $_POST['return_url'] ?? 'admin-dashboard.php';

    if ($submission_id > 0) {
        $query = "UPDATE program_submissions SET status = ?, admin_remarks = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $status, $remarks, $submission_id);

        if ($stmt->execute()) {
            $stmt->close();
            // Redirect back to the specific admin page that made the request
            header("Location: " . $return_url . "?success=Status updated successfully");
            exit();
        }
    }
}

header("Location: admin-dashboard.php");
exit();