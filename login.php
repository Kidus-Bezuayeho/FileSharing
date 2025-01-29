<?php
session_start();

$file_path = '/home/bkidus/secure/users.txt';

$username = trim($_POST['username']);

$usernames = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

if (in_array($username, $usernames)) {
    $_SESSION['authenticated_user'] = $username;
    header("Location: list_files.php");
    exit;
} else {
    echo "Bad Login";
    echo '<br><button onclick="window.history.back();">Go Back</button>';
}
?>
