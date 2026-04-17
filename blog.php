<?php
/**
 * STUDIOLEX - Pagina Blog
 * File: blog.php
 * 
 * Mostra tutti gli articoli pubblicati con filtro per categoria.
 */

require_once 'includes/config.php';

// Recupera il parametro categoria dalla URL (se presente)
$category_slug = isset($_GET['category']) ? $_GET['category'] : null;
$category_name = 'Tutti gli articoli';

// Se è selezionata una categoria, recupera il nome
if ($category_slug) {
    $cat_sql = "SELECT name FROM categories WHERE slug = :slug";
    $cat_result = querySingle($cat_sql, [':slug' => $category_slug]);
    if ($cat_result) {
        $category_name = htmlspecialchars($cat_result['name']);
    }
}

// Query per contare il totale degli articoli (per paginazione futura)
$count_sql = "SELECT COUNT(*) as total FROM articles WHERE published = 1";
$count_params = [];

if ($category_slug) {
    $count_sql .= " AND category_id IN (SELECT id FROM categories WHERE slug = :slug)";
    $count_params[':slug'] = $category_slug;
}

$total_articles = querySingle($count_sql, $count_params)['total'];

// Query per recuperare TUTTI gli articoli pubblicati
$sql = "SELECT a.id, a.title, a.slug, a.excerpt, a.content, a.created_at, a.views,
               c.name as category_name, c.slug as category_slug,
               u.full_name as author_name
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        JOIN users u ON a.user_id = u.id
        WHERE a.published = 1";

$params = [];

if ($category_slug) {
    $sql .= " AND c.slug = :slug";
    $params[':slug'] = $category_slug;
}

$sql .= " ORDER BY a.created_at DESC";

$articles = query($sql, $params);

// Recupera tutte le categorie per il filtro
$categories = query("SELECT * FROM categories ORDER BY name");

// Imposta titolo SEO
$page_title = $category_name . ' - Blog';
$page_description = 'Leggi gli ultimi articoli e approfondimenti legali dello StudioLex. ' . 
                   'Consulenza professionale in diritto civile, penale e del lavoro.';

require_once 'includes/header.php';
?>

<!-- ==================== PAGE HEADER ==================== -->
<section class="page-header">
    <div class="container">
        <h1 class="page-title"><?php echo $category_name; ?></h1>
        <p class="page-subtitle">
            <?php echo $total_articles; ?> articoli pubblicati
        </p>
    </div>
</section>

<!-- ==================== BLOG CONTENT ==================== -->
<section class="blog-section">
    <div class="container blog-container">
        <!-- Sidebar con filtri -->
        <aside class="blog-sidebar">
            <div class="sidebar-widget">
                <h3 class="widget-title">Categorie</h3>
                <ul class="category-list">
                    <li>
                        <a href="blog.php" 
                           class="<?php echo !$category_slug ? 'active' : ''; ?>">
                            📚 Tutti gli articoli
                            <span class="count">(<?php echo $total_articles; ?>)</span>
                        </a>
                    </li>
                    <?php foreach ($categories as $cat): ?>
                        <?php
                        // Conta articoli per questa categoria
                        $cat_count_sql = "SELECT COUNT(*) as cnt FROM articles a 
                                         JOIN categories c ON a.category_id = c.id 
                                         WHERE c.slug = :slug AND a.published = 1";
                        $cat_count = querySingle($cat_count_sql, [':slug' => $cat['slug']])['cnt'];
                        ?>
                        <li>
                            <a href="blog.php?category=<?php echo urlencode($cat['slug']); ?>" 
                               class="<?php echo ($category_slug === $cat['slug']) ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                                <span class="count">(<?php echo $cat_count; ?>)</span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="sidebar-widget">
                <h3 class="widget-title">Articoli più letti</h3>
                <?php
                $popular_sql = "SELECT title, slug, views 
                               FROM articles 
                               WHERE published = 1 
                               ORDER BY views DESC 
                               LIMIT 5";
                $popular = query($popular_sql);
                ?>
                <?php if (!empty($popular)): ?>
                    <ul class="popular-list">
                        <?php foreach ($popular as $pop): ?>
                            <li>
                                <a href="article.php?slug=<?php echo urlencode($pop['slug']); ?>">
                                    <?php echo htmlspecialchars($pop['title']); ?>
                                    <span class="views">👁️ <?php echo $pop['views']; ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Lista articoli -->
        <div class="blog-content">
            <?php if (empty($articles)): ?>
                <div class="no-articles">
                    <p>📭 Nessun articolo pubblicato in questa categoria.</p>
                    <a href="blog.php" class="btn btn-primary">Vedi tutti gli articoli</a>
                </div>
            <?php else: ?>
                <div class="articles-list">
                    <?php foreach ($articles as $article): ?>
                        <article class="article-item">
                            <div class="article-header">
                                <span class="article-category-badge">
                                    <?php echo htmlspecialchars($article['category_name'] ?? 'Senza categoria'); ?>
                                </span>
                                <h2 class="article-item-title">
                                    <a href="article.php?slug=<?php echo urlencode($article['slug']); ?>">
                                        <?php echo htmlspecialchars($article['title']); ?>
                                    </a>
                                </h2>
                            </div>
                            
                            <div class="article-item-meta">
                                <span>✍️ <?php echo htmlspecialchars($article['author_name']); ?></span>
                                <span>📅 <?php echo date('d/m/Y', strtotime($article['created_at'])); ?></span>
                                <span>👁️ <?php echo $article['views']; ?> visualizzazioni</span>
                            </div>
                            
                            <div class="article-item-excerpt">
                                <?php 
                                $excerpt = $article['excerpt'] ?? strip_tags($article['content']);
                                echo htmlspecialchars(substr($excerpt, 0, 250)) . '...';
                                ?>
                            </div>
                            
                            <a href="article.php?slug=<?php echo urlencode($article['slug']); ?>" 
                               class="read-more-link">
                                Continua a leggere →
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>