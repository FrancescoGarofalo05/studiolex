<?php
/**
 * STUDIOLEX - Logout
 * File: admin/logout.php
 * 
 * Distrugge la sessione e reindirizza al login.
 */

// Avvia la sessione
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cancella tutte le variabili di sessione
$_SESSION = [];

// Se esiste un cookie di sessione, lo cancella
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Distrugge la sessione
session_destroy();

// Reindirizza al login con messaggio
session_start();
$_SESSION['logout_message'] = 'Sei stato disconnesso con successo.';
header('Location: login.php');
exit;