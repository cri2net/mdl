<?php
    if (empty($payments)) {
        ?>
        <div class="big-warning-message">Немає платежів для відображення</div>
        <?php
        return;
    }
?>
<div class="check-box-line" style="margin-top: 20px;">
    <span class="niceCheck checked" id="payments_only_success"><input type="checkbox" checked="checked"></span>
    <label style="position: relative; top:-3px; left:10px;" onclick="$('#payments_only_success').click();">
        Тільки успішні платежі
    </label>
</div>
<div class="real-full-width-block">
    <table class="full-width-table datailbill-table no-border">
        <thead>
            <tr>
                <th class="first">Номер</th>
                <th>Дата створення</th>
                <th>Тип</th>
                <th class="td-sum">Сума, грн</th>
                <th class="td-last">Статус</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $counter = 0;
                
                foreach ($payments as $item) {
                    $counter++;
                    $time = ($item['go_to_payment_time']) ? $item['go_to_payment_time'] : $item['timestamp'];
                    ?>
                    <tr style="<?= ($item['status'] == 'success') ? '' : 'display: none;'; ?>" class="item-row <?= ($counter % 2 == 0) ? 'even' : 'odd'; ?> item-payment-status item-payment-status-<?= $item['status']; ?>">
                        <td class="first">
                            <a href="<?= BASE_URL; ?>/cabinet/payments/details/<?= $item['id']; ?>/"><?= $item['id']; ?></a>
                        </td>
                        <td>
                            <span class="date-day"><?= getUkraineDate('j m Y', $time); ?></span><br>
                            <span class="date-time"><?= date('H:i:s', $time); ?></span>
                        </td>
                        <td>
                            <?php
                                switch ($item['type']) {
                                    case 'komdebt':
                                        echo 'Комунальні послуги';
                                        break;

                                    case 'gai':
                                        echo 'Штрафи за порушення ПДР';
                                        break;
                                }
                            ?>
                        </td>
                        <td>
                            <?php
                                $summ = explode('.', number_format($item['summ_total'], 2));
                            ?>
                            <span class="item-summ">
                                <?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span>
                            </span>
                        </td>
                        <td>
                            <?php
                                switch ($item['status']) {
                                    case 'new':
                                    case 'timeout':
                                        echo 'Новий (не оплачений)';
                                        break;

                                    case 'success':
                                        echo 'Успішний';
                                        break;

                                    case 'error':
                                        echo 'Помилка';
                                        break;

                                    case 'reverse':
                                        echo 'Cторнований';
                                        break;
                                }
                            ?>
                        </td>
                    </tr>
                    <?php
                }
            ?>
        </tbody>
    </table>
</div>
<script>
$(document).ready(function(){
    $(".niceCheck").click(function() {
        changeCheck($(this), 'check-group');
    });
    $("#payments_only_success input").change(function() {
        var checked = ($("#payments_only_success input").is(':checked'));
        if (checked) {
            $('.item-payment-status').not('.item-payment-status-success').css('display', 'none');
        } else {
            $('.item-payment-status').css('display', '');
        }
    });
});
</script>
