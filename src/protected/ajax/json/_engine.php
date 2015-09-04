<?php
    $objClassName = basename($_REQUEST['obj']);
    $action = $_REQUEST['ac'];
    $response = array();

    $accepted_classes = array('Flat', 'House', 'Street');

    try {
        if (!in_array($objClassName, $accepted_classes)) {
            throw new Exception('PROTECTED CLASS CALLED : '. $objClassName, 666);
        }

        if (file_exists(ROOT . "/protected/classes/$objClassName.class.php")) {
            if (!class_exists($objClassName)) {
                throw new Exception('UNKNOWN CLASS CALLED : '. $objClassName, 666);
            }
        } else {
            throw new Exception('UNKNOWN CLASS CALLED: '.$objClassName, 666);
        }
        
        $obj = new $objClassName();
        
        if (method_exists($obj, $action)) {
            $response['result'] = call_user_func_array(array($obj, $action), (array)$_REQUEST['params']);
        } else {
            throw new Exception('UNKNOWN METHOD CALLED : '.$action, 666);
        }
        
        $response['success'] = true;
    } catch (Exception $e) {
        $response['record'] = array('msg' => $e->getMessage());
        $response['success'] = false;
    }
    
    echo json_encode($response);
