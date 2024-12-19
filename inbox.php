<?php
header("Access-Control-Allow-Origin: http://localhost:8100");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // Preflight response
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get the userId from query parameters
    $userId = $_GET['userId'] ?? null;

    if ($userId) {
        // Database connection
        $conn = new mysqli('localhost', 'hq2app', "", 'hq2app');

        if ($conn->connect_error) {
            http_response_code(500);
            echo json_encode(['message' => 'Database connection failed']);
            exit;
        }

        // Query to fetch chats and the last message for each chat
        $stmt = $conn->prepare("
            SELECT 
                chats.id AS chat_id,
                chats.chat_name,
                chats.subChatName,
                chats.sender_id,
                chats.receiver_id,
                users.firstName AS sender_name,
                last_messages.message AS last_message,
                last_messages.timestamp AS last_message_time
            FROM chats
            LEFT JOIN (
                SELECT 
                    chat_id,
                    message,
                    timestamp
                FROM messages
                WHERE id IN (
                    SELECT MAX(id) 
                    FROM messages 
                    GROUP BY chat_id
                )
            ) AS last_messages ON last_messages.chat_id = chats.id
            LEFT JOIN users ON users.id = chats.sender_id
            WHERE chats.sender_id = ? OR chats.receiver_id = ?
            ORDER BY last_message_time DESC
        ");
        $stmt->bind_param("ss", $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $chats = [];
            while ($row = $result->fetch_assoc()) {
                $chats[] = [
                    'id' => $row['chat_id'],
                    'chat_name' => $row['chat_name'],
                    'subcategory_name' => $row['subChatName'],
                    'sender_id' => $row['sender_id'],
                    'receiver_id' => $row['receiver_id'],
                    'sender_name' => $row['sender_name'],
                    'last_message' => $row['last_message'],
                    'last_message_time' => $row['last_message_time'],
                ];
            }

            echo json_encode($chats);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'No chats found for this user']);
        }

        $stmt->close();
        $conn->close();
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'User ID is required']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
}
