<?php
header("Content-Type: application/json");

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'cs_election';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database connection failed."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Voter ID is missing."]);
    exit;
}

function generateVoterKey(): string {
    $letters = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 3);
    $symbols = str_split('!@#$%&*()');
    $symbol = $symbols[array_rand($symbols)];
    $numbers = str_pad(strval(rand(0, 999)), 3, '0', STR_PAD_LEFT);
    return $letters . $symbol . $numbers;
}

// Step 1: Fetch voter data
$stmt = $conn->prepare("SELECT student_id, last_name, first_name, middle_name, course, year_level, email FROM voter_request WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($student_id, $last_name, $first_name, $middle_name, $course, $year_level, $email);

if ($stmt->fetch()) {
    $stmt->close();

    // Step 2: Generate unique voter key
    for ($i = 0; $i < 5; $i++) {
        $voter_key = generateVoterKey();

        $check = $conn->prepare("SELECT COUNT(*) FROM approved_voters WHERE voter_key = ?");
        $check->bind_param("s", $voter_key);
        $check->execute();
        $check->bind_result($exists);
        $check->fetch();
        $check->close();

        if ($exists == 0) break;
    }

    // Step 3: Insert into approved_voters
    $insert = $conn->prepare("INSERT INTO approved_voters 
        (student_id, last_name, first_name, middle_name, course, year_level, email, voter_key)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    $insert->bind_param("ssssssss", $student_id, $last_name, $first_name, $middle_name, $course, $year_level, $email, $voter_key);

    if ($insert->execute()) {
        $insert->close();

        // Step 4: Delete from voter_request
        $delete = $conn->prepare("DELETE FROM voter_request WHERE id = ?");
        $delete->bind_param("i", $id);
        $delete->execute();
        $delete->close();

        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Failed to insert approved voter."]);
        $insert->close();
    }
} else {
    $stmt->close();
    echo json_encode(["success" => false, "error" => "Voter not found."]);
}

$conn->close();
?>
