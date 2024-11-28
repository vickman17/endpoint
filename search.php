<?php
// Set Content-Type to JSON
header('Content-Type: application/json');

// Enable CORS headers
header("Access-Control-Allow-Origin: https://localhost:8100"); // Allow all origins or specify a domain
header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); // Allowed HTTP methods
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With"); // Allowed headers

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Respond to the preflight request with appropriate headers and exit
    http_response_code(200);
    exit();
}

// Hardcode database connection details
$host = 'localhost';       // Database host
$user = 'root';            // Database username
$password = "";            // Database password
$dbname = 'hq2app'; // Database name

// Create a new connection to MySQL database
$conn = new mysqli($host, $user, $password, $dbname);

// Check the database connection
if ($conn->connect_error) {
    // Return error if the connection fails
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// Check if the query parameter is passed
if (isset($_GET['query'])) {
    $searchQuery = $_GET['query'];
    
    // Sanitize the input to prevent SQL injection
    $searchQuery = "%" . $searchQuery . "%"; // Using % for partial matching

    // SQL query to search for job categories and subcategories
    $sql = "SELECT jc.category_name, js.subcategory_name 
            FROM job_category jc 
            JOIN job_subcategory js ON jc.id = js.category_id 
            WHERE jc.category_name LIKE ? OR js.subcategory_name LIKE ?";
    
    // Prepare the SQL query
    if ($stmt = $conn->prepare($sql)) {
        // Bind the parameters
        $stmt->bind_param('ss', $searchQuery, $searchQuery);

        // Execute the query
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();

        // Check if we have results
        if ($result->num_rows > 0) {
            $searchResults = [];

            // Fetch all the results
            while ($row = $result->fetch_assoc()) {
                $searchResults[] = [
                    'category' => $row['category_name'],
                    'subcategory' => $row['subcategory_name']
                ];
            }

            // Output the search results as JSON
            echo json_encode($searchResults);
        } else {
            // No results found
            echo json_encode([]);
        }

        // Close the statement
        $stmt->close();
    } else {
        // Query preparation failed
        echo json_encode(["error" => "Query failed to prepare."]);
    }

    // Close the database connection
    $conn->close();
} else {
    // No search query parameter passed
    echo json_encode(["error" => "No search query provided."]);
}
?>
