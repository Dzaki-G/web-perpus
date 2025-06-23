<?php
require_once '../auth/auth.php';
require_once '../config/db.php';
redirectIfNotAdmin();

$pdo = getConnection();
$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die('Invalid document ID.');
}

// Fetch current document
$stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ?");
$stmt->execute([$id]);
$doc = $stmt->fetch();

if (!$doc) {
    die('Document not found.');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = $_POST['category'] ?? '';

    $info = [
        'no_panggil'     => trim($_POST['no_panggil']     ?? '') ?: '-',
        'penerbit'       => trim($_POST['penerbit']       ?? '') ?: '-',
        'deskripsi_fisik'=> trim($_POST['deskripsi_fisik']?? '') ?: '-',
        'bahasa'         => trim($_POST['bahasa']         ?? '') ?: '-',
        'isbn_issn'      => trim($_POST['isbn_issn']      ?? '') ?: '-',
        'klasifikasi'    => trim($_POST['klasifikasi']    ?? '') ?: '-',
        'subjek'         => trim($_POST['subjek']         ?? '') ?: '-',
    ];

    if ($title === '' || $description === '') {
        $error = 'Title and description are required.';
    } else {
        $stmt = $pdo->prepare("
            UPDATE documents SET 
                title = ?, category_code = ?, description = ?,
                no_panggil = ?, penerbit = ?, deskripsi_fisik = ?, bahasa = ?,
                isbn_issn = ?, klasifikasi = ?, subjek = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $title, $category, $description,
            $info['no_panggil'], $info['penerbit'], $info['deskripsi_fisik'], $info['bahasa'],
            $info['isbn_issn'], $info['klasifikasi'], $info['subjek'],
            $id
        ]);

        $success = 'Document updated successfully.';
        // Refresh data
        $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ?");
        $stmt->execute([$id]);
        $doc = $stmt->fetch();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Edit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style/edit.css">
</head>
<body>

<div class="container edit-page">
    <h1 class="page-title">Edit Document</h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST" class="edit-form">
        <!-- Title -->
        <div class="form-row">
            <label for="title">Title <span class="required">*</span></label>
            <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($doc['title']); ?>" required>
        </div>

        <!-- Category -->
        <div class="form-row">
            <label for="category">Category <span class="required">*</span></label>
            <select name="category" id="category" required>
                <?php
                $categories = [
                    '000' => '000: Umum',
                    '001' => '001: Pemerintahan',
                    '002' => '002: Politik',
                    '003' => '003: Keamanan & Ketertiban',
                    '004' => '004: Kesejahteraan Rakyat',
                    '005' => '005: Pengawasan Perekonomian',
                    '006' => '006: Pekerjaan Umum & Ketenagakerjaan',
                    '007' => '007: Pengawasan',
                    '008' => '008: Kepegawaian',
                    '009' => '009: Keuangan'
                ];
                foreach ($categories as $code => $label) {
                    $selected = $doc['category_code'] === $code ? 'selected' : '';
                    echo "<option value=\"$code\" $selected>$label</option>";
                }
                ?>
            </select>
        </div>

        <!-- Description -->
        <div class="form-row">
            <label for="description">Description <span class="required">*</span></label>
            <textarea name="description" id="description" rows="3" required><?php echo htmlspecialchars($doc['description']); ?></textarea>
        </div>

        <!-- Optional Information -->
        <fieldset class="form-section">
            <legend>Information Detail</legend>

            <div class="form-grid">
                <div class="field">
                    <label for="no_panggil">No. Panggil</label>
                    <input type="text" name="no_panggil" id="no_panggil" value="<?php echo htmlspecialchars($doc['no_panggil']); ?>">
                </div>

                <div class="field">
                    <label for="penerbit">Penerbit</label>
                    <input type="text" name="penerbit" id="penerbit" value="<?php echo htmlspecialchars($doc['penerbit']); ?>">
                </div>

                <div class="field">
                    <label for="deskripsi_fisik">Deskripsi Fisik</label>
                    <input type="text" name="deskripsi_fisik" id="deskripsi_fisik" value="<?php echo htmlspecialchars($doc['deskripsi_fisik']); ?>">
                </div>

                <div class="field">
                    <label for="bahasa">Bahasa</label>
                    <input type="text" name="bahasa" id="bahasa" value="<?php echo htmlspecialchars($doc['bahasa']); ?>">
                </div>

                <div class="field">
                    <label for="isbn_issn">ISBN/ISSN</label>
                    <input type="text" name="isbn_issn" id="isbn_issn" value="<?php echo htmlspecialchars($doc['isbn_issn']); ?>">
                </div>

                <div class="field">
                    <label for="klasifikasi">Klasifikasi</label>
                    <input type="text" name="klasifikasi" id="klasifikasi" value="<?php echo htmlspecialchars($doc['klasifikasi']); ?>">
                </div>

                <div class="field">
                    <label for="subjek">Subjek</label>
                    <input type="text" name="subjek" id="subjek" value="<?php echo htmlspecialchars($doc['subjek']); ?>">
                </div>
            </div>
        </fieldset>

        <!-- Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="dashboard.php" class="btn btn-link">Back to Dashboard</a>
        </div>
    </form>
</div>

</body>
</html>
