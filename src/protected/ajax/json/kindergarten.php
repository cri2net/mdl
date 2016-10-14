<?php
try {
    switch ($_POST['action']) {
        case 'get_children_list':
            $response = Kinders::getChildrenList($_POST['id_rono_group'], $_POST['fio']);
            break;

        case 'get_district_list':
            $response = Kinders::getDistrictList($_POST['city_id']);
            break;
            
        case 'get_classes_list':
            $response = Kinders::getClassesList($_POST['id_sad']);
            break;

        case 'get_institution_list':
            $list = Kinders::getInstitutionList($_POST['id_district']);
            $response = ['list' => []];

            for ($i=0; $i < count($list); $i++) {
                $response['list'][] = [
                    'id'   => $list[$i]['R101'],
                    'name' => $list[$i]['NAME_SAD'],
                ];
            }
            break;

        default:
            throw new Exception("Невідома дія");
    }

    $response['status'] = true;
    
} catch (Exception $e) {
    $response = ['status' => false, 'text' => $e->getMessage()];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
