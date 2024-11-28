<?php
// Enable CORS for development purposes
header("Access-Control-Allow-Origin: http://localhost:8100"); 
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// For OPTIONS preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Start the session (this needs to be at the top of the script)
session_start();

// Set content type to JSON
header("Content-Type: application/json; charset=UTF-8");

// Database connection settings
$host = 'localhost';
$db_name = 'hq2app';
$dbusername = 'root';
$dbpassword = "";

try {
    // Establish a connection to the MySQL database
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $dbusername, $dbpassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the input data (JSON body)
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['email'], $input['password'])) {
        echo json_encode(['error' => 'Please provide login ID and password']);
        exit;
    }

    // Sanitize the input data
    $login_id = htmlspecialchars(strip_tags($input['email']));
    $password_input = htmlspecialchars(strip_tags($input['password']));
    $token = bin2hex(random_bytes(16));

    // Query to check user either by email or phone
    $query = "SELECT * FROM users WHERE (email = :email OR phone = :phone)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':email', $login_id);
    $stmt->bindParam(':phone', $login_id);

    // Execute query
    $stmt->execute();

    // Check if a user was found
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify the password
        if (password_verify($password_input, $user['password'])) {
            // Store user session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['firstName'] = $user['firstName'];
            $_SESSION['lastName'] = $user['lastName'];
            $_SESSION['role'] = $user['user_role'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['phone'] = $user['phone'];

            $user_id = $_SESSION['user_id'];



            // Password is correct, generate a success response
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'firstName' => $user['firstName'],
                    'lastName' => $user['lastName'],
                    'email' => $user['email'],
                    'user_id' => $user['id'],
                    'role' => $user['user_role'],
                    'phoneNumber' => $user['phone'],
                    'id'=> $user['id']
                ],
                'token' => $token
            ]);
        } else {
            // Invalid password
            echo json_encode(['error' => 'Invalid password']);
        }
    } else {
        // No user found with the provided login ID
        echo json_encode(['error' => 'No user found with that email or phone']);
    }
} else {
    // Invalid request method
    echo json_encode(['error' => 'Invalid request method']);
}
?>
