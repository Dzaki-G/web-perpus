<?php
require_once '../auth/auth.php';
redirectIfNotAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../config/db.php';
    $pdo = getConnection();

    // Form inputs
    $category = $_POST['category'] ?? '';
    $description = trim($_POST['description'] ?? '');

    // Optional info-detail fields (default "-" if empty)
    $info = [
        'no_panggil' => trim($_POST['no_panggil'] ?? '') ?: '-',
        'penerbit' => trim($_POST['penerbit'] ?? '') ?: '-',
        'deskripsi_fisik' => trim($_POST['deskripsi_fisik'] ?? '') ?: '-',
        'bahasa' => trim($_POST['bahasa'] ?? '') ?: '-',
        'isbn_issn' => trim($_POST['isbn_issn'] ?? '') ?: '-',
        'klasifikasi' => trim($_POST['klasifikasi'] ?? '') ?: '-',
        'subjek' => trim($_POST['subjek'] ?? '') ?: '-',
    ];

    $file = $_FILES['document'] ?? null;

    // Validation
    $validCategories = [
        '000', '001', '002', '003', '004', '005', '006', '007', '008', '009'
    ];

    if (!in_array($category, $validCategories, true)) {
        $error = 'Invalid category selected';
    } elseif ($description === '') {
        $error = 'File description is required';
    } elseif (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        $error = 'File upload failed';
    } else {
        // MIME-type and size checks
        $allowedTypes = ['application/pdf'];
        $maxSize = 5 * 1024 * 1024; // 5 MB

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedTypes, true)) {
            $error = 'Only PDF files are allowed';
        } elseif ($file['size'] > $maxSize) {
            $error = 'File too large. Maximum size is 5 MB';
        } else {
            // Store file
            $originalName = pathinfo($file['name'], PATHINFO_FILENAME);
            $title = trim($originalName) ?: 'Untitled Document';

            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('', true) . '.' . $extension;
            $filepath = '../uploads/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Insert into DB
                $stmt = $pdo->prepare("
                    INSERT INTO documents
                      (title, category_code, description,
                       filename, filepath, file_size, uploaded_by,
                       no_panggil, penerbit, deskripsi_fisik, bahasa,
                       isbn_issn, klasifikasi, subjek)
                    VALUES
                      (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $title,
                    $category,
                    $description,
                    $filename,
                    $filepath,
                    $file['size'],
                    $_SESSION['user_id'] ?? 0,
                    $info['no_panggil'],
                    $info['penerbit'],
                    $info['deskripsi_fisik'],
                    $info['bahasa'],
                    $info['isbn_issn'],
                    $info['klasifikasi'],
                    $info['subjek'],
                ]);

                header('Location: dashboard.php?upload=success');
                exit;
            } else {
                $error = 'Failed to save file';
            }
        }
    }
}

// Category options for the form
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Document | Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style/upload.css">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Header -->
        <header class="admin-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-cloud-upload-fill me-2"></i>
                        <h1 class="header-title mb-0">Upload Document</h1>
                    </div>
                    <nav class="breadcrumb-nav">
                        <a href="dashboard.php" class="breadcrumb-link">
                            <i class="bi bi-house-door"></i> Dashboard
                        </a>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-current">Upload</span>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="container-fluid">
                <!-- Alert Messages -->
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Upload Form Card -->
                <div class="upload-card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="bi bi-file-earmark-pdf"></i>
                            Document Upload Form
                        </h2>
                        <p class="card-subtitle">Upload and categorize your PDF documents</p>
                    </div>

                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" class="upload-form">
                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-lg-6">
                                    <!-- Category Selection -->
                                    <div class="form-group">
                                        <label for="category" class="form-label">
                                            <i class="bi bi-tag"></i>
                                            Document Category
                                            <span class="required">*</span>
                                        </label>
                                        <select id="category" name="category" class="form-select" required>
                                            <option value="">-- Select Category --</option>
                                            <?php foreach ($categories as $code => $name): ?>
                                                <option value="<?php echo $code; ?>" 
                                                    <?php echo (isset($_POST['category']) && $_POST['category'] === $code) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($name); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Description -->
                                    <div class="form-group">
                                        <label for="description" class="form-label">
                                            <i class="bi bi-file-text"></i>
                                            Document Description
                                            <span class="required">*</span>
                                        </label>
                                        <textarea id="description" name="description" class="form-control" rows="4" 
                                                  placeholder="Enter a detailed description of the document..." required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                                    </div>

                                    <!-- File Upload -->
                                    <div class="form-group">
                                        <label for="document" class="form-label">
                                            <i class="bi bi-cloud-upload"></i>
                                            PDF File
                                            <span class="required">*</span>
                                        </label>
                                        <div class="file-upload-wrapper">
                                            <input type="file" id="document" name="document" class="form-control" 
                                                   accept=".pdf" required>
                                            <div class="file-upload-info">
                                                <small class="text-muted">
                                                    <i class="bi bi-info-circle"></i>
                                                    Maximum file size: 5 MB | Accepted format: PDF only
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column - Optional Information -->
                                <div class="col-lg-6">
                                    <div class="optional-section">
                                        <h3 class="section-title">
                                            <i class="bi bi-info-square"></i>
                                            Additional Information
                                            <span class="optional-badge">Optional</span>
                                        </h3>

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="no_panggil" class="form-label">No. Panggil</label>
                                                    <input type="text" name="no_panggil" id="no_panggil" class="form-control"
                                                           value="<?php echo htmlspecialchars($_POST['no_panggil'] ?? ''); ?>">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="penerbit" class="form-label">Penerbit</label>
                                                    <input type="text" name="penerbit" id="penerbit" class="form-control"
                                                           value="<?php echo htmlspecialchars($_POST['penerbit'] ?? ''); ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="deskripsi_fisik" class="form-label">Deskripsi Fisik</label>
                                                    <input type="text" name="deskripsi_fisik" id="deskripsi_fisik" class="form-control"
                                                           value="<?php echo htmlspecialchars($_POST['deskripsi_fisik'] ?? ''); ?>">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="bahasa" class="form-label">Bahasa</label>
                                                    <input type="text" name="bahasa" id="bahasa" class="form-control"
                                                           value="<?php echo htmlspecialchars($_POST['bahasa'] ?? ''); ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="isbn_issn" class="form-label">ISBN/ISSN</label>
                                                    <input type="text" name="isbn_issn" id="isbn_issn" class="form-control"
                                                           value="<?php echo htmlspecialchars($_POST['isbn_issn'] ?? ''); ?>">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label for="klasifikasi" class="form-label">Klasifikasi</label>
                                                    <input type="text" name="klasifikasi" id="klasifikasi" class="form-control"
                                                           value="<?php echo htmlspecialchars($_POST['klasifikasi'] ?? ''); ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="subjek" class="form-label">Subjek</label>
                                            <input type="text" name="subjek" id="subjek" class="form-control"
                                                   value="<?php echo htmlspecialchars($_POST['subjek'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-cloud-upload"></i>
                                    Upload Document
                                </button>
                                <a href="dashboard.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i>
                                    Back to Dashboard
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // File upload preview
        document.getElementById('document').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const info = document.querySelector('.file-upload-info small');
            
            if (file) {
                const size = (file.size / 1024 / 1024).toFixed(2);
                info.innerHTML = `<i class="bi bi-file-earmark-pdf text-success"></i> Selected: ${file.name} (${size} MB)`;
            } else {
                info.innerHTML = `<i class="bi bi-info-circle"></i> Maximum file size: 5 MB | Accepted format: PDF only`;
            }
        });
    </script>
</body>
</html>