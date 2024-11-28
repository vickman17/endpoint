<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: https://localhost:8100"); // Enable cross-origin requests for development

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hq2app";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Get the category ID from the query parameter
$categoryId = isset($_GET['categoryId']) ? intval($_GET['categoryId']) : 0;

if ($categoryId <= 0) {
    echo json_encode(["error" => "Invalid category ID"]);
    exit();
}

// Fetch subcategories for the given category ID
$sql = "SELECT id, subcategory_name FROM job_subcategories WHERE category_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $categoryId);
$stmt->execute();
$result = $stmt->get_result();

$subcategories = [];

while ($row = $result->fetch_assoc()) {
    $subcategories[] = $row;
}

$stmt->close();
$conn->close();

// Return the subcategories as a JSON response
echo json_encode($subcategories);
