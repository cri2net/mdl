<?php

    return;



    $months = array('1'=>'январь', '2'=>'февраль', '3'=>'март', '4'=>'апрель', '5'=>'май', '6'=>'июнь', '7'=>'июль', '8'=>'август', '9'=>'сентябрь', '10'=>'октябрь', '11'=>'ноябрь', '12'=>'декабрь');
    $months_1 = array('1'=>'января', '2'=>'февраля', '3'=>'марта', '4'=>'апреля', '5'=>'мая', '6'=>'июня', '7'=>'июля', '8'=>'августа', '9'=>'сентября', '10'=>'октября', '11'=>'ноября', '12'=>'декабря');
    $months_when = array('1'=>'январе', '2'=>'феврале', '3'=>'марте', '4'=>'апреле', '5'=>'мае', '6'=>'июне', '7'=>'июле', '8'=>'августе', '9'=>'сентябре', '10'=>'октябре', '11'=>'ноябре', '12'=>'декабре');
    
    try {
        $user_id = Authorization::getLoggedUserId();
        $houses = Flat::getUserFlats($_SESSION['auth']['id']);
        $debt = new KomDebt();
        
        for ($i=0; $i < count($houses); $i++) {
            if (!$debt->haveDataToThisMounth($houses[$i]['flat_id'])) {
                if (date("n") == 1) {
                    $prevMonth = 12;
                    $year = date("Y") - 1;
                } else {
                    $prevMonth = date("n") - 1;
                    if (strlen($prevMonth) == 1) {
                        $prevMonth = '0'.$prevMonth;
                    }
                    $year = date("Y");
                }
                $dateBegin = "1.".$prevMonth.".".$year;
            } else {
                $dateBegin = date('1.m.Y');
            }


            $debtData = $debt->getData($houses[$i]['flat_id'], $dateBegin);
            $houses[$i]['debt_sum'] = $debt->getDebtSum($houses[$i]['flat_id'], $dateBegin);

            
            $this_year = substr($debtData['dbegin'], strlen($debtData['dbegin'])-4);
            $this_month = (int)substr($debtData['dbegin'], strlen($debtData['dbegin'])-7, 2);
            $houses[$i]['in_this_month'] = $months_when[(int)$this_month];
            $houses[$i]['date'] = '1 '.$months_1[$this_month].' '.$this_year;

            $oplat = $debt->getPayOnThisMonth($houses[$i]['flat_id'], $dateBegin);

            $tmp_oplat = (double)str_replace(',', '.', $oplat);
            $tmp_debt_summ = (double)str_replace(',', '.', $houses[$i]['debt_sum']);

            $houses[$i]['payed'] = (($tmp_oplat > 0) && ($tmp_oplat >= $tmp_debt_summ))?'bbox-green':'';
            $houses[$i]['oplat_this_month'] = $oplat;
            $houses[$i]['oplat_this_month_str'] = substr($oplat, 0, strlen($oplat) - 3);
            $houses[$i]['oplat_this_month_str'] .= '<span class="small">'.substr($oplat, strlen($oplat) - 3).' <span class="currency">грн</span></span>';

            $house_debt_sum_class = ($houses[$i]['debt_sum'] == '0,00') ? 'green':'red';
            $houses[$i]['debt_sum_str'] = '<span class="right '.$house_debt_sum_class.'">'.substr($houses[$i]['debt_sum'], 0, strlen($houses[$i]['debt_sum']) - 3);
            $houses[$i]['debt_sum_str'] .= '<span class="small">'.substr($houses[$i]['debt_sum'], strlen($houses[$i]['debt_sum']) - 3).' <span class="currency">грн</span></span>';
            $houses[$i]['debt_sum_str'] .= '</span>';
            $houses[$i]['icon'] = ($houses[$i]['kvartira'] > 0) ? 'flat' : '';
        }
    } catch (Exception $e) {
        ?>
        <div class="error">
            <?= $e->getMessage(); ?>
        </div>
        <?php
        return;
    }
