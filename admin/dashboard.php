<?php
/**
 * STUDIOLEX - Dashboard Admin
 * File: admin/dashboard.php
 * 
 * Pannello di controllo principale dopo il login.
 */

// Avvia la sessione
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include la connessione al database
require_once '../includes/config.php';

// Verifica che l'utente sia loggato
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Recupera statistiche per la dashboard
$stats = [];

// Conteggio articoli totali
$sql = "SELECT COUNT(*) as total FROM articles";
$result = querySingle($sql);
$stats['total_articles'] = $result['total'];

// Conteggio articoli pubblicati
$sql = "SELECT COUNT(*) as total FROM articles WHERE published = 1";
$result = querySingle($sql);
$stats['published_articles'] = $result['total'];

// Conteggio articoli in bozza
$sql = "SELECT COUNT(*) as total FROM articles WHERE published = 0";
$result = querySingle($sql);
$stats['draft_articles'] = $result['total'];

// Conteggio categorie
$sql = "SELECT COUNT(*) as total FROM categories";
$result = querySingle($sql);
$stats['total_categories'] = $result['total'];

// Visualizzazioni totali
$sql = "SELECT SUM(views) as total FROM articles";
$result = querySingle($sql);
$stats['total_views'] = $result['total'] ?? 0;

// Ultimi 5 articoli
$sql = "SELECT a.id, a.title, a.slug, a.published, a.views, a.created_at,
               c.name as category_name
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        ORDER BY a.created_at DESC
        LIMIT 5";
$recent_articles = query($sql);

// Articoli più visti
$sql = "SELECT id, title, slug, views 
        FROM articles 
        WHERE published = 1 
        ORDER BY views DESC 
        LIMIT 5";
$popular_articles = query($sql);

