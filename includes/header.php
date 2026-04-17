<?php
/**
 * STUDIOLEX - Header Pubblico
 * File: includes/header.php
 * 
 * Contiene l'intestazione HTML comune a tutte le pagine pubbliche.
 * Viene incluso in index.php, blog.php, article.php, contact.php
 */

// Avvia la sessione (necessaria per messaggi flash e eventuale utente loggato)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determina la pagina attuale per evidenziare il menu
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    
    <!-- SEO Dinamico (verrà sovrascritto dalle singole pagine) -->
    <title><?php echo isset($page_title) ? $page_title . ' | StudioLex' : 'StudioLex - Consulenza Legale Professionale'; ?></title>
    <meta name="description" content="<?php echo isset($page_description) ? $page_description : 'StudioLex offre consulenza legale professionale in diritto civile, penale e del lavoro. Affidati ai nostri esperti.'; ?>">
    <meta name="keywords" content="avvocato, consulenza legale, diritto civile, diritto penale, diritto del lavoro, studio legale">
    <meta name="author" content="Francesco Garofalo">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo isset($page_title) ? $page_title : 'StudioLex - Consulenza Legale'; ?>">
    <meta property="og:description" content="<?php echo isset($page_description) ? $page_description : 'StudioLex offre consulenza legale professionale.'; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="http://localhost/studiolex/<?php echo $current_page; ?>">
    
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚖️</text></svg>">
    
    <!-- CSS Personalizzato -->
    <link rel="stylesheet" href="./assets/css/style.css">
    
    <!-- Font Google (opzionale, per un look professionale) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Skip to content (accessibilità) -->
    <a href="#main-content" class="skip-to-content">Salta al contenuto principale</a>

    <!-- ==================== HEADER ==================== -->
    <header class="site-header">
        <div class="container header-container">
            <!-- Logo + Titolo -->
            <div class="logo-area">
                <a href="index.php" class="logo-link">
                    <span class="logo-icon" aria-hidden="true">⚖️</span>
                    <span class="site-title">Studio<span class="title-accent">Lex</span></span>
                </a>
            </div>

            <!-- Menu di Navigazione -->
            <nav class="main-nav" aria-label="Menu principale">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a href="blog.php" class="nav-link <?php echo $current_page == 'blog.php' ? 'active' : ''; ?>">Blog</a>
                    </li>
                    <li class="nav-item">
                        <a href="contact.php" class="nav-link <?php echo $current_page == 'contact.php' ? 'active' : ''; ?>">Contatti</a>
                    </li>
                </ul>
            </nav>

            <!-- Bottone Area Riservata -->
            <div class="header-actions">
                <a href="admin/login.php" class="btn btn-outline">Area Riservata</a>
            </div>
        </div>
    </header>

    <!-- ==================== MAIN CONTENT ==================== -->
    <main id="main-content">