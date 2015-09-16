<?php
    $payments = PDO_DB::table_list(
        ShoppingCart::TABLE,
        "user_id={$__userData['id']} AND processing IS NOT NULL",
        "go_to_payment_time DESC"
    );


    if (empty($payments)) {
        ?>
        <div class="big-warning-message">
            Немає платежів для відображення
        </div>
        <?php
        return;
    }

?>
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
                    ?>
                    <tr class="item-row <?= ($counter % 2 == 0) ? 'even' : 'odd'; ?>">
                        <td class="first">
                            <a href="<?= BASE_URL; ?>/cabinet/payments/details/<?= $item['id']; ?>/"><?= $item['id']; ?></a>
                        </td>
                        <td>
                            <span class="date-day"><?= getUkraineDate('j m Y', $item['go_to_payment_time']); ?></span><br>
                            <span class="date-time"><?= date('H:i:s', $item['go_to_payment_time']); ?></span>
                        </td>
                        <td>
                            <?php
                                switch ($item['type']) {
                                    case 'komdebt':
                                        echo 'Комунальні послуги';
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
                                        echo 'Новий (не оплачений)';
                                        break;

                                    case 'success':
                                        echo 'Успішний';
                                        break;

                                    case 'error':
                                        echo 'Помилка';
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
