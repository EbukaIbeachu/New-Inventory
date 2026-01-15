<div class="bg-dark text-white border-end" id="sidebar-wrapper" style="width: 250px; min-height: 100vh;">
    <div class="sidebar-heading p-3 border-bottom fs-4 fw-bold">
        <?php echo APP_NAME; ?>
    </div>
    <div class="list-group list-group-flush">
        <a href="<?php echo BASE_URL; ?>index.php" class="list-group-item list-group-item-action bg-dark text-white p-3">
            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
        </a>
        <a href="<?php echo BASE_URL; ?>inventory.php" class="list-group-item list-group-item-action bg-dark text-white p-3">
            <i class="fas fa-boxes me-2"></i> Inventory
        </a>
        <a href="<?php echo BASE_URL; ?>receipts.php" class="list-group-item list-group-item-action bg-dark text-white p-3">
            <i class="fas fa-receipt me-2"></i> Receipts
        </a>
        
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <a href="<?php echo BASE_URL; ?>users.php" class="list-group-item list-group-item-action bg-dark text-white p-3">
                <i class="fas fa-users me-2"></i> Users
            </a>
            <a href="<?php echo BASE_URL; ?>activity_log.php" class="list-group-item list-group-item-action bg-dark text-white p-3">
                <i class="fas fa-clipboard-list me-2"></i> Activity Log
            </a>
        <a href="<?php echo BASE_URL; ?>automation.php" class="list-group-item list-group-item-action bg-dark text-white p-3">
            <i class="fas fa-cogs me-2"></i> Automation
        </a>
        <?php endif; ?>
        
        <a href="<?php echo BASE_URL; ?>profile.php" class="list-group-item list-group-item-action bg-dark text-white p-3">
            <i class="fas fa-user me-2"></i> Profile
        </a>
        <a href="<?php echo BASE_URL; ?>logout.php" class="list-group-item list-group-item-action bg-dark text-white p-3 text-danger">
            <i class="fas fa-sign-out-alt me-2"></i> Logout
        </a>
    </div>
</div>
