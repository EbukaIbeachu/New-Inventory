<?php
require_once 'config/config.php';
require_once 'config/db.php';
require_once 'includes/functions.php';

require_login();

$page_title = 'Dashboard';
include 'includes/header.php';

// Analytics Data
// 1. Total Inventory Count & Value
$stmt = $pdo->query("SELECT COUNT(*) as count, SUM(quantity * unit_price) as value FROM inventory");
$inv_stats = $stmt->fetch();

// 2. Low Stock Items
$stmt = $pdo->query("SELECT COUNT(*) FROM inventory WHERE quantity <= low_stock_threshold");
$low_stock_count = $stmt->fetchColumn();

// 3. Recent Receipts
$stmt = $pdo->query("SELECT COUNT(*) FROM receipts WHERE receipt_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$monthly_receipts = $stmt->fetchColumn();

// 4. Pending Users (Admin only)
$pending_users = 0;
if (is_admin()) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'pending'");
    $pending_users = $stmt->fetchColumn();
}
?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white shadow h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="h6">Total Inventory Items</div>
                        <div class="h3 mb-0"><?php echo $inv_stats['count']; ?></div>
                    </div>
                    <i class="fas fa-boxes fa-2x opacity-50"></i>
                </div>
                <small>Value: ₦<?php echo number_format($inv_stats['value'], 2); ?></small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-danger text-white shadow h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="h6">Low Stock Alerts</div>
                        <div class="h3 mb-0"><?php echo $low_stock_count; ?></div>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
                </div>
                <small><a href="./inventory.php" class="text-white">View Items</a></small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-success text-white shadow h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="h6">Receipts (30 Days)</div>
                        <div class="h3 mb-0"><?php echo $monthly_receipts; ?></div>
                    </div>
                    <i class="fas fa-receipt fa-2x opacity-50"></i>
                </div>
                <small>Recent Activity</small>
            </div>
        </div>
    </div>
    
    <?php if (is_admin()): ?>
    <div class="col-md-3">
        <div class="card bg-warning text-dark shadow h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="h6">Pending Approvals</div>
                        <div class="h3 mb-0"><?php echo $pending_users; ?></div>
                    </div>
                    <i class="fas fa-user-clock fa-2x opacity-50"></i>
                </div>
                <small><a href="./users.php" class="text-dark">Manage Users</a></small>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Receipts</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Receipt #</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT * FROM receipts ORDER BY receipt_date DESC LIMIT 5");
                            while ($row = $stmt->fetch()):
                            ?>
                            <tr>
                                <td><a href="<?php echo BASE_URL; ?>view_receipt.php?id=<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['receipt_number']); ?></a></td>
                                <td><?php echo ucfirst($row['type']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($row['receipt_date'])); ?></td>
                                <td><?php echo number_format($row['total_amount'], 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-danger">Low Stock Items</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php
                    $stmt = $pdo->query("SELECT name, quantity, low_stock_threshold FROM inventory WHERE quantity <= low_stock_threshold LIMIT 5");
                    while ($row = $stmt->fetch()):
                    ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?php echo htmlspecialchars($row['name']); ?>
                        <span class="badge bg-danger rounded-pill"><?php echo $row['quantity']; ?> / <?php echo $row['low_stock_threshold']; ?></span>
                    </li>
                    <?php endwhile; ?>
                </ul>
                <?php if ($stmt->rowCount() == 0): ?>
                    <p class="text-center text-muted mt-3">No low stock items.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
