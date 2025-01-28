<?php
header('Content-Type: application/json');
require_once 'config.php';

function handleExit($card_id) {
    global $conn;
    
    // Get active session with TIMESTAMPDIFF calculation
    $stmt = $conn->prepare("
        SELECT 
            s.session_id,
            s.entry_time,
            u.balance,
            TIMESTAMPDIFF(MINUTE, s.entry_time, NOW()) as duration_minutes
        FROM parking_sessions s 
        JOIN users u ON s.card_id = u.card_id 
        WHERE s.card_id = ? AND s.status = 'active'
    ");
    $stmt->bind_param("s", $card_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['status' => 'error', 'message' => 'No active session found'];
    }
    
    $session = $result->fetch_assoc();
    
    // Get rate per minute
    $rate_result = $conn->query("SELECT rate_per_minute FROM config LIMIT 1");
    $rate = $rate_result->fetch_assoc()['rate_per_minute'];
    
    // Calculate amount based on minutes
    $minutes = max(1, $session['duration_minutes']); // Minimum 1 minute
    $amount = $minutes * $rate;
    
    // Check if user has enough balance
    if ($session['balance'] < $amount) {
        return [
            'status' => 'error',
            'message' => 'Insufficient balance',
            'required_amount' => $amount,
            'current_balance' => $session['balance']
        ];
    }
    
    // Update session and deduct balance
    $conn->begin_transaction();
    try {
        // Update session
        $stmt = $conn->prepare("
            UPDATE parking_sessions 
            SET exit_time = NOW(), 
                amount_charged = ?, 
                status = 'completed' 
            WHERE session_id = ?
        ");
        $stmt->bind_param("di", $amount, $session['session_id']);
        $stmt->execute();
        
        // Update user balance
        $stmt = $conn->prepare("
            UPDATE users 
            SET balance = balance - ? 
            WHERE card_id = ?
        ");
        $stmt->bind_param("ds", $amount, $card_id);
        $stmt->execute();
        
        $conn->commit();
        return [
            'status' => 'success',
            'message' => 'Exit processed',
            'amount_charged' => $amount,
            'new_balance' => $session['balance'] - $amount,
            'minutes_parked' => $minutes
        ];
    } catch (Exception $e) {
        $conn->rollback();
        return ['status' => 'error', 'message' => 'Transaction failed'];
    }
}

// Handle POST request
$data = json_decode(file_get_contents('php://input'), true);
echo json_encode(handleExit($data['card_id']));
?>