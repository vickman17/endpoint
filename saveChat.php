<?php
header("Access-Control-Allow-Origin: http\://localhost:8100");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // Preflight response
    exit;
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Parse incoming JSON data
    $input = json_decode(file_get_contents('php://input'), true);

    $chatName = $input['chatName'] ?? null;
    $subChatName = $input['subChatName'] ?? null;
    $senderName = $input['senderName'] ?? null;
    $senderId = $input['senderId'] ?? null;
    $receiverId = $input['receiverId'] ?? null;

    if ($chatName && $senderName && $senderId && $receiverId) {
        // Database connection
        $conn = new mysqli('localhost', 'root', "", 'hq2app');

        if ($conn->connect_error) {
            http_response_code(500);
            echo json_encode(['message' => 'Database connection failed']);
            exit;
        }

        // Insert chat info into the database
        $stmt = $conn->prepare("INSERT INTO chats (chat_name, subChatName, sender_name, sender_id, receiver_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $chatName, $subChatName, $senderName, $senderId, $receiverId);

        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(['message' => 'Chat saved successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to save chat']);
        }

        $stmt->close();
        $conn->close();
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid input']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
}