?>
<div class="boxes" id="boxes">
    <?php
        if(count($houses) > 0) {
            foreach ($houses as $house) {
                if($house['error']) {
                    ?>
                    <div id="bbox_house_<?= $house['hash_id']; ?>" class="bbox <?= $house['icon']; ?>">
                        <div class="top">
                            <a href="#" class="del-icon" onclick="deleteHouse('<?= $house['hash_id']; ?>', '<?= $user_id; ?>'); return false;" title="Удалить Дом"></a>
                            <div class="bottom">
                                <div class="cont dom-error">
                                    <h4>Ошибка</h4>
                                    <p>Произошла временная ошибка. Попробуйте позже.</p>
                                    <div class="oplata"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                    continue;
                }
                ?>
                <div id="bbox_house_<?= $house['hash_id']; ?>" class="bbox <?= $house['payed']; ?> <?= $house['icon']; ?>">
                    <span class="is_payed">Счет на <?= $house['date']; ?> оплачен</span>
                    <div class="top">
                        <a href="#" class="del-icon" onclick="deleteHouse('<?= $house['hash_id']; ?>'); return false;" title="Удалить Дом"></a>
                        <div class="bottom">
                            <div class="cont dom">
                                <h4><?= $house['street_name']; ?></h4>
                                <span><?= $house['address']; ?></span>
                                <div class="oplata">
                                    <span>Посмотреть историю:<br></span>
                                    <a href="<?= BASE_URL; ?>/infocenter/historybill/<?= $house['hash_id']; ?>">платежей</a>&nbsp;/&nbsp;<a href="<?= BASE_URL; ?>/infocenter/detailbill/<?= $house['hash_id']; ?>">начислений</a>
                                </div>
                                <div class="links">
                                    <h2 class="bill-to-date">Счёт на <?= $house['date']; ?> года</h2>
                                    <span class="bill-to-date summ">Сумма к оплате <a class="nostyle" href="<?= BASE_URL; ?>/infocenter/bill/<?= $house['hash_id']; ?>"><?= $house['debt_sum_str']; ?></a></span>
                                    <span class="bill-to-date paysum">
                                        Оплачено в <?= $house['in_this_month']; ?>:
                                        <span class="right">
                                            <a class="nostyle" href="<?= BASE_URL; ?>/infocenter/historybill/<?= $house['hash_id']; ?>"><?= $house['oplat_this_month_str']; ?></a><br>
                                            <a class="history_link" href="<?= BASE_URL; ?>/infocenter/historybill/<?= $house['hash_id']; ?>">история платежей</a>
                                        </span>
                                    </span>
                                    <a href="<?= BASE_URL; ?>/infocenter/bill/<?= $house['hash_id']; ?>" class="big-pay-button"></a>
                                </div>
                                <div class="bot-bg-img"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?= ($house['position'] == 2) ? '<div class="clearr" id="boxesClear"></div>' : ''; ?>
                <?php
            }
        }
    ?>
</div>

<div class="overlay" id="overlay_dom_new" style="display:none">
    <div class="bgr">
        <div class="fone dom">
            <div class="close" onclick="fade('#divId'), fade('#overlay_dom_new'); return false">Закрыть окно</div>
            <div class="content">
                <h6>Добавление квартиры, дома <br /> </h6>
                <form action="#" method="post" name="" class="dom-popup-form" id="add_house_form" autocomplete="off">
                    <div class="input" id="street_div">
                         <label for="street">Выберите улицу *:</label>
                         <input id="street" type="text" value="" class="ac_input" autocomplete="off"/>
                         <span class="or_inet" style="font-size: 10px;color: ##333333;">Введите первые буквы улицы и <u>обязательно</u> выберите её из списка</span>
                    </div>
                    <div class="input" id="house_div">
                        <label for="house">Номер дома *:</label>
                        <select id="house" disabled="disabled"><option>-- выбрать --</option></select>
                        <a target="_blank" href="./nohousefaq/">Нет номера дома в списке?</a>
                    </div>
                    <div class="input" id="flat_div">
                        <label for="appartment">Номер квартиры *: </label>
                        <select id="flat" disabled="disabled"><option>-- выбрать --</option></select>
                    </div>
                    <div class="error" style="display: none;" id="error_house">
                        <p><span id="error_msg"></span><br />
                        </p>
                    </div>
                    <div class="blue_button add">
                        <input id="add_house_button" type="button" value="Добавить" onclick="addNewHouse(); return false;"/>
                    </div>
                    <div class="clearr"></div>
                </form>
                <div class="clear"></div>
            </div>
        </div>
    </div>
</div>