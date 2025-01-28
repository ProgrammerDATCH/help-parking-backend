<?php
header('Content-Type: application/json');
require_once '../config.php';

$query = "
    SELECT 
        ps.session_id,
        u.name,
        u.card_id,
        ps.entry_time,
        ps.exit_time,
        ps.amount_charged,
        ps.status
    FROM parking_sessions ps
    JOIN users u ON ps.card_id = u.card_id
    ORDER BY ps.entry_time DESC
";

$result = $conn->query($query);
$sessions = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $sessions[] = $row;
    }
    echo json_encode([
        'status' => 'success',
        'data' => $sessions
    ]);
} else {
    echo json_encode([
        'status' => 'success',
        'data' => [],
        'message' => 'No records found'
    ]);
}
?>