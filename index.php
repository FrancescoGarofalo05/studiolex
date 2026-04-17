<?php
/**
 * STUDIOLEX - Homepage Pubblica
 * File: index.php
 * 
 * Mostra un hero di benvenuto e gli ultimi 3 articoli del blog.
 */

// Include la connessione al database
require_once 'includes/config.php';

// Recupera gli ultimi 3 articoli pubblicati
$sql = "SELECT a.id, a.title, a.slug, a.excerpt, a.created_at, 
               c.name as category_name, c.slug as category_slug,
               u.full_name as author_name
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        JOIN users u ON a.user_id = u.id
        WHERE a.published = 1
        ORDER BY a.created_at DESC
        LIMIT 3";

$latest_articles = query($sql);

// Imposta titolo e descrizione per SEO
$page_title = 'Consulenza Legale Professionale';
$page_description = 'StudioLex offre consulenza legale in diritto civile, penale e del lavoro. Affidati ai nostri esperti per tutelare i tuoi diritti.';

// Include l'header
require_once 'includes/header.php';
?>

<!-- ==================== HERO SECTION ==================== -->
<section class="hero">
    <div class="container hero-container">
        <div class="hero-content">
            <h1 class="hero-title">
                Tutela Legale<br>
                <span class="hero-accent">su Misura per Te</span>
            </h1>
            <p class="hero-text">
                Dal 2010 offriamo consulenza legale professionale in diritto civile, 
                penale e del lavoro. La tua serenità è la nostra priorità.
            </p>
            <div class="hero-actions">
                <a href="contact.php" class="btn btn-primary">Richiedi una Consulenza</a>
                <a href="blog.php" class="btn btn-outline">Leggi il Nostro Blog</a>
            </div>
        </div>
        <div class="hero-image" aria-hidden="true">
            <span class="hero-icon">⚖️</span>
        </div>
    </div>
</section>

<!-- ==================== ULTIMI ARTICOLI ==================== -->
<section class="featured-articles">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Ultimi Articoli dal Blog</h2>
            <p class="section-subtitle">Rimani aggiornato sulle ultime novità legali</p>
        </div>

        <?php if (empty($latest_articles)): ?>
            <!-- Nessun articolo trovato -->
            <div class="no-articles">
                <p>📭 Nessun articolo pubblicato. Torna presto!</p>
            </div>
        <?php else: ?>
            <!-- Griglia articoli -->
            <div class="articles-grid">
                <?php foreach ($latest_articles as $article): ?>
                    <article class="article-card">
                        <div class="article-category">
                            <a href="blog.php?category=<?php echo htmlspecialchars($article['category_slug']); ?>" 
                               class="category-badge">
                                <?php echo htmlspecialchars($article['category_name'] ?? 'Senza categoria'); ?>
                            </a>
                        </div>
                        <h3 class="article-title">
                            <a href="article.php?slug=<?php echo htmlspecialchars($article['slug']); ?>">
                                <?php echo htmlspecialchars($article['title']); ?>
                            </a>
                        </h3>
                        <p class="article-excerpt">
                            <?php echo htmlspecialchars($article['excerpt'] ?? substr(strip_tags($article['content'] ?? ''), 0, 150) . '...'); ?>
                        </p>
                        <div class="article-meta">
                            <span class="article-author">✍️ <?php echo htmlspecialchars($article['author_name']); ?></span>
                            <span class="article-date">📅 <?php echo date('d/m/Y', strtotime($article['created_at'])); ?></span>
                        </div>
                        <a href="article.php?slug=<?php echo htmlspecialchars($article['slug']); ?>" 
                           class="read-more">
                            Leggi tutto →
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="view-all">
            <a href="blog.php" class="btn btn-secondary">📚 Vedi tutti gli articoli</a>
        </div>
    </div>
</section>

<!-- ==================== SEZIONE AREE DI PRATICA ==================== -->
<section class="practice-areas">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Le Nostre Aree di Pratica</h2>
            <p class="section-subtitle">Competenza ed esperienza al tuo servizio</p>
        </div>
        
        <div class="areas-grid">
            <div class="area-card">
                <div class="area-icon">🏛️</div>
                <h3>Diritto Civile</h3>
                <p>Contratti, responsabilità civile, diritti reali, successioni e diritto di famiglia.</p>
                <a href="blog.php?category=diritto-civile">Scopri di più →</a>
            </div>
            <div class="area-card">
                <div class="area-icon">⚖️</div>
                <h3>Diritto Penale</h3>
                <p>Difesa penale, reati contro la persona, reati patrimoniali e diritto penale d'impresa.</p>
                <a href="blog.php?category=diritto-penale">Scopri di più →</a>
            </div>
            <div class="area-card">
                <div class="area-icon">💼</div>
                <h3>Diritto del Lavoro</h3>
                <p>Contratti di lavoro, licenziamenti, mobbing, sicurezza sul lavoro e previdenza.</p>
                <a href="blog.php?category=diritto-lavoro">Scopri di più →</a>
            </div>
        </div>
    </div>
</section>

<!-- ==================== CALL TO ACTION ==================== -->
<section class="cta-section">
    <div class="container cta-container">
        <h2 class="cta-title">Hai bisogno di una consulenza legale?</h2>
        <p class="cta-text">Contattaci oggi stesso per una prima valutazione del tuo caso.</p>
        <a href="contact.php" class="btn btn-primary btn-large">📞 Contattaci Ora</a>
    </div>
</section>

<?php
// Include il footer
require_once 'includes/footer.php';
?>