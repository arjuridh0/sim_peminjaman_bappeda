</main>

<!-- Footer -->
<footer class="mt-20 bg-gradient-to-r from-blue-900 via-blue-800 to-indigo-900 text-white">
    <div class="max-w-7xl mx-auto px-6 py-12">
        <div class="text-center">
            <p class="text-lg font-semibold mb-2">&copy;
                <?= date('Y') ?> BAPPEDA Provinsi Jawa Tengah
            </p>
            <p class="text-blue-200 text-sm">Sistem Informasi Peminjaman Ruangan</p>
        </div>
    </div>
</footer>

<script>
    // Auto-hide alerts
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(el => {
            el.style.transition = 'opacity 0.5s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 500);
        });
    }, 5000);
</script>
</body>

</html>