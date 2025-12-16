<?php
/**
 * Database Configuration
 * 
 * Establishes PDO connection to MySQL database.
 * Used by all backend scripts and views that need database access.
 * 
 * @package Flarify
 * @author Flarify Team
 */

// Create PDO instance with MySQL connection
// Host: localhost, Database: flarify, Charset: UTF-8
$pdo = new PDO("mysql:host=localhost;dbname=flarify;charset=utf8","root","root");

// Set error mode to throw exceptions for better error handling
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Set default fetch mode to associative array
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
?>