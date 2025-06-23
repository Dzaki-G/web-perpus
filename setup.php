<?php
/* run this file once to (re)create tables and seed a default admin */

require_once __DIR__ . '/config/db.php';

try {
    $pdo = getConnection();

    /* ------------------------- USERS ------------------------- */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            username   VARCHAR(50) NOT NULL UNIQUE,
            password   VARCHAR(255) NOT NULL,
            role       ENUM('admin','user') DEFAULT 'admin',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✅ Table 'users' ready.<br>";

    /* ---------------------- DOCUMENTS ------------------------ */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS documents (
            id              INT AUTO_INCREMENT PRIMARY KEY,
            title           VARCHAR(255) NOT NULL,
            category_code   CHAR(3)      NOT NULL,
            description     TEXT         NOT NULL,
            filename        VARCHAR(255) NOT NULL UNIQUE,
            filepath        VARCHAR(255) NOT NULL,
            file_size       INT          NOT NULL,
            upload_date     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
            uploaded_by     INT,
            no_panggil      VARCHAR(255) NOT NULL DEFAULT '-',
            penerbit        VARCHAR(255) NOT NULL DEFAULT '-',
            deskripsi_fisik VARCHAR(255) NOT NULL DEFAULT '-',
            bahasa          VARCHAR(100) NOT NULL DEFAULT '-',
            isbn_issn       VARCHAR(100) NOT NULL DEFAULT '-',
            klasifikasi     VARCHAR(100) NOT NULL DEFAULT '-',
            subjek          VARCHAR(255) NOT NULL DEFAULT '-',
            FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
        )
    ");
    echo "✅ Table 'documents' ready.<br>";

    /* ------------------ OPTIONAL COLUMN PATCH ------------------
       If the table already existed without the new columns,
       add whichever columns are missing. (MySQL 8.0+ supports
       IF NOT EXISTS; for 5.7 you can wrap SHOW COLUMNS checks
       in PHP—kept simple here.)
    -------------------------------------------------------------*/
    $columns = [
        'category_code   CHAR(3)      NOT NULL',
        'description     TEXT         NOT NULL',
        'no_panggil      VARCHAR(255) NOT NULL DEFAULT \'-\'',
        'penerbit        VARCHAR(255) NOT NULL DEFAULT \'-\'',
        'deskripsi_fisik VARCHAR(255) NOT NULL DEFAULT \'-\'',
        'bahasa          VARCHAR(100) NOT NULL DEFAULT \'-\'',
        'isbn_issn       VARCHAR(100) NOT NULL DEFAULT \'-\'',
        'klasifikasi     VARCHAR(100) NOT NULL DEFAULT \'-\'',
        'subjek          VARCHAR(255) NOT NULL DEFAULT \'-\''
    ];

    foreach ($columns as $definition) {
        preg_match('/^(\w+)/', $definition, $m);       // extract column name
        $col = $m[1];
        $exists = $pdo->query("
            SELECT COUNT(*) FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME   = 'documents'
              AND COLUMN_NAME  = '{$col}'
        ")->fetchColumn();

        if (!$exists) {
            $pdo->exec("ALTER TABLE documents ADD COLUMN {$definition}");
            echo "ℹ️ Added missing column <strong>{$col}</strong>.<br>";
        }
    }

    /* -------------------- DEFAULT ADMIN --------------------- */
    $defaultUsername = 'admin';
    $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);

    $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $check->execute([$defaultUsername]);

    if (!$check->fetchColumn()) {
        $insert = $pdo->prepare("
            INSERT INTO users (username, password, role)
            VALUES (?, ?, 'admin')
        ");
        $insert->execute([$defaultUsername, $defaultPassword]);
        echo "✅ Default admin user created: <strong>admin / admin123</strong>";
    } else {
        echo "ℹ️ Default admin already exists.";
    }

} catch (PDOException $e) {
    echo "❌ Error: " . htmlspecialchars($e->getMessage());
}
