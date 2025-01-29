<?php
session_start();

// Check if the user is authenticated
if (!isset($_SESSION['authenticated_user'])) {
    echo "Invalid request.";
    exit;
}

// Define the base directory where user files are stored
$baseDirectory = "/home/bkidus/secure/";

// Get the requested file name from the query parameter
$requestedFile = isset($_GET['file']) ? $_GET['file'] : '';

// Ensure the file name is safe (you should validate it further as needed)
if (!preg_match('/^[\w_\.\-]+$/', $requestedFile)) {
    echo "Invalid filename";
    exit;
}

// Construct the full path to the requested file
$filePath = $baseDirectory . $_SESSION['authenticated_user'] . '/' . $requestedFile;

// Check if the file exists
if (file_exists($filePath)) {
    // Determine the file's MIME type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($filePath);

    // Set the appropriate headers based on whether it's a download or view
    if (isset($_GET['download'])) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
    } else {
        header("Content-Type: " . $mime);
        header('content-disposition: inline; filename="' . basename($filePath) . '";');
    }

    // Read and output the file contents
    readfile($filePath);
    exit;
} else {
    echo "File not found";
    exit;
}
?>
