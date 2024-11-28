<?php
// Allow requests from any origin
header("Access-Control-Allow-Origin: https://localhost:8100");

// Allow specific HTTP methods (GET, POST, PUT, DELETE, etc.)
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

// Allow specific headers that might be used in requests
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// If it's a preflight request, exit early
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
// Include the CORS head
header("Content-Type: application/json");

// Handle preflight (OPTIONS) request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}



// Your API logic here
