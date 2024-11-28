<?php


// Enable CORS
header("Access-Control-Allow-Origin: https://localhost:8100"); // This allows requests from any origin
header("Access-Control-Allow-Methods: POST, GET, OPTIONS"); // Allow methods (preflight request)
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allow specific headers

// Handle preflight request (OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Your existing logic for uploading the profile image



header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Database connection
    $conn = new mysqli("localhost", "hq2app", "", "hq2app");

    if ($conn->connect_error) {
        echo json_encode(["status" => "error", "message" => "Database connection failed."]);
        exit();
    }

    // Validate file and user ID
    if (isset($_FILES['image']) && isset($_POST['userId'])) {
        $userId = intval($_POST['userId']);
        $image = $_FILES['image'];

        // Ensure it's a valid image file
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($image['type'], $allowedTypes)) {
            echo json_encode(["status" => "error", "message" => "Invalid image type."]);
            exit();
        }

        // Read the file content
        $imageData = file_get_contents($image['tmp_name']);

        // Prepare and execute the SQL statement
        $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
        $stmt->bind_param('bi', $imageData, $userId); // 'b' for blob

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Profile picture updated successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update profile picture."]);
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid input."]);
    }

    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
