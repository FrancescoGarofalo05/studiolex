<?php
/**
 * STUDIOLEX - Configurazione Database
 * File: includes/config.php
 * 
 * Questo file gestisce la connessione al database MySQL usando PDO.
 * Viene incluso in tutte le pagine che necessitano di accesso ai dati.
 */

// ============================================================
// 1. CREDENZIALI DATABASE (MODIFICA SE NECESSARIO)
// ============================================================
define('DB_HOST', 'localhost');      // Server del database (XAMPP usa localhost)
define('DB_NAME', 'studiolex_db');   // Nome del database che hai creato
define('DB_USER', 'root');           // Utente predefinito di XAMPP
define('DB_PASS', '');               // Password predefinita di XAMPP (vuota)
define('DB_CHARSET', 'utf8mb4');     // Set di caratteri per supportare emoji e caratteri speciali

// ============================================================
// 2. OPZIONI PDO (CONFIGURAZIONE AVANZATA)
// ============================================================
$options = [
    // Attiva il lancio di eccezioni in caso di errore SQL
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    
    // Imposta il fetch mode predefinito: restituisce un array associativo
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    
    // Disabilita l'emulazione dei prepared statements (più sicuro)
    PDO::ATTR_EMULATE_PREPARES   => false,
    
    // Usa la codifica UTF-8 per tutte le comunicazioni
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
];

// ============================================================
// 3. CREAZIONE DELLA CONNESSIONE
// ============================================================
try {
    // Stringa di connessione DSN (Data Source Name)
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    
    // Crea l'oggetto PDO
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    // La connessione è riuscita (nessun messaggio per non sporcare l'output)
    
} catch (PDOException $e) {
    // In caso di errore, mostra un messaggio e interrompe l'esecuzione
    // NOTA: In produzione, non mostrare mai l'errore dettagliato all'utente!
    die("❌ Errore di connessione al database: " . $e->getMessage());
}

// ============================================================
// 4. FUNZIONE DI SUPPORTO (OPZIONALE MA UTILE)
// ============================================================
/**
 * Esegue una query SQL e restituisce tutti i risultati
 * 
 * @param string $sql La query SQL da eseguire
 * @param array $params Parametri per i prepared statements (opzionale)
 * @return array Array di risultati
 */
function query($sql, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Esegue una query SQL e restituisce una singola riga
 * 
 * @param string $sql La query SQL da eseguire
 * @param array $params Parametri per i prepared statements (opzionale)
 * @return array|null Array associativo della riga o null se non trovata
 */
function querySingle($sql, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}

/**
 * Esegue una query di INSERT/UPDATE/DELETE
 * 
 * @param string $sql La query SQL da eseguire
 * @param array $params Parametri per i prepared statements (opzionale)
 * @return int Numero di righe interessate o ultimo ID inserito
 */
function execute($sql, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

/**
 * Restituisce l'ultimo ID inserito nel database
 * 
 * @return int L'ultimo ID auto-incrementato
 */
function lastInsertId() {
    global $pdo;
    return $pdo->lastInsertId();
}