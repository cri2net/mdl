<?php


// может это брать в онлайне с reports?
private $districts = array(
    array('name' => 'СУВОРОВЬСКА РАОМР', 'id' => 28366),
    array('name' => 'КИЇВСЬКА РАОМР',    'id' => 28367),
    array('name' => 'МАЛИНОВСЬКА РАОМР', 'id' => 28368),
    array('name' => 'ПРИМОРЬСКА РАОМР',  'id' => 28369),
    array('name' => 'ГОРОНО',            'id' => 111020),
);

private $list = array(
    array('R101' => '28421'),
    array('R101' => '28422'),
    array('R101' => '28423'),
    array('R101' => '28424'),
    array('R101' => '28530'),
);

$err = array();
$Kinders = new Kinders();

foreach ($this->districts as $item) {
   
    $list = $Kinders->getInstitutionList($item['id']);
   
    for ($i=0; $i < count($list); $i++) {
        $kindergarten = $list[$i];
        $kindergarten['IDAREA'] = 1;
        $kindergarten['ID_DISTRICT'] = $item['id'];
        $kindergarten['FIRME'] = $item['id'];
        
        $this->list[] = $kindergarten;
    }
}

$penalty_email       = (isset($_SESSION['auth']['email']))        ? $_SESSION['auth']['email'] : '';
$penalty_first_name  = (isset($_SESSION['auth']['first_name']))   ? $_SESSION['auth']['first_name'] : '';
$penalty_middle_name = (isset($_SESSION['auth']['middle_name']))  ? $_SESSION['auth']['middle_name'] : '';
$penalty_last_name   = (isset($_SESSION['auth']['last_name']))    ? $_SESSION['auth']['last_name'] : '';

$penalty_email       = (isset($_POST['penalty_user_email']))      ? $_POST['penalty_user_email'] : $penalty_email;
$penalty_first_name  = (isset($_POST['penalty_user_name']))       ? $_POST['penalty_user_name'] : $penalty_first_name;
$penalty_middle_name = (isset($_POST['penalty_user_fathername'])) ? $_POST['penalty_user_fathername'] : $penalty_middle_name;
$penalty_last_name   = (isset($_POST['penalty_user_lastname']))   ? $_POST['penalty_user_lastname'] : $penalty_last_name;
$child_fio           = (isset($_POST['child_fio']))               ? $_POST['child_fio'] : '';
$child_class         = (isset($_POST['child_class']))             ? $_POST['child_class'] : '';

$this->smarty->assign('penalty_email', $penalty_email);
$this->smarty->assign('penalty_first_name', $penalty_first_name);
$this->smarty->assign('penalty_middle_name', $penalty_middle_name);
$this->smarty->assign('penalty_last_name', $penalty_last_name);
$this->smarty->assign('child_fio', $child_fio);
$this->smarty->assign('child_class', $child_class);

$this->smarty->assign('list', $this->list);
$this->smarty->assign('districts', $this->districts);

$id_district = (isset($_POST['id_district'])) ? ((int)$_POST['id_district']) : $this->districts[0]['id'];

$this->smarty->assign('isRegionStep', '1');

