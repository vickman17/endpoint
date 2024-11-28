<?php
// CORS headers to allow requests from specified origin
header("Access-Control-Allow-Origin: https://localhost:8100");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Set the Content-Type header for JSON response
header("Content-Type: application/json");

// Database connection parameters
$host = 'localhost';
$dbname = 'hq2app';
$username = 'root';
$password = "";

// Connect to the database
$conn = new mysqli($host, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Check if a file and category name were uploaded
if (isset($_FILES['image']) && isset($_POST['name'])) {
    // Set the upload directory
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Generate a unique name for the image file
    $imageName = uniqid() . '-' . basename($_FILES['image']['name']);
    $imagePath = $uploadDir . $imageName;
    $fullImagePath = __DIR__ . '/' . $imagePath;

    // Move the uploaded file to the server directory
    if (move_uploaded_file($_FILES['image']['tmp_name'], $fullImagePath)) {
        // Insert category name and image path into the database
        $stmt = $conn->prepare("INSERT INTO job_categories (image_path) VALUES (?)");
        $stmt->bind_param("s", $imagePath);

        if ($stmt->execute()) {
            echo json_encode(['success' => 'Category and image uploaded successfully.']);
        } else {
            echo json_encode(['error' => 'Failed to insert data into database.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Failed to upload image.']);
    }
} else {
    echo json_encode(['error' => 'No image or category name provided.']);
}

// Close the database connection
$conn->close();
