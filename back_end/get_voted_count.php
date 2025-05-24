<?php
header('Content-Type: application/json');

// Database config - adjust if needed
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cs_election";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "SELECT COUNT(*) as total_votes FROM vote_logs";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'count' => (int)$result['total_votes'],
        'success' => true
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage(),
        'success' => false
    ]);
}
?>
