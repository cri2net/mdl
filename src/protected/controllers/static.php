<?php
try {
    switch ($__route_result['values']['type']) {
        case 'payment':
            if (!Authorization::isLogin()) {
                throw new Exception(ERROR_USER_NOT_LOGGED_IN);
            }

            $id = $__route_result['values']['id'];
            $payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $id);
            
            if (!$payment || ($payment['user_id'] != Authorization::getLoggedUserId())) {
                throw new Exception(ERROR_GET_PAYMENT);
            }

            if ($payment['status'] != 'success') {
                throw new Exception(ERROR_GET_PAYMENT_PDF);
            }

            $pdf = ShoppingCart::getPDF($payment['id']);

            $filename = (isset($__route_result['values']['filename']))
                ? basename($__route_result['values']['filename'])
                : "GIOC-Invoice-{$payment['id']}.pdf";

            header('Content-Description: File Transfer');
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename=' . $filename);
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . strlen($pdf));

            echo $pdf;

            break;
    }

} catch (Exception $e) {
    die($e->getMessage());
}
