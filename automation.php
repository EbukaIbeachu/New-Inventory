<?php
require_once 'config/config.php';
require_once 'config/db.php';
require_once 'includes/functions.php';

require_admin();

$page_title = 'Automation';
$message = '';

// Handle Actions
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'toggle' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("UPDATE automation_tasks SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);
        redirect('automation.php');
    } elseif ($_GET['action'] === 'run' && isset($_GET['id'])) {
        // Manually run (mock) - ideally call the logic from cron.php
        // For now, just set last_run to now to simulate
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("UPDATE automation_tasks SET last_run = NOW() WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Task triggered successfully (Simulated).";
    }
}

// Add Task
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean_input($_POST['name']);
    $type = clean_input($_POST['task_type']);
    $schedule = clean_input($_POST['schedule_cron']);
    
    $stmt = $pdo->prepare("INSERT INTO automation_tasks (name, task_type, schedule_cron) VALUES (?, ?, ?)");
    $stmt->execute([$name, $type, $schedule]);
    $message = "Task added successfully.";
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Automation Engine</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal"><i class="fas fa-plus"></i> Add Task</button>
</div>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="alert alert-info">
            <strong>Note:</strong> To ensure these tasks run, please set up a system cron job to run <code>php <?php echo ROOT_PATH; ?>cron.php</code> every minute.
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Task Name</th>
                        <th>Type</th>
                        <th>Schedule (Cron)</th>
                        <th>Last Run</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM automation_tasks");
                    while ($row = $stmt->fetch()):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['task_type']); ?></td>
                        <td><code><?php echo htmlspecialchars($row['schedule_cron']); ?></code></td>
                        <td><?php echo $row['last_run'] ? $row['last_run'] : 'Never'; ?></td>
                        <td>
                            <span class="badge <?php echo $row['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                <?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="automation.php?action=toggle&id=<?php echo $row['id']; ?>" class="btn btn-sm <?php echo $row['is_active'] ? 'btn-warning' : 'btn-success'; ?>">
                                <?php echo $row['is_active'] ? 'Disable' : 'Enable'; ?>
                            </a>
                            <a href="automation.php?action=run&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info text-white">Run Now</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Automation Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Task Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Task Type</label>
                    <select name="task_type" class="form-select" required>
                        <option value="email_alert">Low Stock Email Alert</option>
                        <option value="report_generation">Daily Report Generation</option>
                        <option value="backup">Database Backup</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Schedule (Cron Expression)</label>
                    <input type="text" name="schedule_cron" class="form-control" placeholder="* * * * *" value="0 0 * * *" required>
                    <small class="text-muted">Minute Hour Day Month DayOfWeek</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save Task</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
