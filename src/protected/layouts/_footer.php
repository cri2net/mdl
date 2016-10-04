</div>
</content>
<script>
    var BASE_URL = '<?= BASE_URL; ?>';
</script>
<?php
    if (USER_REAL_IP !== '127.0.0.1') {
        require_once(PROTECTED_DIR . '/scripts/yandex-metrika.php');
    }
?>
</body>
</html>