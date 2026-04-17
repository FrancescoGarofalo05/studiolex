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
- 📞 **Pagina contatti** con form di invio messaggi e mappa Google (placeholder - in produzione sarebbe personalizzabile per ogni studio)
- 🔍 **SEO ottimizzato** con meta tag dinamici e URL slug

### Admin Panel (Area Riservata)
- 🔐 **Sistema a doppia registrazione:**
  - **Admin Studio:** crea lo studio e riceve una **Passkey** univoca da comunicare ai dipendenti
  - **Dipendenti:** si registrano utilizzando la Passkey fornita dall'admin
- 🏢 **Multi-tenancy:** ogni studio vede solo i propri articoli e dati (isolamento completo)
- 📊 **Dashboard** con statistiche filtrate per studio
- 🔑 **Visualizzazione Passkey** nella dashboard dell'Admin (simulata via schermo)
- ✏️ **CRUD Completo Articoli:**
  - **C**reate: crea nuovi articoli con validazione
  - **R**ead: lista articoli con filtri, ricerca e paginazione
  - **U**pdate: modifica articoli esistenti
  - **D**elete: elimina articoli con conferma
- 👤 **Gestione ruoli:** Admin ed Editor
- 📱 **Dashboard completamente responsive** con menu mobile (hamburger)
- 🛡️ **Sicurezza backend:** Prepared Statements PDO anti SQL Injection

---

## 🛠️ Tecnologie Utilizzate

| Tecnologia | Utilizzo |
|------------|----------|
| **PHP 8+** | Backend, logica di business, autenticazione |
| **MySQL** | Database relazionale |
| **PDO** | Connessione sicura al database con Prepared Statements |
| **HTML5** | Struttura semantica delle pagine |
| **CSS3** | Styling responsive mobile-first (Flexbox, Grid) |
| **JavaScript (ES6)** | Validazione form, interattività UI, menu mobile |
| **XAMPP** | Ambiente di sviluppo locale (Apache + MySQL) |

---

## 📁 Struttura del Progetto
studiolex/
├── index.php # Homepage pubblica
├── blog.php # Lista articoli pubblici
├── article.php # Singolo articolo
├── contact.php # Pagina contatti
├── database.sql # Struttura del database (esportazione)
├── admin/
│ ├── register.php # Registrazione (Admin + Dipendenti con Passkey)
│ ├── login.php # Accesso area riservata
│ ├── logout.php # Disconnessione
│ ├── dashboard.php # Dashboard admin (mostra Passkey)
│ └── articles/
│ ├── list.php # Lista articoli (READ) - filtrati per studio
│ ├── create.php # Crea articolo (CREATE) - con studio_id
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
│ │ └── style.css # Stili completi responsive
│ └── js/
│ └── main.js # JavaScript personalizzato
├── logs/
│ ├── .gitkeep # Mantiene la cartella su Git
│ └── passkeys.log # Log delle Passkey generate (simulazione)
├── .gitignore # File da escludere da Git
├── .htaccess # Protezione cartelle (opzionale)
├── README.md # Documentazione
└── LICENSE # Licenza MIT

text

---

## 🔐 Sicurezza Implementata

| Minaccia | Protezione |
|----------|------------|
| **SQL Injection** | Prepared Statements PDO con placeholder |
| **XSS (Cross-Site Scripting)** | `htmlspecialchars()` su tutti gli output da database |
| **Password in chiaro** | Hashing con `password_hash()` e verifica con `password_verify()` |
| **Session Fixation** | `session_regenerate_id(true)` dopo login |
| **Session Hijacking** | Cookie di sessione HTTP-only |
| **Accesso non autorizzato** | Controllo `$_SESSION['user_id']` su ogni pagina admin |
| **Isolamento dati multi-studio** | Filtro per `studio_id` su tutte le query |
| **Directory Listing** | `Options -Indexes` in `.htaccess` |

---

## 🗄️ Struttura Database

### Tabella `studios` (NUOVA)
| Campo | Tipo | Descrizione |
|-------|------|-------------|
| `id` | INT (PK) | Chiave primaria |
| `name` | VARCHAR(100) | Nome dello studio |
| `passkey` | VARCHAR(50) UNIQUE | Codice univoco per registrazione dipendenti |
| `owner_id` | INT (FK) NULL | Riferimento all'utente Admin proprietario |
| `created_at` | TIMESTAMP | Data creazione |

### Tabella `users`
| Campo | Tipo | Descrizione |
|-------|------|-------------|
| `id` | INT (PK) | Chiave primaria |
| `username` | VARCHAR(50) UNIQUE | Nome utente |
| `email` | VARCHAR(100) UNIQUE | Email |
| `password_hash` | VARCHAR(255) | Password hashata |
| `full_name` | VARCHAR(100) | Nome completo |
| `role` | ENUM('admin','editor') | Ruolo utente |
| `studio_id` | INT (FK) NULL | Riferimento allo studio di appartenenza |
| `created_at` | TIMESTAMP | Data registrazione |

### Tabella `categories`
| Campo | Tipo | Descrizione |
|-------|------|-------------|
| `id` | INT (PK) | Chiave primaria |
| `name` | VARCHAR(50) | Nome categoria |
| `slug` | VARCHAR(50) UNIQUE | Slug per URL |
| `created_at` | TIMESTAMP | Data creazione |

