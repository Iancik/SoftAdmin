<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if ($_SESSION['admin_role'] !== 'super_admin') {
    echo '<script>window.location.href="index.php";</script>';
    exit;
}

// Procesăm adăugarea unui nou admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'admin';
        
        if (!empty($username) && !empty($email) && !empty($password)) {
            try {
                // Verificăm dacă username-ul sau email-ul există
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                if ($stmt->fetchColumn() > 0) {
                    $error = 'Numele de utilizator sau email-ul există deja.';
                } else {
                    // Adăugăm noul admin
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO admins (username, email, password_hash, role, created_at) VALUES (?, ?, ?, ?, NOW())");
                    $stmt->execute([$username, $email, $password_hash, $role]);
                    $success = 'Administrator adăugat cu succes!';
                }
            } catch (PDOException $e) {
                $error = 'A apărut o eroare la adăugarea administratorului.';
                error_log("Add admin error: " . $e->getMessage());
            }
        }
    } elseif ($_POST['action'] === 'delete' && isset($_POST['admin_id'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ? AND role != 'super_admin'");
            $stmt->execute([$_POST['admin_id']]);
            $success = 'Administrator șters cu succes!';
        } catch (PDOException $e) {
            $error = 'A apărut o eroare la ștergerea administratorului.';
            error_log("Delete admin error: " . $e->getMessage());
        }
    }
}

// Obținem lista de admini
try {
    $stmt = $pdo->query("SELECT id, username, email, role, status, last_login, created_at FROM admins ORDER BY created_at DESC");
    $admins = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'A apărut o eroare la încărcarea listei de administratori.';
    error_log("Get admins error: " . $e->getMessage());
    $admins = [];
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Administrare Utilizatori</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
            <i class="bi bi-plus-circle me-2"></i>Adaugă Administrator
        </button>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nume utilizator</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Status</th>
                            <th>Ultima logare</th>
                            <th>Data creării</th>
                            <th>Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($admin['username']); ?></td>
                                <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $admin['role'] === 'super_admin' ? 'danger' : 'primary'; ?>">
                                        <?php echo $admin['role'] === 'super_admin' ? 'Super Admin' : 'Admin'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $admin['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo $admin['status'] === 'active' ? 'Activ' : 'Inactiv'; ?>
                                    </span>
                                </td>
                                <td><?php echo $admin['last_login'] ? date('d.m.Y H:i', strtotime($admin['last_login'])) : 'Niciodată'; ?></td>
                                <td><?php echo date('d.m.Y H:i', strtotime($admin['created_at'])); ?></td>
                                <td>
                                    <?php if ($admin['role'] !== 'super_admin'): ?>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Sigur doriți să ștergeți acest administrator?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Adaugă Administrator -->
<div class="modal fade" id="addAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adaugă Administrator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Nume utilizator</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Parolă</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Rol</label>
                        <select class="form-select" id="role" name="role">
                            <option value="admin">Admin</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anulează</button>
                    <button type="submit" class="btn btn-primary">Adaugă</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    box-shadow: 0 0 20px rgba(0,0,0,0.05);
    border-radius: 10px;
}

.table {
    margin-bottom: 0;
}

.table th {
    font-weight: 600;
    color: #23282d;
    border-bottom: 2px solid #e3e6ea;
}

.table td {
    vertical-align: middle;
    color: #23282d;
}

.badge {
    padding: 0.5em 0.8em;
    font-weight: 500;
}

.btn-primary {
    background: #23282d;
    border-color: #23282d;
}

.btn-primary:hover {
    background: #191c20;
    border-color: #191c20;
}

.modal-content {
    border: none;
    border-radius: 10px;
    box-shadow: 0 0 30px rgba(0,0,0,0.1);
}

.modal-header {
    border-bottom: 1px solid #e3e6ea;
    background: #f8f9fa;
    border-radius: 10px 10px 0 0;
}

.modal-footer {
    border-top: 1px solid #e3e6ea;
    background: #f8f9fa;
    border-radius: 0 0 10px 10px;
}

.form-control, .form-select {
    border-color: #e3e6ea;
    padding: 0.6rem 1rem;
}

.form-control:focus, .form-select:focus {
    border-color: #23282d;
    box-shadow: 0 0 0 0.2rem rgba(35, 40, 45, 0.25);
}
</style> 