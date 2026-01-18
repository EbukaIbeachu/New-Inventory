<?php

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
            <table class="table table-bordered table-striped table-sm datatable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Receipt #</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Customer & Phone</th>
                        <th>Status</th>
                        <th>Total Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Mark overdue outbound receipts before listing
                    $update = $pdo->prepare("
                        UPDATE receipts
                        SET status = 'overdue'
                        WHERE type = 'outbound'
                          AND status = 'unpaid'
                          AND due_date IS NOT NULL
                          AND due_date < CURDATE()
                    ");
                    $update->execute();

                    // Fetch and display receipts
                    $stmt = $pdo->query("SELECT * FROM receipts ORDER BY receipt_date DESC");
                    $receipts_found = false;
                    while ($row = $stmt->fetch()):
                        $receipts_found = true;
                    ?>
                    <tr>
                        <td>
                            <a href="view_receipt.php?id=<?php echo $row['id']; ?>" class="text-decoration-underline">
                                <?php echo htmlspecialchars($row['receipt_number']); ?>
                            </a>
                        </td>
                        <td>
                            <span class="badge <?php echo $row['type'] === 'inbound' ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                <?php echo ucfirst($row['type']); ?>
                            </span>
                        </td>
                        <td><?php echo date('Y-m-d H:i', strtotime($row['receipt_date'])); ?></td>
                        <td><?php echo htmlspecialchars($row['customer_name']); ?><br><small class="text-muted"><?php echo htmlspecialchars($row['customer_phone']); ?></small></td>
                        <td>
                            <span class="badge <?php echo $row['status'] === 'paid' ? 'bg-success' : ($row['status'] === 'overdue' ? 'bg-danger' : 'bg-warning'); ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                            <?php
                            $statusLower = strtolower($row['status']);
                            if (($statusLower === 'unpaid' || $statusLower === 'overdue') && !empty($row['due_date'])): ?>
                                <div><small class="text-muted">Due: <?php echo $row['due_date']; ?></small></div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo number_format($row['total_amount'], 2); ?></td>
                        <td>
                            <a href="view_receipt.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info text-white" title="View"><i class="fas fa-eye"></i></a>
                            <a href="view_receipt.php?id=<?php echo $row['id']; ?>&print=true" target="_blank" class="btn btn-sm btn-secondary" title="Print"><i class="fas fa-print"></i></a>
                            <a href="view_receipt.php?id=<?php echo $row['id']; ?>&download=1" class="btn btn-sm btn-outline-secondary" title="Download"><i class="fas fa-download"></i></a>
                            <a href="delete_receipt.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger ms-1" onclick="return confirm('Are you sure you want to delete this receipt? This will restore inventory quantities.');" title="Delete"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if (!$receipts_found): ?>
                        <tr><td colspan="7" class="text-center text-muted">No receipts found or error fetching receipts.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<style>
@media (max-width: 576px) {
    .table-responsive, .table {
        font-size: 0.95rem;
    }
    .table th, .table td {
        padding: 0.4rem 0.3rem;
        white-space: normal;
    }
    .btn {
        font-size: 0.85rem;
        padding: 0.3rem 0.5rem;
    }
    .badge {
        font-size: 0.8rem;
        padding: 0.3em 0.5em;
    }
    /* Hide less important columns on mobile */
    .table th:nth-child(2), .table td:nth-child(2) /* Type */
    {
        display: none;
    }
    /* Stack table rows as cards */
    .table-bordered > tbody > tr {
        display: block;
        margin-bottom: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    }
    .table-bordered > tbody > tr > td {
        display: block;
        width: 100%;
        border: none;
        border-bottom: 1px solid #dee2e6;
    }
    .table-bordered > tbody > tr > td:last-child {
        border-bottom: none;
    }
    .table-bordered > thead {
        display: none;
    }
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
