<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';



// Verificăm dacă utilizatorul este autentificat
requireLogin();

$error = '';
$success = '';

// Obținem datele admin-ului curent
$admin = getCurrentAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    try {
        // Verificăm parola curentă
        $stmt = $pdo->prepare("SELECT password_hash FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $current_hash = $stmt->fetchColumn();
        
        if (!password_verify($current_password, $current_hash)) {
            $error = 'Parola curentă este incorectă.';
        } else {
            // Verificăm dacă noul username sau email există deja
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE (username = ? OR email = ?) AND id != ?");
            $stmt->execute([$username, $email, $_SESSION['admin_id']]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'Numele de utilizator sau email-ul există deja.';
            } else {
                // Construim query-ul de update
                $updates = [];
                $params = [];
                
                if ($username !== $admin['username']) {
                    $updates[] = "username = ?";
                    $params[] = $username;
                }
                
                if ($email !== $admin['email']) {
                    $updates[] = "email = ?";
                    $params[] = $email;
                }
                
                if (!empty($new_password)) {
                    if (strlen($new_password) < 6) {
                        $error = 'Parola nouă trebuie să aibă cel puțin 6 caractere.';
                    } elseif ($new_password !== $confirm_password) {
                        $error = 'Parolele noi nu coincid.';
                    } else {
                        $updates[] = "password_hash = ?";
                        $params[] = password_hash($new_password, PASSWORD_DEFAULT);
                    }
                }
                
                if (empty($error) && !empty($updates)) {
                    $params[] = $_SESSION['admin_id'];
                    $sql = "UPDATE admins SET " . implode(", ", $updates) . " WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    
                    // Actualizăm sesiunea
                    $_SESSION['admin_username'] = $username;
                    
                    $success = 'Setările au fost actualizate cu succes.';
                    
                    // Reîmprospătăm datele admin-ului
                    $admin = getCurrentAdmin();
                } elseif (empty($error) && empty($updates)) {
                    $success = 'Nu ați modificat niciun câmp.';
                }
            }
        }
    } catch (PDOException $e) {
        $error = 'A apărut o eroare la actualizarea setărilor.';
        error_log("Settings update error: " . $e->getMessage());
    }
}
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Setări cont</h1>
    </div>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Nume utilizator</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?php echo htmlspecialchars($admin['username']); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                </div>
                
                <hr class="my-4">
                
                <h6 class="mb-3">Schimbă parola</h6>
                
                <div class="mb-3">
                    <label for="current_password" class="form-label">Parola curentă</label>
                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                </div>
                
                <div class="mb-3">
                    <label for="new_password" class="form-label">Parola nouă</label>
                    <input type="password" class="form-control" id="new_password" name="new_password">
                    <div class="form-text">Lăsați gol dacă nu doriți să schimbați parola.</div>
                </div>
                
                <div class="mb-4">
                    <label for="confirm_password" class="form-label">Confirmă parola nouă</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                </div>
                
                <button type="submit" class="btn btn-primary">Salvează modificările</button>
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
.form-control {
    border-color: #e3e6ea;
    padding: 0.6rem 1rem;
}
.form-control:focus {
    border-color: #23282d;
    box-shadow: 0 0 0 0.2rem rgba(35, 40, 45, 0.25);
}
.btn-primary {
    background: #23282d;
    border-color: #23282d;
}
.btn-primary:hover {
    background: #191c20;
    border-color: #191c20;
}
</style> 