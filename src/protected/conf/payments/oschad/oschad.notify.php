<?php
    header("Content-Type: text/plain; charset=UTF-8");

    require_once('oschad.conf.php');

    if(!isset($_POST)) die('$_POST not set');
    if(!isset($_POST['Function'])) die('$_POST[Function] not set');
    if(!isset($_POST['Result'])) die('$_POST[Result] not set');

//if($_POST['Function'] == 'TransResponse'){
/*
  $mess = date('d-m-Y H:i:s').' -- '.USER_REAL_IP."\r\n";
  $mess .= "POST: ".var_export($_POST, true)."\r\n\r\n\r\n\r\n";
  $log_folder = ROOT . "/protected/logs/paysystems/oschad";
  if (!file_exists($log_folder)) {
      mkdir($log_folder, 0755, true);
  }
  $handle = fopen($log_folder . "/oschad.txt", 'a+');
  fwrite($handle, $mess);
  fclose($handle);
*/
  log_paysys('oschad', 'POST', var_export($_POST, true));  
  $_payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $_POST['Order']);
  if ($_payment === null) {
      throw new Exception("Unknow OrderID {$_POST['Order']}");
      exit();
  }

  switch($_POST['Result']){
    case 0:
      $processing_data = (array)(@json_decode($_payment['processing_data']));
      $processing_data['requests'] = (array)$processing_data['requests'];
      $processing_data['dates'][] = $date;
      $processing_data['requests'][$date] = $_POST;
      $to_update = [
          'processing_data' => json_encode($processing_data),
          'send_payment_status_to_reports' => 0
      ];
      $to_update['status'] = 'success';
      break;
    default:
      $to_update['status'] = 'error';
  //}

  PDO_DB::update($to_update, ShoppingCart::TABLE, $_payment['id']);
  ShoppingCart::send_payment_status_to_reports($_payment['id']);
}


?>
