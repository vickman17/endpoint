<?php
// CORS headers to allow requests from specified origin
header("Access-Control-Allow-Origin: https://localhost:8100");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight (OPTIONS) request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

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

// SQL query to fetch categories
$sql = "SELECT * FROM job_categories";
$result = $conn->query($sql);

// Prepare an array to store categories
$categories = [];

// Check if any categories are returned
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
} else {
    // Send an error message if the query fails or returns no results
    echo json_encode(['error' => 'No categories found']);
    $conn->close();
    exit();
}

// Return categories as JSON
echo json_encode($categories);

// Close the database connection
$conn->close();
