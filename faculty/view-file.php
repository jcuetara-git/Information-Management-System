<?php
session_start();

// Security check: ensure user is logged in as faculty
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    die("Unauthorized access.");
}

if (isset($_GET['file'])) {
    $file = $_GET['file'];

    // Clean up potential directory traversal security exploits
    $file = str_replace(['../', '..\\'], '', $file);
    
    // Resolve path relative to this 'faculty/' folder up to the project root
    $realServerPath = dirname(__DIR__) . '/' . ltrim($file, '/');

    if (file_exists($realServerPath) && is_file($realServerPath)) {
        $extension = strtolower(pathinfo($realServerPath, PATHINFO_EXTENSION));

        // If it's a Word document or Excel sheet, route it through Microsoft Web Viewer
        if (in_array($extension, ['docx', 'doc', 'xlsx', 'xls', 'pptx', 'ppt'])) {
            // Get the absolute HTTP URL of the file on your local network/live server
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $domainName = $_SERVER['HTTP_HOST'];
            
            // Construct the clean web link (e.g., http://localhost/ccj-sms/uploads/...)
            $publicFileUrl = $protocol . $domainName . '/ccj-sms/' . ltrim($file, '/');
            
            // Redirect to Microsoft Office View Gateway
            $officeViewerUrl = "https://view.officeapps.live.com/op/view.aspx?src=" . urlencode($publicFileUrl);
            header("Location: " . $officeViewerUrl);
            exit;
        }

        // For PDFs and Images, stream them straight to the browser tab
        if (ob_get_level()) ob_end_clean();
        $mimeType = mime_content_type($realServerPath);
        
        header("Content-Type: " . $mimeType);
        header("Content-Disposition: inline; filename=\"" . basename($realServerPath) . "\"");
        header("Content-Length: " . filesize($realServerPath));
        
        readfile($realServerPath);
        exit;
    } else {
        die("<h3>File not found on server.</h3><p>Path: " . htmlspecialchars($realServerPath) . "</p>");
    }
} else {
    die("Invalid request.");
}