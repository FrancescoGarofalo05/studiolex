<?php
/**
 * STUDIOLEX - Pagina Singolo Articolo
 * File: article.php
 * 
 * Mostra il contenuto completo di un articolo e incrementa il contatore visualizzazioni.
 */

require_once 'includes/config.php';

// Verifica che sia stato passato uno slug
if (!isset($_GET['slug']) || empty($_GET['slug'])) {
    header('Location: blog.php');
    exit;
}

$slug = $_GET['slug'];

// Recupera l'articolo dal database
$sql = "SELECT a.id, a.title, a.slug, a.excerpt, a.content, a.views, a.created_at, a.updated_at,
               c.id as category_id, c.name as category_name, c.slug as category_slug,
               u.full_name as author_name, u.username
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        JOIN users u ON a.user_id = u.id
        WHERE a.slug = :slug AND a.published = 1";

$article = querySingle($sql, [':slug' => $slug]);

// Se l'articolo non esiste, reindirizza al blog
if (!$article) {
    header('Location: blog.php');
    exit;
}

// Incrementa il contatore visualizzazioni
$update_sql = "UPDATE articles SET views = views + 1 WHERE id = :id";
execute($update_sql, [':id' => $article['id']]);
$article['views']++; // Aggiorna anche il valore in memoria

// Recupera articoli correlati (stessa categoria)
$related_sql = "SELECT a.id, a.title, a.slug, a.excerpt, a.created_at,
                       u.full_name as author_name
                FROM articles a
                JOIN users u ON a.user_id = u.id
                WHERE a.category_id = :category_id 
                  AND a.id != :current_id 
                  AND a.published = 1
                ORDER BY a.created_at DESC
                LIMIT 3";

$related_articles = query($related_sql, [
    ':category_id' => $article['category_id'],
    ':current_id' => $article['id']
]);

// Imposta titolo e descrizione SEO
$page_title = htmlspecialchars($article['title']);
$page_description = htmlspecialchars($article['excerpt'] ?? substr(strip_tags($article['content']), 0, 160));

require_once 'includes/header.php';
?>

<!-- ==================== ARTICLE HEADER ==================== -->
<section class="article-header-section">
    <div class="container">
        <div class="article-header-content">
            <!-- Breadcrumb -->
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <ol>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="blog.php">Blog</a></li>
                    <?php if ($article['category_name']): ?>
                        <li>
                            <a href="blog.php?category=<?php echo urlencode($article['category_slug']); ?>">
                                <?php echo htmlspecialchars($article['category_name']); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li aria-current="page"><?php echo htmlspecialchars($article['title']); ?></li>
                </ol>
            </nav>
            
            <!-- Categoria -->
            <span class="article-category-tag">
                <?php echo htmlspecialchars($article['category_name'] ?? 'Senza categoria'); ?>
            </span>
            
            <!-- Titolo -->
            <h1 class="article-main-title"><?php echo htmlspecialchars($article['title']); ?></h1>
            
            <!-- Meta informazioni -->
            <div class="article-main-meta">
                <span class="meta-author">
                    <span aria-hidden="true">✍️</span> 
                    <?php echo htmlspecialchars($article['author_name']); ?>
                </span>
                <span class="meta-date">
                    <span aria-hidden="true">📅</span> 
                    <?php echo date('d/m/Y', strtotime($article['created_at'])); ?>
                </span>
                <?php if ($article['updated_at'] && $article['updated_at'] != $article['created_at']): ?>
                    <span class="meta-updated">
                        <span aria-hidden="true">🔄</span> 
                        Aggiornato: <?php echo date('d/m/Y', strtotime($article['updated_at'])); ?>
                    </span>
                <?php endif; ?>
                <span class="meta-views">
                    <span aria-hidden="true">👁️</span> 
                    <?php echo $article['views']; ?> visualizzazioni
                </span>
            </div>
        </div>
    </div>
</section>

<!-- ==================== ARTICLE CONTENT ==================== -->
<section class="article-content-section">
    <div class="container article-container">
        <!-- Contenuto principale -->
        <div class="article-main">
            <div class="article-body">
                <?php echo $article['content']; ?>
            </div>
            
            <!-- Share buttons -->
            <div class="article-share">
                <p class="share-title">📤 Condividi questo articolo</p>
                <div class="share-buttons">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://localhost/studiolex/article.php?slug=' . $article['slug']); ?>" 
                       target="_blank" rel="noopener" class="share-btn facebook">Facebook</a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('http://localhost/studiolex/article.php?slug=' . $article['slug']); ?>&text=<?php echo urlencode($article['title']); ?>" 
                       target="_blank" rel="noopener" class="share-btn twitter">Twitter</a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode('http://localhost/studiolex/article.php?slug=' . $article['slug']); ?>" 
                       target="_blank" rel="noopener" class="share-btn linkedin">LinkedIn</a>
                    <a href="whatsapp://send?text=<?php echo urlencode($article['title'] . ' - ' . 'http://localhost/studiolex/article.php?slug=' . $article['slug']); ?>" 
                       class="share-btn whatsapp">WhatsApp</a>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <aside class="article-sidebar">
            <!-- Autore -->
            <div class="sidebar-card author-card">
                <h3 class="sidebar-title">✍️ L'Autore</h3>
                <div class="author-info">
                    <p class="author-name"><?php echo htmlspecialchars($article['author_name']); ?></p>
                    <p class="author-bio">Avvocato specializzato in <?php echo htmlspecialchars($article['category_name'] ?? 'diritto'); ?>.</p>
                </div>
            </div>
            
            <!-- In questa categoria -->
            <div class="sidebar-card">
                <h3 class="sidebar-title">📂 In questa categoria</h3>
                <a href="blog.php?category=<?php echo urlencode($article['category_slug']); ?>" class="category-link">
                    <?php echo htmlspecialchars($article['category_name'] ?? 'Senza categoria'); ?>
                </a>
            </div>
        </aside>
    </div>
</section>

<!-- ==================== ARTICOLI CORRELATI ==================== -->
<?php if (!empty($related_articles)): ?>
<section class="related-articles">
    <div class="container">
        <h2 class="related-title">📖 Articoli correlati</h2>
        <div class="related-grid">
            <?php foreach ($related_articles as $related): ?>
                <article class="related-card">
                    <h3 class="related-card-title">
                        <a href="article.php?slug=<?php echo urlencode($related['slug']); ?>">
                            <?php echo htmlspecialchars($related['title']); ?>
                        </a>
                    </h3>
                    <p class="related-card-excerpt">
                        <?php echo htmlspecialchars($related['excerpt'] ?? 'Leggi l\'articolo completo...'); ?>
                    </p>
                    <div class="related-card-meta">
                        <span><?php echo htmlspecialchars($related['author_name']); ?></span>
                        <span><?php echo date('d/m/Y', strtotime($related['created_at'])); ?></span>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ==================== CTA ==================== -->
<section class="article-cta">
    <div class="container cta-container">
        <h2 class="cta-title">Hai bisogno di una consulenza su questo tema?</h2>
        <p class="cta-text">Contattaci per una valutazione personalizzata del tuo caso.</p>
        <a href="contact.php" class="btn btn-primary btn-large">📞 Richiedi una Consulenza</a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>