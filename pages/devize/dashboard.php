<?php
require_once __DIR__ . '/../../includes/config.php';

// Utilizatori activi
$stmt = $pdo->query("SELECT COUNT(*) FROM admins WHERE status = 'active'");
$utilizatori_activi = $stmt->fetchColumn();

// Specificații
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM specificatii");
    $nr_specificatii = $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT Simbol, Descriere FROM specificatii ORDER BY codSpecificatie DESC LIMIT 4");
    $ultimele_spec = $stmt->fetchAll();
} catch (Exception $e) {
    $nr_specificatii = 0;
    $ultimele_spec = [];
}

// Prețuri (dacă există tabelul preturi)
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM preturi");
    $nr_preturi = $stmt->fetchColumn();
} catch (Exception $e) {
    $nr_preturi = 0;
}
?>
<div class="dashboard-bg py-5">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12 col-lg-8 mb-4 mb-lg-0">
                <div class="welcome-card card shadow-sm h-100">
                    <div class="card-body d-flex flex-column flex-md-row align-items-center justify-content-between">
                        <div>
                            <h2 class="fw-bold mb-2" style="color:#23282d">Bine ai revenit, <span style="color:#007bff"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>!</h2>
                            <p class="mb-0 text-muted">Acesta este panoul tău de administrare. Poți gestiona rapid toate resursele platformei.</p>
                        </div>
                        <div class="d-none d-md-block">
                            <i class="bi bi-speedometer2" style="font-size:4rem;color:#007bff;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card quick-actions-card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-3" style="color:#23282d">Acțiuni rapide</h5>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="?action=cursuri" class="btn btn-outline-primary rounded-pill"><i class="bi bi-journal-bookmark me-2"></i>Cursuri</a>
                            <a href="?action=noutati" class="btn btn-outline-primary rounded-pill"><i class="bi bi-newspaper me-2"></i>Noutăți</a>
                            <a href="?action=preturi" class="btn btn-outline-primary rounded-pill"><i class="bi bi-cash-coin me-2"></i>Prețuri</a>
                            <a href="?action=adauga_specificatii" class="btn btn-outline-primary rounded-pill"><i class="bi bi-file-earmark-plus me-2"></i>Specificații</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-4 mb-4">
            <div class="col-12 col-md-6 col-xl-4">
                <div class="stat-card card shadow-sm h-100 text-center">
                    <div class="card-body">
                        <i class="bi bi-people" style="font-size:2.5rem;color:#23282d;"></i>
                        <h3 class="fw-bold mt-2" style="color:#007bff"><?php echo $utilizatori_activi; ?></h3>
                        <div class="text-muted">Utilizatori activi</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="stat-card card shadow-sm h-100 text-center">
                    <div class="card-body">
                        <i class="bi bi-currency-dollar" style="font-size:2.5rem;color:#23282d;"></i>
                        <h3 class="fw-bold mt-2" style="color:#007bff"><?php echo $nr_preturi; ?></h3>
                        <div class="text-muted">Prețuri actualizate</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="stat-card card shadow-sm h-100 text-center">
                    <div class="card-body">
                        <i class="bi bi-file-earmark-plus" style="font-size:2.5rem;color:#23282d;"></i>
                        <h3 class="fw-bold mt-2" style="color:#007bff"><?php echo $nr_specificatii; ?></h3>
                        <div class="text-muted">Specificații noi</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-12 col-xl-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-3" style="color:#23282d">Ultimele specificații</h5>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($ultimele_spec as $spec): ?>
                                <li class="list-group-item">
                                    <b><?php echo htmlspecialchars($spec['Simbol']); ?></b> <span class="text-muted small"><?php echo strip_tags(mb_substr($spec['Descriere'],0,40)); ?>...</span>
                                </li>
                            <?php endforeach; ?>
                            <?php if (empty($ultimele_spec)): ?>
                                <li class="list-group-item text-muted">Nu există specificații recente.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 

<style>
.dashboard-bg {
    background: linear-gradient(120deg, #f4f6f8 60%, #e3e6ea 100%);
    min-height: 100vh;
}
.welcome-card {
    background: linear-gradient(90deg, #f4f6f8 60%, #e3e6ea 100%);
    border: none;
}
.quick-actions-card {
    background: #fff;
    border: none;
}
.stat-card {
    background: #fff;
    border: none;
    transition: box-shadow 0.2s;
}
.stat-card:hover {
    box-shadow: 0 4px 24px rgba(0,123,255,0.08);
}
.card-title {
    font-weight: 600;
}
.list-group-item {
    border: none;
    border-bottom: 1px solid #f4f6f8;
    background: transparent;
    color: #23282d;
}
.list-group-item:last-child {
    border-bottom: none;
}
</style> 