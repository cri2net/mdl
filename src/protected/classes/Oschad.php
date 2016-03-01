<?php

class Oschad
{
  private $fields = array();
  private $trtype_str = '';
  private $last_mac_str = '';

  private $fields_order = array(
    'preauth' => array('AMOUNT','CURRENCY','ORDER','DESC','MERCH_NAME','MERCH_URL',
                        'MERCHANT','TERMINAL','EMAIL','TRTYPE','COUNTRY','MERCH_GMT',
                      'TIMESTAMP','NONCE','BACKREF'),
    'auth' => array('AMOUNT','CURRENCY','ORDER','DESC','MERCH_NAME','MERCH_URL',
                        'MERCHANT','TERMINAL','EMAIL','TRTYPE','COUNTRY','MERCH_GMT',
                      'TIMESTAMP','NONCE','BACKREF'),
    'revers' => array('ORDER','ORG_AMOUNT','AMOUNT','CURRENCY','RRN','INT_REF','TRTYPE',
                      'TERMINAL','BACKREF','TIMESTAMP','NONCE'),
    'complete' => array('ORDER','AMOUNT','CURRENCY','RRN','INT_REF','TRTYPE',
                      'TERMINAL','BACKREF','TIMESTAMP','NONCE'),
  );

  private $trans_types = array(
    'preauth' => 0,
    'auth' => 1,
    'complete' => 21,
    'revers' => 24
  );

  public function trtype_by_code($trcode){
    foreach($this->trans_types as $key => $trc){
      if($trc == $trcode) return $key;
    }
    return 'unknown';
  }

  public function __construct(){
        date_default_timezone_set('Europe/Kiev');
  }
  public function set_merchant($settings_array){
    $this->fields = $settings_array;
/*
          $this->fields['CURRENCY'] = $CURRENCY;
          $this->fields['MERCH_NAME'] = $MERCH_NAME;
          $this->fields['MERCH_URL'] = $MERCH_URL;
          $this->fields['MERCHANT'] = $MERCHANT;
          $this->fields['TERMINAL'] = $TERMINAL;
          $this->fields['EMAIL'] = $EMAIL;
          $this->fields['COUNTRY'] = $COUNTRY;
          $this->fields['MERCH_GMT'] = $MERCH_GMT;
          $this->fields['BACKREF'] = $BACKREF;
  */
  }

  public function set_order($AMOUNT, $ORDER, $DESC){
          $this->fields['AMOUNT'] = $AMOUNT;
          $this->fields['ORDER'] = $ORDER;
          $this->fields['DESC'] = $DESC;
  }

  public function set_transaction($TRTYPE){
          $this->fields['TRTYPE'] = $this->trans_types[$TRTYPE];
          $this->trtype_str = $TRTYPE;
          $this->fields['NONCE'] = md5(time().'hvji87.}%3@3*6hg');
          $this->fields['TIMESTAMP'] = gmdate('YmdHis');
  }

  public function set_reversal($ORDER, $AMOUNT, $RRN, $INT_REF){
    $this->fields['ORDER'] = $ORDER;
    $this->fields['ORG_AMOUNT'] = $AMOUNT;
    $this->fields['AMOUNT'] = $AMOUNT;
    $this->fields['TRTYPE'] = '24';
    $this->fields['RRN'] = $RRN;
    $this->fields['INT_REF'] = $INT_REF;
    $this->trtype_str = 'revers';
    $this->fields['NONCE'] = md5(time().'hvji87.}%3@3*6hg');
    $this->fields['TIMESTAMP'] = gmdate('YmdHis');
  }

  public function sign($key_hex){
    $fcount = count($this->fields_order[$this->trtype_str]);
    $text = '';
    for($i=0; $i<$fcount; $i++){
      $fname = $this->fields_order[$this->trtype_str][$i];
      $text .= mb_strlen( $this->fields[$fname]) . $this->fields[$fname];
    }
    $key = pack('H*', $key_hex);
    $this->fields['P_SIGN'] = hash_hmac('sha1',$text,$key);
    $this->last_mac_str = $text;
  }

  public function get_html_fields($test_mode=false){
    $ftype = ($test_mode)?'text':'hidden';
    $fcount = count($this->fields_order[$this->trtype_str]);
    $text = '';
    for($i=0; $i<$fcount; $i++){
      $fname = $this->fields_order[$this->trtype_str][$i];
      $text .= '<input type="'.$ftype.'" name="'.$fname.'" value="'.$this->fields[$fname].'">';
    }
    $text .= '<input type="'.$ftype.'" name="P_SIGN" value="'.$this->fields['P_SIGN'].'">';

    if($test_mode){
      $text .= '<input type="'.$ftype.'" value="'.$this->last_mac_str.'">';
      $text .= '<br><input type="submit" value="submit">';
    }
    return $text;
  }

  public function get_fields(){
      return $this->fields;
  }

  public function parse_post($POST){
      $this->fields = $POST;

  }
/*
  public function check_sign($key_hex){
    $fcount = count($this->fields_order[$this->trtype_str]);
    $text = '';
    $trtype_str = $this->trtype_by_code($this->fields['TRTYPE']);
    for($i=0; $i<$fcount; $i++){
      $fname = $this->fields_order[$trtype_str][$i];
      $text .= mb_strlen( $this->fields[$fname]) . $this->fields[$fname];
    }
    $key = pack('H*', $key_hex);
    return ($this->fields['P_SIGN'] == hash_hmac('sha1',$text,$key));
  }
*/
}
