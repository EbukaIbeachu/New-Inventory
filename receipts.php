<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$page_title = 'Receipts';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Receipt Management</h2>
    <a href="create_receipt.php" class="btn btn-primary"><i class="fas fa-plus"></i> Create Receipt</a>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped datatable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Receipt #</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Customer/Supplier</th>
                        <th>Status</th>
                        <th>Total Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM receipts ORDER BY receipt_date DESC");
                    while ($row = $stmt->fetch()):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['receipt_number']); ?></td>
                        <td>
                            <span class="badge <?php echo $row['type'] === 'inbound' ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                <?php echo ucfirst($row['type']); ?>
                            </span>
                        </td>
                        <td><?php echo date('Y-m-d H:i', strtotime($row['receipt_date'])); ?></td>
                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                        <td>
                             <span class="badge <?php echo $row['status'] === 'paid' ? 'bg-success' : ($row['status'] === 'overdue' ? 'bg-danger' : 'bg-warning'); ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                        </td>
                        <td><?php echo number_format($row['total_amount'], 2); ?></td>
                        <td>
                            <a href="view_receipt.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info text-white"><i class="fas fa-eye"></i></a>
                            <a href="view_receipt.php?id=<?php echo $row['id']; ?>&print=true" target="_blank" class="btn btn-sm btn-secondary"><i class="fas fa-print"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
