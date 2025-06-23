<?php
// Data buku
$judul_buku = "Konstitusi & konstitusionalisme Indonesia";

$deskripsi = "Dalam buku ini Prof. Dr. Jimly Asshiddiqie, S.H., ketua Mahkamah Konstitusi RI yang juga guru besar fakultas hukum UI, membahas sejarah mula konstitusi dan sejarah konstitusi Indonesia, membicarakan demokrasi dan nomokrasi, mengetengahkan prinsip pemisahan kekuasaan dan bagaimana penerapan-penerapan ideal sebuah konstitusi.";

// Informasi detail
$no_panggil = "342.02 ASS k";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../assets/style/style.css">
    <link rel="stylesheet" href="../assets/style/detail.css">
    <script src="../assets/script/script.js"></script>   
    <title>Perpustakaan Bappeda - <?php echo $judul_buku; ?></title>
</head>
<body>
    
    <?php include '../components/navbar.php'; ?>

    
    <div class="container">
        <a href="./katalog.php" class="home-icon">
            <i class="bi bi-arrow-left-circle-fill"></i>
        </a>
        <header class="book-header">
            <h1 class="book-title"><?php echo $judul_buku; ?></h1>
        </header>

        <div class="book-image">
            <img src="/assets/img/buku/buku1.jpg" alt="<?php echo $judul_buku; ?>" class="img-fluid rounded">
        </div>

        <h2>Deskripsi Singkat</h2>
        <section class="book-description">
            <p><?php echo $deskripsi; ?></p>
        </section>

        <section class="detail-section">
            <h2>Informasi Detail</h2>
            <div class="detail-list">
                <div><strong>No. Panggil:</strong> 342.02 ASS k</div>
                <div><strong>Penerbit:</strong> Konstitusi Press, Jakarta., 2006</div>
                <div><strong>Deskripsi Fisik:</strong> xviii, 414 hal. ; 21 cm.</div>
                <div><strong>Bahasa:</strong> Indonesia</div>
                <div><strong>ISBN/ISSN:</strong> 979-96962-7-5</div>
                <div><strong>Klasifikasi:</strong> 342.02</div>
                <div><strong>Subjek:</strong> Konstitusi, konstitusionalisme</div>
            </div>
        </section>
    </div>

    <?php include '../components/footer.php'; ?>

</body>
</html>
