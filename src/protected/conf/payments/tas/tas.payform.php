<div id="tas_frame_box" style="text-align:center;">
    <div class="error-description" id="tas_frame_error" style="display: none;">
        Шановні клієнти! <br><br>
        Для забезпечення максимально безпечного платежу за допомогою нашого сервісу просимо оновити Ваш браузер до останньої доступної версії.
        (В разі проблем з відображенням просимо в налаштування браузера включити протокол безпеки TLS 1.2)
    </div>
    <iframe id="tas_frame" onload="tas_frame_load();" src="<?= $tas_frame_src; ?>" frameborder="0" style="width: 840px; height:890px;"></iframe>
</div>
<script>
    var tas_frame_not_load = false;
    var current_order_id = <?= $payment_id; ?>;
    $(document).ready(function(){
        tas_timeout_id = setTimeout(function(){
            $('#tas_frame_error').fadeIn(200);
            $('#tas_frame').css('display', 'none');
            tas_frame_not_load = true;
        }, 3500);
    });

    // Блокируем обновление страницы
    $(document).keydown(function(e) {
        if ((e.keyCode == 116) || (e.keyCode == 82 && e.ctrlKey)) {
            return false;
        }
    });
</script>
