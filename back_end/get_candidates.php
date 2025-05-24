<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "cs_election";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT id, name, partylist, position, photo_path FROM candidates";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
            <td><img src='{$row['photo_path']}' alt='Photo' style='width: 60px; height: 60px; object-fit: cover; border-radius: 5px;'></td>
            <td>" . htmlspecialchars($row['name']) . "</td>
            <td>" . htmlspecialchars($row['partylist']) . "</td>
            <td>" . htmlspecialchars($row['position']) . "</td>
            <td>
                <button 
                    class='btn btn-sm btn-warning me-1 edit-btn' 
                    data-id='{$row['id']}'
                    data-name=\"" . htmlspecialchars($row['name'], ENT_QUOTES) . "\"
                    data-party=\"" . htmlspecialchars($row['partylist'], ENT_QUOTES) . "\"
                    data-position=\"" . htmlspecialchars($row['position'], ENT_QUOTES) . "\"
                    data-photo='{$row['photo_path']}'
                    data-bs-toggle='modal' 
                    data-bs-target='#addCandidateModal'>
                    <i class='bi bi-pencil-square'></i>
                </button>
            </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='5' class='text-center'>No candidates found.</td></tr>";
}

$conn->close();
?>
