<?php
// CORS headers
header("Access-Control-Allow-Origin: http://localhost:8100");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight (OPTIONS) request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Set Content-Type header for JSON response
header("Content-Type: application/json");

// Connect to the database
$conn = mysqli_connect("localhost", "hq2app", "", "hq2app");

// Check connection
if (!$conn) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed: " . mysqli_connect_error()]);
    exit();
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $senderId = $_POST['sender_id'] ?? null;
    $senderName = $_POST['senderName'] ?? null;
    $recipientId = $_POST['chatWithID'] ?? null;
    $message = $_POST['message'] ?? null;
    $chatName = $_POST['chatName'] ?? null;
    $subChatName = $_POST['subChatName'] ?? null;

    // Validate required fields
    if (empty($senderId) || empty($recipientId) || empty($message)) {
        http_response_code(400);
        echo json_encode(["error" => "Required fields are missing"]);
        exit();
    }

    // Check if a chat already exists between sender and recipient
    $getChatQuery = "SELECT id FROM chats WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)";
    $chatStmt = mysqli_prepare($conn, $getChatQuery);
    if (!$chatStmt) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to prepare chat retrieval query: " . mysqli_error($conn)]);
        exit();
    }
    mysqli_stmt_bind_param($chatStmt, "ssss", $senderId, $recipientId, $recipientId, $senderId);
    mysqli_stmt_execute($chatStmt);
    mysqli_stmt_bind_result($chatStmt, $chatId);
    mysqli_stmt_fetch($chatStmt);
    mysqli_stmt_close($chatStmt);

    // If no chat exists, create one
    if (empty($chatId)) {
        $createChatQuery = "INSERT INTO chats (sender_id, receiver_id, chat_name, subChatName) VALUES (?, ?, ?, ?)";
        $createChatStmt = mysqli_prepare($conn, $createChatQuery);
        if (!$createChatStmt) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to prepare chat creation query: " . mysqli_error($conn)]);
            exit();
        }
        mysqli_stmt_bind_param($createChatStmt, "ssss", $senderId, $recipientId, $chatName, $subChatName);
        if (mysqli_stmt_execute($createChatStmt)) {
            $chatId = mysqli_insert_id($conn);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to create new chat: " . mysqli_stmt_error($createChatStmt)]);
            mysqli_stmt_close($createChatStmt);
            exit();
        }
        mysqli_stmt_close($createChatStmt);
    }

    // Handle file upload
    $uploadedFilePath = null;
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/'; // Ensure this directory exists and is writable
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileName = basename($_FILES['file']['name']);
        $targetFilePath = $uploadDir . time() . "_" . $fileName;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFilePath)) {
            $uploadedFilePath = $targetFilePath;
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to upload the file"]);
            exit();
        }
    }

    // Insert the message
    $insertMessageQuery = "INSERT INTO messages (chat_id, sender_id, senderName, receiver_id, message, timestamp, file_path) VALUES (?, ?, ?, ?, ?, NOW(), ?)";
    $insertStmt = mysqli_prepare($conn, $insertMessageQuery);
    if (!$insertStmt) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to prepare message insertion query: " . mysqli_error($conn)]);
        exit();
    }
    mysqli_stmt_bind_param($insertStmt, "isssss", $chatId, $senderId, $senderName, $recipientId, $message, $uploadedFilePath);

    if (mysqli_stmt_execute($insertStmt)) {
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Message sent successfully",
            "uploadedFile" => $uploadedFilePath
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Failed to send message: " . mysqli_stmt_error($insertStmt)]);
    }
    mysqli_stmt_close($insertStmt);
}

// Close the database connection
mysqli_close($conn);
