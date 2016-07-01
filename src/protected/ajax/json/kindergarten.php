<?php
try {
    switch ($_POST['action']) {
        case 'get_children_list':
            $response = Kinders::getChildrenList($_POST['id_rono_group'], $_POST['fio']);
            break;
            
        case 'get_classes_list':
            $response = Kinders::getClassesList($_POST['id_sad']);
            break;

        default:
            throw new Exception("Невідома дія");
    }

    $response['status'] = true;
    
} catch (Exception $e) {
    $response = ['status' => false, 'text' => $e->getMessage()];
}

echo json_encode($response);
