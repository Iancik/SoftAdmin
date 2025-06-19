<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function login($username, $password) {
    global $pdo;
    
    try {
        // Debug: Afișăm credențialele (fără parolă)
        error_log("Încercare login pentru username: " . $username);
        
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        // Debug: Verificăm dacă am găsit admin-ul
        error_log("Admin găsit: " . ($admin ? "DA" : "NU"));
        
        if ($admin) {
            // Debug: Verificăm parola
            $password_verify = password_verify($password, $admin['password_hash']);
            error_log("Verificare parolă: " . ($password_verify ? "CORECTĂ" : "INCORECTĂ"));
            
            if ($password_verify) {
                // Actualizăm ultima dată de logare
                $updateStmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$admin['id']]);

                // Setăm sesiunea
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_role'] = $admin['role'];
                
                return true;
            }
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
    }
    
    return false;
}

function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}

function getCurrentAdmin() {
    global $pdo;
    
    if (!isLoggedIn()) {
        return null;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, role, last_login FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error getting admin: " . $e->getMessage());
        return null;
    }
} 