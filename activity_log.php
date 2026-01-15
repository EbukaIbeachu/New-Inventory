<?php
require_once 'config/config.php';
require_once 'config/db.php';
require_once 'includes/functions.php';

require_admin();

$page_title = 'Activity Log';
$error = '';

try {
    $userFilter = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
    if ($userFilter > 0) {
        $stmt = $pdo->prepare("SELECT al.*, u.username FROM activity_log al LEFT JOIN users u ON al.user_id = u.id WHERE al.user_id = ? ORDER BY al.created_at DESC LIMIT 200");
        $stmt->execute([$userFilter]);
    } else {
        $stmt = $pdo->query("SELECT al.*, u.username FROM activity_log al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 200");
    }
    $logs = $stmt->fetchAll();
} catch (Exception $e) {
    $logs = [];
    $error = 'Activity log table not found. Please update your database schema to include the activity_log table.';
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Activity Log</h2>
</div>

<?php if ($error): ?>
    <div class="alert alert-warning"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm datatable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        <td>
                            <?php if (!empty($row['username'])): ?>
                                <a href="activity_log.php?user_id=<?php echo (int)$row['user_id']; ?>"><?php echo htmlspecialchars($row['username']); ?></a>
                            <?php else: ?>
                                <span class="text-muted">System/Unknown</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['action_type']); ?></span></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo htmlspecialchars($row['ip_address']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
