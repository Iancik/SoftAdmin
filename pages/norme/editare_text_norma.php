<?php
require_once __DIR__ . '/../../config.php';

// Debug - afișăm toți parametrii primiți
echo "<!-- Debug Info:\n";
echo "GET: "; print_r($_GET);
echo "POST: "; print_r($_POST);
echo "REQUEST: "; print_r($_REQUEST);
echo "-->";

// Inițializare variabile
$search = '';
$norme = [];
$error = '';
$success = false;

// Debug - afișăm variabilele
echo "<!-- Variables:\n";
echo "Search: " . $search . "\n";
echo "-->";

// Procesare căutare
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $search = trim($_POST['search']);
    if (!empty($search)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM ArticoleVariante WHERE Simbol LIKE ? ORDER BY Simbol LIMIT 20");
            $stmt->execute(["%$search%"]);
            $norme = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $error = 'Eroare la căutare: ' . $e->getMessage();
        }
    }
}
?>
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h2 class="h4 mb-4 text-primary">
                        <i class="bi bi-pencil-square me-2"></i>Editare text normă
                    </h2>

                    <!-- Formular de căutare -->
                    <form method="post" class="mb-4" id="searchForm">
                        <div class="input-group">
                            <input type="text" class="form-control form-control-lg" 
                                   name="search" 
                                   placeholder="Introduceți simbolul normei..." 
                                   value="<?= htmlspecialchars($search) ?>" 
                                   required>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-2"></i>Caută
                            </button>
                        </div>
                    </form>

                    <!-- Mesaje de succes/eroare -->
                    <div id="alertContainer"></div>

                    <!-- Lista de norme -->
                    <?php if (!empty($search)): ?>
                        <div class="card border-0">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-4">
                                    <i class="bi bi-list-ul me-2"></i>Norme găsite
                                </h5>
                                
                                <?php if (count($norme) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-nowrap">ID</th>
                                                    <th class="text-nowrap">Simbol</th>
                                                    <th>Denumire</th>
                                                    <th class="text-nowrap">UM</th>
                                                    <th class="text-end">Acțiuni</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($norme as $n): ?>
                                                    <tr>
                                                        <td class="text-nowrap"><?= htmlspecialchars($n['id']) ?></td>
                                                        <td class="text-nowrap"><?= htmlspecialchars($n['Simbol']) ?></td>
                                                        <td><?= htmlspecialchars($n['Denumire']) ?></td>
                                                        <td class="text-nowrap"><?= htmlspecialchars($n['Um']) ?></td>
                                                        <td class="text-end">
                                                            <button type="button" 
                                                                    class="btn btn-outline-primary edit-norma"
                                                                    data-simbol="<?= htmlspecialchars($n['Simbol']) ?>"
                                                                    data-denumire="<?= htmlspecialchars($n['Denumire']) ?>"
                                                                    data-um="<?= htmlspecialchars($n['Um']) ?>">
                                                                <i class="bi bi-pencil me-2"></i>Editează
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info mb-0">
                                        <i class="bi bi-info-circle me-2"></i>Nu s-au găsit norme cu simbolul căutat.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pentru editare -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editează norma</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="post">
                <div class="modal-body">
                    <input type="hidden" name="simbol" id="edit_simbol">
                    <input type="hidden" name="denumire_veche" id="edit_denumire_veche">
                    <div class="mb-3">
                        <label class="form-label">Simbol</label>
                        <input type="text" class="form-control" id="edit_simbol_display" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Denumire</label>
                        <input type="text" class="form-control" name="denumire" id="edit_denumire" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unitate de măsură</label>
                        <input type="text" class="form-control" name="um" id="edit_um" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>
                    <button type="submit" class="btn btn-primary">Salvează</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Verificăm dacă Bootstrap este disponibil
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap nu este încărcat!');
        // Adăugăm Bootstrap din CDN
        var bootstrapScript = document.createElement('script');
        bootstrapScript.src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js';
        bootstrapScript.onload = initializeModals;
        document.head.appendChild(bootstrapScript);
    } else {
        initializeModals();
    }

    function initializeModals() {
        // Inițializăm toate modalele Bootstrap
        var modals = document.querySelectorAll('.modal');
        modals.forEach(function(modal) {
            new bootstrap.Modal(modal);
        });
    }

    // Handler pentru butonul de editare
    document.querySelectorAll('.edit-norma').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Obținem datele din atributul data-*
            var simbol = this.getAttribute('data-simbol');
            var denumire = this.getAttribute('data-denumire');
            var um = this.getAttribute('data-um');

            console.log('Date pentru editare:', { simbol, denumire, um }); // Debug

            // Setăm valorile în formular
            document.getElementById('edit_simbol').value = simbol || '';
            document.getElementById('edit_simbol_display').value = simbol || '';
            document.getElementById('edit_denumire_veche').value = denumire || '';
            document.getElementById('edit_denumire').value = denumire || '';
            document.getElementById('edit_um').value = um || '';

            // Afișăm modalul
            var modalElement = document.getElementById('editModal');
            if (typeof bootstrap !== 'undefined') {
                var editModal = new bootstrap.Modal(modalElement);
                editModal.show();
            } else {
                modalElement.style.display = 'block';
                modalElement.classList.add('show');
            }
        });
    });

    // Handler pentru formularul de editare
    document.getElementById('editForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Obținem valorile din formular
        var simbol = document.getElementById('edit_simbol').value;
        var denumire_veche = document.getElementById('edit_denumire_veche').value;
        var denumire_noua = document.getElementById('edit_denumire').value;
        var um = document.getElementById('edit_um').value;

        console.log('Date pentru salvare:', { simbol, denumire_veche, denumire_noua, um }); // Debug

        // Creăm FormData și adăugăm toate câmpurile
        var formData = new FormData();
        formData.append('simbol', simbol);
        formData.append('denumire_veche', denumire_veche);
        formData.append('denumire', denumire_noua);
        formData.append('um', um);

        // Trimitem datele către server
        fetch('pages/norme/salvare_text_norma.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Răspuns server:', response); // Debug
            return response.json();
        })
        .then(data => {
            console.log('Date primite:', data); // Debug
            const alertContainer = document.getElementById('alertContainer');
            if (data.success) {
                alertContainer.innerHTML = `
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>Modificările au fost salvate cu succes!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                // Reîncărcăm lista după salvare
                document.getElementById('searchForm').submit();
            } else {
                alertContainer.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
            }
            // Închidem modalul
            var modalElement = document.getElementById('editModal');
            if (typeof bootstrap !== 'undefined') {
                var editModal = bootstrap.Modal.getInstance(modalElement);
                if (editModal) {
                    editModal.hide();
                }
            } else {
                modalElement.style.display = 'none';
                modalElement.classList.remove('show');
            }
        })
        .catch(error => {
            console.error('Eroare la salvare:', error);
            const alertContainer = document.getElementById('alertContainer');
            alertContainer.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Eroare la salvare: ${error}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
        });
    });
});
</script> 