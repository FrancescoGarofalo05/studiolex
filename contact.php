<?php
/**
 * STUDIOLEX - Pagina Contatti
 * File: contact.php
 * 
 * Mostra le informazioni di contatto e un form per inviare messaggi.
 */

require_once 'includes/config.php';

// Variabili per il form
$name = $email = $phone = $message = '';
$errors = [];
$success = false;

// Gestione invio form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recupera i dati dal form
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validazione lato server
    if (empty($name)) {
        $errors['name'] = 'Il nome è obbligatorio.';
    } elseif (strlen($name) < 2) {
        $errors['name'] = 'Il nome deve contenere almeno 2 caratteri.';
    }
    
    if (empty($email)) {
        $errors['email'] = 'L\'email è obbligatoria.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Inserisci un indirizzo email valido.';
    }
    
    if (empty($message)) {
        $errors['message'] = 'Il messaggio è obbligatorio.';
    } elseif (strlen($message) < 10) {
        $errors['message'] = 'Il messaggio deve contenere almeno 10 caratteri.';
    }
    
    // Se non ci sono errori, salva il messaggio nel database
    if (empty($errors)) {
        // Qui potresti salvare il messaggio in una tabella 'contacts'
        // Oppure inviare un'email di notifica
        
        // Per ora, simula l'invio con successo
        $success = true;
        
        // Resetta i campi dopo l'invio
        $name = $email = $phone = $message = '';
    }
}

// Imposta titolo SEO
$page_title = 'Contatti - StudioLex';
$page_description = 'Contatta lo StudioLex per una consulenza legale. Siamo a Napoli in Via Roma 123. Chiamaci o scrivici per informazioni.';

require_once 'includes/header.php';
?>

<!-- ==================== PAGE HEADER ==================== -->
<section class="page-header">
    <div class="container">
        <h1 class="page-title">Contattaci</h1>
        <p class="page-subtitle">Siamo qui per aiutarti. Scrivici o chiamaci per una consulenza.</p>
    </div>
</section>

<!-- ==================== CONTACT CONTENT ==================== -->
<section class="contact-section">
    <div class="container contact-container">
        <!-- Informazioni di contatto -->
        <div class="contact-info">
            <h2 class="contact-info-title">📞 Informazioni di Contatto</h2>
            
            <div class="info-cards">
                <div class="info-card">
                    <div class="info-icon">📍</div>
                    <h3>Indirizzo</h3>
                    <p>Via Roma 123<br>80100 Napoli (NA)<br>Italia</p>
                </div>
                
                <div class="info-card">
                    <div class="info-icon">📞</div>
                    <h3>Telefono</h3>
                    <p>+39 333 1234567<br>+39 081 1234567</p>
                </div>
                
                <div class="info-card">
                    <div class="info-icon">✉️</div>
                    <h3>Email</h3>
                    <p>info@studiolex.it<br>consulenze@studiolex.it</p>
                </div>
                
                <div class="info-card">
                    <div class="info-icon">🕐</div>
                    <h3>Orari</h3>
                    <p>Lunedì - Venerdì<br>9:00 - 13:00 / 15:00 - 19:00<br><br>Sabato su appuntamento</p>
                </div>
            </div>
            
            <!-- Mappa -->
            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d12071.48936428197!2d14.2095!3d40.9305!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133babbd6c5e6b5d%3A0xabcdef1234567890!2sVia%20Roma%2C%20Napoli%20NA!5e0!3m2!1sit!2sit!4v1712345678901!5m2!1sit!2sit" 
                        width="100%" 
                        height="350" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Mappa dello StudioLex a Napoli">
                </iframe>
            </div>
        </div>
        
        <!-- Form di contatto -->
        <div class="contact-form-container">
            <h2 class="contact-form-title">✍️ Scrivici un Messaggio</h2>
            <p class="contact-form-subtitle">Compila il form e ti risponderemo entro 24 ore.</p>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <span class="alert-icon">✅</span>
                    <div class="alert-content">
                        <strong>Messaggio inviato con successo!</strong>
                        <p>Grazie per averci contattato. Ti risponderemo al più presto.</p>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="contact.php" id="contact-form" class="contact-form" novalidate>
                <!-- Campo Nome -->
                <div class="form-group <?php echo isset($errors['name']) ? 'has-error' : ''; ?>">
                    <label for="name" class="form-label">Nome Completo *</label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           class="form-input" 
                           value="<?php echo htmlspecialchars($name); ?>"
                           placeholder="es. Mario Rossi"
                           required>
                    <?php if (isset($errors['name'])): ?>
                        <span class="form-error"><?php echo $errors['name']; ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Campo Email -->
                <div class="form-group <?php echo isset($errors['email']) ? 'has-error' : ''; ?>">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-input" 
                           value="<?php echo htmlspecialchars($email); ?>"
                           placeholder="es. mario.rossi@email.it"
                           required>
                    <?php if (isset($errors['email'])): ?>
                        <span class="form-error"><?php echo $errors['email']; ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Campo Telefono -->
                <div class="form-group">
                    <label for="phone" class="form-label">Telefono (opzionale)</label>
                    <input type="tel" 
                           id="phone" 
                           name="phone" 
                           class="form-input" 
                           value="<?php echo htmlspecialchars($phone); ?>"
                           placeholder="es. 333 1234567">
                </div>
                
                <!-- Campo Messaggio -->
                <div class="form-group <?php echo isset($errors['message']) ? 'has-error' : ''; ?>">
                    <label for="message" class="form-label">Messaggio *</label>
                    <textarea id="message" 
                              name="message" 
                              class="form-textarea" 
                              rows="5"
                              placeholder="Descrivi brevemente il tuo caso o la tua richiesta..."
                              required><?php echo htmlspecialchars($message); ?></textarea>
                    <?php if (isset($errors['message'])): ?>
                        <span class="form-error"><?php echo $errors['message']; ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Privacy Checkbox -->
                <div class="form-group form-checkbox">
                    <input type="checkbox" id="privacy" name="privacy" required>
                    <label for="privacy">
                        Ho letto e accetto la <a href="#" target="_blank">Privacy Policy</a> *
                    </label>
                </div>
                
                <!-- Pulsante Invio -->
                <button type="submit" class="btn btn-primary btn-block">
                    <span class="btn-text">📤 Invia Messaggio</span>
                </button>
                
                <p class="form-note">* Campi obbligatori</p>
            </form>
        </div>
    </div>
