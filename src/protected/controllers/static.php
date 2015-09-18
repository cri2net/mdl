<?php
try {
    
    if (isset($__route_result['values']['path'])) {
        $ext = pathinfo(basename($__route_result['values']['path']), PATHINFO_EXTENSION);

        switch ($ext) {
            case 'css':
                $filename = ROOT . '/style/' . $__route_result['values']['path'];
                if (file_exists($filename)) {
                    header("Content-Type: text/css; charset=utf-8");
                    Http::gzip(file_get_contents($filename), true, 'text/css');
                    exit();
                } else {
                    header("HTTP/1.1 404 Not Found");
                    exit();
                }
                break;

            case 'js':
                $filename = ROOT . '/js/' . $__route_result['values']['path'];
                if (file_exists($filename)) {
                    Http::gzip(file_get_contents($filename), true, 'application/javascript');
                    exit();
                } else {
                    header("HTTP/1.1 404 Not Found");
                    exit();
                }
                break;
        }

        header("HTTP/1.1 404 Not Found");
        exit();
    }

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
