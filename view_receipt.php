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
$is_download = isset($_GET['download']);

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

if ($is_print || $is_download) {
    // Capture the existing print HTML into a buffer so we can either
    // echo it (for print) or feed it to a PDF generator for download.
    ob_start();
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
    <body<?php echo $is_print && !$is_download ? ' onload="window.print()"' : ''; ?>>
        <div class="container">
            <div class="row mb-4 doc-header">
                <div class="col-6">
                    <h2>Kings Trading Company</h2>
                    <p>Plaza B70 APT Imj Trade Fair Shopping Complex Badagry Expressway<br>Email: Kingstrading19@gmail.com<br>Phone Number : 08034734000</p>
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
                    <?php echo htmlspecialchars($receipt['customer_name']); ?><br>
                    <small class="text-muted"><?php echo htmlspecialchars($receipt['customer_phone']); ?></small>
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
    $html = ob_get_clean();

    if ($is_download) {
        $generated = false;
        $autoload = __DIR__ . '/vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
            if (class_exists('Dompdf\\Dompdf')) {
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $filename = 'receipt-' . preg_replace('/[^A-Za-z0-9_-]/', '_', $receipt['receipt_number']) . '.pdf';
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                echo $dompdf->output();
                $generated = true;
            }
        }

        if (!$generated) {
            // Fallback to HTML download if Dompdf isn't installed
            $filename = 'receipt-' . preg_replace('/[^A-Za-z0-9_-]/', '_', $receipt['receipt_number']) . '.html';
            header('Content-Type: text/html; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            echo $html;
        }
    } else {
        // Normal print view
        echo $html;
    }

    exit();
}

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>View Receipt #<?php echo $receipt['receipt_number']; ?></h2>
    <div>
        <a href="receipts.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
        <a href="view_receipt.php?id=<?php echo $id; ?>&print=true" target="_blank" class="btn btn-primary"><i class="fas fa-print"></i> Print</a>
        <a href="view_receipt.php?id=<?php echo $id; ?>&download=1" class="btn btn-outline-secondary"><i class="fas fa-download"></i> Download</a>
    </div>
</div>

<div class="card shadow">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h5>Details</h5>
                <p><strong>Date:</strong> <?php echo date('Y-m-d H:i', strtotime($receipt['receipt_date'])); ?></p>
                <p><strong>Customer & Phone:</strong> <?php echo htmlspecialchars($receipt['customer_name']); ?> <br><small class="text-muted"> <?php echo htmlspecialchars($receipt['customer_phone']); ?> </small></p>
                <p><strong>Type:</strong> <span class="badge <?php echo $receipt['type'] === 'inbound' ? 'bg-success' : 'bg-warning text-dark'; ?>"><?php echo ucfirst($receipt['type']); ?></span></p>
                <p><strong>Created By:</strong> <?php echo htmlspecialchars($receipt['username']); ?></p>
                <p><strong>Status:</strong> 
                    <select class="form-select form-select-sm w-auto d-inline receipt-status-dropdown" data-id="<?php echo $receipt['id']; ?>">
                        <option value="Paid" <?php if(strtolower($receipt['status'])=='paid') echo 'selected'; ?>>Paid</option>
                        <option value="Unpaid" <?php if(strtolower($receipt['status'])=='unpaid') echo 'selected'; ?>>Unpaid</option>
                        <option value="Overdue" <?php if(strtolower($receipt['status'])=='overdue') echo 'selected'; ?>>Overdue</option>
                    </select>
                    <span id="receipt-status-msg" class="ms-2"></span>
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
        
        <table class="table table-hover table-compact table-bordered table-sm">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    var dropdown = document.querySelector('.receipt-status-dropdown');
    if (dropdown) {
        dropdown.addEventListener('change', function() {
            var receiptId = this.getAttribute('data-id');
            var newStatus = this.value;
            var selectElem = this;
            var msgElem = document.getElementById('receipt-status-msg');
            selectElem.disabled = true;
            fetch('update_receipt_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + encodeURIComponent(receiptId) + '&status=' + encodeURIComponent(newStatus)
            })
            .then(response => response.json())
            .then(data => {
                selectElem.disabled = false;
                if (data.success) {
                    msgElem.textContent = 'Status updated.';
                    msgElem.className = 'text-success ms-2';
                } else {
                    msgElem.textContent = 'Failed to update.';
                    msgElem.className = 'text-danger ms-2';
                }
                setTimeout(function(){ msgElem.textContent = ''; }, 2000);
            })
            .catch(() => {
                selectElem.disabled = false;
                msgElem.textContent = 'Error updating status.';
                msgElem.className = 'text-danger ms-2';
                setTimeout(function(){ msgElem.textContent = ''; }, 2000);
            });
        });
    }
});
</script>


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
    .doc-header h2, .doc-header h3 {
        font-size: 1.1rem;
    }
    /* Hide less important columns on mobile */
    .table th:nth-child(2), .table td:nth-child(2), /* SKU */
    .table th:nth-child(4), .table td:nth-child(4)  /* Unit Price */
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
