<?php
header("Content-Type: application/json");

// --- DATABASE CONFIGURATION ---
$host = "localhost";       // Change if different
$username = "root";        // Change if different
$password = "";            // Change if different
$database = "cs_election"; // Your actual DB name

// --- CONNECT TO DATABASE ---
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "error" => "Connection failed: " . $conn->connect_error
    ]);
    exit;
}

// --- PARSE INPUT ---
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id'])) {
    echo json_encode([
        "success" => false,
        "error" => "Missing voter ID"
    ]);
    exit;
}

$voterId = intval($data['id']);

// --- DELETE FROM approved_voters TABLE ---
try {
    $stmt = $conn->prepare("DELETE FROM approved_voters WHERE id = ?");
    $stmt->bind_param("i", $voterId);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode([
            "success" => false,
            "error" => "Execution failed"
        ]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => "Exception: " . $e->getMessage()
    ]);
}

$conn->close();
?>
