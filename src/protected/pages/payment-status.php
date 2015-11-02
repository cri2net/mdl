<?php
    if (!Authorization::isLogin()) {
        define('SHOW_NEED_AUTH_MESSAGE', true);
        return require_once(ROOT . '/protected/pages/cabinet/login.php');
    }

    try {
        $success = true;
        $_payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $__route_result['values']['payment_id']);
        if (($_payment === null) || ($_payment['user_id'] != Authorization::getLoggedUserId())) {
            throw new Exception(ERROR_TRANSACTION_FOUND);
        }

        if ($_payment['status'] === 'error') {
            throw new Exception(ERROR_TRANSACTION_NOT_SUCCESS);
        }
        
        if ($_payment['status'] === 'new') {
            throw new Exception(ERROR_TRANSACTION_NEW);
        }
    } catch(Exception $e) {
        $error = $e->getMessage();
        $success = false;
    }
?>
<h1 class="big-title">Статус транзакції</h1> <br>
<?php
    if ($success) {
        ?>
        <h2 class="big-success-message">Транзакція пройшла успішно.</h2>
        <div class="main-page-text">
            <?php
                if ($_payment['type'] == 'komdebt') {
                    ?><div>Дякуємо за сплату комунальних послуг!</div> <?php
                }
            ?>
            <div>
                На Вашу електронну скриньку також було надіслано лист з підтвердженням оплати.<br>
                Повторно підтвердження платежу можна завантажити в особистому кабінеті
            </div>
            <div>
                Якщо у Вас виникнуть які-небудь питання звертайтеся <a href="<?= BASE_URL; ?>/contacts/">в службу підтримки</a>.
            </div>
        </div>
        <?php
    } else {
        ?>
        <h2 class="big-error-message">Помилка транзакції.</h2>
        <?php
            if ($error) {
                ?><div class="error-description"><?= $error; ?></div> <?php
            }
        ?>
        <div>
            Транзакція не була здійснена. Перевірте правильність введення даних або зверніться <a href="<?= BASE_URL; ?>/contacts/">в службу підтримки</a>.
        </div>
        <?php
    }
