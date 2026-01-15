<?php
require_once 'config/config.php';
require_once 'config/db.php';
require_once 'includes/functions.php';

require_login();

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Get image path to delete file
    $stmt = $pdo->prepare("SELECT image_path FROM inventory WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    
    if ($item) {
        if ($item['image_path'] && file_exists(ROOT_PATH . $item['image_path'])) {
            unlink(ROOT_PATH . $item['image_path']);
        }
        
        $stmt = $pdo->prepare("DELETE FROM inventory WHERE id = ?");
        $stmt->execute([$id]);
        log_activity('delete_item', 'Deleted item ID ' . $id);

        flash('main_flash', 'Item deleted successfully.');
    }
}

redirect('inventory.php');
