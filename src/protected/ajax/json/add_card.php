<?php
try {
    $response = [];
    $user_id = Authorization::getLoggedUserId();
    if (!$user_id) {
        throw new Exception(ERROR_USER_NOT_LOGGED_IN);
    }

    switch ($_POST['action']) {

        case 'addcard':
            $birthday    = trim($_POST['birthday']);
            $pasp_number = trim($_POST['pasp_number']);
            $card_number = trim($_POST['card_number']);
            $card_number = preg_replace('/[^0-9]/', '', $card_number);

            $birthday = DateTime::createFromFormat('d.m.Y', $birthday);

            if (empty($card_number)) {
                throw new Exception(ERROR_ADD_CARD_EMPTY_CARD_NUMBER);
            }
            if (empty($pasp_number)) {
                throw new Exception(ERROR_ADD_CARD_EMPTY_PASP_NUMBER);
            }
            if ($birthday === false) {
                throw new Exception(ERROR_ADD_CARD_EMPTY_BIRTHDAY);
            }

            $birthday = date_timestamp_get($birthday);
            $birthday = date('d.m.Y', $birthday);

            $khreshchatyk = new Khreshchatyk;
            $card_data = $khreshchatyk->getCardData($card_number, $pasp_number, $birthday);
            if ($card_data == false) {
                throw new Exception(ERROR_ADD_CARD_CARD_NOT_FOUND);
            }

            if ($card_data['card_state_id'] != 5) {
                throw new Exception(ERROR_ADD_CARD_BAD_CARD_STATE_ID);
            }

            $pdo = PDO_DB::getPDO();
            $stm = $pdo->prepare("SELECT * FROM " . TABLE_PREFIX . "user_cards WHERE pan=? AND user_id=? LIMIT 1");
            $stm->execute([$card_number, $user_id]);
            
            $item = $stm->fetch();
            if ($item) {
                throw new Exception(ERROR_ADD_CARD_ALREADY_EXISTS);
            }

            $time = microtime(true);
            $pos = PDO_DB::max_pos(TABLE_PREFIX . 'user_cards', "user_id='$user_id'") + 1;
            $additional = [
                'acc_bank'      => $card_data['acc_bank'],
                'card_state_id' => $card_data['card_state_id'],
            ];

            $arr = [
                'user_id'     => $user_id,
                'created_at'  => microtime(true),
                'updated_at'  => microtime(true),
                'pos'         => $pos,
                'pan'         => $card_number,
                'last_verify' => $time,
                'additional'  => json_encode($additional)
            ];

            $user_card_id = PDO_DB::insert($arr, TABLE_PREFIX . 'user_cards');
            
            $response['card_id'] = $user_card_id;
            $response['card_number'] = substr($card_number, 0, 4) . '********' . substr($card_number, 12);
            break;
        
        case 'remove_card':
            $card_id = $_POST['card_id'];
            $pdo = PDO_DB::getPDO();

            $stm = $pdo->prepare("SELECT * FROM " . TABLE_PREFIX . "user_cards WHERE id=? AND user_id=? LIMIT 1");
            $stm->execute([$card_id, $user_id]);
            $cart_item = $stm->fetch();
            
            if ($cart_item) {
                $stm = $pdo->prepare("DELETE FROM " . TABLE_PREFIX . "user_cards WHERE id=? LIMIT 1");
                $stm->execute([$cart_item['id']]);
                PDO_DB::rebuild_pos(TABLE_PREFIX . 'user_cards', "user_id='$user_id'");
            }
            
            break;

        default:
            throw new Exception("Невідома дія");
    }

    $response['status'] = true;
    
} catch (Exception $e) {
    $response = ['status' => false, 'text' => $e->getMessage()];
}

echo json_encode($response);
