<?php
/**
 * STUDIOLEX - Lista Articoli (CRUD - READ)
 * File: admin/articles/list.php
 * 
 * Mostra tutti gli articoli con filtri, ricerca e azioni (modifica, elimina).
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

// Parametri di filtro e ricerca
$search = trim($_GET['search'] ?? '');
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Costruisce la query con filtri
$where_clauses = [];
$params = [];

if (!empty($search)) {
    $where_clauses[] = "(a.title LIKE :search OR a.content LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if (!empty($category_filter)) {
    $where_clauses[] = "c.slug = :category";
    $params[':category'] = $category_filter;
}

if ($status_filter !== '') {
    $where_clauses[] = "a.published = :status";
    $params[':status'] = $status_filter;
}

$where_sql = empty($where_clauses) ? '' : 'WHERE ' . implode(' AND ', $where_clauses);

// Query per contare il totale (per paginazione)
$count_sql = "SELECT COUNT(*) as total 
              FROM articles a 
              LEFT JOIN categories c ON a.category_id = c.id 
              $where_sql";
$total_result = querySingle($count_sql, $params);
$total_articles = $total_result['total'];
$total_pages = ceil($total_articles / $per_page);

// Query per recuperare gli articoli
$sql = "SELECT a.id, a.title, a.slug, a.published, a.views, a.created_at,
               c.name as category_name, c.slug as category_slug,
               u.full_name as author_name
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        JOIN users u ON a.user_id = u.id
        $where_sql
        ORDER BY a.created_at DESC
        LIMIT :offset, :per_page";

$params[':offset'] = $offset;
$params[':per_page'] = $per_page;

// Esegui query con parametri di paginazione (gestione speciale per LIMIT)
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmt->bindValue($key, $value, $type);
}
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recupera tutte le categorie per il filtro
$categories = query("SELECT * FROM categories ORDER BY name");

// Messaggio di successo dopo operazioni (create/update/delete)
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Imposta titolo pagina
$page_title = 'Gestione Articoli - StudioLex Admin';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        /* Stili Admin Panel (ereditati da dashboard) */
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
        
        /* Filtri */
        .filters-container {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        
        .filters-form {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #4a5568;
        }
        
        .filter-input, .filter-select {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
        }
        
        .filter-input:focus, .filter-select:focus {
            outline: none;
            border-color: #8B4513;
        }
        
        .filter-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        /* Tabella */
        .table-container {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .admin-table th {
            text-align: left;
            padding: 0.75rem;
            border-bottom: 2px solid #e2e8f0;
            color: #4a5568;
            font-weight: 600;
            font-size: 0.85rem;
            background: #f7fafc;
        }
        
        .admin-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #e2e8f0;
            color: #1A1A1A;
        }
        
        .admin-table tr:last-child td {
            border-bottom: none;
        }
        
        .admin-table tr:hover {
            background: #f7fafc;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-published { background: #c6f6d5; color: #22543d; }
        .status-draft { background: #fed7d7; color: #742a2a; }
        
        .action-links {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-icon {
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }
        
        .btn-edit { background: #8B4513; color: white; }
        .btn-edit:hover { background: #2C1810; }
        .btn-delete { background: #e53e3e; color: white; }
        .btn-delete:hover { background: #c53030; }
        
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .alert-success { background: #c6f6d5; border: 1px solid #68d391; color: #22543d; }
        .alert-error { background: #fed7d7; border: 1px solid #f56565; color: #742a2a; }
        
        /* Paginazione */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }
        
        .page-link {
            padding: 0.5rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            text-decoration: none;
            color: #4a5568;
            transition: all 0.3s ease;
        }
        
        .page-link:hover, .page-link.active {
            background: #8B4513;
            color: white;
            border-color: #8B4513;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #4a5568;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar (identica alla dashboard) -->
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
                <h1 class="page-title">Gestione Articoli</h1>
                <div class="header-actions">
                    <a href="create.php" class="btn btn-primary">✏️ Nuovo Articolo</a>
                </div>
            </div>
            
            <!-- Messaggi di successo/errore -->
            <?php if ($success_message): ?>
                <div class="alert alert-success">✅ <?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-error">❌ <?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <!-- Filtri e Ricerca -->
            <div class="filters-container">
                <form method="GET" action="list.php" class="filters-form">
                    <div class="filter-group">
                        <label for="search" class="filter-label">🔍 Cerca</label>
                        <input type="text" id="search" name="search" class="filter-input" 
                               placeholder="Titolo o contenuto..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="filter-group">
                        <label for="category" class="filter-label">📂 Categoria</label>
                        <select id="category" name="category" class="filter-select">
                            <option value="">Tutte le categorie</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['slug']; ?>" <?php echo $category_filter === $cat['slug'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="status" class="filter-label">📌 Stato</label>
                        <select id="status" name="status" class="filter-select">
                            <option value="">Tutti</option>
                            <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>Pubblicati</option>
                            <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>Bozze</option>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">Filtra</button>
                        <a href="list.php" class="btn btn-outline">Reset</a>
                    </div>
                </form>
            </div>
            
            <!-- Tabella Articoli -->
            <div class="table-container">
                <?php if (empty($articles)): ?>
                    <div class="empty-state">
                        <p style="font-size: 2rem; margin-bottom: 1rem;">📭</p>
                        <p>Nessun articolo trovato.</p>
                        <a href="create.php" class="btn btn-primary" style="margin-top: 1rem;">Crea il primo articolo</a>
                    </div>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Titolo</th>
                                <th>Categoria</th>
                                <th>Autore</th>
                                <th>Stato</th>
                                <th>Views</th>
                                <th>Data</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($articles as $article): ?>
                                <tr>
                                    <td>
                                        <a href="../../article.php?slug=<?php echo urlencode($article['slug']); ?>" target="_blank" style="color: #1A1A1A; text-decoration: none; font-weight: 500;">
                                            <?php echo htmlspecialchars($article['title']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($article['category_name'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($article['author_name']); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $article['published'] ? 'status-published' : 'status-draft'; ?>">
                                            <?php echo $article['published'] ? 'Pubblicato' : 'Bozza'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $article['views']; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($article['created_at'])); ?></td>
                                    <td class="action-links">
                                        <a href="edit.php?id=<?php echo $article['id']; ?>" class="btn-icon btn-edit">✏️ Modifica</a>
                                        <a href="delete.php?id=<?php echo $article['id']; ?>" 
                                           class="btn-icon btn-delete" 
                                           onclick="return confirm('Sei sicuro di voler eliminare questo articolo? L\'operazione è irreversibile.');">
                                            🗑️ Elimina
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <!-- Paginazione -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>&status=<?php echo urlencode($status_filter); ?>" class="page-link">← Precedente</a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>&status=<?php echo urlencode($status_filter); ?>" 
                                   class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>&status=<?php echo urlencode($status_filter); ?>" class="page-link">Successiva →</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>