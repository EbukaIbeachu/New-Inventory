<?php
require_once 'config/config.php';
require_once 'config/db.php';
require_once 'includes/functions.php';

require_admin();

$page_title = 'Add Inventory Item';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean_input($_POST['name']);
    $sku = clean_input($_POST['sku']);
    $category = clean_input($_POST['category']);
    $quantity = (int)$_POST['quantity'];
    $price = (float)$_POST['unit_price'];
    $location = clean_input($_POST['location']);
    $description = clean_input($_POST['description']);
    $low_stock = (int)$_POST['low_stock_threshold'];
    
    // Barcode: if empty, generate one from SKU
    $barcode = clean_input($_POST['barcode_data']);
    if (empty($barcode)) {
        $barcode = $sku;
    }

    // Image Upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $dest = UPLOAD_PATH . 'inventory/' . $new_filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $image_path = 'uploads/inventory/' . $new_filename;
            }
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO inventory (name, sku, category, quantity, unit_price, location, barcode_data, description, low_stock_threshold, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $sku, $category, $quantity, $price, $location, $barcode, $description, $low_stock, $image_path]);
        $itemId = (int)$pdo->lastInsertId();
        log_activity('add_item', 'Added item ID ' . $itemId . ' (' . $name . ')');
        flash('main_flash', 'Item added successfully!');
        redirect('inventory.php');
    } catch (PDOException $e) {
        $error = "Error adding item: " . $e->getMessage();
    }
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Add New Item</h2>
    <a href="inventory.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="card shadow">
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Item Name</label>
                    <input type="text" name="name" class="form-control" data-validate="required" required>
                    <div class="invalid-feedback">This field is required.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">SKU (Stock Keeping Unit)</label>
                    <input type="text" name="sku" class="form-control" data-validate="required" required>
                    <div class="form-text">Unique code used to identify the item.</div>
                    <div class="invalid-feedback">This field is required.</div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Category</label>
                    <input type="text" name="category" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity" class="form-control" value="0" min="0" data-validate="required">
                    <div class="invalid-feedback">Please enter a valid quantity.</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Unit Price</label>
                    <div class="input-group">
                        <span class="input-group-text">₦</span>
                        <input type="text" name="unit_price" class="form-control money" value="0.00" inputmode="decimal" placeholder="0.00" data-validate="required">
                    </div>
                    <div class="form-text">Enter amount in Naira (e.g., 12,500.00).</div>
                    <div class="invalid-feedback">Please enter a valid amount.</div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-control" placeholder="e.g. Aisle 3, Shelf B">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Barcode Data (Auto-generated from SKU if empty)</label>
                    <input type="text" name="barcode_data" class="form-control">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Low Stock Threshold</label>
                    <input type="number" name="low_stock_threshold" class="form-control" value="10">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Image</label>
                    <input type="file" name="image" class="form-control">
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Save Item</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
