<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

if (!isset($_GET['id'])) {
    redirect('receipts.php');
}

$id = (int)$_GET['id'];
$is_print = isset($_GET['print']);

// Fetch Receipt
$stmt = $pdo->prepare("SELECT r.*, u.username FROM receipts r JOIN users u ON r.created_by = u.id WHERE r.id = ?");
$stmt->execute([$id]);
$receipt = $stmt->fetch();

if (!$receipt) {
    redirect('receipts.php');
}

// Fetch Items
$stmt = $pdo->prepare("SELECT ri.*, i.name, i.sku FROM receipt_items ri JOIN inventory i ON ri.inventory_id = i.id WHERE ri.receipt_id = ?");
$stmt->execute([$id]);
$items = $stmt->fetchAll();

if ($is_print) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Print Receipt - <?php echo $receipt['receipt_number']; ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
        <style>
            body { padding: 20px; font-size: 12pt; }
            .doc-header h2 { margin-bottom: .25rem; }
            .doc-header p { margin: 0; color: #6c757d; }
            .table th, .table td { vertical-align: middle; }
            .totals-row th { background: #f8f9fa; }
            @media print {
                .no-print { display: none !important; }
                @page { margin: 12mm; }
                body { padding: 0; }
                thead { display: table-header-group; }
                tfoot { display: table-footer-group; }
                table { page-break-inside: auto; }
                tr, td, th { page-break-inside: avoid; page-break-after: auto; }
            }
        </style>
    </head>
    <body onload="window.print()">
        <div class="container">
            <div class="row mb-4 doc-header">
                <div class="col-6">
                    <h2><?php echo APP_NAME; ?></h2>
                    <p>Address Line 1<br>City, Country<br>Email: support@example.com</p>
                </div>
                <div class="col-6 text-end">
                    <h3>RECEIPT</h3>
                    <p><strong>#<?php echo $receipt['receipt_number']; ?></strong></p>
                    <svg id="barcode"></svg>
                    <script>JsBarcode("#barcode", "<?php echo $receipt['receipt_number']; ?>", {height: 40, displayValue: false});</script>
                    <p>Date: <?php echo date('Y-m-d', strtotime($receipt['receipt_date'])); ?></p>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-6">
                    <strong>Bill To:</strong><br>
                    <?php echo htmlspecialchars($receipt['customer_name']); ?>
                </div>
                <div class="col-6 text-end">
                    <strong>Type:</strong> <?php echo ucfirst($receipt['type']); ?>
                </div>
            </div>
            
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>SKU</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo htmlspecialchars($item['sku']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>₦<?php echo number_format($item['unit_price'], 2); ?></td>
                        <td>₦<?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end totals-row">Grand Total</th>
                        <th class="totals-row">₦<?php echo number_format($receipt['total_amount'], 2); ?></th>
                    </tr>
                </tfoot>
            </table>
            
            <div class="mt-5 text-center">
                <p>Thank you for your business!</p>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>View Receipt #<?php echo $receipt['receipt_number']; ?></h2>
    <div>
        <a href="receipts.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
        <a href="view_receipt.php?id=<?php echo $id; ?>&print=true" target="_blank" class="btn btn-primary"><i class="fas fa-print"></i> Print</a>
    </div>
</div>

<div class="card shadow">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h5>Details</h5>
                <p><strong>Date:</strong> <?php echo date('Y-m-d H:i', strtotime($receipt['receipt_date'])); ?></p>
                <p><strong>Customer/Supplier:</strong> <?php echo htmlspecialchars($receipt['customer_name']); ?></p>
                <p><strong>Type:</strong> <span class="badge <?php echo $receipt['type'] === 'inbound' ? 'bg-success' : 'bg-warning text-dark'; ?>"><?php echo ucfirst($receipt['type']); ?></span></p>
                <p><strong>Created By:</strong> <?php echo htmlspecialchars($receipt['username']); ?></p>
                <p><strong>Status:</strong> 
                    <span class="badge <?php echo $receipt['status'] === 'paid' ? 'bg-success' : ($receipt['status'] === 'overdue' ? 'bg-danger' : 'bg-warning'); ?>">
                        <?php echo ucfirst($receipt['status']); ?>
                    </span>
                </p>
                <?php if($receipt['due_date']): ?>
                <p><strong>Due Date:</strong> <?php echo $receipt['due_date']; ?></p>
                <?php endif; ?>
            </div>
            <div class="col-md-6 text-end">
                <svg id="barcode-view"></svg>
                <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
                <script>JsBarcode("#barcode-view", "<?php echo $receipt['receipt_number']; ?>", {height: 40, displayValue: true});</script>
            </div>
        </div>
        
        <table class="table table-hover table-compact table-bordered">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>SKU</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo htmlspecialchars($item['sku']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo number_format($item['unit_price'], 2); ?></td>
                    <td><?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-end">Grand Total</th>
                    <th><?php echo number_format($receipt['total_amount'], 2); ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
