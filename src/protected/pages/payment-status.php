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
            <h2 class="success-pay">Транзакция прошла успешно.</h2>
        </div>
        <p class="p-ch" style="margin-top:-10px; color:#101207; font-size:16px;">Спасибо за оплату коммунальных услуг!</p>
        <p class="p-ch" style="font-size:16px;">
            На Ваш электронный ящик также было выслано письмо с подтверждением оплаты.<br>
            Повторно подтверждение платежа можно скачать в разделе МОИ ПЛАТЕЖИ
        </p>
        <p class="p-ch" style="font-size:16px;">
            Деньги будут зачислены поставщикам услуг на следующий банковский день. <br>
            Если у Вас возникнут какие-либо вопросы обращайтесь <a href="<?= BASE_URL; ?>/feedback/">в службу поддержки</a>.
        </p>
        <?php
    } else {
        ?>
        <div class="mini_info_block">
            <h2 class="error-pay">Ошибка транзакции.</h2>
        </div>
        <?php
            if($error)
            {
                ?><div id="error_center_red"><?= $error; ?></div> <?php
            }
        ?>
        <p class="p-ch">
            Транзакция не была осуществлена. Проверьте правильность ввода данных или обратитесь <a href="<?= BASE_URL; ?>/feedback/">в службу поддержки</a>.
        </p>
        <?php
    }
