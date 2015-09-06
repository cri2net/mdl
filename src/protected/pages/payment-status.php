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


    if ($success) {
        ?>
        <div class="mini_info_block">
            <h2 class="success-pay">Транзакція пройшла успішно.</h2>
        </div>
        <p class="p-ch" style="margin-top:-10px; color:#101207; font-size:16px;">Дякуємо за сплату комунальних послуг!</p>
        <p class="p-ch" style="font-size:16px;">
            На Вашу електронну скриньку також було надіслано лист з підтвердженням оплати.<br>
            Повторно підтвердження платежу можна завантажити в особистому кабінеті
        </p>
        <p class="p-ch" style="font-size:16px;">
            Гроші будуть зараховані постачальникам послуг на наступний банківський день. <br>
            Якщо у Вас виникнуть які-небудь питання звертайтеся <a href="<?= BASE_URL; ?>/contacts/">в службу підтримки</a>.
        </p>
        <?php
    } else {
        ?>
        <div class="mini_info_block">
            <h2 class="error-pay">Помилка транзакції.</h2>
        </div>
        <?php
            if ($error) {
                ?><div id="error_center_red"><?= $error; ?></div> <?php
            }
        ?>
        <p class="p-ch">
            Транзакція не була здійснена. Перевірте правильність введення даних або зверніться <a href="<?= BASE_URL; ?>/contacts/">в службу підтримки</a>.
        </p>
        <?php
    }
