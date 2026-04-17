<?php
/**
 * STUDIOLEX - Footer Pubblico
 * File: includes/footer.php
 * 
 * Contiene il footer comune a tutte le pagine pubbliche.
 */
?>
    </main>
    <!-- Fine main content -->

    <!-- ==================== FOOTER ==================== -->
    <footer class="site-footer">
        <div class="container footer-container">
            <div class="footer-grid">
                <!-- Colonna 1: Info Studio -->
                <div class="footer-col">
                    <h3 class="footer-title">StudioLex</h3>
                    <p class="footer-text">Consulenza legale professionale dal 2010. Affidati alla nostra esperienza per tutelare i tuoi diritti.</p>
                    <p class="footer-text">
                        <span aria-hidden="true">📍</span> Via Roma 123, 80100 Napoli<br>
                        <span aria-hidden="true">📞</span> +39 333 1234567<br>
                        <span aria-hidden="true">✉️</span> info@studiolex.it
                    </p>
                </div>
                
                <!-- Colonna 2: Link Rapidi -->
                <div class="footer-col">
                    <h3 class="footer-title">Link Rapidi</h3>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="blog.php">Blog</a></li>
                        <li><a href="contact.php">Contatti</a></li>
                        <li><a href="admin/login.php">Area Riservata</a></li>
                    </ul>
                </div>
                
                <!-- Colonna 3: Aree di Pratica -->
                <div class="footer-col">
                    <h3 class="footer-title">Aree di Pratica</h3>
                    <ul class="footer-links">
                        <li><a href="blog.php?category=diritto-civile">Diritto Civile</a></li>
                        <li><a href="blog.php?category=diritto-penale">Diritto Penale</a></li>
                        <li><a href="blog.php?category=diritto-lavoro">Diritto del Lavoro</a></li>
                        <li><a href="blog.php?category=news-studio">News dello Studio</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Copyright -->
            <div class="footer-bottom">
                <p class="copyright">
                    © <?php echo date('Y'); ?> StudioLex - Tutti i diritti riservati | P.IVA 01234567890<br>
                    Designed by <strong>Francesco Garofalo</strong>
                </p>
                <div class="footer-legal">
                    <a href="#">Privacy Policy</a>
                    <span aria-hidden="true">|</span>
                    <a href="#">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="./assets/js/main.js"></script>
</body>
</html>