// Imposta titolo pagina
$page_title = 'Dashboard - StudioLex Admin';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Stili Admin Panel */
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
            background: #f7fafc;
        }
        
        /* Sidebar */
        .admin-sidebar {
            width: 280px;
            background: var(--color-dark);
            color: white;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
        }
        
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid #2d3748;
        }
        
        .sidebar-logo {
            font-family: var(--font-secondary);
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }
        
        .sidebar-logo span {
            color: var(--color-secondary);
        }
        
        .sidebar-user {
            padding: 1.5rem;
            border-bottom: 1px solid #2d3748;
        }
        
        .user-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .user-role {
            font-size: 0.85rem;
            opacity: 0.7;
        }
        
        .sidebar-nav {
            flex: 1;
            padding: 1.5rem 0;
        }
        
        .nav-section {
            margin-bottom: 1.5rem;
        }
        
        .nav-section-title {
            padding: 0 1.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.5;
            margin-bottom: 0.75rem;
        }
        
        .nav-menu {
            list-style: none;
        }
        
        .nav-item {
            margin-bottom: 0.25rem;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            color: #cbd5e0;
            text-decoration: none;
            transition: var(--transition);
        }
        
        .nav-link:hover,
        .nav-link.active {
            background: var(--color-primary);
            color: white;
        }
        
        .nav-icon {
            font-size: 1.2rem;
        }
        
        .sidebar-footer {
            padding: 1.5rem;
            border-top: 1px solid #2d3748;
        }
        
        /* Main Content */
        .admin-main {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-family: var(--font-secondary);
            font-size: 2rem;
            color: var(--color-dark);
        }
        
        .header-actions {
            display: flex;
            gap: 1rem;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            background: var(--color-primary);
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .stat-content h3 {
            font-size: 0.85rem;
            color: var(--color-gray);
            margin-bottom: 0.25rem;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--color-dark);
        }
        
        /* Tables */
        .table-container {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            margin-bottom: 2rem;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .table-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--color-dark);
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .admin-table th {
            text-align: left;
            padding: 0.75rem 0;
            border-bottom: 2px solid #e2e8f0;
            color: var(--color-gray);
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .admin-table td {
            padding: 0.75rem 0;
            border-bottom: 1px solid #e2e8f0;
            color: var(--color-dark);
        }
        
        .admin-table tr:last-child td {
            border-bottom: none;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-published {
            background: #c6f6d5;
            color: #22543d;
        }
        
        .status-draft {
            background: #fed7d7;
            color: #742a2a;
        }
        
        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.8rem;
            background: var(--color-primary);
            color: white;
            border-radius: 6px;
            text-decoration: none;
            transition: var(--transition);
        }
        
        .btn-sm:hover {
            background: var(--color-dark);
        }
        
        .btn-outline-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.8rem;
            border: 1px solid var(--color-primary);
            color: var(--color-primary);
            border-radius: 6px;
            text-decoration: none;
            transition: var(--transition);
        }
        
        .btn-outline-sm:hover {
            background: var(--color-primary);
            color: white;
        }
        
        .action-links {
            display: flex;
            gap: 0.5rem;
        }
        
        .welcome-message {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        
        .welcome-message h2 {
            color: var(--color-primary);
            margin-bottom: 0.5rem;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                display: none;
            }
            
            .admin-main {
                margin-left: 0;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="sidebar-logo">
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
                            <a href="dashboard.php" class="nav-link active">
                                <span class="nav-icon">📊</span>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="articles/list.php" class="nav-link">
                                <span class="nav-icon">📝</span>
                                Articoli
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <div class="sidebar-footer">
                <a href="../index.php" class="nav-link" target="_blank">
                    <span class="nav-icon">🌐</span>
                    Vai al sito
                </a>
                <a href="logout.php" class="nav-link" style="color: #f56565;">
                    <span class="nav-icon">🚪</span>
                    Logout
                </a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1 class="page-title">Dashboard</h1>
                <div class="header-actions">
                    <a href="articles/create.php" class="btn btn-primary">✏️ Nuovo Articolo</a>
                </div>
            </div>
            
            <!-- Messaggio di benvenuto -->
            <div class="welcome-message">
                <h2>👋 Bentornato, <?php echo htmlspecialchars(explode(' ', $_SESSION['full_name'])[0]); ?>!</h2>
                <p>Ecco un riepilogo del tuo sito. Puoi gestire articoli e contenuti dal menu laterale.</p>
            </div>
            
            <!-- Statistiche -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📄</div>
                    <div class="stat-content">
                        <h3>Articoli Totali</h3>
                        <div class="stat-value"><?php echo $stats['total_articles']; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <h3>Pubblicati</h3>
                        <div class="stat-value"><?php echo $stats['published_articles']; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-content">
                        <h3>Bozze</h3>
                        <div class="stat-value"><?php echo $stats['draft_articles']; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👁️</div>
                    <div class="stat-content">
                        <h3>Visualizzazioni</h3>
                        <div class="stat-value"><?php echo number_format($stats['total_views']); ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Ultimi Articoli -->
            <div class="table-container">
                <div class="table-header">
                    <h2 class="table-title">📋 Ultimi Articoli</h2>
                    <a href="articles/list.php" class="btn-outline-sm">Vedi tutti →</a>
                </div>
                
                <?php if (empty($recent_articles)): ?>
                    <p style="color: var(--color-gray);">Nessun articolo presente. <a href="articles/create.php">Crea il primo articolo</a>.</p>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Titolo</th>
                                <th>Categoria</th>
                                <th>Stato</th>
                                <th>Visualizzazioni</th>
                                <th>Data</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_articles as $article): ?>
                                <tr>
                                    <td>
                                        <a href="../article.php?slug=<?php echo urlencode($article['slug']); ?>" target="_blank" style="color: var(--color-dark); text-decoration: none;">
                                            <?php echo htmlspecialchars($article['title']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($article['category_name'] ?? '-'); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $article['published'] ? 'status-published' : 'status-draft'; ?>">
                                            <?php echo $article['published'] ? 'Pubblicato' : 'Bozza'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $article['views']; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($article['created_at'])); ?></td>
                                    <td class="action-links">
                                        <a href="articles/edit.php?id=<?php echo $article['id']; ?>" class="btn-outline-sm">Modifica</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <!-- Articoli più visti -->
            <?php if (!empty($popular_articles)): ?>
            <div class="table-container">
                <div class="table-header">
                    <h2 class="table-title">🔥 Articoli più letti</h2>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Titolo</th>
                            <th>Visualizzazioni</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($popular_articles as $article): ?>
                            <tr>
                                <td>
                                    <a href="../article.php?slug=<?php echo urlencode($article['slug']); ?>" target="_blank" style="color: var(--color-dark); text-decoration: none;">
                                        <?php echo htmlspecialchars($article['title']); ?>
                                    </a>
                                </td>
                                <td><?php echo $article['views']; ?></td>
                                <td>
                                    <a href="articles/edit.php?id=<?php echo $article['id']; ?>" class="btn-outline-sm">Modifica</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>