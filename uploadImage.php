<?php
// Allow CORS for localhost:8100 (Ionic app) and handle preflight requests
header("Access-Control-Allow-Origin: https://localhost:8100");
header("Access-Control-Allow-Methods: *, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Database connection settings
$host = 'localhost'; // Replace with your database host
$db = 'hq2app'; // Replace with your database name
$user = 'root'; // Replace with your database username
$pass = ""; // Replace with your database password

// Connect to the database
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]));
}

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Get userId and profile details from the formData
    $userId = $_POST['userId'] ?? null;
    $firstName = $_POST['firstName'] ?? null;
    $lastName = $_POST['lastName'] ?? null;
    $email = $_POST['email'] ?? null;
    $phoneNumber = $_POST['phoneNumber'] ?? null;

    // Validate userId
    if (!$userId) {
        echo json_encode(["status" => "error", "message" => "User ID is required"]);
        exit;
    }

    // Update profile details if provided
    if ($firstName || $lastName || $email || $phoneNumber) {
        $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone_number = ? WHERE id = ?");
        $stmt->bind_param(
            "sssss",
            $firstName,
            $lastName,
            $email,
            $phoneNumber,
            $userId
        );
        if (!$stmt->execute()) {
            echo json_encode(["status" => "error", "message" => "Failed to update profile details"]);
            exit;
        }
    }

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "uploads/profile_pictures/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $imageName = basename($_FILES['image']['name']);
        $imageExtension = pathinfo($imageName, PATHINFO_EXTENSION);
        $newImageName = "user_{$userId}_" . time() . "." . $imageExtension;
        $uploadPath = $uploadDir . $newImageName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            // Save image path to the database
            $stmt = $conn->prepare("UPDATE users SET profileImage = ? WHERE id = ?");
            $stmt->bind_param("ss", $uploadPath, $userId);
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Image uploaded successfully", "imagePath" => $uploadPath]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to save image path to database"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to move uploaded file"]);
        }
    } else {
        echo json_encode(["status" => "success", "message" => "Profile updated successfully"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

$conn->close();
