<?php
/**
 * STUDIOLEX - Login Area Riservata
 * File: admin/login.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';

// Se già loggato, vai alla dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$login = '';
$errors = [];
$success_message = '';

if (isset($_SESSION['register_success'])) {
    $success_message = 'Registrazione completata con successo! Ora puoi accedere.';
    unset($_SESSION['register_success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($login)) {
        $errors['login'] = 'Inserisci username o email.';
    }
    
    if (empty($password)) {
        $errors['password'] = 'La password è obbligatoria.';
    }
    
    if (empty($errors)) {
        // CERCA PER USERNAME O EMAIL (con placeholder separati)
        // AGGIUNTO: recuperiamo anche studio_id
        $sql = "SELECT id, username, email, password_hash, full_name, role, studio_id 
                FROM users 
                WHERE username = :username OR email = :email
                LIMIT 1";
        
        $user = querySingle($sql, [
            ':username' => $login,
            ':email' => $login
        ]);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['studio_id'] = $user['studio_id']; // <-- AGGIUNTO: Salva lo studio_id
            $_SESSION['login_time'] = time();
            
            session_regenerate_id(true);
            
            header('Location: dashboard.php');
            exit;
        } else {
            $errors['general'] = 'Credenziali non valide. Riprova.';
            error_log("Login fallito per: " . $login);
        }
    }
}

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
        .admin-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1a365d 0%, #2b6cb0 100%);
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
            font-family: 'Merriweather', serif;
            font-size: 2rem;
            font-weight: 700;
            color: #1a202c;
        }
        .admin-logo span {
            color: #1a365d;
        }
        .admin-title {
            font-size: 1.5rem;
            text-align: center;
            margin-bottom: 0.5rem;
        }
        .admin-subtitle {
            text-align: center;
            color: #4a5568;
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
            color: #1a202c;
        }
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-input:focus {
            outline: none;
            border-color: #1a365d;
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
        .forgot-password {
            color: #1a365d;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .btn-admin {
            width: 100%;
            padding: 0.875rem;
            background: #1a365d;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-admin:hover {
            background: #2c1810;
            transform: translateY(-2px);
        }
        .admin-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: #4a5568;
        }
        .admin-footer a {
            color: #1a365d;
            text-decoration: none;
            font-weight: 600;
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
                <div class="alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($errors['general'])): ?>
                <div class="alert-error">⚠️ <?php echo $errors['general']; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="login.php" novalidate>
                <div class="form-group <?php echo isset($errors['login']) ? 'has-error' : ''; ?>">
                    <label for="login" class="form-label">Username o Email</label>
                    <input type="text" id="login" name="login" class="form-input" 
                           value="<?php echo htmlspecialchars($login); ?>"
                           placeholder="es. mario_rossi o mario@email.it" required>
                    <?php if (isset($errors['login'])): ?>
                        <span class="form-error"><?php echo $errors['login']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group <?php echo isset($errors['password']) ? 'has-error' : ''; ?>">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" class="form-input" 
                               placeholder="••••••••" required>
                        <button type="button" class="password-toggle" id="togglePassword">👁️</button>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <span class="form-error"><?php echo $errors['password']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember"> Ricordami
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
    
    <script>
    document.getElementById('togglePassword').addEventListener('click', function() {
        const pwd = document.getElementById('password');
        const type = pwd.getAttribute('type') === 'password' ? 'text' : 'password';
        pwd.setAttribute('type', type);
        this.textContent = type === 'password' ? '👁️' : '🙈';
    });
    </script>
</body>
</html>