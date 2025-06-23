<!-- type http://(link)/config/test_db.php to test -->
<?php
require_once 'db.php';

try {
    $conn = getConnection();
    echo "✅ Connection successful to database: <strong>" . $conn->query("SELECT DATABASE()")->fetchColumn() . "</strong>";
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}
?>
