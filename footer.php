</div>
    
    <footer class="footer mt-5">
        <div class="container">
            <div class="text-center py-3">
                &copy; <?php echo date('Y'); ?> AdCombo Tracker - Todos os direitos reservados
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (isset($extra_js)): ?>
        <?php echo $extra_js; ?>
    <?php endif; ?>
</body>
</html>
