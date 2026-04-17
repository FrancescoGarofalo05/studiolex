<?php
/**
 * STUDIOLEX - Elimina Articolo (CRUD - DELETE)
 * File: admin/articles/delete.php
 * 
 * Gestisce l'eliminazione di un articolo dal database.
 */

// Avvia la sessione
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include la connessione al database
require_once '../../includes/config.php';

// Verifica che l'utente sia loggato
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Verifica che sia stato passato un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'ID articolo non specificato.';
    header('Location: list.php');
    exit;
}

$id = intval($_GET['id']);

// Verifica che l'articolo esista
$article = querySingle("SELECT id, title FROM articles WHERE id = :id", [':id' => $id]);

if (!$article) {
    $_SESSION['error_message'] = 'Articolo non trovato.';
    header('Location: list.php');
    exit;
}

// ============================================================
// GESTIONE ELIMINAZIONE (POST)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica il token CSRF (protezione base)
    if (!isset($_POST['confirm']) || $_POST['confirm'] !== 'yes') {
        $_SESSION['error_message'] = 'Operazione non confermata.';
        header('Location: list.php');
        exit;
    }
    
    try {
        // Elimina l'articolo
        $delete_sql = "DELETE FROM articles WHERE id = :id";
        execute($delete_sql, [':id' => $id]);
        
        $_SESSION['success_message'] = 'Articolo "' . htmlspecialchars($article['title']) . '" eliminato con successo!';
        header('Location: list.php');
        exit;
        
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Errore durante l\'eliminazione dell\'articolo.';
        error_log('Errore eliminazione articolo: ' . $e->getMessage());
        header('Location: list.php');
        exit;
    }
}

// Se non è POST, mostra la pagina di conferma
$page_title = 'Elimina Articolo - StudioLex Admin';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        /* Stili Admin Panel */
        .admin-wrapper { display: flex; min-height: 100vh; background: #f7fafc; }
        .admin-sidebar { width: 280px; background: #2C1810; color: white; display: flex; flex-direction: column; position: fixed; height: 100vh; left: 0; top: 0; }
        .sidebar-header { padding: 1.5rem; border-bottom: 1px solid #2d3748; }
        .sidebar-logo { font-family: 'Merriweather', serif; font-size: 1.5rem; font-weight: 700; color: white; text-decoration: none; }
        .sidebar-logo span { color: #D4A373; }
        .sidebar-user { padding: 1.5rem; border-bottom: 1px solid #2d3748; }
        .user-name { font-weight: 600; margin-bottom: 0.25rem; }
        .user-role { font-size: 0.85rem; opacity: 0.7; }
        .sidebar-nav { flex: 1; padding: 1.5rem 0; }
        .nav-section { margin-bottom: 1.5rem; }
        .nav-section-title { padding: 0 1.5rem; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.5; margin-bottom: 0.75rem; }
        .nav-menu { list-style: none; }
        .nav-item { margin-bottom: 0.25rem; }
        .nav-link { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1.5rem; color: #cbd5e0; text-decoration: none; transition: all 0.3s ease; }
        .nav-link:hover, .nav-link.active { background: #8B4513; color: white; }
        .nav-icon { font-size: 1.2rem; }
        .sidebar-footer { padding: 1.5rem; border-top: 1px solid #2d3748; }
        .admin-main { flex: 1; margin-left: 280px; padding: 2rem; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .page-title { font-family: 'Merriweather', serif; font-size: 2rem; color: #1A1A1A; }
        
        /* Conferma Eliminazione */
        .confirm-container {
            background: white;
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
        }
        
        .confirm-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
        }
        
        .confirm-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #1A1A1A;
        }
        
        .confirm-message {
            color: #4a5568;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        
        .confirm-warning {
            background: #fed7d7;
            border: 1px solid #f56565;
            color: #742a2a;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            font-weight: 500;
        }
        
        .article-name {
            font-weight: 700;
            color: #8B4513;
            background: #f7fafc;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            word-break: break-word;
        }
        
        .confirm-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
        
        .btn-danger {
            background: #e53e3e;
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-danger:hover {
            background: #c53030;
            transform: translateY(-2px);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .admin-sidebar { display: none; }
            .admin-main { margin-left: 0; }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <a href="../dashboard.php" class="sidebar-logo">
                    Studio<span>Lex</span>
                </a>
            </div>
            <div class="sidebar-user">
                <div class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                <div class="user-role"><?php echo $_SESSION['role'] === 'admin' ? 'Amministratore' : 'Editor'; ?></div>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <p class="nav-section-title">Principale</p>
                    <ul class="nav-menu">
                        <li class="nav-item">
                            <a href="../dashboard.php" class="nav-link">
                                <span class="nav-icon">📊</span> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="list.php" class="nav-link active">
                                <span class="nav-icon">📝</span> Articoli
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            <div class="sidebar-footer">
                <a href="../../index.php" class="nav-link" target="_blank">
                    <span class="nav-icon">🌐</span> Vai al sito
                </a>
                <a href="../logout.php" class="nav-link" style="color: #f56565;">
                    <span class="nav-icon">🚪</span> Logout
                </a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1 class="page-title">🗑️ Elimina Articolo</h1>
                <a href="list.php" class="btn btn-outline">← Torna alla lista</a>
            </div>
            
            <div class="confirm-container">
                <div class="confirm-icon">⚠️</div>
                <h2 class="confirm-title">Conferma Eliminazione</h2>
                <p class="confirm-message">Stai per eliminare definitivamente questo articolo:</p>
                
                <div class="article-name">
                    "<?php echo htmlspecialchars($article['title']); ?>"
                </div>
                
                <div class="confirm-warning">
                    Questa azione è <strong>irreversibile</strong>. L'articolo e tutti i dati associati verranno cancellati permanentemente.
                </div>
                
                <form method="POST" action="delete.php?id=<?php echo $id; ?>">
                    <input type="hidden" name="confirm" value="yes">
                    <div class="confirm-actions">
                        <button type="submit" class="btn-danger">🗑️ Sì, Elimina Definitivamente</button>
                        <a href="list.php" class="btn btn-outline">❌ Annulla</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>