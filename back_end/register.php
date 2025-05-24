<?php
// Enable error reporting (optional for development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set JSON response
header('Content-Type: application/json');

// Use exception-based error handling for mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $db = 'cs_election';

    $conn = new mysqli($host, $user, $pass, $db);

    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['studentId'], $data['course'], $data['yearLevel'], $data['lastName'], $data['firstName'], $data['middleName'], $data['email'])) {
        echo json_encode(["success" => false, "error" => "Incomplete input data."]);
        exit;
    }

    $studentId = $data['studentId'];
    $course = $data['course'];
    $yearLevel = $data['yearLevel'];
    $lastName = $data['lastName'];
    $firstName = $data['firstName'];
    $middleName = $data['middleName'];
    $email = $data['email'];

    $sql = "INSERT INTO voter_request (student_id, course, year_level, last_name, first_name, middle_name, email)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssissss", $studentId, $course, $yearLevel, $lastName, $firstName, $middleName, $email);
    $stmt->execute();

    echo json_encode(["success" => true]);
} catch (mysqli_sql_exception $e) {
    // Handle duplicate email error
    if (strpos($e->getMessage(), 'Duplicate entry') !== false && strpos($e->getMessage(), 'email') !== false) {
        echo json_encode(["success" => false, "error" => "This email is already registered."]);
    } else {
        echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
    }
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>
