<?php
header('Content-Type: application/json');
require_once 'config.php';

function handleEntry($card_id) {
    global $conn;
    
    // Check if card exists
    $stmt = $conn->prepare("SELECT uid, balance FROM users WHERE card_id = ?");
    $stmt->bind_param("s", $card_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['status' => 'error', 'message' => 'Invalid card'];
    }
    
    // Check if there's already an active session
    $stmt = $conn->prepare("SELECT session_id FROM parking_sessions WHERE card_id = ? AND status = 'active'");
    $stmt->bind_param("s", $card_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        return ['status' => 'error', 'message' => 'Active session exists'];
    }
    
    // Create new parking session
    $stmt = $conn->prepare("INSERT INTO parking_sessions (card_id, entry_time) VALUES (?, NOW())");
    $stmt->bind_param("s", $card_id);
    
    if ($stmt->execute()) {
        return ['status' => 'success', 'message' => 'Entry recorded'];
    }
    
    return ['status' => 'error', 'message' => 'Failed to record entry'];
}

// Handle POST request
$data = json_decode(file_get_contents('php://input'), true);
echo json_encode(handleEntry($data['card_id']));
?>