if (isset($_POST['paygairegionform']) && ($_POST['paygairegionform'] == 1)) {

    // validate data:
    
    $kindergarten = (int)$_POST['kindergarten']; // учреждение
    $child_fio = trim($_POST['child_fio']); // ФИО ученика
    $child_class = trim($_POST['child_class']); // группа / класс ученика
    $penalty_user_lastname = trim($_POST['penalty_user_lastname']); // фамилия плательщика
    $penalty_user_name = trim($_POST['penalty_user_name']); // имя плательщика
    $penalty_user_fathername = trim($_POST['penalty_user_fathername']); // отчество плательщика
    $penalty_user_address = trim($_POST['penalty_user_address']); // адрес плательщика
    $penalty_user_email = trim($_POST['penalty_user_email']); // email плательщика
    
    $summ = trim($_POST['summ']);
    $summ = str_replace(',', '.', $summ);
    $summ = (double)$summ;
    $summ = (int)($summ * 100);

    
    $real_child_fio = $Kinders->getChildrenList($child_class, $child_fio);
    if (is_array($real_child_fio)) {
        $child_fio = $real_child_fio['list'][0]['id'];
    }

    if (strlen($child_fio) == 0) {
        $err[] = 'Фамилия ученика не указана';
    }
    if (strlen($child_class) == 0) {
        $err[] = 'Укажите группу / класс ученика';
    }
    if ($summ <= 0) {
        $err[] = 'Сумма платежа не указана';
    }

    if (strlen($penalty_user_lastname) == 0) {
        $err[] = 'Фамилия плательщика не указана';
    }
    if (strlen($penalty_user_name) == 0) {
        $err[] = 'Имя плательщика не указано';
    }
    if (strlen($penalty_user_address) == 0) {
        $err[] = 'Адрес плательщика не указано';
    }
    if (!filter_var($penalty_user_email, FILTER_VALIDATE_EMAIL)) {
        $err[] = 'E-mail плательщика некорректный';
    }
    
    $kindergarten_ok = false;
    foreach ($this->list as $item) {
        if ($item['R101'] == $kindergarten) {
            $kindergarten_item = $item;
            $kindergarten_ok = true;
        }
    }

    if (!$kindergarten_ok) {
        $err[] = 'Учреждение не выбрано';
    } else {
        $this->smarty->assign('R101', $kindergarten);
        $id_district = (int)$kindergarten['ID_DISTRICT'];
    }

    if (count($err) == 0) {
        $this->smarty->assign('isRegionStep', '0');
        $this->smarty->assign('isDetailsStep', '1');

        $fio = "$penalty_user_lastname $penalty_user_name $penalty_user_fathername";

        $user_id = UPC::get_site_user_id($penalty_user_email);

        if ($user_id === false) {
            $user_id = UPC::reg_user($penalty_user_email, $penalty_user_name, $penalty_user_lastname, $penalty_user_fathername);
        }

        $record = $Kinders->pppCreatePayment(
            $error_str,
            $kindergarten_item['IDAREA'],
            $kindergarten_item['FIRME'],
            $summ,
            $user_id,
            $fio,
            $penalty_user_address,
            $kindergarten_item['R101'],
            $child_class,
            $child_fio
        );

        if ($record == false) {
            $err[] = $error_str;
        } else {
            $_SESSION['kinders_last_payment_id'] = $record['id'];
        }
        
        $record['date'] = date("d.m.Y", $record['timestamp']);
        
        if ($record['processing'] == 'tas') {
            $TasLink = new TasLink('budget');
            $tas_session_id = $TasLink->initSession($record['id']);
            $TasLink->makePayment($record['summ_plat'] / 100, $record['summ_komis'] / 100);
        }

        $record['fio'] = htmlspecialchars($fio);
        $record['register'] = htmlspecialchars($penalty_user_address);

        // суммы в копейках, переводим в гривны:
        $record['summ_plat'] = number_format($record['summ_plat'] / 100, 1);
        $record['summ_komis'] = number_format($record['summ_komis'] / 100, 1);
        $record['summ_total'] = number_format($record['summ_total'] / 100, 1);

        $record['summ_plat'] .= (substr($record['summ_plat'], strlen($record['summ_plat']) - 2) == '.0') ? '0' : '';
        $record['summ_komis'] .= (substr($record['summ_komis'], strlen($record['summ_komis']) - 2) == '.0') ? '0' : '';
        $record['summ_total'] .= (substr($record['summ_total'], strlen($record['summ_total']) - 2) == '.0') ? '0' : '';

        $this->smarty->assign('record', $record);
        $_SESSION['kindergartens']['id'] = $record['id'];
    }
    
    if (count($err) > 0) {
        $this->smarty->assign('error_msg', $err[0]);
        $this->smarty->assign('isRegionStep', '1');
    }
}

$this->smarty->assign('id_district', $id_district);
$this->smarty->assign('isSuccessStep', 0);
$this->smarty->assign('isErrorStep', 0);
$this->smarty->assign('isFrameStep', 0);

if (($_GET['step'] == 'success') || ($_GET['step'] == 'error')) {

    if (!isset($_SESSION['kindergartens'])) {
        $this->smarty->assign('error_msg', 'Просмотр отплаты более недоступен');
    } else {
        $record = PDO_DB::row_by_id(ShoppingCart::TABLE, str_replace('gioc-', '', $_SESSION['instant-payments-kinders']['record_id']));

        if ($record['status'] == 'new') {
            // оплата ещё не завершена
        } elseif($record['status'] == 'success') {
            $this->smarty->assign('isSuccessStep', '1');
        } else {
            $error_msg = UPC::get_upc_error($record['trancode']);
            $this->smarty->assign('error_msg', $error_msg);
        }

    }
} elseif (isset($_POST['get_last_step'])) {
    $id = $_SESSION['instant-payments-dai']['kinders_last_payment_id'];
    $payment = $record = PDO_DB::row_by_id(ShoppingCart::TABLE, str_replace('gioc-', '', $id));
    if ($payment) {
        $payment['processing_data'] = json_decode($payment['processing_data']);
        $this->smarty->assign('iframe_src', TasLink::IFRAME_SRC . $payment['processing_data']->first->oid);
        $_SESSION['instant-payments-kinders']['step'] = 'frame';
    }
}






