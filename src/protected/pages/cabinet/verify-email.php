<?php
    use cri2net\php_pdo_db\PDO_DB;
?>
<h1 class="big-title">Підтвердження електронної пошти</h1>
<?php
    if (Authorization::isLogin() && $__userData['activated'] && $__userData['verified_email'] && !$__userData['broken_email']) {
        ?><h2 class="big-success-message">Ваша адреса вже підтверджена</h2><?php
        return;
    }
    
    try {
        $code = (isset($__route_result['values']['section'])) ? $__route_result['values']['section'] : false;
        if ($code) {
            $record = Authorization::verifyUserCode($code, 'verify_email');
            $user = User::getUserById($record['user_id']);

            $update = [
                'activated'      => 1,
                'broken_email'   => 0,
                'verified_email' => 1,
            ];

            PDO_DB::update($update, User::TABLE, $user['id']);
            Authorization::unsetUserCode($record['id']);
        }
    } catch (Exception $e) {
        ?>
        <h2 class="big-error-message"><?= $e->getMessage(); ?></h2>
        <?php
        $code = false;
    }

    if (!$code && Authorization::isLogin()) {
        ?>
        <div class="clear"></div>
        <div style="display:inline-block;">
            <div onclick="send_activation_code(this);" class="btn big bold green">Вислати код підтвердження</div>
            <div style="display:none;" id="verify-email_send">
            Код відправлено на електронну пошту.
            <?php
                $link = Email::getLinkToService($__userData['email']);
                if ($link) {
                    ?> Перейти до <a href="<?= $link['link']; ?>"><?= $link['title']; ?></a> <?php
                }
            ?>
            </div>
        </div>
        <?php
    } else {
        ?>
        <h2 class="big-success-message">Ваша адреса успішно підтверджена</h2>
        <script>
            $('#verify-email-header-warning').fadeOut(0);
        </script>
        <?php
    }
?>
