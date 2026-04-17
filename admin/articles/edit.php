<?php
/**
 * STUDIOLEX - Modifica Articolo (CRUD - UPDATE)
 * File: admin/articles/edit.php
 * 
 * Form per la modifica di un articolo esistente.
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

// Recupera l'articolo dal database
$article = querySingle("SELECT * FROM articles WHERE id = :id", [':id' => $id]);

// Se l'articolo non esiste, reindirizza
if (!$article) {
    $_SESSION['error_message'] = 'Articolo non trovato.';
    header('Location: list.php');
    exit;
}

// Variabili per il form (precaricate con i dati esistenti)
$title = $article['title'];
$excerpt = $article['excerpt'];
$content = $article['content'];
$category_id = $article['category_id'];
$published = $article['published'];
$errors = [];

// Recupera tutte le categorie per il select
$categories = query("SELECT id, name FROM categories ORDER BY name");

// Gestione aggiornamento articolo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recupera e sanitizza i dati
    $title = trim($_POST['title'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category_id = $_POST['category_id'] ?? '';
    $published = isset($_POST['published']) ? 1 : 0;
    
    // ============================================================
    // VALIDAZIONE BACKEND (Sicurezza reale qui!)
    // ============================================================
    
    // Validazione titolo
    if (empty($title)) {
        $errors['title'] = 'Il titolo è obbligatorio.';
    } elseif (strlen($title) < 3) {
        $errors['title'] = 'Il titolo deve contenere almeno 3 caratteri.';
    } elseif (strlen($title) > 200) {
        $errors['title'] = 'Il titolo non può superare i 200 caratteri.';
    }
    
    // Validazione contenuto
    if (empty($content)) {
        $errors['content'] = 'Il contenuto è obbligatorio.';
    } elseif (strlen($content) < 10) {
        $errors['content'] = 'Il contenuto deve essere di almeno 10 caratteri.';
    }
    
    // Validazione categoria
    if (empty($category_id)) {
        $errors['category_id'] = 'Seleziona una categoria.';
    } else {
        $cat_check = querySingle("SELECT id FROM categories WHERE id = :id", [':id' => $category_id]);
        if (!$cat_check) {
            $errors['category_id'] = 'Categoria non valida.';
        }
    }
    
    // Validazione excerpt
    if (!empty($excerpt) && strlen($excerpt) < 10) {
        $errors['excerpt'] = 'L\'estratto deve contenere almeno 10 caratteri.';
    }
    
    // Se non ci sono errori, aggiorna l'articolo
    if (empty($errors)) {
        // Genera lo slug dal titolo (solo se il titolo è cambiato)
        if ($title !== $article['title']) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
            
            // Verifica se lo slug esiste già (escludendo l'articolo corrente)
            $original_slug = $slug;
            $counter = 1;
            while (querySingle("SELECT id FROM articles WHERE slug = :slug AND id != :id", 
                              [':slug' => $slug, ':id' => $id])) {
                $slug = $original_slug . '-' . $counter;
                $counter++;
            }
        } else {
            $slug = $article['slug'];
        }
        
        // Se excerpt è vuoto, genera dal contenuto
        if (empty($excerpt)) {
            $excerpt = substr(strip_tags($content), 0, 150) . '...';
        }
        
        // Aggiorna nel database
        $update_sql = "UPDATE articles SET 
                       title = :title, 
                       slug = :slug, 
                       excerpt = :excerpt, 
                       content = :content, 
                       category_id = :category_id, 
                       published = :published,
                       updated_at = NOW()
                       WHERE id = :id";
        
        try {
            execute($update_sql, [
                ':title' => $title,
                ':slug' => $slug,
                ':excerpt' => $excerpt,
                ':content' => $content,
                ':category_id' => $category_id ?: null,
                ':published' => $published,
                ':id' => $id
            ]);
            
            $_SESSION['success_message'] = 'Articolo aggiornato con successo!';
            header('Location: list.php');
            exit;
            
        } catch (PDOException $e) {
            $errors['general'] = 'Errore durante l\'aggiornamento dell\'articolo. Riprova.';
            error_log('Errore aggiornamento articolo: ' . $e->getMessage());
        }
    }
}

// Imposta titolo pagina
$page_title = 'Modifica Articolo - StudioLex Admin';
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
        
        /* Form */
        .form-container {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            max-width: 900px;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #1A1A1A;
        }
        
        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }
        
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #8B4513;
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
        }
        
        .form-textarea {
            resize: vertical;
            min-height: 300px;
        }
        
        .has-error .form-input, .has-error .form-select, .has-error .form-textarea {
            border-color: #e53e3e;
        }
        
        .form-error {
            color: #e53e3e;
            font-size: 0.85rem;
            margin-top: 0.25rem;
            display: block;
        }
        
        .form-hint {
            color: #718096;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        
        .form-checkbox {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .form-checkbox input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .form-checkbox label {
            cursor: pointer;
            font-weight: 500;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }
        
        .alert-error {
            background: #fed7d7;
            border: 1px solid #f56565;
            color: #742a2a;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .article-meta {
            background: #f7fafc;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            color: #4a5568;
        }
        
        .article-meta span {
            margin-right: 1.5rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .admin-sidebar { display: none; }
            .admin-main { margin-left: 0; }
            .form-row { grid-template-columns: 1fr; }
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
                <h1 class="page-title">✏️ Modifica Articolo</h1>
                <a href="list.php" class="btn btn-outline">← Torna alla lista</a>
            </div>
            
            <?php if (isset($errors['general'])): ?>
                <div class="alert-error">❌ <?php echo $errors['general']; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <!-- Meta informazioni articolo -->
                <div class="article-meta">
                    <span>📅 Creato: <?php echo date('d/m/Y H:i', strtotime($article['created_at'])); ?></span>
                    <?php if ($article['updated_at']): ?>
                        <span>🔄 Ultima modifica: <?php echo date('d/m/Y H:i', strtotime($article['updated_at'])); ?></span>
                    <?php endif; ?>
                    <span>👁️ Visualizzazioni: <?php echo $article['views']; ?></span>
                    <span>🔗 Slug: <?php echo htmlspecialchars($article['slug']); ?></span>
                </div>
                
                <form method="POST" action="edit.php?id=<?php echo $id; ?>" novalidate>
                    <!-- Titolo -->
                    <div class="form-group <?php echo isset($errors['title']) ? 'has-error' : ''; ?>">
                        <label for="title" class="form-label">Titolo *</label>
                        <input type="text" 
                               id="title" 
                               name="title" 
                               class="form-input" 
                               value="<?php echo htmlspecialchars($title); ?>"
                               placeholder="es. La riforma del processo civile 2026"
                               required>
                        <?php if (isset($errors['title'])): ?>
                            <span class="form-error"><?php echo $errors['title']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-row">
                        <!-- Categoria -->
                        <div class="form-group <?php echo isset($errors['category_id']) ? 'has-error' : ''; ?>">
                            <label for="category_id" class="form-label">Categoria *</label>
                            <select id="category_id" name="category_id" class="form-select" required>
                                <option value="">-- Seleziona una categoria --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['category_id'])): ?>
                                <span class="form-error"><?php echo $errors['category_id']; ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Stato Pubblicazione -->
                        <div class="form-group">
                            <label class="form-label">Stato</label>
                            <div class="form-checkbox">
                                <input type="checkbox" id="published" name="published" value="1" <?php echo $published ? 'checked' : ''; ?>>
                                <label for="published">✅ Pubblicato</label>
                            </div>
                            <p class="form-hint">Se deselezionato, l'articolo verrà salvato come bozza.</p>
                        </div>
                    </div>
                    
                    <!-- Estratto -->
                    <div class="form-group <?php echo isset($errors['excerpt']) ? 'has-error' : ''; ?>">
                        <label for="excerpt" class="form-label">Estratto</label>
                        <textarea id="excerpt" 
                                  name="excerpt" 
                                  class="form-textarea" 
                                  rows="3"
                                  placeholder="Un breve riassunto dell'articolo"><?php echo htmlspecialchars($excerpt); ?></textarea>
                        <?php if (isset($errors['excerpt'])): ?>
                            <span class="form-error"><?php echo $errors['excerpt']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Contenuto -->
                    <div class="form-group <?php echo isset($errors['content']) ? 'has-error' : ''; ?>">
                        <label for="content" class="form-label">Contenuto *</label>
                        <textarea id="content" 
                                  name="content" 
                                  class="form-textarea" 
                                  placeholder="Scrivi il contenuto dell'articolo..."
                                  required><?php echo htmlspecialchars($content); ?></textarea>
                        <?php if (isset($errors['content'])): ?>
                            <span class="form-error"><?php echo $errors['content']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Azioni -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">💾 Aggiorna Articolo</button>
                        <a href="list.php" class="btn btn-outline">Annulla</a>
                        <a href="../../article.php?slug=<?php echo urlencode($article['slug']); ?>" target="_blank" class="btn btn-outline" style="margin-left: auto;">👁️ Visualizza</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>