### Tabella `articles`
| Campo | Tipo | Descrizione |
|-------|------|-------------|
| `id` | INT (PK) | Chiave primaria |
| `title` | VARCHAR(200) | Titolo articolo |
| `slug` | VARCHAR(200) UNIQUE | Slug univoco per URL |
| `excerpt` | TEXT | Estratto/riassunto |
| `content` | LONGTEXT | Contenuto completo (HTML) |
| `category_id` | INT (FK) | Riferimento categoria |
| `user_id` | INT (FK) | Autore articolo |
| `studio_id` | INT (FK) | Riferimento allo studio (isolamento dati) |
| `published` | BOOLEAN | Stato pubblicazione |
| `views` | INT | Contatore visualizzazioni |
| `created_at` | TIMESTAMP | Data creazione |
| `updated_at` | TIMESTAMP | Data ultima modifica |

> **Nota:** Il file `database.sql` incluso nel repository contiene la struttura completa del database (senza dati sensibili) per una rapida installazione.

---

## 🔑 Sistema a Doppia Registrazione (Passkey)

Il progetto implementa un sistema di registrazione a due livelli per garantire l'isolamento dei dati tra diversi studi legali.

### Flusso di Registrazione Admin (Titolare dello Studio)
1. L'utente seleziona **"Registra Studio"**
2. Compila: Nome Studio, Nome Utente, Email, Password
3. Il sistema:
   - Crea un nuovo record nella tabella `studios`
   - Genera una **Passkey** univoca (formato: `STUDIO-XXXXXXXX`)
   - Crea l'utente con ruolo `admin` e lo associa allo studio
4. La Passkey viene **mostrata a schermo** e salvata in `logs/passkeys.log`
5. L'Admin comunica questa Passkey ai propri dipendenti

> **Nota:** In un ambiente di produzione reale, la Passkey verrebbe inviata **via email** all'indirizzo fornito durante la registrazione. La visualizzazione a schermo è una simulazione per l'ambiente di sviluppo locale.

### Flusso di Registrazione Dipendente
1. Il dipendente seleziona **"Sono Dipendente"**
2. Compila: Nome Utente, Email, Password + **Passkey** (fornita dal suo Admin)
3. Il sistema:
   - Verifica che la Passkey esista nella tabella `studios`
   - Crea l'utente con ruolo `editor`
   - Lo associa automaticamente allo studio corretto
4. Il dipendente accede e vede solo gli articoli del proprio studio

### Dashboard Admin vs Editor
| Funzionalità | Admin | Editor |
|--------------|-------|--------|
| Visualizzare Passkey dello Studio | ✅ | ❌ |
| Creare/Modificare articoli | ✅ | ✅ |
| Eliminare articoli | ✅ | ❌ (solo i propri) |
| Vedere statistiche complete | ✅ | ✅ (filtrate per studio) |

---

## 🚀 Come Testare in Locale

### Prerequisiti
- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL)
- PHP 7.4 o superiore
- MySQL 5.7 o superiore

### Installazione

1. **Clona il repository:**
   ```bash
   git clone https://github.com/tuo-username/studiolex.git
Sposta la cartella in htdocs:

text
C:\xampp\htdocs\studiolex\
Avvia Apache e MySQL dal pannello di controllo XAMPP

Crea il database:

Vai su http://localhost/phpmyadmin

Crea un nuovo database chiamato studiolex_db (utf8mb4_general_ci)

Importa il file database.sql incluso nel repository

Configura la connessione:

Apri includes/config.php

Verifica le credenziali:

php
define('DB_HOST', 'localhost');
define('DB_NAME', 'studiolex_db');
define('DB_USER', 'root');
define('DB_PASS', '');
Crea la cartella logs:

Nella root del progetto, la cartella logs/ esiste già (con .gitkeep)

Assicurati che abbia i permessi di scrittura

Accedi al sito:

Sito pubblico: http://localhost/studiolex/

Admin Panel: http://localhost/studiolex/admin/register.php

📱 Responsive Design
Il sito è completamente responsive e testato su:

📱 Mobile (< 640px) - Menu hamburger, layout a colonna singola

📱 Tablet (640px - 1024px) - Sidebar compattata, 2 colonne

💻 Desktop (≥ 1024px) - Sidebar completa, 3-4 colonne

📺 Large Screen (≥ 1440px) - Layout ottimizzato

⚠️ Note Importanti
Pagine Legali (Privacy e Cookie)
I link a Privacy Policy e Cookie Policy nel footer sono placeholder. Le pagine non contengono testi legali reali, ma rimandano a # (ancora vuota). In un ambiente di produzione, queste pagine devono essere compilate con informative conformi al GDPR.

Pagina Contatti
La pagina contatti è attualmente generica. In una versione production, ogni studio potrebbe personalizzare i propri recapiti (indirizzo, telefono, email) dalla dashboard, e la pagina contatti mostrerebbe i dati specifici dello studio.

Sistema Passkey
La Passkey viene mostrata a schermo dopo la registrazione dell'Admin per scopi dimostrativi

In un ambiente reale, la Passkey verrebbe inviata via email all'indirizzo dell'Admin

Il file logs/passkeys.log simula il registro delle email inviate

Sicurezza in Produzione
Prima di deployare online:

Cambiare le credenziali del database

Impostare display_errors = Off in php.ini

Usare HTTPS con certificato SSL

Implementare rate limiting sul login

Aggiungere protezione CSRF sui form

Rimuovere la visualizzazione a schermo della Passkey e implementare invio email reale

🔮 Possibili Miglioramenti Futuri
Upload immagini per articoli

Sistema di commenti

Newsletter

Recupero password via email

Invio email reale per Passkey e conferma account

Personalizzazione contatti per ogni studio

WYSIWYG Editor (TinyMCE/CKEditor)

API REST per headless CMS

Multilingua (IT/EN)

Pannello gestione utenti per Admin

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