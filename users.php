<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

require_admin(); // Only admin can access

$page_title = 'User Management';
$message = '';

// Handle Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $user_id = (int)$_GET['id'];
    
    // Prevent self-action
    if ($user_id == $_SESSION['user_id']) {
        $message = "You cannot modify your own account here.";
    } else {
        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE users SET status = 'approved' WHERE id = ?");
            $stmt->execute([$user_id]);
            log_activity('user_approve', 'Approved user ID ' . $user_id);
            $message = "User approved.";
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE users SET status = 'rejected' WHERE id = ?");
            $stmt->execute([$user_id]);
            log_activity('user_reject', 'Rejected user ID ' . $user_id);
            $message = "User rejected.";
        } elseif ($action === 'make_admin') {
            $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
            $stmt->execute([$user_id]);
            log_activity('user_make_admin', 'Promoted user ID ' . $user_id . ' to admin');
            $message = "User promoted to Admin.";
        } elseif ($action === 'revoke_admin') {
            $stmt = $pdo->prepare("UPDATE users SET role = 'user' WHERE id = ?");
            $stmt->execute([$user_id]);
            log_activity('user_revoke_admin', 'Demoted user ID ' . $user_id . ' to user');
            $message = "User demoted to User.";
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>User Management</h2>
</div>

<?php if ($message): ?>
    <div class="alert alert-info"><?php echo $message; ?></div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Pending Approvals</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Registered Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM users WHERE status = 'pending' ORDER BY created_at ASC");
                    while ($row = $stmt->fetch()):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo $row['created_at']; ?></td>
                        <td>
                            <a href="users.php?action=approve&id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm">Approve</a>
                            <a href="users.php?action=reject&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Reject</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($stmt->rowCount() == 0): ?>
                        <tr><td colspan="4" class="text-center">No pending approvals.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">All Users</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered datatable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM users WHERE status != 'pending' ORDER BY id ASC");
                    while ($row = $stmt->fetch()):
                    ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td>
                            <span class="badge <?php echo $row['role'] === 'admin' ? 'bg-primary' : 'bg-secondary'; ?>">
                                <?php echo ucfirst($row['role']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?php echo $row['status'] === 'approved' ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                <?php if ($row['role'] === 'user'): ?>
                                    <a href="users.php?action=make_admin&id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm" title="Make Admin"><i class="fas fa-user-shield"></i></a>
                                <?php else: ?>
                                    <a href="users.php?action=revoke_admin&id=<?php echo $row['id']; ?>" class="btn btn-secondary btn-sm" title="Revoke Admin"><i class="fas fa-user"></i></a>
                                <?php endif; ?>
                                
                                <?php if ($row['status'] === 'approved'): ?>
                                    <a href="users.php?action=reject&id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm" title="Suspend"><i class="fas fa-ban"></i></a>
                                <?php else: ?>
                                    <a href="users.php?action=approve&id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm" title="Activate"><i class="fas fa-check"></i></a>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">Current User</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
