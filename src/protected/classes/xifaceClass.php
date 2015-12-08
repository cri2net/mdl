<?

	class xIfaceClass
	{
		var $version = 1.0;
		var $DB = null;
		var $account = null;
		var $ch = null; // CURL Handler
		var $url = '';
		var $result = '';
		var $resultCode = 0;
		var $queryXML = null;
		var $replyXML = null;
		var $replyXMLData = null;
		var $replyXMLNodeList = null;
		var $cache = true;
		var $certsPath = '';
		
		var $isTest = false;
		
		function xIfaceClass()
		{
			$this->isTest = preg_match('/^[^.]*test[^.]*\..*/i', $_SERVER['SERVER_NAME']);
			$this->url = $this->isTest ? 'https://xiface-test.gioc.kiev.ua' : 'https://xiface.gioc.kiev.ua';
			$this->certsPath = '/usr/local/www/apache22/data/protected/certs/';
			
			$this->ch = curl_init();
			curl_setopt($this->ch, CURLOPT_URL, $this->url . '/xiface');
			curl_setopt($this->ch, CURLOPT_PORT, 443);
			curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($this->ch, CURLOPT_CAINFO, $this->certsPath.'ca.crt');
			curl_setopt($this->ch, CURLOPT_POST, true);
			curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
			
			

		} // function xIfaceClass()

		function Query($iface, $module, $dataArray, $cacheTTL = 604800/*7days*/, $showQueryOnly = false, $certName = 'giocwww')
		{
			$this->replyXML = null;
			$this->replyXMLData = null;
			$this->result = '';
			$this->resultCode = 0;

			$dataXML = $this->createXMLDataNode($dataArray);
			//$this->account->logWrite($dataXML);
			//
			// Prepare Query
			//
			$this->queryXML = new DOMDocument('1.0', 'utf-8');
			$this->queryXML->formatOutput = true;
			$qryXMLRoot = $this->queryXML->appendChild( $this->queryXML->createElement('DataExchangeModule') );
			$qryXMLRoot->setAttribute('Interface', $iface);
			$qryXMLHead = $qryXMLRoot->appendChild( $this->queryXML->createElement('Head') );
			$qryXMLHead->setAttribute('Type', 'Query');
			$qryXMLHead->setAttribute('Query', $module);
			$qryXMLData = $qryXMLRoot->appendChild( $this->queryXML->createElement('Data') );
			$qryXMLData->appendChild( $this->queryXML->importNode($dataXML->firstChild, true) );

			$xmlQuery = $this->queryXML->saveXML();
			//$this->account->logWrite($xmlQuery);
			if( $showQueryOnly ) return $xmlQuery;
			
			$xmlQueryMD5Hash = md5($xmlQuery);
			
			//
			// Check cache
			//
			if( $this->cache && $cacheTTL)
			{
				// clear timed out
				PDO_DB::getPDO()->query(sprintf("
					delete from xifcache where CURRENT_TIMESTAMP()>valid_until
					", Authorization::getLoggedUserId(), $xmlQueryMD5Hash));
				// read
				
				/**
				 * $stm = $pdo->prepare("SELECT * FROM ". self::TABLE ." WHERE city_id=? AND object_id=? LIMIT 1");
			        $stm->execute(array($city_id, $object_id));
			        $flat = $stm->fetch();
				 * */
				$stm = PDO_DB::getPDO()->query(sprintf("
					select created,UNCOMPRESS(xml_reply) from xifcache where user_id=%d and checksumm='%s' and CURRENT_TIMESTAMP()<valid_until
					", Authorization::getLoggedUserId(), $xmlQueryMD5Hash));
				$stm->execute();
				if( $stm->rowCount() > 0 ) list($retXMLCreated, $retXMLText) = $stm->fetchAll();
			}

			$certFile = $this->certsPath.$certName.'.pem';
			if( !file_exists($certFile) )
			{
				$this->result = "Cert file '$certFile' not found";
				return false;
			}

			
					
			//
			// Send Query
			//
			if( !isset($retXMLText) )
			{
				curl_setopt($this->ch, CURLOPT_URL, $this->url . '/xiface');
				curl_setopt($this->ch, CURLOPT_SSLCERT, $certFile);
				curl_setopt($this->ch, CURLOPT_SSLKEY, $certFile);
				curl_setopt($this->ch, CURLOPT_POSTFIELDS, array('xmlstr'=>$xmlQuery));
				$retXMLText = curl_exec($this->ch);
			}

			//file_put_contents('/data/www/account/temp/retXMLText.xml', $retXMLText);
			
			if( curl_errno($this->ch) > 0 ) return false;

			//
			// Load reply
			//
			//file_put_contents('/data/www/account/temp/retXMLText.xml', $retXMLText);
			$this->replyXML = new DOMDocument('1.0', 'utf-8');
			$this->replyXML->loadXML( $retXMLText );
			
			$retXPath = new DOMXPath($this->replyXML);
			$retXMLHead = $retXPath->query('/DataExchangeModule/Head')->item(0);
			$this->replyXMLNodeList = $retXPath->query('/DataExchangeModule/Data/*');
			$this->replyXMLData = $this->replyXMLNodeList->item(0);
		
			preg_match('/^([^\(]+)\((\d+)/', $retXMLHead->getAttribute('Result'), $matches);
			$this->result = $matches[1];
			$this->resultCode = (int)$matches[2];

// 			if( !isset($retXMLCreated) && $cacheTTL )
// 			{
				
// 				PDO_DB::getPDO()->query(sprintf("
// 					insert into xifcache
// 					set 
// 						user_id=%d, iface='%s', module='%s', checksumm='%s',
// 						xml_query=COMPRESS('%s'), xml_reply=COMPRESS('%s'), 
// 						valid_until=FROM_UNIXTIME(UNIX_TIMESTAMP()+%d),
// 						result_code=%d
// 					",
// 					Authorization::getLoggedUserId(),  mysql_real_escape_string($iface),  mysql_real_escape_string($module), $xmlQueryMD5Hash, 
// 					 mysql_real_escape_string($xmlQuery),  mysql_real_escape_string($retXMLText),
// 					$cacheTTL,
// 					$this->resultCode));
// 			}
			//file_put_contents('/usr/local/www/apache22/data/protected/certs/qryXMLText.xml', $retXMLText);
			return true;
			
		} // function xIfaceQuery();

		function getPDF($uri, $certName = 'account')
		{
			$certFile = $this->certsPath.$certName.'.pem';
			if( !file_exists($certFile) )
			{
				$this->account->logWrite("Cert file '$certFile' not found");
				return false;
			}
			curl_setopt($this->ch, CURLOPT_SSLCERT, $certFile);
			curl_setopt($this->ch, CURLOPT_SSLKEY, $certFile);

			curl_setopt($this->ch, CURLOPT_URL, $this->url . $uri);
			$this->pdfData = curl_exec($this->ch);
			
			if( curl_errno($this->ch) > 0 ) return false;
		
			return true;
			
		} // function getPDF()


		function getPaymentNames()
		{
			global $account;
			
			// Get PaymentNames
			$this->Query('MunicipalBilling-1.4', 'GetPaymentNames', 
													 array('QueryParams'=>array()));
			if( $this->resultCode != 200 ) 
			{
				$this->account->logWrite('Error while attempting to GetBill with code '.$this->resultCode);
				exit;
			}
			$resXml = new SimpleXMLElement($this->replyXML->saveXML());
			
			$payNameArray = array();
			foreach($resXml->Data->PaymentsList->Payment as $payNameObj)
			{
				$payNameArray[(integer)$payNameObj->attributes()->Code][(integer)$payNameObj->attributes()->SubCode] = trim($payNameObj->attributes()->LeftName);
			}
			
			return $payNameArray;
			
		} // function getPaymentNames()


		function createXMLDataNode($arr, $dom=null, $domNode=null)
		{
			if( !$dom )
			{
				$dom = new DOMDocument('1.0', 'utf-8');
				$domNode = $dom;
			}
	
			list($nodeName, $nodeData) = each($arr);
	
			if( substr($nodeName,0,1) == '@' && !is_array($nodeData) ) $domNode->setAttribute(substr($nodeName,1), $nodeData);
			elseif( substr($nodeName,0,1) != '@' && !is_array($nodeData) ) $domNode->appendChild( $dom->createElement($nodeName, $nodeData) );
			elseif( substr($nodeName,0,1) != '@' && is_array($nodeData) )
			{
				$ismulti = false;
				if( count($nodeData) > 0 )
				{
					$ismulti = true;
					foreach($nodeData as $n=>$d) if( !is_int($n) ) { $ismulti = false; break; }
				}
				
				if( !$ismulti )
				{
					$thisNode = $domNode->appendChild( $dom->createElement($nodeName) );
					foreach($nodeData as $n=>$d)
					{ 
						$this->createXMLDataNode(array($n=>$d), $dom, $thisNode);
					}
				}
				else
				{
					foreach($nodeData as $n=>$d)
					{ 
						$this->createXMLDataNode(array($nodeName=>$d), $dom, $domNode);
					}
				}
			}
		
			return $dom;
			
		} // createXMLDataNode
		
		function __destruct()
		{
			curl_close($this->ch);
			unset($this->ch);

		} // function __destruct()
		
	} // class xIfaceClass

	/*************************************************************************************************************************
	**
	** EXAMPLES
	**
	$qryXMLDataArray = array(
		'PaymentsList' => array(
		
			'Payment' => array(
					array('@JEK' => '505', '@PACC' => '17981', '@Period' => '200804', '@PayDate' => '20070925', '@Summ' => '1.99',
					'Municipal' => array('@NachSumm' => '1.99', '@DebtSumm' => '0.00'),
					'CountersList' => array(
						'Counter' => array(
							array('@Code' => '2', '@CurrentValue' => '0', '@PreviousValue' => '0', '@UsedValue' => '0', '@Summ' => '0.00'),
							array('@Code' => '4', '@CurrentValue' => '0', '@PreviousValue' => '0', '@UsedValue' => '0', '@Summ' => '0.00'),
							array('@Code' => '5', '@CurrentValue' => '0', '@PreviousValue' => '0', '@UsedValue' => '0', '@Summ' => '0.00'),
							array('@Code' => '7', '@CurrentValue' => '0', '@PreviousValue' => '0', '@UsedValue' => '0', '@Summ' => '0.00')
						)
					),
					'SupplyData' => ''
				),
				array('@JEK' => '505', '@PACC' => '17981', '@Period' => '200804', '@PayDate' => '20070925', '@Summ' => '1.99',
				'Municipal' => array('@NachSumm' => '1.99', '@DebtSumm' => '0.00'),
				'CountersList' => array(
					'Counter' => array(
						array('@Code' => '2', '@CurrentValue' => '0', '@PreviousValue' => '0', '@UsedValue' => '0', '@Summ' => '0.00'),
					  array('@Code' => '4', '@CurrentValue' => '0', '@PreviousValue' => '0', '@UsedValue' => '0', '@Summ' => '0.00'),
					  array('@Code' => '5', '@CurrentValue' => '0', '@PreviousValue' => '0', '@UsedValue' => '0', '@Summ' => '0.00'),
					  array('@Code' => '7', '@CurrentValue' => '0', '@PreviousValue' => '0', '@UsedValue' => '0', '@Summ' => '0.00')
					)
				),
				'SupplyData' => ''
			))
		)
	);
	
	$xif = new xIfaceClass();
	$xif->Query('MunicipalBilling-1.4', 'GetBill', array('QueryParams'=>array('@JEK'=>'505', '@PACC'=>'17981', '@Period'=>'200804')));

	print_r($xif)
	
	*************************************************************************************************************************/
?>