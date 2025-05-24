<?php
header("Content-Type: application/json");

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'cs_election';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "DB connection failed."]);
    exit;
}

$sql = "SELECT id, student_id, last_name, first_name, middle_name, course, year_level, email, voter_key 
        FROM approved_voters ORDER BY approved_at DESC";
$result = $conn->query($sql);

$voters = [];

while ($row = $result->fetch_assoc()) {
    $voters[] = $row;
}

echo json_encode(["success" => true, "data" => $voters]);
$conn->close();
?>
