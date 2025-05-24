<?php
header('Content-Type: application/json');

// Database configuration
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "cs_election";

try {
    // Create connection
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to get total votes by position
    $query = "SELECT 
                position,
                SUM(votes) as total_votes
              FROM voting_count
              GROUP BY position
              ORDER BY position";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare data for chart
    $positions = [];
    $voteCounts = [];

    foreach ($results as $row) {
        $positions[] = $row['position'];
        $voteCounts[] = (int)$row['total_votes'];
    }

    echo json_encode([
        'success' => true,
        'positions' => $positions,
        'voteCounts' => $voteCounts
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage(),
        'success' => false
    ]);
}
?>