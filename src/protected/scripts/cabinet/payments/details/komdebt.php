<div class="real-full-width-block">
    <table class="full-width-table datailbill-table no-border">
        <thead>
            <tr>
                <th class="first" colspan="5">Деталі платежу № <?= $payment['id']; ?></th>
            </tr>
        </thead>
        <tbody>
            <tr class="item-row even">
                <td colspan="1" class="first">Тип платежу</td>
                <td colspan="4" class="">Комунальні послуги</td>
            </tr>
            <tr class="item-row odd">
                <td colspan="1" class="first">Статус</td>
                <td colspan="4" class="">
                    <?php
                        switch ($payment['status']) {
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
            <tr class="item-row even">
                <td colspan="1" class="first">Дата та час</td>
                <td colspan="4" class="">
                    <span class="date-day"><?= getUkraineDate('j m Y', $payment['go_to_payment_time']); ?></span>
                    <span class="date-time"><?= getUkraineDate('H:i:s', $payment['go_to_payment_time']); ?></span>
                </td>
            </tr>
            <tr class="item-row odd">
                <td colspan="1" class="first">Сума</td>
                <td colspan="4" class="">
                    <?php
                        $summ = explode('.', number_format($payment['summ_plat'], 2));
                    ?>
                    <span class="item-summ">
                        <?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span>
                    </span>
                    грн
                </td>
            </tr>
            <tr class="item-row even">
                <td colspan="1" class="first">Комісія</td>
                <td colspan="4">
                    <?php
                        $summ = explode('.', number_format($payment['summ_komis'], 2));
                    ?>
                    <span class="item-summ">
                        <?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span>
                    </span>
                    грн
                </td>
            </tr>
            <tr class="item-row odd">
                <td colspan="1" class="first">Усього</td>
                <td colspan="4">
                    <?php
                        $summ = explode('.', number_format($payment['summ_total'], 2));
                    ?>
                    <span class="item-summ">
                        <?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span>
                    </span>
                    грн
                </td>
            </tr>
        </tbody>
        <thead>
            <tr>
                <th class="first" colspan="5">Склад платежу</th>
            </tr>
        </thead>
        <tbody>
            <tr class="bank-name title">
                <td class="first" colspan="2">Назва послуги та підприємства</td>
                <td>Особовий рахунок</td>
                <td>Сума, грн</td>
                <td>Період</td>
            </tr>
            <?php
                $counter = 0;
                
                foreach ($services as $item) {
                    $counter++;

                    $item['data'] = json_decode($item['data']);
                    $from_date = DateTime::createFromFormat('Y-m-d', $item['data']->dbegin);
                    $to_date = DateTime::createFromFormat('Y-m-d', $item['data']->dend);
                    ?>
                    <tr class="item-row <?= ($counter % 2 == 0) ? 'even' : 'odd'; ?>">
                        <td class="first" colspan="2">
                            <span><?= $item['data']->name_plat; ?></span>
                            <br>
                            <?= $item['data']->firm_name; ?>
                        </td>
                        <td><?= $item['data']->abcount; ?></td>
                        <td>
                            <?php
                                $summ = explode('.', number_format($item['sum'], 2));
                            ?>
                            <span class="item-summ">
                                <?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span>
                            </span>
                        </td>
                        <td style="white-space:nowrap">
                            <span class="date-day"><?= getUkraineDate('j m Y', $from_date->getTimestamp()); ?></span> —
                            <span class="date-day"><?= getUkraineDate('j m Y', $to_date->getTimestamp()); ?></span>
                        </td>
                    </tr>
                    <?php
                }
            ?>
        </tbody>
    </table>
</div>
<div class="clear"></div>
<br>
<br>
<?php
    if ($payment['status'] == 'success') {
        ?>
        <a class="btn green big" href="<?= BASE_URL; ?>/static/pdf/payment/<?= $payment['id']; ?>/GIOC-Invoice-<?= $payment['id']; ?>.pdf">&darr; Завантажити квитанцію</a>
        <?php
    }
?>
