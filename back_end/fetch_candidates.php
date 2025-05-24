<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// Database configuration
$config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'cs_election'
];

try {
    // Create connection
    $conn = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception('Database connection failed');
    }

    // Get all candidates from database
    $result = $conn->query("SELECT id, name, partylist, position, photo_path FROM candidates");
    
    $candidates = [];
    while ($row = $result->fetch_assoc()) {
        $candidates[] = $row;
    }

    echo json_encode($candidates);

    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>