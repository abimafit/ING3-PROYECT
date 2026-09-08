<?php
date_default_timezone_set('America/Panama');
session_start(); // Obligatorio para que funcione la sesión
$host = 'localhost';
$dbname = 'rentwheels_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>