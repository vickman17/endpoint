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

// Get the data from the request
$data = json_decode(file_get_contents("php://input"));

// Retrieve the sender's ID, recipient's ID, and typing status
$sender_id = $data->sender_id;
$chat_with_id = $data->chat_with_id;
$is_typing = $data->is_typing;

// Prepare the SQL query
$query = "INSERT INTO typing_status (sender_id, chat_with_id, is_typing) 
          VALUES (?, ?, ?)
          ON DUPLICATE KEY UPDATE is_typing = ?";

// Prepare statement
$stmt = $conn->prepare($query);

// Check for errors
if ($stmt === false) {
    die('MySQL prepare error: ' . $conn->error);
}

// Bind the parameters
$stmt->bind_param("iiii", $sender_id, $chat_with_id, $is_typing, $is_typing);

// Execute the statement
if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update typing status']);
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
