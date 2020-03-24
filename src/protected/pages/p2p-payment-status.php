<?php

    use cri2net\php_pdo_db\PDO_DB;
    
    if (!Authorization::isLogin()) {
        define('SHOW_NEED_AUTH_MESSAGE', true);
        return require_once(PROTECTED_DIR . '/pages/cabinet/login.php');
    }

    try {
        $success = true;
        $_payment = PDO_DB::row_by_id(TABLE_PREFIX . 'payment', $__route_result['values']['payment_id']);
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
                            if ($_payment['type'] == 'p2p') {
                                ?>
                                <p>
                                    Дякуємо що скористалися нашим сервісом переказу з карти на карту! <br>
                                </p>
                                <?php
                            }
                        ?>
                        <p>
                            На Вашу електронну скриньку також було надіслано лист з підтвердженням транзакції.<br>
                            Повторно підтвердження транзакції можна завантажити в особистому кабінеті. <br>
                            <a href="<?= BASE_URL; ?>/static/pdf/payment/<?= $_payment['id']; ?>/KMDA-Invoice-<?= $_payment['id']; ?>.pdf">
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
