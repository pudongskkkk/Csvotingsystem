<?php
header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = "localhost";
$user = "root";
$pass = "";
$db = "cs_election";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

$id = $_POST['id'] ?? null;
$name = $_POST['name'] ?? '';
$partylist = $_POST['partylist'] ?? '';
$student_id = $_POST['student_id'] ?? '';  // new
$position = $_POST['position'] ?? '';
$photoUpdated = isset($_FILES['photo']) && $_FILES['photo']['tmp_name'] !== '';

$photoBlob = null;
$photoPath = null;

if ($photoUpdated) {
    $photoTmp = $_FILES['photo']['tmp_name'];
    $photoName = basename($_FILES['photo']['name']);
    $photoBlob = file_get_contents($photoTmp);
    $uploadDir = "../candidate_photo/";
    $photoPath = $uploadDir . time() . "_" . $photoName;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (!move_uploaded_file($photoTmp, $photoPath)) {
        echo json_encode(["success" => false, "error" => "Failed to move uploaded photo."]);
        exit;
    }
}

if ($id) {
    if ($photoUpdated) {
        $stmt = $conn->prepare("UPDATE candidates SET name=?, partylist=?, student_id=?, position=?, photo=?, photo_path=? WHERE id=?");
        $stmt->bind_param("ssssssi", $name, $partylist, $student_id, $position, $photoBlob, $photoPath, $id);
    } else {
        $stmt = $conn->prepare("UPDATE candidates SET name=?, partylist=?, student_id=?, position=? WHERE id=?");
        $stmt->bind_param("ssssi", $name, $partylist, $student_id, $position, $id);
    }

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Error updating candidate: " . $stmt->error]);
    }

    $stmt->close();

} else {
    if (!$photoUpdated) {
        echo json_encode(["success" => false, "error" => "Photo is required for new candidates."]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO candidates (name, partylist, student_id, position, photo, photo_path) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $partylist, $student_id, $position, $photoBlob, $photoPath);

    if ($stmt->execute()) {
        $voteStmt = $conn->prepare("INSERT INTO voting_count (candidate_name, partylist, student_id, position, photo, photo_path, votes) VALUES (?, ?, ?, ?, ?, ?, 0)");
        $voteStmt->bind_param("ssssss", $name, $partylist, $student_id, $position, $photoBlob, $photoPath);

        if ($voteStmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Candidate saved, but failed to insert into voting_count: " . $voteStmt->error]);
        }

        $voteStmt->close();
    } else {
        echo json_encode(["success" => false, "error" => "Error adding candidate: " . $stmt->error]);
    }

    $stmt->close();
}

$conn->close();

