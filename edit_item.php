<?php
require_once 'config/config.php';
require_once 'config/db.php';
require_once 'includes/functions.php';

require_login();

if (!isset($_GET['id'])) {
    redirect('inventory.php');
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM inventory WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    redirect('inventory.php');
}

$page_title = 'Edit Inventory Item';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean_input($_POST['name']);
    $sku = clean_input($_POST['sku']);
    $category = clean_input($_POST['category']);
    $quantity = (int)$_POST['quantity'];
    $price = (float)$_POST['unit_price'];
    $location = clean_input($_POST['location']);
    $description = clean_input($_POST['description']);
    $low_stock = (int)$_POST['low_stock_threshold'];
    $barcode = clean_input($_POST['barcode_data']);

    // Image Upload
    $image_path = $item['image_path'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $dest = UPLOAD_PATH . 'inventory/' . $new_filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                // Delete old image if exists
                if ($image_path && file_exists(ROOT_PATH . $image_path)) {
                    unlink(ROOT_PATH . $image_path);
                }
                $image_path = 'uploads/inventory/' . $new_filename;
            }
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE inventory SET name=?, sku=?, category=?, quantity=?, unit_price=?, location=?, barcode_data=?, description=?, low_stock_threshold=?, image_path=? WHERE id=?");
        $stmt->execute([$name, $sku, $category, $quantity, $price, $location, $barcode, $description, $low_stock, $image_path, $id]);
        log_activity('edit_item', 'Edited item ID ' . $id . ' (' . $name . ')');
        flash('main_flash', 'Item updated successfully!');
        redirect('inventory.php');
    } catch (PDOException $e) {
        $error = "Error updating item: " . $e->getMessage();
    }
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Edit Item</h2>
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
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($item['name']); ?>" data-validate="required" required>
                    <div class="invalid-feedback">This field is required.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">SKU</label>
                    <input type="text" name="sku" class="form-control" value="<?php echo htmlspecialchars($item['sku']); ?>" data-validate="required" required>
                    <div class="form-text">Unique code used to identify the item.</div>
                    <div class="invalid-feedback">This field is required.</div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Category</label>
                    <input type="text" name="category" class="form-control" value="<?php echo htmlspecialchars($item['category']); ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity" class="form-control" value="<?php echo $item['quantity']; ?>" min="0" data-validate="required">
                    <div class="invalid-feedback">Please enter a valid quantity.</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Unit Price</label>
                    <div class="input-group">
                        <span class="input-group-text">₦</span>
                        <input type="text" name="unit_price" class="form-control money" value="<?php echo number_format($item['unit_price'], 2); ?>" inputmode="decimal" placeholder="0.00" data-validate="required">
                    </div>
                    <div class="form-text">Enter amount in Naira (e.g., 12,500.00).</div>
                    <div class="invalid-feedback">Please enter a valid amount.</div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($item['location']); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Barcode Data</label>
                    <input type="text" name="barcode_data" class="form-control" value="<?php echo htmlspecialchars($item['barcode_data']); ?>">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($item['description']); ?></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Low Stock Threshold</label>
                    <input type="number" name="low_stock_threshold" class="form-control" value="<?php echo $item['low_stock_threshold']; ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Current Image</label>
                    <?php if ($item['image_path']): ?>
                        <div class="mb-2">
                            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="Img" style="height: 100px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" class="form-control">
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Update Item</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
