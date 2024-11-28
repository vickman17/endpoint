<?php
// CORS Headers
header("Access-Control-Allow-Origin: https://localhost:8100");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Database connection details
$host = "localhost";
$username = "root"; // Replace with your database username
$password = "";     // Replace with your database password
$dbname = "hq2app"; // Replace with your database name

// Connect to the database
$conn = new mysqli($host, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to connect to the database."]);
    exit();
}

// Check if required parameters are present
if (!isset($_GET['user_id']) || !isset($_GET['chat_with_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required parameters."]);
    exit();
}

$user_id = $conn->real_escape_string($_GET['user_id']);
$chat_with_id = $conn->real_escape_string($_GET['chat_with_id']);

// Fetch messages between the two users
$sql = "SELECT sender_id, senderName, receiver_id, message, subChatName, timestamp
        FROM messages 
        WHERE (sender_id = '$user_id' AND receiver_id = '$chat_with_id') 
           OR (sender_id = '$chat_with_id' AND receiver_id = '$user_id')
        ORDER BY timestamp ASC";

$result = $conn->query($sql);

if ($result === false) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to fetch messages: " . $conn->error]);
    exit();
}

// Fetch and prepare messages
$messages = [];
while ($row = $result->fetch_assoc()) {
    // Ensure the `message` field is treated as a raw string
    $messages[] = [
        "sender_id" => $row["sender_id"],
        "subChatName" => $row["subChatName"],
        "senderName" => $row["senderName"],
        "receiver_id" => $row["receiver_id"],
        "message" => $row["message"], // This retains all original characters, including newlines
        "timestamp" => $row["timestamp"],
    ];
}

// Close the database connection
$conn->close();

// Return the messages as JSON
http_response_code(200);
echo json_encode($messages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
