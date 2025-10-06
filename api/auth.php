<?php // auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$ROLE = $_SESSION['role'] ?? null;            // 'student' | 'company' | 'admin' | null
$WORKSPACE_ID = (int)($_SESSION['workspace_id'] ?? 0); 
