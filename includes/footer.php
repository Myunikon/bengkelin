<footer class="app-footer text-center text-muted small py-3 mt-auto border-top">
        &copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.
    </footer>

</main>
</div>

<div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body p-0 text-center">
                <img id="lightbox-img" src="" alt="Foto Servis" class="img-fluid rounded-3 shadow-lg">
            </div>
            <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>
