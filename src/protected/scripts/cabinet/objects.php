<?php
    require_once(PROTECTED_DIR . '/layouts/navbar_inner.php');
    require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php');
?>
<div class="object">
    <content>
        <?php
            if (isset($_SESSION['objects-auth']['status']) && !$_SESSION['objects-auth']['status']) {
                ?>
                <h3 class="error"><?= $_SESSION['objects-auth']['error']['text']; ?></h3>
                <?php
                unset($_SESSION['objects-auth']['status']);
            }

            try {
                define('IS_ONLINE_REP', true);
                $user_id = Authorization::getLoggedUserId();
                $flats = Flat::getUserFlats($user_id);
                $debt = new KomDebt();

                for ($i=0; $i < count($flats); $i++) {

                    try {
                        $debtData = $debt->getData($flats[$i]['flat_id'], null, 0);
                        $dateBegin = date('1.m.Y', $debtData['timestamp']);
                        $flats[$i]['timestamp'] = $debtData['timestamp'];

                        // Оплаты за месяц надо запрашивать, передавая dbegin на 1 число след. след. месяца от dbegin оплат
                        // Чтобы было: начисления за ноябрь, dbegin = 1.10, а оплаты dbegin оплат = 1.12
                        $oplat_timestamp = strtotime('first day of next month', $debtData['timestamp']);
                        $oplat_timestamp = strtotime('first day of next month', $oplat_timestamp);

                        $flats[$i]['debt_sum'] = $debtData['full_dept'];

                        $flats[$i]['on_this_month'] = $MONTHS_NAME[date('n', $debtData['timestamp'])]['ua']['small'];
                        $flats[$i]['date'] = $debtData['date'];

                        try {
                            $oplat = $debt->getPayOnThisMonth($flats[$i]['flat_id'], date('1.m.Y', $oplat_timestamp));
                        } catch (Exception $e) {
                            $oplat = '0,00';
                        }

                        $tmp_oplat = (double)str_replace(',', '.', $oplat);
                        $tmp_debt_summ = (double)str_replace(',', '.', $flats[$i]['debt_sum']);

                        $flats[$i]['payed'] = (($tmp_oplat > 0) && ($tmp_oplat >= $tmp_debt_summ)) ? 'bbox-green' : '';
                        $flats[$i]['oplat_this_month'] = $oplat;
                        $flats[$i]['oplat_this_month_str'] = substr($oplat, 0, strlen($oplat) - 3);
                        $flats[$i]['oplat_this_month_str'] .= '<span class="small">'.substr($oplat, strlen($oplat) - 3).' <span class="currency">&#8372;</span></span>';

                        $flats[$i]['debt_sum_str'] = substr($flats[$i]['debt_sum'], 0, strlen($flats[$i]['debt_sum']) - 3);
                        $flats[$i]['debt_sum_str'] .= '<span class="small">'.substr($flats[$i]['debt_sum'], strlen($flats[$i]['debt_sum']) - 3).' <span class="currency">&#8372;</span></span>';
                    } catch (Exception $e) {
                        $flats[$i]['error'] = 1;
                    }
                }
            } catch (Exception $e) {
                ?><h3 class="error"><?= $e->getMessage(); ?></h3> <?php
                return;
            }
        ?>
        <div class="object__cabinet">
            <?php
                ?>
                <div class="object__inner-cabinet">
                    <?php
                        if (count($flats) > 0) {

                            for ($i=0; $i < count($flats); $i++) {
                                $flat = $flats[$i];
                                ?>
                                <div class="card card--outer">
                                    <div class="card__container house_item flat matchHeight <?= $flat['payed']; ?>">
                                        <div class="info-section card__wrapper">
                                            <div class="payed-icon"></div>
                                            <div class="card__title">
                                                <?php
                                                    if ($flat['title']) {
                                                        ?>
                                                        <a href="<?= BASE_URL; ?>/cabinet/objects/<?= $flat['id']; ?>/" class="header"><?= htmlspecialchars($flat['title']); ?></a>
                                                        <div class="address"><?= $flat['address']; ?></div>
                                                        <?php
                                                    } else {
                                                        $street_name = ($flat['street_name'] !== $flat['street_name_full'])
                                                            ? $street_name = '<span title="'. $flat['street_name_full'] .'">' . $flat['street_name'] . '</span>'
                                                            : $flat['street_name'];
                                                        ?>
                                                        <a href="<?= BASE_URL; ?>/cabinet/objects/<?= $flat['id']; ?>/" class="card__address">
                                                            <?= $flat['detail_address']['city']; ?>, <br> <?= $street_name; ?>, <?= $flat['detail_address']['house']; ?> кв. <?= $flat['detail_address']['flat']; ?>
                                                        </a>
                                                        <?php
                                                    }

                                                    if (!$flat['error']):
                                                    ?><div class="bydate card__bill">Рахунок за <?= $MONTHS_NAME[date('n', $flat['timestamp'])]['ua']['small']; ?> <?= date('Y', $flat['timestamp']); ?></div>
                                                    <?php endif;
                                                ?>
                                            </div>
                                            <a data-object-id="<?= $flat['id']; ?>" class="remove remove-object-check card__delete">&times;</a>
                                            <?php
                                                if ($flat['error']) {
                                                    ?>
                                                    <div class="values align-center">
                                                        <b style="color:#900;">Виникла тимчасова помилка</b>
                                                    </div>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <div class="values card__values">

                                                        <div class="card__line row">
                                                            <div class="value-title value-title-orange col-lg-8 col-md-7 col-ms-6">Сума до сплати</div>
                                                            <div class="align-right col-lg-4 col-md-5 col-ms-6 pull-right">
                                                                <div class="value card__value">
                                                                    <?= $flat['debt_sum_str']; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="card__line row">
                                                            <div class="value-title value-title-green col-lg-8 col-md-8 col-ms-6">Сплачено за <?= $flat['on_this_month']; ?></div>
                                                            <div class="align-right col-lg-4 col-md-4 col-ms-6 pull-right">
                                                                <div class="value card__value">
                                                                    <?= $flat['oplat_this_month_str']; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php
                                                }
                                            ?>
                                            <span class="align-center"><a href="<?= BASE_URL; ?>/cabinet/objects/<?= $flat['id']; ?>/" class="btn button button__form button__form--card"><span class="fa  fa-check"></span> Перейти до об’єкту</a></span>
                                        </div>
                                        <div class="remove-section card__remove">
                                            <p class="card__remove-text">Ви впевнені, що хочете видали цей об’єкт з аккаунту?</p>
                                            <a href="#" class="btn btn-orange remove-object button button__form button__form--remove button__form--registration" data-object-id="<?= $flat['id']; ?>"><span class="fa fa-trash"></span> Видалили</span></a>
                                            <a href="#" class="btn btn-green-bordered remove-object-cancel button button__form button__form--remove button__form--registration"><span class="fa fa-close"></span> Скасувати</span></a>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        ?>
                    <?php
                }

                if ((Authorization::isLogin() && (Flat::getFlatCount() < Flat::getMaxUserFlats()))) {
                    ?>
                    <div class="col-md-4 col-sm-6 card__button">
                        <div class="house_item flat house_add matchHeight <?= $flat['payed']; ?>">
                            <span class="align-center">
                                <a class="btn btn-green-darker add-new add-new-object button button__form button__form--register" onclick="$('#add-object-form').slideToggle(300);"><span class="fa fa-plus"></span>Додати об’єкт</a>
                            </span>
                            <div class="modal fade" id="modal-object-add" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div id="add-object-form" class="add-object-form" style="display:none;">
                                            <form class="form-block form form__login form__login--outer" method="post" action="<?= BASE_URL; ?>/post/cabinet/objects/">
                                                <?php require_once(PROTECTED_DIR . '/scripts/cabinet/objects-add-form.php'); ?>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            ?>
            </div>
        </div>
    </content>
</div>

<form id="remove_object_form" style="display: none;" action="<?= BASE_URL; ?>/post/cabinet/object-item/edit/" method="post">
    <input type="hidden" name="flat_id" id="remove_object_id">
    <input name="delete_object" value="1" type="hidden">
</form>
