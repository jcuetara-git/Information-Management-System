<?php
session_start();
include("../config/db.php");

// Ensure logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $submission_id = $_POST['submission_id'] ?? null;
    $status        = $_POST['status'] ?? 'Pending';
    $return_url    = $_POST['return_url'] ?? 'admin-jones.php';

    if (!$submission_id) {
        header("Location: " . $return_url . "?error=Invalid submission ID.");
        exit();
    }

    $query = "UPDATE indiana_jones_records SET status = ? WHERE id = ?";
    
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        // This stops the fatal error and tells you exactly what went wrong with the SQL query
        die("Database preparation failed: " . $conn->error);
    }

    $stmt->bind_param("si", $status, $submission_id);

    if ($stmt->execute()) {
        header("Location: " . $return_url . "?success=Status updated successfully!");
    } else {
        header("Location: " . $return_url . "?error=Failed to update status in database.");
    }

    $stmt->close();
    $conn->close();
    exit();
} else {
    header("Location: admin-jones.php");
    exit();
}
?>