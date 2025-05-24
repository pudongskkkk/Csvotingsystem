<?php
header('Content-Type: application/json');

// Database configuration
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "cs_election";

// Validate table parameter
$allowedTables = ['candidates', 'approved_voters'];
$table = isset($_GET['table']) ? $_GET['table'] : '';

if (!in_array($table, $allowedTables)) {
    die(json_encode(['error' => 'Invalid table specified']));
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// Query to count records in the specified table
$sql = "SELECT COUNT(*) as count FROM $table";
$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    echo json_encode(['count' => $row['count']]);
} else {
    echo json_encode(['error' => 'Error executing query: ' . $conn->error]);
}

$conn->close();
?>