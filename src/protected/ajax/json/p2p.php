<?php
try {

    $response = [];

    $form_data = Validator::preparationArr($_POST);

    $form_data['card_no'] = preg_replace('/[^0-9]/', '', $form_data['card_no']);
    Validator::isNumeric('Номер картки', $form_data['card_no']);
    Validator::isLength('Номер картки', $form_data['card_no'], 16);

    $form_data['card_no_dest'] = preg_replace('/[^0-9]/', '', $form_data['card_no_dest']);
    Validator::isNumeric('Номер картки', $form_data['card_no_dest']);
    Validator::isLength('Номер картки', $form_data['card_no_dest'], 16);

    Validator::isNumeric('Pік', $form_data['year']);
    Validator::isLength('Pік', $form_data['year'], 2);
    Validator::isInRange('Pік', $form_data['year'], 20, 45);
    $form_data['year'] = '20'.(int)$form_data['year'];

    Validator::isLength('Місяць', $form_data['month'], 2);
    Validator::isNumeric('Місяць', $form_data['month']);
    Validator::isInRange('Місяць', $form_data['month'], 1, 12);
    $form_data['month'] = (int)$form_data['month'].'';

    Validator::isNumeric('CVV', $form_data['cvv']);
    Validator::isLength('CVV', $form_data['cvv'], 3);
    $form_data['cvv'] = (int)$form_data['cvv'].'';

    $form_data['sum'] = round(floatval(str_replace(',', '.', $form_data['sum'])), 2);
    if ($form_data['sum'] <= 0) {
        throw new Exception('Сума платежу не може бути менше або дорівнювати нулю');
    }
    if ($form_data['sum'] > MAX_AMOUNT) {
        throw new Exception(EXCESS_PAYMENT_AMOUNT);
    }

    $response['result'] = KmdaP2P::createPayment(
        $form_data['card_no'],
        $form_data['month'],
        $form_data['year'],
        $form_data['sum'],
        $form_data['cvv'],
        $form_data['card_no_dest'],
        null,
        null,
        Authorization::getLoggedUserId()
    );

    $response['status'] = true;

} catch (Exception $e) {
    $response = ['status' => false, 'text' => $e->getMessage()];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
