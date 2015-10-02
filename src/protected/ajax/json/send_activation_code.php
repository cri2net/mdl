<?php
    try {
        if (!Authorization::isLogin()) {
            throw new Exception(ERROR_USER_NOT_LOGGED_IN);
        }
        
        $verify_code = Authorization::generateUserCode($__userData['id'], 'verify_email');
        $verify_link = BASE_URL . '/cabinet/verify-email/' . $verify_code . '/';
        
        $email = new Email();

        return $email->send(
            [$__userData['email'], "{$__userData['name']} {$__userData['fathername']}"],
            'Підтвердження електронної пошти',
            '',
            'verify-email',
            [
                'username'    => htmlspecialchars("{$__userData['name']} {$__userData['fathername']}"),
                'email'       => $__userData['email'],
                'verify_link' => $verify_link
            ]
        );

        $response = ['status' => true];
    } catch (Exception $e) {
        $response = ['status' => false, 'text' => $e->getMessage()];
    }

    echo json_encode($response);
