<?php
header("Content-Type: application/json");

// Allow cross-origin requests
header("Access-Control-Allow-Origin: https://localhost:8100"); // Replace * with your specific domain for better security
header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); // Allowed HTTP methods
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // Send a 200 OK response
    exit();
}

// Database connection
$host = "localhost";
$dbname = "hq2app";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch users
    $stmt = $pdo->query("SELECT id, firstName FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($users);
} catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
