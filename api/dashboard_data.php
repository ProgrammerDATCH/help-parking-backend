<?php
header('Content-Type: application/json');
require_once '../config.php';

// Get stats
$active_sessions = $conn->query("SELECT COUNT(*) as count FROM parking_sessions WHERE status='active'")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(amount_charged) as total FROM parking_sessions WHERE status='completed'")->fetch_assoc()['total'] ?? 0;
$total_cards = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];

// Get sessions table HTML
ob_start();
$sessions = $conn->query("
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
");

if($sessions->num_rows > 0): ?>
<div class="table-responsive">
    <table class="table table-striped table-hover" id="sessionTable">
        <thead class="table-dark">
            <tr>
                <th>Session ID</th>
                <th>User</th>
                <th>Card ID</th>
                <th>Entry Time</th>
                <th>Exit Time</th>
                <th>Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $sessions->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['session_id']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td>
                    <span class="badge bg-primary">
                        <?php echo htmlspecialchars($row['card_id']); ?>
                    </span>
                </td>
                <td>
                    <i class="fas fa-clock"></i>
                    <?php echo date('Y-m-d H:i:s', strtotime($row['entry_time'])); ?>
                </td>
                <td>
                    <?php if($row['exit_time']): ?>
                        <i class="fas fa-clock"></i>
                        <?php echo date('Y-m-d H:i:s', strtotime($row['exit_time'])); ?>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">Active</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($row['amount_charged']): ?>
                        <span class="badge bg-success">
                            <?php echo number_format($row['amount_charged'], 2); ?> RWF
                        </span>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($row['status'] == 'active'): ?>
                        <span class="badge bg-warning text-dark">Active</span>
                    <?php else: ?>
                        <span class="badge bg-success">Completed</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> No parking sessions found.
</div>
<?php endif;
$sessions_table = ob_get_clean();

// Get cards table HTML
ob_start();
$cards = $conn->query("SELECT card_id, name, balance FROM users ORDER BY created_at DESC");
?>
<div class="table-responsive">
    <table class="table table-sm" id="cardsTable">
        <thead>
            <tr>
                <th>Card ID</th>
                <th>Owner</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            <?php while($card = $cards->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($card['card_id']); ?></td>
                <td><?php echo htmlspecialchars($card['name']); ?></td>
                <td><?php echo number_format($card['balance'], 2); ?> RWF</td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php
$cards_table = ob_get_clean();

// Return JSON response
echo json_encode([
    'status' => 'success',
    'active_sessions' => $active_sessions,
    'total_revenue' => number_format($total_revenue, 2),
    'total_cards' => $total_cards,
    'sessions_table' => $sessions_table,
    'cards_table' => $cards_table
]);
?>