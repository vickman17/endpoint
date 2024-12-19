<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set headers for CORS
header("Access-Control-Allow-Origin: http://localhost:8100");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json'); // Ensure JSON response

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Check if userId is provided
if (isset($_GET['userId'])) {
    $userId = $_GET['userId'];

    // Connect to the database
    $db = new mysqli("localhost", "hq2app", "", "hq2app");

    if ($db->connect_error) {
        echo json_encode(["status" => "error", "message" => "Database connection failed."]);
        exit;
    }

    // Query the database for the user's profile image (BLOB data) and MIME type
    $stmt = $db->prepare("SELECT profile_picture, mime_type FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($profilePictureBlob, $mimeType);

    if ($stmt->fetch()) {
        // Convert BLOB data to base64 string
        $base64Image = base64_encode($profilePictureBlob);
        
        // Return the base64 image and MIME type in the response
        echo json_encode([
            "status" => "success",
            "profile_picture" => $base64Image,
            "mime_type" => $mimeType // Return MIME type for dynamic image rendering
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "User not found or no profile picture"]);
    }

    $stmt->close();
    $db->close();
} else {
    echo json_encode(["status" => "error", "message" => "User ID is required"]);
}
?>
