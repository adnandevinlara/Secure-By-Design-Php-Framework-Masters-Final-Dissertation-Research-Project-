<?php
// ==============================================================================
// VERSION A: INTENTIONALLY INSECURE BASELINE
// This file bypasses the Secure Framework entirely. It exposes the raw PDO 
// connection and intentionally leaks verbose database errors to the screen.
// ==============================================================================

$dbPath = __DIR__ . '/baseline_app.sqlite';

try {
    // Direct PDO instantiation without ORM encapsulation
    $pdo = new PDO("sqlite:" . $dbPath);
    
    // INSECURE: Forcing the database to leak detailed architecture errors to the screen
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Initialize a raw, unencrypted users table for the vulnerable application
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL
        )
    ");

} catch (PDOException $e) {
    // INSECURE XSS/Information Disclosure: Echoing raw exceptions directly into the DOM
    echo "<h3>Fatal Database Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    exit;
}
?>