<?php
    try {
        $_SESSION['cabinet-settings'] = [];
        $__userData = User::getUserById(Authorization::getLoggedUserId());

        if (!$__userData) {
            throw new Exception(ERROR_USER_NOT_LOGGED_IN);
        }

        $update = ['notify_email' => (int)isset($_POST['notify_email'])];
        PDO_DB::update($update, User::TABLE, $__userData['id']);

        $houses = Flat::getUserFlats($__userData['id']);
        
        for ($i=0; $i < count($houses); $i++) {
            $update = [
                'notify' => (int)isset($_POST['notify_object_' . $houses[$i]['id']])
            ];
            PDO_DB::update($update, Flat::USER_FLATS_TABLE, $houses[$i]['id']);
        }

        $_SESSION['cabinet-settings']['status'] = true;
        $_SESSION['cabinet-settings']['text'] = 'Налаштування повідомлень збережено';
    } catch (Exception $e) {
        $_SESSION['cabinet-settings']['status'] = false;
        $_SESSION['cabinet-settings']['error']['text'] = $e->getMessage();
    }
