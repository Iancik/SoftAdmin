<?php
require_once 'auth.php';
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        $error = "Token CSRF invalid!";
    } else {
        $user = authenticate_user($username, $password);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Date de logare incorecte!";
        }
    }
}
$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Logare Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh;">
    <div class="card p-4 shadow" style="min-width:340px;">
        <h3 class="mb-3 text-center">Logare Admin</h3>
        <?php if ($error): ?>
            <div class="alert alert-danger"> <?= htmlspecialchars($error) ?> </div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <div class="mb-3">
                <input type="text" name="username" class="form-control" placeholder="Utilizator" required autofocus>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Parola" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Logare</button>
        </form>
    </div>
</body>
</html> 