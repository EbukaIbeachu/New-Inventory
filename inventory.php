<?php
require_once 'config/config.php';
require_once 'config/db.php';
require_once 'includes/functions.php';

require_login();

$page_title = 'Inventory';
include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Inventory Management</h2>
    <div>
        <?php if (is_admin()): ?>
            <a href="add_item.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Item</a>
        <?php endif; ?>
        <a href="import_export.php" class="btn btn-success"><i class="fas fa-file-csv"></i> Import/Export</a>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped datatable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Location</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM inventory ORDER BY created_at DESC");
                    while ($row = $stmt->fetch()):
                    ?>
                    <tr>
                        <td class="text-center">
                            <?php if ($row['image_path']): ?>
                                <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="Img" style="height: 50px; width: 50px; object-fit: cover;">
                            <?php else: ?>
                                <i class="fas fa-box fa-2x text-secondary"></i>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['sku']); ?></td>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td>
                            <span class="badge <?php echo $row['quantity'] <= $row['low_stock_threshold'] ? 'bg-danger' : 'bg-success'; ?>">
                                <?php echo $row['quantity']; ?>
                            </span>
                        </td>
                        <td><?php echo number_format($row['unit_price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($row['location']); ?></td>
                        <td>
                            <a href="edit_item.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info text-white"><i class="fas fa-edit"></i></a>
                            <a href="delete_item.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
