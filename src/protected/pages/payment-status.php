<?php

    use cri2net\php_pdo_db\PDO_DB;
    
    if (!Authorization::isLogin()) {
        define('SHOW_NEED_AUTH_MESSAGE', true);
        return require_once(PROTECTED_DIR . '/pages/cabinet/login.php');
    }

    try {
        $success = true;
        $_payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $__route_result['values']['payment_id']);
        if (($_payment === null) || ($_payment['user_id'] != Authorization::getLoggedUserId())) {
            throw new Exception(ERROR_TRANSACTION_FOUND);
        }

        if ($_payment['status'] === 'error') {

            $error_desc = ShoppingCart::getErrorDescription($_payment['processing'], $_payment['trancode']);

            if (!empty($error_desc)) {
                throw new Exception($error_desc);
            }

            throw new Exception(ERROR_TRANSACTION_NOT_SUCCESS);
        }
        
        if ($_payment['status'] === 'new') {
            throw new Exception(ERROR_TRANSACTION_NEW);
        }
    } catch(Exception $e) {
        $error = $e->getMessage();
        $success = false;
    }

    require_once(PROTECTED_DIR . '/layouts/navbar_inner.php');
    require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php');


?>
<div class="container">
    <content>
        <div class="text">
            <?php
                if ($success) {
                    ?>
                    <h2 class="big-success-message">Транзакція пройшла успішно.</h2>
                    <div class="main-page-text">
                        <?php
                            if ($_payment['type'] == 'komdebt') {
                                ?>
                                <p>
                                    Дякуємо за сплату комунальних послуг! <br>
                                    Зміни стосовно Вашої успішної оплати буде включено у рахунок за наступний календарний місяць.
                                </p>
                                <?php
                            }
                        ?>
                        <p>
                            На Вашу електронну скриньку також було надіслано лист з підтвердженням оплати.<br>
                            Повторно підтвердження платежу можна завантажити в особистому кабінеті. <br>
                            <a href="<?= BASE_URL; ?>/static/pdf/payment/<?= $_payment['id']; ?>/CKS-Invoice-<?= $_payment['id']; ?>.pdf">
                                <button class="btn btn-blue btn-md">&darr; Завантажити квитанцію</button> <br>
                            </a>
                        </p>
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
                        Транзакція не була здійснена. Перевірте правильність введення даних або зверніться в службу підтримки.
                    </div>
                    <?php
                }
            ?>
        </div>
    </content>
</div>
