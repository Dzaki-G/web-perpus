<!-- components/table.php -->
<div class="table-responsive rounded overflow-hidden bg-white">
    <table class="table custom-table table-hover">
        <thead>
            <tr>
                <th scope="col">No</th>
                <th scope="col">Gambar Buku</th>
                <th scope="col">Nama Buku</th>
                <th scope="col">Jumlah Buku</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th scope="row" class="fs-5">1</th>   
                <th><img src="/assets/img/buku/buku1.jpg" alt="" class="img-fluid rounded" style="width: 150px; height: 200px;"></th>
                <td class="fs-5">Das Kapital</td>
                <td class="fs-5">500</td>
                <td><button class="btn btn-primary" onclick="window.location.href='./detail.php'">Lihat Selengkapnya</button></td>
            </tr>
        </tbody>
    </table>
    <nav aria-label="Page navigation example">
        <ul class="pagination d-flex justify-content-center gap-1">
            <li class="page-item"><a class="page-link fw-bold" href="#">Previous</a></li>
            <li class="page-item"><a class="page-link fw-bold" href="#">1</a></li>
            <li class="page-item"><a class="page-link fw-bold" href="#">2</a></li>
            <li class="page-item"><a class="page-link fw-bold" href="#">3</a></li>
            <li class="page-item"><a class="page-link fw-bold" href="#">Next</a></li>
        </ul>
    </nav>
</div>