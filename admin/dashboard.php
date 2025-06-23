<?php
require_once '../auth/auth.php';
require_once '../config/db.php';
redirectIfNotAdmin();

$pdo = getConnection();

// Configuration
$limit = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$filterCategory = $_GET['category'] ?? '';

// Category labels
$categoryLabels = [
    '000' => 'Umum',
    '001' => 'Pemerintahan',
    '002' => 'Politik',
    '003' => 'Keamanan & Ketertiban',
    '004' => 'Kesejahteraan Rakyat',
    '005' => 'Pengawasan Perekonomian',
    '006' => 'Pekerjaan Umum & Ketenagakerjaan',
    '007' => 'Pengawasan',
    '008' => 'Kepegawaian',
    '009' => 'Keuangan',
];

// Database queries
function getTotalDocuments($pdo, $filterCategory) {
    $categoryClause = $filterCategory !== '' ? 'WHERE d.category_code = ?' : '';
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM documents d $categoryClause");
    
    if ($filterCategory !== '') {
        $countStmt->execute([$filterCategory]);
    } else {
        $countStmt->execute();
    }
    
    return $countStmt->fetchColumn();
}

function getDocuments($pdo, $filterCategory, $limit, $offset) {
    $categoryClause = $filterCategory !== '' ? 'WHERE d.category_code = ?' : '';
    $sql = "
        SELECT d.*, u.username
        FROM documents d
        LEFT JOIN users u ON d.uploaded_by = u.id
        $categoryClause
        ORDER BY d.upload_date DESC
        LIMIT $limit OFFSET $offset
    ";
    
    $stmt = $pdo->prepare($sql);
    if ($filterCategory !== '') {
        $stmt->execute([$filterCategory]);
    } else {
        $stmt->execute();
    }
    
    return $stmt->fetchAll();
}