</section>

<!-- ==================== FAQ ==================== -->
<section class="faq-section">
    <div class="container">
        <h2 class="section-title">Domande Frequenti</h2>
        <p class="section-subtitle">Trova risposta alle domande più comuni</p>
        
        <div class="faq-grid">
            <div class="faq-item">
                <h3 class="faq-question">📋 Come posso fissare un appuntamento?</h3>
                <p class="faq-answer">Puoi chiamarci al numero +39 333 1234567, inviarci un'email a info@studiolex.it o compilare il form di contatto. Ti risponderemo entro 24 ore per fissare un appuntamento.</p>
            </div>
            
            <div class="faq-item">
                <h3 class="faq-question">💰 Quanto costa una consulenza?</h3>
                <p class="faq-answer">La prima consulenza conoscitiva è gratuita. Durante l'incontro valuteremo il tuo caso e ti forniremo un preventivo personalizzato in base alla complessità della pratica.</p>
            </div>
            
            <div class="faq-item">
                <h3 class="faq-question">🏛️ In quali aree del diritto operate?</h3>
                <p class="faq-answer">Siamo specializzati in diritto civile, diritto penale e diritto del lavoro. Offriamo consulenza e assistenza legale sia per privati che per aziende.</p>
            </div>
            
            <div class="faq-item">
                <h3 class="faq-question">📄 Quali documenti devo portare?</h3>
                <p class="faq-answer">Per la prima consulenza, porta tutti i documenti relativi al tuo caso: contratti, lettere, email, sentenze precedenti. Ti comunicheremo eventuali documenti aggiuntivi necessari.</p>
            </div>
        </div>
    </div>
</section>

<!-- JavaScript per validazione form -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contact-form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            let hasError = false;
            
            // Validazione nome
            const name = document.getElementById('name');
            const nameError = name.parentElement.querySelector('.form-error') || document.createElement('span');
            if (!name.value.trim()) {
                showError(name, 'Il nome è obbligatorio.');
                hasError = true;
            } else if (name.value.trim().length < 2) {
                showError(name, 'Il nome deve contenere almeno 2 caratteri.');
                hasError = true;
            } else {
                clearError(name);
            }
            
            // Validazione email
            const email = document.getElementById('email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email.value.trim()) {
                showError(email, 'L\'email è obbligatoria.');
                hasError = true;
            } else if (!emailRegex.test(email.value.trim())) {
                showError(email, 'Inserisci un indirizzo email valido.');
                hasError = true;
            } else {
                clearError(email);
            }
            
            // Validazione messaggio
            const message = document.getElementById('message');
            if (!message.value.trim()) {
                showError(message, 'Il messaggio è obbligatorio.');
                hasError = true;
            } else if (message.value.trim().length < 10) {
                showError(message, 'Il messaggio deve contenere almeno 10 caratteri.');
                hasError = true;
            } else {
                clearError(message);
            }
            
            // Validazione privacy
            const privacy = document.getElementById('privacy');
            if (!privacy.checked) {
                alert('Devi accettare la Privacy Policy per inviare il messaggio.');
                hasError = true;
            }
            
            if (hasError) {
                e.preventDefault();
            }
        });
        
        function showError(field, message) {
            field.classList.add('input-error');
            let errorSpan = field.parentElement.querySelector('.form-error');
            if (!errorSpan) {
                errorSpan = document.createElement('span');
                errorSpan.className = 'form-error';
                field.parentElement.appendChild(errorSpan);
            }
            errorSpan.textContent = message;
            field.parentElement.classList.add('has-error');
        }
        
        function clearError(field) {
            field.classList.remove('input-error');
            const errorSpan = field.parentElement.querySelector('.form-error');
            if (errorSpan) {
                errorSpan.remove();
            }
            field.parentElement.classList.remove('has-error');
        }
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>