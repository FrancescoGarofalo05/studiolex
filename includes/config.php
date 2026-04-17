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
define('DB_HOST', 'localhost');
define('DB_NAME', 'studiolex_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ============================================================
// 2. OPZIONI PDO (CONFIGURAZIONE AVANZATA)
// ============================================================
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
];

// ============================================================
// 3. CREAZIONE DELLA CONNESSIONE
// ============================================================
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die("❌ Errore di connessione al database: " . $e->getMessage());
}

// ============================================================
// 4. FUNZIONI HELPER
// ============================================================

/**
 * Esegue una query e restituisce tutte le righe
 */
function query($sql, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Esegue una query e restituisce una singola riga
 */
function querySingle($sql, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();
    return $result ? $result : null;
}

/**
 * Esegue INSERT/UPDATE/DELETE
 */
function execute($sql, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

/**
 * Restituisce l'ultimo ID inserito
 */
function lastInsertId() {
    global $pdo;
    return $pdo->lastInsertId();
}