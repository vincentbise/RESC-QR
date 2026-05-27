    </div><!-- .page-content -->
</div><!-- .main-content -->
</div><!-- .app-layout -->

<?php if (isset($extraJs)): foreach ((array)$extraJs as $js): ?>
    <script src="<?= publicUrl('js/' . $js) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>