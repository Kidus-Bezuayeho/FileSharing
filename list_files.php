<?php
session_start();

if (isset($_SESSION['authenticated_user'])) {
    $username = $_SESSION['authenticated_user'];
} else {
    echo "Invalid request.";
    exit;
}

$directory = "/home/bkidus/secure/" . $username . "/";

// Function to delete a file
function deleteFile($fileToDelete) {
    if (file_exists($fileToDelete)) {
        if (unlink($fileToDelete)) {
            return true; // File deleted successfully
        } else {
            echo "Failed to delete the file.";
            return false;
        }
    }
    return true; // File does not exist, consider it deleted
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['deletefile'])) {
    $fileToDelete = $directory . $_POST['deletefile'];
    if (deleteFile($fileToDelete)) {
        header("Location: " . $_SERVER["PHP_SELF"]);
        exit;
    }
}

if (is_dir($directory)) {
    $files = scandir($directory);
    $files = array_diff($files, array('.', '..'));
    echo "<h2>Files for user: $username</h2>";
    echo "<ul>";
    foreach ($files as $file) {
        $fileLink = urlencode($file);
        echo "<li>" . htmlspecialchars($file);
        echo " <a href='download.php?file=$fileLink'>View</a>";
        echo " <a href='download.php?file=$fileLink&download=1'>Download</a>";
        echo ' <form action="' . $_SERVER["PHP_SELF"] . '" method="post" style="display: inline;">';
        echo '<input type="hidden" name="deletefile" value="' . htmlspecialchars($file) . '">';
        echo '<input type="submit" value="Delete" onclick="return confirm(\'Are you sure?\');">';
        echo '</form>';
        echo "</li>";
    }
    echo "</ul>";
} else {
    echo "Directory not found for the given user.";
}

echo '<h3>Upload a File</h3>';
echo '<form action="' . $_SERVER["PHP_SELF"] . '" method="post" enctype="multipart/form-data">';
echo 'Select file to upload:';
echo '<input type="file" name="userfile">';
echo '<input type="submit" value="Upload File" name="submit">';
echo '</form>';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    if (isset($_FILES['userfile']) && $_FILES['userfile']['error'] === UPLOAD_ERR_OK) {
        $target_file = $directory . basename($_FILES['userfile']['name']);
        if (file_exists($target_file)) {
            if (deleteFile($target_file)) { // Delete the existing file
                // Continue with the upload logic below
            }
        }
        
        // Upload the new file from the temporary variable
        if (move_uploaded_file($_FILES['userfile']['tmp_name'], $target_file)) {
            header("Location: " . $_SERVER["PHP_SELF"]);
            exit;
        } else {
            echo "Error: Failed to move the uploaded file.";
            exit;
        }
    } elseif ($_FILES['userfile']['error'] !== UPLOAD_ERR_OK) {
        echo "Upload error code: " . $_FILES['userfile']['error'];
        exit;
    }
}

echo '<h3>Logout</h3>';
echo '<form action="' . $_SERVER["PHP_SELF"] . '" method="post">';
echo '<input type="submit" value="Logout" name="logout">';
echo '</form>';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout'])) {
    session_destroy();
    header("location: logoutpage.html");
}

echo '<h3>Send File to Another User</h3>';
echo '<form action="' . $_SERVER["PHP_SELF"] . '" method="post">';
echo 'Username: <input type="text" name="recipient_username" required>';
echo 'File: <input type="text" name="file_to_send" placeholder="Enter file name from your directory" required>';
echo '<input type="submit" value="Send File" name="sendfile">';
echo '</form>';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['sendfile'])) {
    $recipient_username = $_POST['recipient_username'];
    $file_to_send = $_POST['file_to_send'];

    $recipient_directory = "/home/bkidus/secure/" . $recipient_username . "/";

    if (is_dir($recipient_directory)) {
        $source_file = $directory . $file_to_send;
        $destination_file = $recipient_directory . $file_to_send;

        if (file_exists($source_file)) {
            if (copy($source_file, $destination_file)) {
                echo "File sent successfully!";
            } else {
                echo "Error: Failed to send the file.";
            }
        } else {
            echo "Error: The specified file does not exist in your directory.";
        }
    } else {
        echo "Error: Directory for the recipient user not found.";
    }
}
?>