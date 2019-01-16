        <script>
            var BASE_URL = '<?= BASE_URL; ?>';

            $(document).ready(function(){

                $(document).on('click', 'a', function() {
                    var href = $(this).attr('href');
                    if (href.indexOf('<?= BASE_URL; ?>') !== -1) {
                        var new_href = href;
                        new_href += (href.indexOf('?') === -1) ? '?' : '&';
                        new_href += 'uid=<?= Authorization::getLoggedUserId(); ?>&hash2=<?= Authorization::get_auth_hash2(Authorization::getLoggedUserId()); ?>';
                        $(this).attr('href', new_href);
                    }
                });
            });
        </script>
        <script src="<?= BASE_URL; ?>/assets/js/scripts.js"></script>
        <script src="<?= BASE_URL; ?>/assets/js/main.js"></script>
        <script src="<?= BASE_URL; ?>/assets/js/jquery.cookie.min.js"></script>
    </div>
</body>
</html>
