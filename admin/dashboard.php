<?php
/**
 * STUDIOLEX - Dashboard Admin
 * File: admin/dashboard.php
 * 
 * Pannello di controllo principale dopo il login.
 * Mostra statistiche filtrate per studio e la Passkey (solo per Admin).
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

// Recupera lo studio_id dalla sessione
$studio_id = $_SESSION['studio_id'] ?? null;

// Se l'utente è admin, recupera la Passkey del suo studio
$studio_passkey = '';
if ($_SESSION['role'] === 'admin' && $studio_id) {
    $studio = querySingle("SELECT passkey, name FROM studios WHERE id = :sid", [':sid' => $studio_id]);
    if ($studio) {
        $studio_passkey = $studio['passkey'];
        $studio_name = $studio['name'];
    }
}

// Recupera statistiche per la dashboard (FILTRATE PER studio_id)
$stats = [];

// Conteggio articoli totali
$sql = "SELECT COUNT(*) as total FROM articles WHERE studio_id = :sid";
$result = querySingle($sql, [':sid' => $studio_id]);
$stats['total_articles'] = $result['total'];

// Conteggio articoli pubblicati
$sql = "SELECT COUNT(*) as total FROM articles WHERE published = 1 AND studio_id = :sid";
$result = querySingle($sql, [':sid' => $studio_id]);
$stats['published_articles'] = $result['total'];

// Conteggio articoli in bozza
$sql = "SELECT COUNT(*) as total FROM articles WHERE published = 0 AND studio_id = :sid";
$result = querySingle($sql, [':sid' => $studio_id]);
$stats['draft_articles'] = $result['total'];

// Conteggio categorie (globale, non filtrato per studio)
$sql = "SELECT COUNT(*) as total FROM categories";
$result = querySingle($sql);
$stats['total_categories'] = $result['total'];

// Visualizzazioni totali
$sql = "SELECT SUM(views) as total FROM articles WHERE studio_id = :sid";
$result = querySingle($sql, [':sid' => $studio_id]);
$stats['total_views'] = $result['total'] ?? 0;

// Ultimi 5 articoli (FILTRATI PER studio_id)
$sql = "SELECT a.id, a.title, a.slug, a.published, a.views, a.created_at,
               c.name as category_name
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.studio_id = :sid
        ORDER BY a.created_at DESC
        LIMIT 5";
$recent_articles = query($sql, [':sid' => $studio_id]);

// Articoli più visti (FILTRATI PER studio_id)
$sql = "SELECT id, title, slug, views 
        FROM articles 
        WHERE published = 1 AND studio_id = :sid
        ORDER BY views DESC 
        LIMIT 5";
$popular_articles = query($sql, [':sid' => $studio_id]);

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
            transition: transform 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
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
            transition: margin-left 0.3s ease;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
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
        
        /* Passkey Box (solo Admin) */
        .passkey-box {
            background: linear-gradient(135deg, var(--color-primary) 0%, #2b6cb0 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .passkey-box h3 {
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .passkey-display {
            background: rgba(255,255,255,0.15);
            padding: 1rem 1.5rem;
            border-radius: 8px;
            font-family: monospace;
            font-size: 1.8rem;
            letter-spacing: 3px;
            text-align: center;
            backdrop-filter: blur(4px);
            border: 1px dashed rgba(255,255,255,0.3);
            margin: 1rem 0;
        }
        
        .passkey-note {
            font-size: 0.9rem;
            opacity: 0.9;
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
            overflow-x: auto;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .table-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--color-dark);
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
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
            white-space: nowrap;
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
            white-space: nowrap;
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
        
        /* Pulsante Menu Mobile */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1001;
            background: var(--color-primary);
            color: white;
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 8px;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            align-items: center;
            justify-content: center;
        }
        
        .mobile-menu-toggle:hover {
            background: var(--color-dark);
        }
        
        /* Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .sidebar-overlay.active {
            opacity: 1;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: flex;
            }
            
            .sidebar-overlay {
                display: block;
                pointer-events: none;
            }
            
            .sidebar-overlay.active {
                pointer-events: auto;
            }
            
            .admin-sidebar {
                transform: translateX(-100%);
                position: fixed;
                top: 0;
                left: 0;
                width: 260px;
                height: 100vh;
                box-shadow: 4px 0 20px rgba(0,0,0,0.3);
            }
            
            .admin-sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .admin-main {
                margin-left: 0 !important;
                padding: 15px !important;
                padding-top: 70px !important;
                width: 100% !important;
            }
            
            .admin-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .header-actions {
                width: 100%;
            }
            
            .header-actions .btn {
                width: 100%;
                text-align: center;
            }
            
            .passkey-display {
                font-size: 1.4rem;
                letter-spacing: 2px;
                padding: 0.75rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            
            .stat-card {
                padding: 15px;
            }
            
            .stat-value {
                font-size: 1.5rem;
            }
            
            .table-container {
                padding: 15px;
            }
            
            .table-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .btn-outline-sm {
                width: 100%;
                text-align: center;
            }
            
            .welcome-message {
                padding: 15px;
            }
            
            .welcome-message h2 {
                font-size: 1.2rem;
            }
            
            .action-links {
                flex-direction: column;
            }
        }
        
        @media (max-width: 480px) {
            .stat-icon {
                width: 40px;
                height: 40px;
                font-size: 1.2rem;
            }
            
            .stat-value {
                font-size: 1.3rem;
            }
            
            .page-title {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <!-- Pulsante Menu Mobile -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Menu">
        ☰
    </button>
    
    <!-- Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
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
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="articles/list.php" class="nav-link">
                                <span class="nav-icon">📝</span>
                                <span>Articoli</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <div class="sidebar-footer">
                <a href="../index.php" class="nav-link" target="_blank">
                    <span class="nav-icon">🌐</span>
                    <span>Vai al sito</span>
                </a>
                <a href="logout.php" class="nav-link" style="color: #f56565;">
                    <span class="nav-icon">🚪</span>
                    <span>Logout</span>
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
                <p>Ecco un riepilogo del tuo studio. Puoi gestire articoli e contenuti dal menu laterale.</p>
            </div>
            
            <!-- BOX PASSKEY (VISIBILE SOLO PER ADMIN) -->
            <?php if ($_SESSION['role'] === 'admin' && $studio_passkey): ?>
            <div class="passkey-box">
                <h3>🔑 Passkey del tuo Studio</h3>
                <p>Comunica questa Passkey ai tuoi dipendenti per permettergli di registrarsi e accedere al sistema.</p>
                <div class="passkey-display"><?php echo htmlspecialchars($studio_passkey); ?></div>
                <p class="passkey-note">⚠️ Conservala in un luogo sicuro. Ogni dipendente dovrà inserirla durante la registrazione.</p>
            </div>
            <?php endif; ?>
            
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
    
    <!-- Script per Menu Mobile -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('mobileMenuToggle');
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        if (toggle && sidebar && overlay) {
            toggle.addEventListener('click', function() {
                sidebar.classList.toggle('mobile-open');
                overlay.classList.toggle('active');
                this.textContent = sidebar.classList.contains('mobile-open') ? '✕' : '☰';
            });
            
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('active');
                toggle.textContent = '☰';
            });
            
            // Chiudi sidebar se si clicca su un link
            sidebar.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        sidebar.classList.remove('mobile-open');
                        overlay.classList.remove('active');
                        toggle.textContent = '☰';
                    }
                });
            });
            
            // Gestisci resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('mobile-open');
                    overlay.classList.remove('active');
                    toggle.textContent = '☰';
                }
            });
        }
    });
    </script>
</body>
</html>