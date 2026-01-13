<?php
require_once 'config/config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/db.php'; // This handles the connection, potentially without DB selected

    try {
        // Create Database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
        $pdo->exec("USE " . DB_NAME);

        // Read Schema
        $sql = file_get_contents('database/schema.sql');
        
        // Execute Schema (splitting by semicolon to handle multiple statements if necessary, but PDO can handle multiple queries in some configs, safer to split)
        // Note: Simple split might break triggers/procedures, but our schema is simple.
        $pdo->exec($sql);

        $message = "Installation successful! Default admin: admin / admin123. <a href='login.php'>Login here</a>";
    } catch (PDOException $e) {
        $message = "Installation failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install <?php echo APP_NAME; ?></title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f4f4f4; }
        .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 400px; text-align: center; }
        .btn { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #0056b3; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-danger { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Install <?php echo APP_NAME; ?></h2>
        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, 'successful') !== false ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (strpos($message, 'successful') === false): ?>
            <p>This will create the database and tables.</p>
            <form method="POST">
                <button type="submit" class="btn">Run Installation</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
