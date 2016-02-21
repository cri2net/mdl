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
            <tr class="item-row even">
                <td colspan="1" class="first">Дата та час</td>
                <td colspan="4" class="">
                    <?php
                        $time = ($payment['go_to_payment_time']) ? $payment['go_to_payment_time'] : $payment['timestamp'];
                    ?>
                    <span class="date-day"><?= getUkraineDate('j m Y', $time); ?></span>
                    <span class="date-time"><?= getUkraineDate('H:i:s', $time); ?></span>
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
                $services_with_counters = [];

                for ($i=0; $i < count($services); $i++) {
                    $services[$i]['counter_data'] = (array)(@json_decode($services[$i]['counter_data']));
                    $services[$i]['data'] = json_decode($services[$i]['data']);
                    
                    if (!empty($services[$i]['counter_data'])) {
                        $services_with_counters[] = $services[$i];
                    }
                }
                
                foreach ($services as $item) {
                    $counter++;

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
    <?php
        if (!empty($services_with_counters)) {
            ?>
            <thead>
                <tr>
                    <th class="first" colspan="5">Показання лічильників</th>
                </tr>
            </thead>
            <thead>
                <tr class="bank-name title">
                    <td class="first">Послуга</td>
                    <td>№ ліч.</td>
                    <td>Поп. пок., м&sup3;/КвтЧ</td>
                    <td>Пот. пок., м&sup3;/КвтЧ</td>
                    <td>Різниця, м&sup3;/КвтЧ</td>
                </tr>
            </thead>
            <tbody>
                <?php
                    $counter = 0;
                    
                    foreach ($services_with_counters as $item) {

                        $counter_number = 0;
                        foreach ($item['counter_data'] as $counter_item) {
                            $counter++;
                            $counter_number++;

                            if (empty($counter_item->abcounter)) {
                                $counter_item->abcounter = $counter_number;
                            }

                            $no_border = (($counter == count($firm['counter'])) && ($counter < count($debtData['firm'])));
                            ?>
                            <tr class="item-row <?= ($counter % 2 == 0) ? 'even' : 'odd'; ?>">
                                <td class="first">
                                    <span><?= $item['data']->name_plat; ?></span>
                                    <br>
                                    <?= $item['data']->firm_name; ?>
                                </td>
                                <td><span class="item-summ"><?= htmlspecialchars($counter_item->abcounter); ?></span></td>
                                <td>
                                    <?php
                                        $summ = explode('.', number_format($counter_item->old_value, 2));
                                    ?>
                                    <span class="item-summ">
                                        <?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                        $summ = explode('.', number_format($counter_item->new_value, 2));
                                    ?>
                                    <span class="item-summ">
                                        <?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                        $summ = explode('.', number_format($counter_item->pcount, 2));
                                    ?>
                                    <span class="item-summ">
                                        <?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span>
                                    </span>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                ?>
            </tbody>
            <?php
        }
    ?>
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
    } elseif ($payment['status'] != 'new') {
        ?>
        <form method="post" action="<?= BASE_URL; ?>/post/cabinet/object-item/repay/">
            <input type="hidden" name="payment_id" value="<?= $payment['id']; ?>" />
            <button class="btn green big">Спробувати сплатити ще раз</button>
        </form>
        <?php
    }
?>
