<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

require_once 'util.php';
$util = new Util();
$pdo = $util->getConnection();

$editAgentId = null;

// Handle Registration
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['register_agent'])) {
        $phone = $_POST['phone'];
        $fullName = $_POST['full_name'];
        $pin = $_POST['pin'];
        try {
            // Check if phone already exists
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM agents WHERE phone_number = ?");
            $checkStmt->execute([$phone]);
            $exists = $checkStmt->fetchColumn();
        
            if ($exists > 0) {
                $error = "Phone number already registered.";
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO agents 
                    (phone_number, full_name, pin_hash, approved, balance)
                    VALUES (?, ?, ?, 1, default)
                ");
                $stmt->execute([
                    $phone,
                    $fullName,
                    Util::hashPin($pin)
                ]);
                $success = "Agent registered successfully!";
            }
        } catch (PDOException $e) {
            $error = "Registration failed: " . $e->getMessage();
        }
        
    }

    if (isset($_POST['edit_balance'])) {
        $editAgentId = $_POST['id'];
    }

    if (isset($_POST['update_balance'])) {
        $agentId = $_POST['id'];
        $amount = $_POST['amount'];
        if ($amount < 0) {
            $error = "Amount cannot be negative.";
        } else {
            try {
                $stmt = $pdo->prepare("
                    UPDATE agents 
                    SET balance = balance + ? 
                    WHERE id = ?
                ");
                $stmt->execute([$amount, $agentId]);
                $success = "Balance updated for agent ID $agentId.";
            } catch (PDOException $e) {
                $error = "Failed: " . $e->getMessage();
            }
        }
    }
}

$agents = $pdo->query("SELECT * FROM agents ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Agent Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <h2 class="mb-4">Agent Management</h2>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="mb-5">
        <h4>Register New Agent</h4>
        <form method="POST" class="row g-2">
            <div class="col-md-3">
                <input class="form-control" type="text" name="phone" placeholder="Phone" required>
            </div>
            <div class="col-md-3">
                <input class="form-control" type="text" name="full_name" placeholder="Full Name" required>
            </div>
            <div class="col-md-2">
                <input class="form-control" type="password" name="pin" placeholder="4-digit PIN" required>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary" type="submit" name="register_agent">Register</button>
            </div>
        </form>
    </div>

    <h4>INCREASE BALANCE</h4>
    <BR></BR>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Balance</th>
                <th>Registered</th>
                <th>UPDATE</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($agents as $agent): ?>
                <tr>
                    <td><?= htmlspecialchars($agent['id']) ?></td>
                    <td><?= htmlspecialchars($agent['full_name']) ?></td>
                    <td><?= htmlspecialchars($agent['phone_number']) ?></td>
                    <td><?= Util::formatAmount($agent['balance']) ?></td>
                    <td><?= htmlspecialchars($agent['created_at']) ?></td>
                    <td>
                        <?php if ($editAgentId == $agent['id']): ?>
                            <form method="POST" class="d-flex gap-2">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($agent['id']) ?>">
                                <input class="form-control form-control-sm" type="number" name="amount" placeholder="Amount" required>
                                <button class="btn btn-success btn-sm" type="submit" name="update_balance">Update</button>
                            </form>
                        <?php else: ?>
                            <form method="POST">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($agent['id']) ?>">
                                <button class="btn btn-warning btn-sm" type="submit" name="edit_balance">Edit</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
