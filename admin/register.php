<?php
/**
 * STUDIOLEX - Registrazione Nuovo Utente
 * File: admin/register.php
 * 
 * Permette la creazione di un nuovo account per accedere all'area riservata.
 */

// Avvia la sessione
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include la connessione al database
require_once '../includes/config.php';

// Se l'utente è già loggato, reindirizza alla dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Variabili per il form
$username = $email = $full_name = '';
$errors = [];
$success = false;

// Gestione registrazione
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recupera e sanitizza i dati
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // ============================================================
    // VALIDAZIONE BACKEND (La vera sicurezza è qui!)
    // ============================================================
    
    // Validazione username
    if (empty($username)) {
        $errors['username'] = 'Il nome utente è obbligatorio.';
    } elseif (strlen($username) < 3) {
        $errors['username'] = 'Il nome utente deve contenere almeno 3 caratteri.';
    } elseif (strlen($username) > 50) {
        $errors['username'] = 'Il nome utente non può superare i 50 caratteri.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors['username'] = 'Solo lettere, numeri e underscore sono permessi.';
    } else {
        // Verifica se username esiste già
        $check_sql = "SELECT id FROM users WHERE username = :username";
        $existing = querySingle($check_sql, [':username' => $username]);
        if ($existing) {
            $errors['username'] = 'Questo nome utente è già in uso.';
        }
    }
    
    // Validazione email
    if (empty($email)) {
        $errors['email'] = 'L\'email è obbligatoria.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Inserisci un indirizzo email valido.';
    } elseif (strlen($email) > 100) {
        $errors['email'] = 'L\'email non può superare i 100 caratteri.';
    } else {
        // Verifica se email esiste già
        $check_sql = "SELECT id FROM users WHERE email = :email";
        $existing = querySingle($check_sql, [':email' => $email]);
        if ($existing) {
            $errors['email'] = 'Questa email è già registrata.';
        }
    }
    
    // Validazione nome completo
    if (empty($full_name)) {
        $errors['full_name'] = 'Il nome completo è obbligatorio.';
    } elseif (strlen($full_name) < 2) {
        $errors['full_name'] = 'Il nome deve contenere almeno 2 caratteri.';
    } elseif (strlen($full_name) > 100) {
        $errors['full_name'] = 'Il nome non può superare i 100 caratteri.';
    }
    
    // Validazione password
    if (empty($password)) {
        $errors['password'] = 'La password è obbligatoria.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'La password deve contenere almeno 6 caratteri.';
    } elseif (strlen($password) > 255) {
        $errors['password'] = 'Password troppo lunga.';
    }
    
    // Validazione conferma password
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Le password non coincidono.';
    }
    
    // Se non ci sono errori, crea l'utente
    if (empty($errors)) {
        // Hash della password (MAI salvare password in chiaro!)
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Ruolo predefinito: 'editor' (puoi cambiare in 'admin' per il primo utente)
        $role = 'editor';
        
        // Inserisci nel database
        $insert_sql = "INSERT INTO users (username, email, password_hash, full_name, role) 
                       VALUES (:username, :email, :password_hash, :full_name, :role)";
        
        try {
            execute($insert_sql, [
                ':username' => $username,
                ':email' => $email,
                ':password_hash' => $password_hash,
                ':full_name' => $full_name,
                ':role' => $role
            ]);
            
            $success = true;
            
            // Resetta i campi
            $username = $email = $full_name = '';
            
        } catch (PDOException $e) {
            $errors['general'] = 'Errore durante la registrazione. Riprova più tardi.';
            // Log dell'errore (in produzione, non mostrare all'utente)
            error_log('Errore registrazione: ' . $e->getMessage());
        }
    }
}

