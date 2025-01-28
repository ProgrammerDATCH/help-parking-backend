<?php
header('Content-Type: application/json');
require_once '../config.php';

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['card_id']) || !isset($data['name']) || !isset($data['balance'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required fields'
    ]);
    exit;
}

// Check if card already exists
$stmt = $conn->prepare("SELECT card_id FROM users WHERE card_id = ?");
$stmt->bind_param("s", $data['card_id']);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Card ID already exists'
    ]);
    exit;
}

// Add new card
$stmt = $conn->prepare("INSERT INTO users (card_id, name, balance) VALUES (?, ?, ?)");
$stmt->bind_param("ssd", $data['card_id'], $data['name'], $data['balance']);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Card added successfully'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to add card'
    ]);
}