<?php

header("Access-Control-Allow-Origin: http://localhost:8100"); // Adjust this to your frontend URL
header("Access-Control-Allow-Methods: POST, OPTIONS"); // Allow specific methods
header("Access-Control-Allow-Headers: Content-Type"); // Allow specific headers

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No content for preflight request
    exit();
}

// Database configuration
$servername = "localhost";
$dbusername = "root"; // replace with your database username
$dbpassword = ""; // replace with your database password
$dbname = "hq2app"; // replace with your database name

// Create connection
$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed.']));
}

// Get JSON data from the request
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(['error' => 'Invalid input.']);
    exit();
}

// Sanitize and validate input
$firstName = filter_var(trim($data['firstName']), FILTER_SANITIZE_SPECIAL_CHARS);
$lastName = filter_var(trim($data['lastName']), FILTER_SANITIZE_SPECIAL_CHARS);
$email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
$phone = filter_var(trim($data['phone']), FILTER_SANITIZE_SPECIAL_CHARS);
$role = $data['role'];
$password = trim($data['password']);
$user_id = bin2hex(random_bytes(8));

if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($password)) {
    echo json_encode(['error' => 'All fields are required.']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => 'Invalid email format.']);
    exit();
}

if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
    echo json_encode(['error' => 'Password must be at least 8 characters long, include at least one uppercase letter and one number.']);
    exit();
}

// Check for existing user
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    echo json_encode(['error' => 'Email already exists.']);
    exit();
}

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Prepare and bind
$stmt = $conn->prepare(query: "INSERT INTO users (id, firstName, lastName, email, phone, password, user_role) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $user_id, $firstName, $lastName, $email, $phone, $hashedPassword, $role);

// Execute the statement
if ($stmt->execute()) {
    echo json_encode(['success' => 'Registration successful!']);
} else {
    echo json_encode(['error' => 'Error: ' . $stmt->error]);
}

// Close connections
$stmt->close();
$conn->close();