// Imposta titolo pagina
$page_title = 'Registrazione - StudioLex Admin';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Stili specifici per pagine admin */
        .admin-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--color-primary) 0%, #2b6cb0 100%);
            padding: 2rem;
        }
        
        .admin-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 480px;
            padding: 2.5rem;
        }
        
        .admin-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .admin-logo a {
            text-decoration: none;
            font-family: var(--font-secondary);
            font-size: 2rem;
            font-weight: 700;
            color: var(--color-dark);
        }
        
        .admin-logo span {
            color: var(--color-primary);
        }
        
        .admin-title {
            font-size: 1.5rem;
            text-align: center;
            margin-bottom: 0.5rem;
        }
        
        .admin-subtitle {
            text-align: center;
            color: var(--color-gray);
            margin-bottom: 2rem;
        }
        
        .alert-error {
            background: #fed7d7;
            border: 1px solid #f56565;
            color: #742a2a;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background: #c6f6d5;
            border: 1px solid #68d391;
            color: #22543d;
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .alert-success h3 {
            margin-bottom: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--color-dark);
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(26, 54, 93, 0.1);
        }
        
        .has-error .form-input {
            border-color: #e53e3e;
        }
        
        .form-error {
            color: #e53e3e;
            font-size: 0.85rem;
            margin-top: 0.25rem;
            display: block;
        }
        
        .btn-admin {
            width: 100%;
            padding: 0.875rem;
            background: var(--color-primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .btn-admin:hover {
            background: var(--color-dark);
            transform: translateY(-2px);
        }
        
        .admin-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--color-gray);
        }
        
        .admin-footer a {
            color: var(--color-primary);
            text-decoration: none;
            font-weight: 600;
        }
        
        .admin-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-card">
            <div class="admin-logo">
                <a href="../index.php">
                    Studio<span>Lex</span>
                </a>
            </div>
            
            <h1 class="admin-title">Crea un Account</h1>
            <p class="admin-subtitle">Registrati per accedere all'area riservata</p>
            
            <?php if ($success): ?>
                <div class="alert-success">
                    <h3>✅ Registrazione completata!</h3>
                    <p>Il tuo account è stato creato con successo.</p>
                    <p style="margin-top: 1rem;">
                        <a href="login.php" style="color: var(--color-primary); font-weight: 600;">Clicca qui per accedere</a>
                    </p>
                </div>
            <?php else: ?>
                <?php if (isset($errors['general'])): ?>
                    <div class="alert-error">
                        <?php echo $errors['general']; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="register.php" novalidate>
                    <!-- Username -->
                    <div class="form-group <?php echo isset($errors['username']) ? 'has-error' : ''; ?>">
                        <label for="username" class="form-label">Nome Utente</label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               class="form-input" 
                               value="<?php echo htmlspecialchars($username); ?>"
                               placeholder="es. mario_rossi"
                               required>
                        <?php if (isset($errors['username'])): ?>
                            <span class="form-error"><?php echo $errors['username']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Email -->
                    <div class="form-group <?php echo isset($errors['email']) ? 'has-error' : ''; ?>">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-input" 
                               value="<?php echo htmlspecialchars($email); ?>"
                               placeholder="es. mario@email.it"
                               required>
                        <?php if (isset($errors['email'])): ?>
                            <span class="form-error"><?php echo $errors['email']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Nome Completo -->
                    <div class="form-group <?php echo isset($errors['full_name']) ? 'has-error' : ''; ?>">
                        <label for="full_name" class="form-label">Nome Completo</label>
                        <input type="text" 
                               id="full_name" 
                               name="full_name" 
                               class="form-input" 
                               value="<?php echo htmlspecialchars($full_name); ?>"
                               placeholder="es. Mario Rossi"
                               required>
                        <?php if (isset($errors['full_name'])): ?>
                            <span class="form-error"><?php echo $errors['full_name']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Password -->
                    <div class="form-group <?php echo isset($errors['password']) ? 'has-error' : ''; ?>">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-input" 
                               placeholder="Almeno 6 caratteri"
                               required>
                        <?php if (isset($errors['password'])): ?>
                            <span class="form-error"><?php echo $errors['password']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Conferma Password -->
                    <div class="form-group <?php echo isset($errors['confirm_password']) ? 'has-error' : ''; ?>">
                        <label for="confirm_password" class="form-label">Conferma Password</label>
                        <input type="password" 
                               id="confirm_password" 
                               name="confirm_password" 
                               class="form-input" 
                               placeholder="Ripeti la password"
                               required>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <span class="form-error"><?php echo $errors['confirm_password']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn-admin">📝 Registrati</button>
                </form>
                
                <div class="admin-footer">
                    Hai già un account? <a href="login.php">Accedi</a>
                </div>
            <?php endif; ?>
            
            <div class="admin-footer" style="margin-top: 2rem;">
                <a href="../index.php">← Torna al sito</a>
            </div>
        </div>
    </div>
</body>
</html>