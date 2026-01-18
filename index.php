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
// Current 30 days
$stmt = $pdo->query("SELECT COUNT(*) FROM receipts WHERE receipt_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$monthly_receipts = $stmt->fetchColumn();
// Previous 30 days
$stmt = $pdo->query("SELECT COUNT(*) FROM receipts WHERE receipt_date < DATE_SUB(NOW(), INTERVAL 30 DAY) AND receipt_date >= DATE_SUB(NOW(), INTERVAL 60 DAY)");
$prev_monthly_receipts = $stmt->fetchColumn();
// Trend calculation
$receipts_trend = 0;
if ($prev_monthly_receipts > 0) {
    $receipts_trend = (($monthly_receipts - $prev_monthly_receipts) / $prev_monthly_receipts) * 100;
}

// 4. Pending Users (Admin only)
$pending_users = 0;
if (is_admin()) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'pending'");
    $pending_users = $stmt->fetchColumn();
}

// 5. Outbound sales per item (top 10)
$sales_labels = [];
$sales_data = [];
try {
    $sales_stmt = $pdo->query("SELECT i.name AS item_name, SUM(ri.quantity) AS total_qty
        FROM receipt_items ri
        JOIN receipts r ON r.id = ri.receipt_id
        JOIN inventory i ON i.id = ri.inventory_id
        WHERE r.type = 'outbound'
        GROUP BY ri.inventory_id, i.name
        ORDER BY total_qty DESC
        LIMIT 10");
    while ($row = $sales_stmt->fetch()) {
        $sales_labels[] = $row['item_name'];
        $sales_data[] = (int)$row['total_qty'];
    }
} catch (Exception $e) {
    $sales_labels = [];
    $sales_data = [];
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
                <?php if (is_admin()): ?>
                    <small>Value: ₦<?php echo number_format($inv_stats['value'], 2); ?></small>
                <?php endif; ?>
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
                        <div class="kpi-trend mt-1">
                            <?php if ($prev_monthly_receipts == 0 && $monthly_receipts > 0): ?>
                                <span class="trend-up"><i class="fas fa-arrow-up"></i> New</span>
                            <?php elseif ($receipts_trend > 0): ?>
                                <span class="trend-up"><i class="fas fa-arrow-up"></i> <?php echo number_format($receipts_trend, 0); ?>%</span>
                            <?php elseif ($receipts_trend < 0): ?>
                                <span class="trend-down"><i class="fas fa-arrow-down"></i> <?php echo number_format(abs($receipts_trend), 0); ?>%</span>
                            <?php else: ?>
                                <span class="trend-flat"><i class="fas fa-minus"></i> 0%</span>
                            <?php endif; ?>
                        </div>
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

<!-- Quick Actions -->
<div class="row g-3 mb-4">
    <?php if (is_admin()): ?>
    <div class="col-auto">
        <a href="add_item.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Item</a>
    </div>
    <?php endif; ?>
    <div class="col-auto">
        <a href="create_receipt.php" class="btn btn-success"><i class="fas fa-file-invoice"></i> New Receipt</a>
    </div>
    <div class="col-auto">
        <a href="import_export.php" class="btn btn-outline-secondary"><i class="fas fa-file-import"></i> Import/Export</a>
    </div>
    <?php if (is_admin()): ?>
    <div class="col-auto">
        <a href="users.php" class="btn btn-outline-warning"><i class="fas fa-user-shield"></i> Manage Users</a>
    </div>
    <?php endif; ?>
    <div class="col-auto ms-auto">
        <a href="inventory.php" class="btn btn-outline-primary"><i class="fas fa-box"></i> View Inventory</a>
    </div>
    
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Receipts</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive table-sticky">
                    <table class="table table-hover table-compact align-middle" width="100%" cellspacing="0">
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
                                <td>
                                    <a href="<?php echo BASE_URL; ?>view_receipt.php?id=<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['receipt_number']); ?></a>
                                    <span class="row-actions ms-2">
                                        <a class="btn btn-sm btn-light" href="<?php echo BASE_URL; ?>view_receipt.php?id=<?php echo $row['id']; ?>" title="View"><i class="fas fa-eye"></i></a>
                                    </span>
                                </td>
                                <td><?php echo ucfirst($row['type']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($row['receipt_date'])); ?></td>
                                <td>₦<?php echo number_format($row['total_amount'], 2); ?></td>
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

<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Outbound Items Sold (Top 10)</h6>
            </div>
            <div class="card-body">
                <canvas id="outboundSalesChart" height="120"></canvas>
            </div>
        </div>
    </div>
    
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function() {
    var labels = <?php echo json_encode($sales_labels); ?>;
    var data = <?php echo json_encode($sales_data); ?>;
    var canvas = document.getElementById('outboundSalesChart');
    if (!canvas) return;

    if (!labels.length) {
        canvas.parentNode.innerHTML = '<p class="text-muted mb-0">No outbound sales data available yet.</p>';
        return;
    }

    var ctx = canvas.getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Quantity Sold (Outbound)',
                data: data,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
