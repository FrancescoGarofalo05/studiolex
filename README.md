# ⚖️ StudioLex - CMS per Studio Legale

**Un sistema di gestione contenuti (CMS) completo per studi legali e professionisti.**

Sviluppato come progetto full-stack per dimostrare competenze in PHP, MySQL, JavaScript e architettura MVC. Include un'area pubblica con blog e un admin panel protetto per la gestione dei contenuti.

![StudioLex Dashboard](https://via.placeholder.com/800x400/1a365d/ffffff?text=StudioLex+Admin+Panel)

---

## 🚀 Caratteristiche Principali

### Area Pubblica

- 🏠 **Homepage** con hero, ultimi articoli e aree di pratica
- 📝 **Blog** con lista articoli, filtri per categoria e sidebar
- 📄 **Pagina singolo articolo** con conteggio visualizzazioni e articoli correlati
- 📞 **Pagina contatti** con form di invio messaggi e mappa Google
- 🔍 **SEO ottimizzato** con meta tag dinamici e URL slug

### Admin Panel (Area Riservata)

- 🔐 **Registrazione e Login** con password hashing (`password_hash`)
- 📊 **Dashboard** con statistiche (articoli totali, pubblicati, bozze, views)
- ✏️ **CRUD Completo Articoli:**
  - **C**reate: crea nuovi articoli con validazione
  - **R**ead: lista articoli con filtri, ricerca e paginazione
  - **U**pdate: modifica articoli esistenti
  - **D**elete: elimina articoli con conferma
- 👤 **Gestione ruoli**: Admin ed Editor
- 🛡️ **Sicurezza backend**: Prepared Statements PDO anti SQL Injection

---

## 🛠️ Tecnologie Utilizzate

| Tecnologia           | Utilizzo                                               |
| -------------------- | ------------------------------------------------------ |
| **PHP 8+**           | Backend, logica di business, autenticazione            |
| **MySQL**            | Database relazionale                                   |
| **PDO**              | Connessione sicura al database con Prepared Statements |
| **HTML5**            | Struttura semantica delle pagine                       |
| **CSS3**             | Styling responsive mobile-first (Flexbox, Grid)        |
| **JavaScript (ES6)** | Validazione form, interattività UI                     |
| **XAMPP**            | Ambiente di sviluppo locale (Apache + MySQL)           |

---

## 📁 Struttura del Progetto

studiolex/
├── index.php # Homepage pubblica
├── blog.php # Lista articoli pubblici
├── article.php # Singolo articolo
├── contact.php # Pagina contatti
├── admin/
│ ├── register.php # Registrazione nuovi utenti
│ ├── login.php # Accesso area riservata
│ ├── logout.php # Disconnessione
│ ├── dashboard.php # Dashboard admin
│ └── articles/
│ ├── list.php # Lista articoli (READ)
│ ├── create.php # Crea articolo (CREATE)
│ ├── edit.php # Modifica articolo (UPDATE)
│ └── delete.php # Elimina articolo (DELETE)
├── includes/
│ ├── config.php # Connessione database PDO
│ ├── header.php # Header pubblico
│ ├── footer.php # Footer pubblico
│ ├── auth.php # Verifica autenticazione
│ └── functions.php # Funzioni helper
├── assets/
│ ├── css/
│ │ └── style.css # Stili completi
│ └── js/
│ └── main.js # JavaScript personalizzato
├── .htaccess # Protezione cartelle (opzionale)
└── README.md # Documentazione

---

## 🔐 Sicurezza Implementata

| Minaccia                       | Protezione                                                       |
| ------------------------------ | ---------------------------------------------------------------- |
| **SQL Injection**              | Prepared Statements PDO con placeholder                          |
| **XSS (Cross-Site Scripting)** | `htmlspecialchars()` su tutti gli output                         |
| **Password in chiaro**         | Hashing con `password_hash()` e verifica con `password_verify()` |
| **Session Fixation**           | `session_regenerate_id(true)` dopo login                         |
| **Accesso non autorizzato**    | Controllo sessione su tutte le pagine admin                      |
| **CSRF base**                  | Conferma eliminazione con POST                                   |

---

## 🗄️ Struttura Database

### Tabella `users`

| Campo           | Tipo                   | Descrizione         |
| --------------- | ---------------------- | ------------------- |
| `id`            | INT (PK)               | Chiave primaria     |
| `username`      | VARCHAR(50)            | Nome utente univoco |
| `email`         | VARCHAR(100)           | Email univoca       |
| `password_hash` | VARCHAR(255)           | Password hashata    |
| `full_name`     | VARCHAR(100)           | Nome completo       |
| `role`          | ENUM('admin','editor') | Ruolo utente        |
| `created_at`    | TIMESTAMP              | Data registrazione  |

### Tabella `categories`

| Campo        | Tipo        | Descrizione     |
| ------------ | ----------- | --------------- |
| `id`         | INT (PK)    | Chiave primaria |
| `name`       | VARCHAR(50) | Nome categoria  |
| `slug`       | VARCHAR(50) | Slug per URL    |
| `created_at` | TIMESTAMP   | Data creazione  |

### Tabella `articles`

| Campo         | Tipo         | Descrizione               |
| ------------- | ------------ | ------------------------- |
| `id`          | INT (PK)     | Chiave primaria           |
| `title`       | VARCHAR(200) | Titolo articolo           |
| `slug`        | VARCHAR(200) | Slug univoco per URL      |
| `excerpt`     | TEXT         | Estratto/riassunto        |
| `content`     | LONGTEXT     | Contenuto completo (HTML) |
| `category_id` | INT (FK)     | Riferimento categoria     |
| `user_id`     | INT (FK)     | Autore articolo           |
| `published`   | BOOLEAN      | Stato pubblicazione       |
| `views`       | INT          | Contatore visualizzazioni |
| `created_at`  | TIMESTAMP    | Data creazione            |
| `updated_at`  | TIMESTAMP    | Data ultima modifica      |

---

## 🚀 Come Testare in Locale

### Prerequisiti

- [XAMPP](https://www.apachefriends.org/) (o qualsiasi stack LAMP/WAMP/MAMP)
- PHP 7.4 o superiore
- MySQL 5.7 o superiore

### Installazione

1. **Clona il repository:**

   ```bash
   git clone https://github.com/tuo-username/studiolex.git

   Sposta la cartella in htdocs:
   ```

text
C:\xampp\htdocs\studiolex\
Avvia Apache e MySQL dal pannello di controllo XAMPP.

Crea il database:

Vai su http://localhost/phpmyadmin

Crea un nuovo database chiamato studiolex_db (utf8mb4_general_ci)

Importa il file database.sql (se fornito) o esegui le query nella sezione "Struttura Database"

Configura la connessione:

Apri includes/config.php

Verifica che le credenziali corrispondano al tuo ambiente:

php
define('DB_HOST', 'localhost');
define('DB_NAME', 'studiolex_db');
define('DB_USER', 'root');
define('DB_PASS', '');
Inserisci dati di esempio (opzionale):

Esegui le query SQL fornite nella sezione "Dati di Esempio"

Accedi al sito:

Sito pubblico: http://localhost/studiolex/

Admin Panel: http://localhost/studiolex/admin/login.php

Responsive Design
Il sito è completamente responsive e testato su:

📱 Mobile (< 640px)

📱 Tablet (640px - 1024px)

💻 Desktop (≥ 1024px)

📺 Large Screen (≥ 1440px)

⚠️ Note Importanti
Pagine Legali (Privacy e Cookie)
I link a Privacy Policy e Cookie Policy nel footer sono placeholder. Le pagine non contengono testi legali reali, ma rimandano a # (ancora vuota). In un ambiente di produzione, queste pagine devono essere compilate con informative conformi al GDPR.

Sicurezza in Produzione
Prima di deployare online:

Cambiare le credenziali del database

Impostare display_errors = Off in php.ini

Usare HTTPS con certificato SSL

Implementare rate limiting sul login

Aggiungere protezione CSRF sui form

🔮 Possibili Miglioramenti Futuri
Upload immagini per articoli

Sistema di commenti

Newsletter

Recupero password via email

WYSIWYG Editor (TinyMCE/CKEditor)

API REST per headless CMS

Multilingua (IT/EN)

👨‍💻 Autore
Francesco Garofalo
Web Developer Full-Stack

📧 Email: francescogarofalo34@gmail.com

💼 Portfolio: francescogarofalo.dev

📄 Licenza
Questo progetto è rilasciato sotto licenza MIT.
Vedi il file LICENSE per i dettagli.

🙏 Ringraziamenti
PHP.net - Documentazione ufficiale

MDN Web Docs - Riferimenti HTML/CSS/JS

Google Fonts - Font Inter e Merriweather
