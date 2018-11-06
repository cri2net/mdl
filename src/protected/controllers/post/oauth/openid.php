<?php

use cri2net\php_pdo_db\PDO_DB;

$conf = require(PROTECTED_DIR . '/conf/openid.php');

$OpenId = new OpenId();

$OpenId->clientId = $conf['clientId'];
$OpenId->secret = $conf['secret'];
$OpenId->redirect_uri = $conf['redirect-url'];

if (isset($_GET['error'])) {
    return BASE_URL . '/cabinet/' . substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], '?'));
}

if (!isset($_GET['code'])) {

    $authUrl = $OpenId->getAuthorizationUrl();
    $_SESSION['_openid_state'] = $OpenId->getState();
    Http::redirect($authUrl);

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['_openid_state'])) {

    // State is invalid, possible CSRF attack in progress
    
    $OpenId->unsetState();
    unset($_SESSION['_openid_state']);
    throw new Exception('Invalid state');
}

try {
    $token = $OpenId->getAccessToken($_GET['code']);
    $token = @json_decode($token);
    
    if (!empty($token->error)) {
        throw new Exception("{$token->error} | {$token->error_description}");
    }

    if (!empty($token)) {

        $decoded = @MyJwt::decode($token->id_token, $OpenId->secret, ['RS256']);
        
        $headers = [
            "Authorization: Bearer {$token->access_token}",
        ];

        $userinfo = $OpenId->getInfoFromEmr($decoded->sub, $token->access_token);
        $userinfo = @json_decode($userinfo);

        if (!empty($decoded)) {

            $stm = PDO_DB::prepare("SELECT * FROM " . User::TABLE . " WHERE openid_id = ? AND deleted = 0 LIMIT 1", [$decoded->sub]);
            $user = $stm->fetch();

            $openid_data = [
                'decoded'              => $decoded,
                'userinfo'             => $userinfo,
                'token'                => $token,
                'access_token_expires' => $token->expires_in + time(),
            ];

            if ($user !== false) {

                if (!Authorization::isLogin()) {
                    // пользователь не авторизаван. Авторизовываем
                    Authorization::login($user['login'], $user['password'], true, true);
                }
                
                $user_id = $user['id'];
            } else {
                
                if (Authorization::isLogin()) {
                    // Пользователь авторизован. Вероятно, это привязка ещё одного аккаунта
                    $user_id = Authorization::getLoggedUserId();
                } else {
                    // создаём нового пользователя

                    $password_key = generateCode(16);
                    $password = generateCode(40);

                    $data = [
                        'email'          => @$userinfo->data->profile->emails[0]->email . '',
                        'password'       => Authorization::generate_db_password($password, $password_key),
                        'password_key'   => $password_key,
                        'verified_email' => ((@$userinfo->data->profile->emails[0]->confirmed) ? 1 : 0),
                        'login'          => '',
                        'lastname'       => '',
                        'name'           => @$decoded->name . '',
                        'fathername'     => '',
                        'created_at'     => microtime(true),
                        'mob_phone'      => '',
                        'auto_reg'       => 1,
                        'openid_id'      => $decoded->sub,
                    ];

                    $user_id = PDO_DB::insert($data, User::TABLE);

                    if (!Authorization::isLogin()) {
                        Authorization::login('', $password);
                    }

                    User::importFlatsFromGioc($user_id);
                }
            }

            PDO_DB::update(
                ['openid_data' => json_encode($openid_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)],
                User::TABLE,
                $user_id
            );
        }
    }

} catch (Exception $e) {

    // Failed to get user details | API error
    throw $e;
}

return BASE_URL;
