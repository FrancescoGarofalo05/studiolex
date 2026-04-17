<?php
/**
 * STUDIOLEX - Registrazione (Admin Studio + Dipendenti con Passkey)
 * File: admin/register.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Genera Passkey Casuale
function generatePasskey() {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $passkey = 'STUDIO-';
    for ($i = 0; $i < 8; $i++) {
        $passkey .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $passkey;
}

// Variabili Form
$account_type = $_POST['account_type'] ?? 'admin';
$studio_name = $username = $email = $full_name = $passkey_input = '';
$errors = [];
$success = false;
$generated_passkey = '';

// Gestione Registrazione
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_type = $_POST['account_type'] ?? 'admin';
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $studio_name = trim($_POST['studio_name'] ?? '');
    $passkey_input = trim($_POST['passkey'] ?? '');

    // ==================== VALIDAZIONE COMUNE ====================
    if (empty($username)) $errors['username'] = 'Campo obbligatorio.';
    elseif (strlen($username) < 3) $errors['username'] = 'Minimo 3 caratteri.';
    elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) $errors['username'] = 'Solo lettere, numeri e underscore.';
    else {
        $check = querySingle("SELECT id FROM users WHERE username = :u", [':u' => $username]);
        if ($check) $errors['username'] = 'Username già in uso.';
    }

    if (empty($email)) $errors['email'] = 'Campo obbligatorio.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email non valida.';
    else {
        $check = querySingle("SELECT id FROM users WHERE email = :e", [':e' => $email]);
        if ($check) $errors['email'] = 'Email già registrata.';
    }

    if (empty($full_name)) $errors['full_name'] = 'Campo obbligatorio.';
    if (empty($password)) $errors['password'] = 'Campo obbligatorio.';
    elseif (strlen($password) < 6) $errors['password'] = 'Minimo 6 caratteri.';
    if ($password !== $confirm_password) $errors['confirm_password'] = 'Le password non coincidono.';

    // ==================== VALIDAZIONE SPECIFICA ====================
    if ($account_type === 'admin') {
        if (empty($studio_name)) $errors['studio_name'] = 'Il nome dello studio è obbligatorio.';
    } else {
        if (empty($passkey_input)) $errors['passkey'] = 'La Passkey è obbligatoria.';
        else {
            $studio = querySingle("SELECT id FROM studios WHERE passkey = :pk", [':pk' => $passkey_input]);
            if (!$studio) $errors['passkey'] = 'Passkey non valida.';
            else $studio_id = $studio['id'];
        }
    }

    // ==================== CREAZIONE RECORD ====================
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        if ($account_type === 'admin') {
            // Genera Passkey
            $passkey = generatePasskey();
            $generated_passkey = $passkey;
            
            // 1. Crea Studio (SENZA owner_id, lo aggiorniamo dopo)
            $insert_studio_sql = "INSERT INTO studios (name, passkey) VALUES (:name, :pk)";
            
            try {
                execute($insert_studio_sql, [
                    ':name' => $studio_name,
                    ':pk' => $passkey
                ]);
                $studio_id = $pdo->lastInsertId();
                
                // 2. Crea Utente Admin
                $insert_user_sql = "INSERT INTO users (username, email, password_hash, full_name, role, studio_id) 
                                   VALUES (:u, :e, :p, :f, 'admin', :sid)";
                
                execute($insert_user_sql, [
                    ':u' => $username,
                    ':e' => $email,
                    ':p' => $password_hash,
                    ':f' => $full_name,
                    ':sid' => $studio_id
                ]);
                $user_id = $pdo->lastInsertId();
                
                // 3. Aggiorna owner_id dello studio
                execute("UPDATE studios SET owner_id = :oid WHERE id = :sid", [
                    ':oid' => $user_id,
                    ':sid' => $studio_id
                ]);
                
                // 4. Crea cartella logs se non esiste e salva la Passkey
                $log_dir = dirname(__DIR__) . '/logs';
                if (!is_dir($log_dir)) {
                    mkdir($log_dir, 0777, true);
                }
                $log_message = date('Y-m-d H:i:s') . " - STUDIO: $studio_name | ADMIN: $email | PASSKEY: $passkey\n";
                @file_put_contents($log_dir . '/passkeys.log', $log_message, FILE_APPEND);
                
                $success = true;
                $username = $email = $full_name = $studio_name = $passkey_input = '';
                
            } catch (PDOException $e) {
                $errors['general'] = 'Errore durante la registrazione. Riprova.';
                error_log('Errore registrazione admin: ' . $e->getMessage());
            }
            
        } else { // Dipendente
            try {
                execute("INSERT INTO users (username, email, password_hash, full_name, role, studio_id) 
                         VALUES (:u, :e, :p, :f, 'editor', :sid)", [
                    ':u' => $username,
                    ':e' => $email,
                    ':p' => $password_hash,
                    ':f' => $full_name,
                    ':sid' => $studio_id
                ]);
                
                $success = true;
                $username = $email = $full_name = $passkey_input = '';
                
            } catch (PDOException $e) {
                $errors['general'] = 'Errore durante la registrazione. Riprova.';
                error_log('Errore registrazione dipendente: ' . $e->getMessage());
            }
        }
    }
}

$page_title = 'Registrazione - StudioLex';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--color-primary) 0%, #2b6cb0 100%); padding: 1rem; }
        .admin-card { background: white; border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); width: 100%; max-width: 520px; padding: 2rem; margin: 1rem; }
        .admin-logo { text-align: center; margin-bottom: 1.5rem; }
        .admin-logo a { text-decoration: none; font-family: var(--font-secondary); font-size: 2rem; font-weight: 700; color: var(--color-dark); }
        .admin-logo span { color: var(--color-primary); }
        .admin-title { font-size: 1.5rem; text-align: center; margin-bottom: 0.25rem; }
        .admin-subtitle { text-align: center; color: var(--color-gray); margin-bottom: 1.5rem; }
        
        .account-type-switch { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; background: #f7fafc; padding: 0.4rem; border-radius: 12px; }
        .type-option { flex: 1; text-align: center; padding: 0.6rem; border-radius: 8px; cursor: pointer; font-weight: 600; transition: var(--transition); color: var(--color-gray); background: transparent; border: none; font-size: 0.9rem; }
        .type-option.active { background: var(--color-primary); color: white; }
        
        .form-group { margin-bottom: 1.2rem; }
        .form-label { display: block; font-weight: 600; margin-bottom: 0.4rem; color: var(--color-dark); font-size: 0.9rem; }
        .form-input { width: 100%; padding: 0.7rem 1rem; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 1rem; transition: var(--transition); background: white; }
        .form-input:focus { outline: none; border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(26, 54, 93, 0.1); }
        .has-error .form-input { border-color: #e53e3e; }
        .form-error { color: #e53e3e; font-size: 0.8rem; margin-top: 0.25rem; display: block; }
        .btn-admin { width: 100%; padding: 0.875rem; background: var(--color-primary); color: white; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: var(--transition); margin-top: 0.5rem; }
        .btn-admin:hover { background: var(--color-dark); transform: translateY(-2px); }
        .admin-footer { text-align: center; margin-top: 1.5rem; color: var(--color-gray); font-size: 0.9rem; }
        .admin-footer a { color: var(--color-primary); text-decoration: none; font-weight: 600; }
        
        .alert-error { background: #fed7d7; border: 1px solid #f56565; color: #742a2a; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .alert-success { background: #c6f6d5; border: 1px solid #68d391; color: #22543d; padding: 1.5rem; border-radius: 8px; text-align: center; margin-bottom: 1.5rem; }
        .passkey-display { background: #f7fafc; border: 2px dashed var(--color-primary); padding: 1rem; border-radius: 8px; font-family: monospace; font-size: 1.8rem; text-align: center; letter-spacing: 2px; margin: 1rem 0; }
        
        @media (max-width: 480px) {
            .admin-card { padding: 1.5rem; }
            .type-option { font-size: 0.8rem; padding: 0.5rem; }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-card">
            <div class="admin-logo">
                <a href="../index.php">Studio<span>Lex</span></a>
            </div>
            
            <h1 class="admin-title">Crea un Account</h1>
            <p class="admin-subtitle">Scegli come vuoi registrarti</p>
            
            <?php if ($success): ?>
                <div class="alert-success">
                    <h3>✅ Registrazione completata!</h3>
                    <?php if ($account_type === 'admin' && $generated_passkey): ?>
                        <p>Il tuo studio <strong><?php echo htmlspecialchars($studio_name); ?></strong> è stato creato.</p>
                        <p><strong>La tua Passkey Studio è:</strong></p>
                        <div class="passkey-display"><?php echo $generated_passkey; ?></div>
                        <p style="font-size: 0.9rem; margin-top: 1rem;">⚠️ <strong>Conserva questa Passkey!</strong> I tuoi dipendenti ne avranno bisogno per registrarsi.</p>
                    <?php else: ?>
                        <p>Il tuo account dipendente è stato creato con successo.</p>
                    <?php endif; ?>
                    <p style="margin-top: 1.5rem;">
                        <a href="login.php" style="color: var(--color-primary); font-weight: 600;">Clicca qui per accedere</a>
                    </p>
                </div>
            <?php else: ?>
                <?php if (isset($errors['general'])): ?>
                    <div class="alert-error"><?php echo $errors['general']; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="register.php" id="registerForm" novalidate>
                    <div class="account-type-switch">
                        <button type="button" id="btnAdmin" class="type-option <?php echo $account_type === 'admin' ? 'active' : ''; ?>">🏢 Registra Studio</button>
                        <button type="button" id="btnEmployee" class="type-option <?php echo $account_type === 'employee' ? 'active' : ''; ?>">👤 Sono Dipendente</button>
                    </div>
                    <input type="hidden" name="account_type" id="accountType" value="<?php echo $account_type; ?>">
                    
                    <div id="adminFields" style="display: <?php echo $account_type === 'admin' ? 'block' : 'none'; ?>;">
                        <div class="form-group <?php echo isset($errors['studio_name']) ? 'has-error' : ''; ?>">
                            <label for="studio_name" class="form-label">Nome Studio</label>
                            <input type="text" id="studio_name" name="studio_name" class="form-input" value="<?php echo htmlspecialchars($studio_name); ?>" placeholder="es. Studio Legale Rossi">
                            <?php if (isset($errors['studio_name'])): ?><span class="form-error"><?php echo $errors['studio_name']; ?></span><?php endif; ?>
                        </div>
                    </div>
                    
                    <div id="employeeFields" style="display: <?php echo $account_type === 'employee' ? 'block' : 'none'; ?>;">
                        <div class="form-group <?php echo isset($errors['passkey']) ? 'has-error' : ''; ?>">
                            <label for="passkey" class="form-label">Passkey Studio</label>
                            <input type="text" id="passkey" name="passkey" class="form-input" value="<?php echo htmlspecialchars($passkey_input); ?>" placeholder="es. STUDIO-ABCD1234">
                            <?php if (isset($errors['passkey'])): ?><span class="form-error"><?php echo $errors['passkey']; ?></span><?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-group <?php echo isset($errors['username']) ? 'has-error' : ''; ?>">
                        <label for="username" class="form-label">Nome Utente</label>
                        <input type="text" id="username" name="username" class="form-input" value="<?php echo htmlspecialchars($username); ?>" placeholder="es. mario_rossi">
                        <?php if (isset($errors['username'])): ?><span class="form-error"><?php echo $errors['username']; ?></span><?php endif; ?>
                    </div>
                    <div class="form-group <?php echo isset($errors['email']) ? 'has-error' : ''; ?>">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($email); ?>" placeholder="es. mario@email.it">
                        <?php if (isset($errors['email'])): ?><span class="form-error"><?php echo $errors['email']; ?></span><?php endif; ?>
                    </div>
                    <div class="form-group <?php echo isset($errors['full_name']) ? 'has-error' : ''; ?>">
                        <label for="full_name" class="form-label">Nome Completo</label>
                        <input type="text" id="full_name" name="full_name" class="form-input" value="<?php echo htmlspecialchars($full_name); ?>" placeholder="es. Mario Rossi">
                        <?php if (isset($errors['full_name'])): ?><span class="form-error"><?php echo $errors['full_name']; ?></span><?php endif; ?>
                    </div>
                    <div class="form-group <?php echo isset($errors['password']) ? 'has-error' : ''; ?>">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-input" placeholder="Almeno 6 caratteri">
                        <?php if (isset($errors['password'])): ?><span class="form-error"><?php echo $errors['password']; ?></span><?php endif; ?>
                    </div>
                    <div class="form-group <?php echo isset($errors['confirm_password']) ? 'has-error' : ''; ?>">
                        <label for="confirm_password" class="form-label">Conferma Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="Ripeti la password">
                        <?php if (isset($errors['confirm_password'])): ?><span class="form-error"><?php echo $errors['confirm_password']; ?></span><?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn-admin">📝 Registrati</button>
                </form>
                
                <div class="admin-footer">
                    Hai già un account? <a href="login.php">Accedi</a>
                </div>
            <?php endif; ?>
            
            <div class="admin-footer" style="margin-top: 1rem;">
                <a href="../index.php">← Torna al sito</a>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnAdmin = document.getElementById('btnAdmin');
        const btnEmployee = document.getElementById('btnEmployee');
        const accountType = document.getElementById('accountType');
        const adminFields = document.getElementById('adminFields');
        const employeeFields = document.getElementById('employeeFields');
        const studioName = document.getElementById('studio_name');
        const passkey = document.getElementById('passkey');
        
        function setActiveType(type) {
            accountType.value = type;
            if (type === 'admin') {
                btnAdmin.classList.add('active');
                btnEmployee.classList.remove('active');
                adminFields.style.display = 'block';
                employeeFields.style.display = 'none';
                studioName.required = true;
                passkey.required = false;
            } else {
                btnEmployee.classList.add('active');
                btnAdmin.classList.remove('active');
                adminFields.style.display = 'none';
                employeeFields.style.display = 'block';
                studioName.required = false;
                passkey.required = true;
            }
        }
        
        btnAdmin.addEventListener('click', function(e) { e.preventDefault(); setActiveType('admin'); });
        btnEmployee.addEventListener('click', function(e) { e.preventDefault(); setActiveType('employee'); });
        
        setActiveType('<?php echo $account_type; ?>');
    });
    </script>
</body>
</html>