// Get data
$totalDocuments = getTotalDocuments($pdo, $filterCategory);
$totalPages = ceil($totalDocuments / $limit);
$documents = getDocuments($pdo, $filterCategory, $limit, $offset);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Document Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style/admin.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4><i class="bi bi-shield-check"></i> Admin Panel</h4>
        </div>
        <nav class="sidebar-nav">
            <a href="#" class="nav-link active">
                <i class="bi bi-house-door"></i> Dashboard
            </a>
            <a href="upload.php" class="nav-link">
                <i class="bi bi-cloud-upload"></i> Upload Document
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="logout-btn">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <header class="main-header">
            <div class="header-left">
                <h2>Document Management</h2>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <i class="bi bi-person-circle"></i>
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </div>
            </div>
        </header>

        <!-- Success Alert -->
        <?php if (isset($_GET['upload']) && $_GET['upload'] === 'success'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                File uploaded successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $totalDocuments; ?></h3>
                    <p>Total Documents</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-cloud-upload"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo count($documents); ?></h3>
                    <p>Current Page</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-folder"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo count($categoryLabels); ?></h3>
                    <p>Categories</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-file-earmark-pdf"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $totalPages; ?></h3>
                    <p>Total Pages</p>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="content-section">
            <div class="section-header">
                <h3>Document Library</h3>
                <div class="section-actions">
                    <a href="upload.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Upload New
                    </a>
                </div>
            </div>

            <div class="filters-section">
                <form method="GET" class="filter-form">
                    <div class="form-group">
                        <label for="category">Filter by Category:</label>
                        <select name="category" id="category" class="form-select" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php foreach ($categoryLabels as $code => $label): ?>
                                <option value="<?php echo $code; ?>" <?php echo ($filterCategory === $code ? 'selected' : ''); ?>>
                                    <?php echo "$code: $label"; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Documents Table -->
        <div class="content-section">
            <?php if (count($documents) > 0): ?>
                <div class="table-responsive">
                    <table class="table documents-table">
                        <thead>
                            <tr>
                                <th>Preview</th>
                                <th>Document Info</th>
                                <th>Category</th>
                                <th>Metadata</th>
                                <th>Uploaded By</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documents as $doc): ?>
                                <tr>
                                    <td>
                                        <div class="preview-container">
                                            <canvas class="pdf-preview" data-pdf="<?php echo $doc['filepath']; ?>"></canvas>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="document-info">
                                            <h6><?php echo htmlspecialchars($doc['title']); ?></h6>
                                            <p class="description"><?php echo nl2br(htmlspecialchars($doc['description'])); ?></p>
                                            <small class="file-size">
                                                <i class="bi bi-file-earmark"></i>
                                                <?php echo number_format($doc['file_size'] / 1024, 2) . ' KB'; ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="category-badge">
                                            <?php echo htmlspecialchars($doc['category_code'] . ': ' . ($categoryLabels[$doc['category_code']] ?? 'Unknown')); ?>
                                        </span>
                                    </td>
                                    <td>
                                    <div class="metadata">
                                        <div class="metadata-item">
                                            <strong>No. Panggil:</strong> <?php echo htmlspecialchars($doc['no_panggil']); ?>
                                        </div>
                                        <div class="metadata-item">
                                            <strong>Penerbit:</strong> <?php echo htmlspecialchars($doc['penerbit']); ?>
                                        </div>
                                        <div class="metadata-item">
                                            <strong>Deskripsi Fisik:</strong> <?php echo htmlspecialchars($doc['deskripsi_fisik']); ?>
                                        </div>
                                        <div class="metadata-item">
                                            <strong>Bahasa:</strong> <?php echo htmlspecialchars($doc['bahasa']); ?>
                                        </div>
                                        <div class="metadata-item">
                                            <strong>ISBN/ISSN:</strong> <?php echo htmlspecialchars($doc['isbn_issn']); ?>
                                        </div>
                                        <div class="metadata-item">
                                            <strong>Klasifikasi:</strong> <?php echo htmlspecialchars($doc['klasifikasi']); ?>
                                        </div>
                                        <div class="metadata-item">
                                            <strong>Subjek:</strong> <?php echo htmlspecialchars($doc['subjek']); ?>
                                        </div>
                                    </div>
                                    </td>
                                    <td>
                                        <div class="user-info-table">
                                            <i class="bi bi-person"></i>
                                            <?php echo htmlspecialchars($doc['username'] ?? 'Unknown'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($doc['upload_date']); ?></small>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="<?php echo $doc['filepath']; ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="<?php echo $doc['filepath']; ?>" download class="btn btn-sm btn-outline-success" title="Download">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            <a href="edit.php?id=<?php echo $doc['id']; ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" action="delete.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this file?');">
                                                <input type="hidden" name="id" value="<?php echo $doc['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination-container">
                    <nav aria-label="Document pagination">
                        <ul class="pagination">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo ($i === $page ? 'active' : ''); ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $filterCategory ? '&category=' . urlencode($filterCategory) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>

            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="bi bi-file-earmark-x"></i>
                    </div>
                    <h4>No Documents Found</h4>
                    <p>No documents have been uploaded yet or match your current filter.</p>
                    <a href="upload.php" class="btn btn-primary">
                        <i class="bi bi-cloud-upload"></i> Upload First Document
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        // PDF Preview functionality
        const previews = document.querySelectorAll('.pdf-preview');
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        previews.forEach(canvas => {
            const url = canvas.getAttribute('data-pdf');
            const context = canvas.getContext('2d');

            pdfjsLib.getDocument(url).promise.then(pdf => {
                return pdf.getPage(1);
            }).then(page => {
                const viewport = page.getViewport({ scale: 0.3 });
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                const renderContext = {
                    canvasContext: context,
                    viewport: viewport
                };
                return page.render(renderContext).promise;
            }).catch(error => {
                console.error("PDF.js error:", error);
                canvas.width = 120;
                canvas.height = 150;
                context.fillStyle = "#f8f9fa";
                context.fillRect(0, 0, canvas.width, canvas.height);
                context.fillStyle = "#6c757d";
                context.font = "12px Arial";
                context.textAlign = "center";
                context.fillText("Preview", canvas.width/2, canvas.height/2 - 10);
                context.fillText("Unavailable", canvas.width/2, canvas.height/2 + 10);
            });
        });
    </script>
</body>
</html>