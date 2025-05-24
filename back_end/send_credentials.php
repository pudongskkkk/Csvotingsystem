<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // adjust path if needed

header("Content-Type: application/json");

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'cs_election';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Database connection failed."]);
    exit;
}

// Get voter IDs from POST
$data = json_decode(file_get_contents("php://input"), true);
$ids = $data['ids'] ?? [];

if (empty($ids)) {
    echo json_encode(["success" => false, "error" => "No voter IDs provided."]);
    exit;
}

// Build SQL query
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids));
$stmt = $conn->prepare("SELECT student_id, last_name, first_name, middle_name, course, year_level, email, voter_key FROM approved_voters WHERE id IN ($placeholders)");
$stmt->bind_param($types, ...$ids);
$stmt->execute();
$result = $stmt->get_result();

$success = 0;
$failures = [];

while ($row = $result->fetch_assoc()) {
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';                // Replace if not using Gmail
        $mail->SMTPAuth   = true;
        $mail->Username   = 'mgg011009@gmail.com';          // ✅ your sender email
        $mail->Password   = 'yacoknhdnihkxyvc';             // ✅ your Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Email content
        $mail->setFrom('your-email@gmail.com', 'CS Election Admin');
        $mail->addAddress($row['email'], $row['first_name'] . ' ' . $row['last_name']);

        $mail->isHTML(true);
        $mail->Subject = 'Your Voter Credentials';
        $mail->Body = "
            <p>Dear <strong>{$row['first_name']} {$row['last_name']}</strong>,</p>
            <p>You have been approved as a voter. Below are your login credentials:</p>
            <ul>
                <li><strong>Student ID:</strong> {$row['student_id']}</li>
                <li><strong>Full Name:</strong> {$row['first_name']} {$row['middle_name']} {$row['last_name']}</li>
                <li><strong>Course:</strong> {$row['course']}</li>
                <li><strong>Year Level:</strong> {$row['year_level']}</li>
                <li><strong>Voter Key:</strong> <span style='color:blue;'>{$row['voter_key']}</span></li>
            </ul>
            <p>Please keep this information confidential.</p>
            <p>Thank you,<br>CS Election Committee</p>
        ";

        $mail->send();
        $success++;
    } catch (Exception $e) {
        $failures[] = $row['email'];
    }
}

echo json_encode([
    "success" => true,
    "sent" => $success,
    "failed" => $failures
]);

$conn->close();
