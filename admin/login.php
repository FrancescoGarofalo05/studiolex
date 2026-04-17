<?php
/**
 * STUDIOLEX - Login Area Riservata
 * File: admin/login.php
 * 
 * Permette l'accesso all'admin panel tramite username/email e password.
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
$login = '';
$errors = [];
$success_message = '';

// Messaggio di successo dopo registrazione
if (isset($_SESSION['register_success'])) {
    $success_message = 'Registrazione completata con successo! Ora puoi accedere.';
    unset($_SESSION['register_success']);
}

// Gestione login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recupera e sanitizza i dati
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // ============================================================
    // VALIDAZIONE BACKEND (Sicurezza reale qui!)
    // ============================================================
    
    // Validazione login (username o email)
    if (empty($login)) {
        $errors['login'] = 'Inserisci username o email.';
    }
    
    // Validazione password
    if (empty($password)) {
        $errors['password'] = 'La password è obbligatoria.';
    }
    
    // Se non ci sono errori di validazione, procedi con l'autenticazione
    if (empty($errors)) {
        // Cerca l'utente per username O email (Prepared Statement anti SQL Injection)
        $sql = "SELECT id, username, email, password_hash, full_name, role 
                FROM users 
                WHERE username = :login OR email = :login
                LIMIT 1";
        
        $user = querySingle($sql, [':login' => $login]);
        
        // Verifica se l'utente esiste e la password è corretta
        if ($user && password_verify($password, $user['password_hash'])) {
            // Login riuscito! Crea la sessione
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_time'] = time();
            
            // Rigenera l'ID di sessione per sicurezza (previene session fixation)
            session_regenerate_id(true);
            
            // Reindirizza alla dashboard
            header('Location: dashboard.php');
            exit;
            
        } else {
            // Credenziali errate - Messaggio generico per sicurezza
            // (non specificare se è sbagliato username o password)
            $errors['general'] = 'Credenziali non valide. Riprova.';
            
            // Log del tentativo fallito (opzionale, per sicurezza)
            error_log("Tentativo di login fallito per: " . $login . " da IP: " . $_SERVER['REMOTE_ADDR']);
        }
    }
}

// Imposta titolo pagina
$page_title = 'Accedi - StudioLex Admin';
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
            max-width: 450px;
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
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
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
        
        .password-wrapper {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            opacity: 0.6;
            transition: var(--transition);
        }
        
        .password-toggle:hover {
            opacity: 1;
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .remember-me input {
            width: 16px;
            height: 16px;
        }
        
        .forgot-password {
            color: var(--color-primary);
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .forgot-password:hover {
            text-decoration: underline;
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
            
            <h1 class="admin-title">Area Riservata</h1>
            <p class="admin-subtitle">Accedi per gestire i contenuti del sito</p>
            
            <?php if ($success_message): ?>
                <div class="alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($errors['general'])): ?>
                <div class="alert-error">
                    ⚠️ <?php echo $errors['general']; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php" novalidate>
                <!-- Username o Email -->
                <div class="form-group <?php echo isset($errors['login']) ? 'has-error' : ''; ?>">
                    <label for="login" class="form-label">Username o Email</label>
                    <input type="text" 
                           id="login" 
                           name="login" 
                           class="form-input" 
                           value="<?php echo htmlspecialchars($login); ?>"
                           placeholder="es. mario_rossi o mario@email.it"
                           autocomplete="username"
                           required>
                    <?php if (isset($errors['login'])): ?>
                        <span class="form-error"><?php echo $errors['login']; ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Password -->
                <div class="form-group <?php echo isset($errors['password']) ? 'has-error' : ''; ?>">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-wrapper">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-input" 
                               placeholder="••••••••"
                               autocomplete="current-password"
                               required>
                        <button type="button" class="password-toggle" id="togglePassword" aria-label="Mostra password">👁️</button>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <span class="form-error"><?php echo $errors['password']; ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Opzioni aggiuntive -->
                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" value="1">
                        <span>Ricordami</span>
                    </label>
                    <a href="#" class="forgot-password">Password dimenticata?</a>
                </div>
                
                <button type="submit" class="btn-admin">🔐 Accedi</button>
            </form>
            
            <div class="admin-footer">
                Non hai un account? <a href="register.php">Registrati</a>
            </div>
            
            <div class="admin-footer" style="margin-top: 2rem;">
                <a href="../index.php">← Torna al sito</a>
            </div>
        </div>
    </div>
    
    <!-- Script per toggle password -->
    <script>
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.textContent = type === 'password' ? '👁️' : '🙈';
    });
    </script>
</body>
</html>