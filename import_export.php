<?php
require_once 'config/config.php';
require_once 'config/db.php';
require_once 'includes/functions.php';

require_login();

$page_title = 'Import / Export';
$message = '';

// Handle Export
if (isset($_POST['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="inventory_export_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Name', 'SKU', 'Category', 'Quantity', 'Unit Price', 'Location', 'Barcode', 'Description', 'Low Stock Threshold']);
    
    $stmt = $pdo->query("SELECT id, name, sku, category, quantity, unit_price, location, barcode_data, description, low_stock_threshold FROM inventory");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

// Handle Import
if (isset($_POST['import'])) {
    if ($_FILES['csv_file']['error'] === 0) {
        $filename = $_FILES['csv_file']['tmp_name'];
        if (($handle = fopen($filename, "r")) !== FALSE) {
            // Skip header
            fgetcsv($handle);
            
            $success_count = 0;
            $error_count = 0;
            
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Assuming standard format: Name, SKU, Category, Quantity, Price, Location, Barcode, Description, Threshold
                // We will ignore ID for new imports to auto-increment, or use it for update if sophisticated
                // For now, simple append logic (or update if SKU exists)
                
                $name = $data[1] ?? ''; // If index 0 is ID, index 1 is name
                $sku = $data[2] ?? '';
                
                if (!$name || !$sku) continue; // Skip invalid
                
                $category = $data[3] ?? '';
                $quantity = (int)($data[4] ?? 0);
                $price = (float)($data[5] ?? 0);
                $location = $data[6] ?? '';
                $barcode = $data[7] ?? '';
                $description = $data[8] ?? '';
                $threshold = (int)($data[9] ?? 10);
                
                // Check if SKU exists
                $stmt = $pdo->prepare("SELECT id FROM inventory WHERE sku = ?");
                $stmt->execute([$sku]);
                if ($stmt->fetch()) {
                    // Update
                    $sql = "UPDATE inventory SET name=?, category=?, quantity=?, unit_price=?, location=?, barcode_data=?, description=?, low_stock_threshold=? WHERE sku=?";
                    $stmt_update = $pdo->prepare($sql);
                    if($stmt_update->execute([$name, $category, $quantity, $price, $location, $barcode, $description, $threshold, $sku])) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                } else {
                    // Insert
                    $sql = "INSERT INTO inventory (name, sku, category, quantity, unit_price, location, barcode_data, description, low_stock_threshold) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt_insert = $pdo->prepare($sql);
                    if($stmt_insert->execute([$name, $sku, $category, $quantity, $price, $location, $barcode, $description, $threshold])) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                }
            }
            fclose($handle);
            $message = "Import complete: $success_count items processed successfully.";
        }
    } else {
        $message = "Error uploading file.";
    }
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Import / Export Inventory</h2>
    <a href="inventory.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Export Inventory</h6>
            </div>
            <div class="card-body">
                <p>Download a CSV file containing all inventory items.</p>
                <form method="POST">
                    <button type="submit" name="export" class="btn btn-success"><i class="fas fa-download"></i> Export CSV</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Import Inventory</h6>
            </div>
            <div class="card-body">
                <p>Upload a CSV file to add or update inventory items.</p>
                <p class="small text-muted">Format: ID, Name, SKU, Category, Quantity, Price, Location, Barcode, Description, Threshold</p>
                <?php if ($message): ?>
                    <div class="alert alert-info"><?php echo $message; ?></div>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                    </div>
                    <button type="submit" name="import" class="btn btn-primary"><i class="fas fa-upload"></i> Import CSV</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
