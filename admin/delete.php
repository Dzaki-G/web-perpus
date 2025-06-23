<?php
require_once '../auth/auth.php';
require_once '../config/db.php';
redirectIfNotAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

$id = $_POST['id'] ?? null;
$csrf = $_POST['csrf_token'] ?? '';

if (!$id || $csrf !== ($_SESSION['csrf_token'] ?? '')) {
    die("Invalid request.");
}

$pdo = getConnection();

// Fetch file path
$stmt = $pdo->prepare("SELECT filepath FROM documents WHERE id = ?");
$stmt->execute([$id]);
$doc = $stmt->fetch();

if ($doc) {
    // Delete file from server
    if (file_exists($doc['filepath'])) {
        unlink($doc['filepath']);
    }

    // Delete from DB
    $delete = $pdo->prepare("DELETE FROM documents WHERE id = ?");
    $delete->execute([$id]);
}

header("Location: dashboard.php");
exit;
