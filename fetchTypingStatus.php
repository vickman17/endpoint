<?php
// Hardcoded Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hq2app"; // Replace with your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the sender's ID and recipient's ID from the query string
$sender_id = $_GET['user_id'];
$chat_with_id = $_GET['chat_with_id'];

// Prepare the SQL query to fetch typing status
$query = "SELECT is_typing FROM typing_status WHERE sender_id = ? AND chat_with_id = ?";

// Prepare the statement
$stmt = $conn->prepare($query);

// Check for errors
if ($stmt === false) {
    die('MySQL prepare error: ' . $conn->error);
}

// Bind the parameters
$stmt->bind_param("ii", $sender_id, $chat_with_id);

// Execute the statement
$stmt->execute();

// Bind the result
$stmt->bind_result($is_typing);

// Fetch the result
$status = ['is_typing' => false];
if ($stmt->fetch()) {
    $status['is_typing'] = (bool) $is_typing; // Convert 0/1 to true/false
}

// Return the status as a JSON response
echo json_encode($status);

// Close the statement and connection
$stmt->close();
$conn->close();
?>
