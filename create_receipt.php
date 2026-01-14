<?php
require_once 'config/config.php';
require_once 'config/db.php';
require_once 'includes/functions.php';

require_login();

$page_title = 'Create Receipt';
$error = '';

// Fetch all inventory items for the dropdown
$stmt = $pdo->query("SELECT id, name, sku, unit_price, quantity FROM inventory ORDER BY name");
$inventory_items = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $customer_name = clean_input($_POST['customer_name']);
    $status = clean_input($_POST['status']);
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
    $items = $_POST['items']; // Array of item ids
    $quantities = $_POST['quantities']; // Array
    $prices = $_POST['prices']; // Array

    if (empty($items) || empty($quantities)) {
        $error = "Please add at least one item.";
    } else {
        try {
            $pdo->beginTransaction();

            // Calculate total
            $total_amount = 0;
            for ($i = 0; $i < count($items); $i++) {
                $total_amount += (float)$quantities[$i] * (float)$prices[$i];
            }

            // Create Receipt
            $receipt_number = 'REC-' . strtoupper(uniqid());
            $stmt = $pdo->prepare("INSERT INTO receipts (receipt_number, type, total_amount, customer_name, status, due_date, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$receipt_number, $type, $total_amount, $customer_name, $status, $due_date, $_SESSION['user_id']]);
            $receipt_id = $pdo->lastInsertId();

            // Process Items
            $stmt_item = $pdo->prepare("INSERT INTO receipt_items (receipt_id, inventory_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
            $stmt_update_inv = $pdo->prepare("UPDATE inventory SET quantity = quantity + ? WHERE id = ?");

            for ($i = 0; $i < count($items); $i++) {
                $inv_id = (int)$items[$i];
                $qty = (int)$quantities[$i];
                $price = (float)$prices[$i];

                if ($inv_id > 0 && $qty > 0) {
                    $stmt_item->execute([$receipt_id, $inv_id, $qty, $price]);

                    // Update Inventory
                    $change = ($type === 'inbound') ? $qty : -$qty;
                    $stmt_update_inv->execute([$change, $inv_id]);
                }
            }

            $pdo->commit();
            flash('main_flash', 'Receipt created successfully!');
            redirect('receipts.php');

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error creating receipt: " . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Create New Receipt</h2>
    <a href="receipts.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="card shadow">
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" id="receiptForm">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Receipt Type</label>
                    <select name="type" class="form-select" required>
                        <option value="outbound">Outbound (Sale/Usage)</option>
                        <option value="inbound">Inbound (Purchase/Return)</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Customer / Supplier Name</label>
                    <input type="text" name="customer_name" class="form-control" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="paid">Paid</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control">
                </div>
            </div>
            
            <h4 class="mt-4 mb-3">Items</h4>
            <table class="table table-bordered table-compact" id="itemsTable">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th width="150">Quantity</th>
                        <th width="150">Unit Price</th>
                        <th width="150">Total</th>
                        <th width="50"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <select name="items[]" class="form-select item-select" required onchange="updatePrice(this)">
                                <option value="">Select Item</option>
                                <?php foreach ($inventory_items as $item): ?>
                                    <option value="<?php echo $item['id']; ?>" data-price="<?php echo $item['unit_price']; ?>">
                                        <?php echo htmlspecialchars($item['name']) . ' (' . $item['sku'] . ') - Stock: ' . $item['quantity']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input type="number" name="quantities[]" class="form-control qty-input" min="1" value="1" onchange="calculateRowTotal(this)" required data-validate="required">
                            <div class="invalid-feedback">Enter quantity.</div>
                        </td>
                        <td>
                            <div class="input-group">
                                <span class="input-group-text">₦</span>
                                <input type="text" name="prices[]" class="form-control price-input money" inputmode="decimal" placeholder="0.00" onchange="calculateRowTotal(this)" required data-validate="required">
                            </div>
                            <div class="invalid-feedback">Enter price.</div>
                        </td>
                        <td>
                            <input type="text" class="form-control row-total" readonly placeholder="₦0.00">
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <div class="mb-3">
                <button type="button" class="btn btn-success btn-sm" onclick="addRow()"><i class="fas fa-plus"></i> Add Row</button>
            </div>
            
            <div class="row justify-content-end">
                <div class="col-md-4">
                    <table class="table table-bordered">
                        <tr>
                            <th>Grand Total</th>
                            <td><strong id="grandTotal">₦0.00</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Create Receipt</button>
            </div>
        </form>
    </div>
</div>

<script>
function updatePrice(select) {
    var price = $(select).find(':selected').data('price');
    var row = $(select).closest('tr');
    row.find('.price-input').val(price);
    calculateRowTotal(select);
}

function calculateRowTotal(element) {
    var row = $(element).closest('tr');
    var qty = parseFloat(row.find('.qty-input').val()) || 0;
    var price = parseFloat((row.find('.price-input').val() || '').toString().replace(/,/g, '')) || 0;
    var total = qty * price;
    row.find('.row-total').val('₦' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
    calculateGrandTotal();
}

function calculateGrandTotal() {
    var total = 0;
    $('.row-total').each(function() {
        var v = ($(this).val() || '').toString().replace(/₦/g, '').replace(/,/g, '');
        total += parseFloat(v) || 0;
    });
    $('#grandTotal').text('₦' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
}

function addRow() {
    var row = $('#itemsTable tbody tr:first').clone();
    row.find('input').val('');
    row.find('.qty-input').val(1);
    row.find('.row-total').val('₦0.00');
    $('#itemsTable tbody').append(row);
}

function removeRow(btn) {
    if ($('#itemsTable tbody tr').length > 1) {
        $(btn).closest('tr').remove();
        calculateGrandTotal();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
