<?php
require_once 'config.php';

// Get all parking sessions with user details
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

$sessions = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Parking System Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .stats-card {
            transition: transform 0.2s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        #sessionTable {
            animation: fadeIn 0.5s;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand">
                <i class="fas fa-parking"></i> 
                Parking System Dashboard
            </span>
            <div>
                <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#addCardModal">
                    <i class="fas fa-plus"></i> Add Card
                </button>
                <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#apiDocsModal">
                    <i class="fas fa-code"></i> API Docs
                </button>
                <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#testApiModal">
                    <i class="fas fa-vial"></i> Test API
                </button>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Stats Row -->
        <div class="row mb-4">
            <?php
            // Get stats
            $active_sessions = $conn->query("SELECT COUNT(*) as count FROM parking_sessions WHERE status='active'")->fetch_assoc()['count'];
            $total_revenue = $conn->query("SELECT SUM(amount_charged) as total FROM parking_sessions WHERE status='completed'")->fetch_assoc()['total'] ?? 0;
            $total_cards = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
            ?>
            <div class="col-md-4">
                <div class="card stats-card bg-primary text-white">
                    <div class="card-body">
                        <h5><i class="fas fa-car"></i> Active Sessions</h5>
                        <h2><?php echo $active_sessions; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card bg-success text-white">
                    <div class="card-body">
                        <h5><i class="fas fa-money-bill"></i> Total Revenue</h5>
                        <h2><?php echo number_format($total_revenue, 2); ?> RWF</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card bg-info text-white">
                    <div class="card-body">
                        <h5><i class="fas fa-credit-card"></i> Total Cards</h5>
                        <h2><?php echo $total_cards; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row">
            <!-- Sessions Table -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-history"></i> Recent Parking Sessions
                        </h5>
                    </div>
                    <div class="card-body" id="sessionsTableContainer">
                        <?php if($sessions->num_rows > 0): ?>
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
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Cards List -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-credit-card"></i> Registered Cards
                        </h5>
                    </div>
                    <div class="card-body" id="cardsListContainer">
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
                                    <?php
                                    $cards_query = "SELECT card_id, name, balance FROM users ORDER BY created_at DESC";
                                    $cards = $conn->query($cards_query);
                                    while($card = $cards->fetch_assoc()): 
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($card['card_id']); ?></td>
                                        <td><?php echo htmlspecialchars($card['name']); ?></td>
                                        <td><?php echo number_format($card['balance'], 2); ?> RWF</td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Card Modal -->
    <div class="modal fade" id="addCardModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Add New Card</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addCardForm" onsubmit="return addNewCard(event)">
                        <div class="mb-3">
                            <label class="form-label">Card ID</label>
                            <input type="text" class="form-control" id="newCardId" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Owner Name</label>
                            <input type="text" class="form-control" id="ownerName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Initial Balance (RWF)</label>
                            <input type="number" class="form-control" id="initialBalance" value="0" min="0" required>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-plus"></i> Add Card
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- API Documentation Modal -->
    <div class="modal fade" id="apiDocsModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-code"></i> API Documentation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6>Entry Endpoint</h6>
                    <pre class="bg-light p-3 rounded">
POST /entry.php
Content-Type: application/json

{
    "card_id": "CARD123"
}

Response:
{
    "status": "success|error",
    "message": "Entry recorded|Error message"
}</pre>

                    <h6 class="mt-4">Exit Endpoint</h6>
                    <pre class="bg-light p-3 rounded">
POST /exit.php
Content-Type: application/json

{
    "card_id": "CARD123"
}

Response:
{
    "status": "success|error",
    "message": "Exit processed|Error message",
    "amount_charged": 1000,
    "new_balance": 4000
}</pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Test API Modal -->
    <div class="modal fade" id="testApiModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-vial"></i> Test API</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="input-group mb-3">
                        <input type="text" id="testCardId" class="form-control" placeholder="Enter Card ID">
                        <button class="btn btn-success" onclick="testEntry()">Test Entry</button>
                        <button class="btn btn-danger" onclick="testExit()">Test Exit</button>
                    </div>
                    <pre id="apiResponse" class="bg-light p-3 rounded">Response will appear here...</pre>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to refresh data
        async function refreshData() {
            try {
                const response = await fetch('api/dashboard_data.php');
                const data = await response.json();
                
                // Update tables
                document.getElementById('sessionsTableContainer').innerHTML = data.sessions_table;
                document.getElementById('cardsListContainer').innerHTML = data.cards_table;
                
                // Update stats
                document.querySelector('.bg-primary h2').textContent = data.active_sessions;
                document.querySelector('.bg-success h2').textContent = data.total_revenue + ' RWF';
                document.querySelector('.bg-info h2').textContent = data.total_cards;
            } catch (error) {
                console.error('Error refreshing data:', error);
            }
        }

        async function addNewCard(event) {
            event.preventDefault();
            
            const cardId = document.getElementById('newCardId').value;
            const ownerName = document.getElementById('ownerName').value;
            const initialBalance = document.getElementById('initialBalance').value;

            try {
                const response = await fetch('api/add_card.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        card_id: cardId,
                        name: ownerName,
                        balance: initialBalance
                    })
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('addCardModal')).hide();
                    
                    // Show success message
                    alert('Card added successfully!');
                    
                    // Reset form
                    document.getElementById('addCardForm').reset();
                    
                    // Refresh data
                    refreshData();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }

        async function testEntry() {
            const cardId = document.getElementById('testCardId').value;
            if (!cardId) {
                alert('Please enter a Card ID');
                return;
            }
            await makeApiCall('entry.php', cardId);
            refreshData(); // Refresh after entry
        }

        async function testExit() {
            const cardId = document.getElementById('testCardId').value;
            if (!cardId) {
                alert('Please enter a Card ID');
                return;
            }
            await makeApiCall('exit.php', cardId);
            refreshData(); // Refresh after exit
        }

        async function makeApiCall(endpoint, cardId) {
            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ card_id: cardId })
                });
                const data = await response.json();
                document.getElementById('apiResponse').innerText = JSON.stringify(data, null, 2);
                return data;
            } catch (error) {
                document.getElementById('apiResponse').innerText = 'Error: ' + error.message;
                throw error;
            }
        }

        // Auto refresh every 30 seconds
        setInterval(refreshData, 30000);
    </script>
</body>
</html>