try {

    $tmp_keys = [
        'region'                  => 'Область',
        'postanova_series'        => 'Серія постанови',
        'postanova_number'        => 'Номер постанови',
        'protocol_date'           => 'Дата постанови',
        'protocol_summ'           => 'Сума штрафу',
        'penalty_user_lastname'   => 'Прізвище платника',
        'penalty_user_name'       => 'Ім’я платника',
        'penalty_user_fathername' => 'По-батькові платника',
        'penalty_user_address'    => 'Адреса платника',
        'penalty_user_email'      => 'E-Mail',
    ];

    foreach ($tmp_keys as $key => $title) {
        $$key = trim(stripslashes($_POST[$key]));
        $_SESSION['instant-payments-kinders']['columns'][$key] = $$key;
    }
    
    foreach ($tmp_keys as $key => $title) {
        if (empty($$key) && !in_array($key, ['penalty_user_fathername', 'protocol_date'])) {
            $err = str_ireplace('{FIELD}', $title, ERROR_FIELD_EMPTY_ERROR_MSG);
            throw new Exception($err);
        }
    }

    $protocol_summ = str_replace(',', '.', $protocol_summ);
    $protocol_summ = (double)$protocol_summ;
    $protocol_summ = (int)($protocol_summ * 100);
    if ($protocol_summ <= 0) {
        throw new Exception(ERROR_INVALID_ZERO_PAYMENT);
    }

    if (!filter_var($penalty_user_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception(ERROR_INCORRECT_EMAIL_ERROR_MSG);
    }
    
    $region_ok = false;
    foreach ($regions as $item) {
        if ($item['ID_AREA'] == $region) {
            $region_ok = true;
        }
    }

    if (!$region_ok) {
        throw new Exception('Область не вказана');
    }

    $_SESSION['instant-payments-kinders']['step'] = 'details';

    if (!Authorization::isLogin()) {
        $user = User::getUserByEmail($_SESSION['instant-payments-kinders']['columns']['penalty_user_email']);

        if ($user !== null) {
            $user_id = $user['id'];
        } else {
            $user_id = User::registerFromPayment(
                $_SESSION['instant-payments-kinders']['columns']['penalty_user_email'],
                $_SESSION['instant-payments-kinders']['columns']['penalty_user_lastname'],
                $_SESSION['instant-payments-kinders']['columns']['penalty_user_name'],
                $_SESSION['instant-payments-kinders']['columns']['penalty_user_fathername']
            );
        }

    } else {
        $user_id = Authorization::getLoggedUserId();
    }

    $fio = "$penalty_user_lastname $penalty_user_name $penalty_user_fathername";
    $record = $Gai->set_request_to_ppp($error_str, $region, $protocol_summ, $user_id, $fio, $penalty_user_address, '', '', '', '', $postanova_series, $postanova_number, '', $protocol_date);
    
    if ($record == false) {
        $err[] = $error_str;
    } else {
        $_SESSION['instant-payments-kinders']['dai_last_payment_id'] = $record['id'];
    }
    
    if ($record['processing'] == 'tas') {
        $TasLink = new TasLink('budget');
        $tas_session_id = $TasLink->initSession($record['id']);
        $TasLink->makePayment($record['summ_plat'], $record['summ_komis']);
    }

    // суммы в копейках, переводим в гривны:
    $record['summ_plat'] = number_format($record['summ_plat'] / 100, 1);
    $record['summ_komis'] = number_format($record['summ_komis'] / 100, 1);
    $record['summ_total'] = number_format($record['summ_total'] / 100, 1);

    $record['summ_plat'] .= (substr($record['summ_plat'], strlen($record['summ_plat']) - 2) == '.0') ? '0' : '';
    $record['summ_komis'] .= (substr($record['summ_komis'], strlen($record['summ_komis']) - 2) == '.0') ? '0' : '';
    $record['summ_total'] .= (substr($record['summ_total'], strlen($record['summ_total']) - 2) == '.0') ? '0' : '';

    $_SESSION['instant-payments-kinders']['record_id'] = $record['id'];

} catch (Exception $e) {
    $_SESSION['instant-payments-kinders']['step'] = 'region';
    $_SESSION['instant-payments-kinders']['status'] = false;
    $_SESSION['instant-payments-kinders']['error']['text'] = $e->getMessage();
}

return BASE_URL . '/cabinet/instant-payments/dai/';
