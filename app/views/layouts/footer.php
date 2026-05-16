    </div><!-- .page-content -->
</div><!-- .main-content -->
</div><!-- .app-layout -->

<script>
    const BASE_URL = '<?= BASE_URL ?>';
    const CSRF_TOKEN = '<?= generateCSRFToken() ?>';
</script>
<script src="<?= publicUrl('js/app.js') ?>"></script>
<?php if (isset($extraJs)): foreach ((array)$extraJs as $js): ?>
    <script src="<?= publicUrl('js/' . $js) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
