<?php

require_once ('resultXML.php');

class WSFE {

  private $WSDL = "";
  const PROXY_ENABLE = false;
  const LOG_XMLS = false;
  private $WSFEURL = "";  
  private $path = './';
  private $TAs = "";
  private $path1 ="";
  private $cuitC = "";
  
  /*
   * manejo de errores
   */
  public $error = '';
  
  /**
   * Cliente SOAP
   */
  private $client;
  
  /**
   * objeto que va a contener el xml de TA
   */
  //private $TA;
  
  public $resp;
  public $respFac;
  

  
  /**
   * tipo_cbte defije si es factura A = 1 o B = 6
   */
  private $tipo_cbte = '1';
  
  /*
   * Constructor
   */
  public function __construct($pt, $cuitCc, $P_o_T) 
  {
    $this->path = './';
    $this->path1=$pt;
    $this->TAs=$pt."/TA.xml";
    $this->cuitC=$cuitCc;
    // seteos en php
    ini_set("soap.wsdl_cache_enabled", "0");    
    
    switch ($P_o_T) {
    	case "p":
    	case "P":
    		$this->WSFEURL="https://servicios1.afip.gov.ar/wsfev1/service.asmx";
    		$this->WSDL="wsdl/produccion/wsfe.wsdl";
    		break;
    	case "t":
    	case "T":
    		$this->WSFEURL="https://wswhomo.afip.gov.ar/wsfev1/service.asmx";
    		$this->WSDL="wsdl/homo/wsfe.wsdl";
    		break;
    }
    
    // validar archivos necesarios
    if (!file_exists($this->path.$this->WSDL)) $this->error .= " Failed to open ".$this->WSDL;
    
    if(!empty($this->error)) {
      throw new Exception('WSFE class. Faltan archivos necesarios para el funcionamiento');
    }        
    
    $this->client = new SoapClient($this->path.$this->WSDL, array( 
              'soap_version' => SOAP_1_2,
              'location'     => $this->WSFEURL,
              'exceptions'   => 0,
              'trace'        => 1)
    );
    
  }
  
  public function setPaths ($pt){
  	$this->TA=$pt."/TA.xml";
  	$this->path1=$pt;
  }
  
  
  
  private function respXML($resultado){
  	$arrCae=objectToArray($resultado);
  	$xml = new SimpleXMLElement('<root/>');
  	array2XML($xml, $arrCae);
  	$xmlCae=htmlentities($xml->asXML());
  	return htmlspecialchars_decode($xmlCae);
  }
  
  /**
   * Chequea los errores en la operacion, si encuentra algun error falta lanza una exepcion
   * si encuentra un error no fatal, loguea lo que paso en $this->error
   */
  
  
  private function _checkErrors($results, $method)
  {
  	
    if (self::LOG_XMLS) {
      file_put_contents($this->path1."/request-".$method.".xml",$this->client->__getLastRequest());
      file_put_contents($this->path1."/response-".$method.".xml",$this->client->__getLastResponse());
    }
    
    if (is_soap_fault($results)) {
      throw new Exception('WSFE class. FaultString: ' . $results->faultcode.' '.$results->faultstring);
    }
    
    if ($method == 'FEDummy') {return;}
    
    $XXX=$method.'Result';
    if ($results->$XXX->RError->percode != 0) {
        $this->error = "Method=$method errcode=".$results->$XXX->RError->percode." errmsg=".$results->$XXX->RError->perrmsg;
    }
    
    return $results->$XXX->RError->percode != 0 ? true : false;
  }

  /**
   * Abre el archivo de TA xml,
   * si hay algun problema devuelve false
   */
  public function openTA()
  {
    $this->TA = simplexml_load_file($this->path.$this->TAs);
    
    return $this->TA == false ? false : true;
  }
  
  /**
   * Retorna la cantidad maxima de registros de detalle que 
   * puede tener una invocacion al FEAutorizarRequest
   */
  public function recuperaQTY()
  {
    $results = $this->client->FECompTotXRequest(
      array('Auth'=>array('Token' => $this->TA->credentials->token,
                              'Sign' => $this->TA->credentials->sign,
                              'Cuit' => $this->cuitC)));
    
    @$e = $this->_checkErrors($results, 'FECompTotXRequest');
        
    return $e == false ? $results->FECompTotXRequestResult->RegXReq : false;
  }

  
 
 public function Dymmy()
  {
  	
  	$results = $this->client->FEDummy();
  
  	//this->_checkErrors($results, 'FEParamGetTiposDoc');
  	//return $results;
  	//fclose($fh);
  	@$e = $this->_checkErrors($results, 'FEDummy');
  	
  	
  	return $e == false ? $this->respXML($results) : false;
  }
  
     
  public function ConsultarComprobanteEmitido ($ptovta, $tipoCmp, $numero)
  {
  	$results = $this->client->FECompConsultar(
  			array('Auth' =>  array('Token'    => $this->TA->credentials->token,
  					'Sign'     => $this->TA->credentials->sign,
  					'Cuit'     => $this->cuitC),
  					'FeCompConsReq' => array(
  					'PtoVta'   => $ptovta,
  					'CbteTipo' => $tipoCmp,
  					'CbteNro' => $numero)));
  
  	//var_dump($results);
  	@$e = $this->_checkErrors($results, 'FECompConsultar');
  	
  	
  	return $e == false ? $this->respXML($results) : false;
  }
  
  public function ConsultarPtoVenta ()
  {
  	$results = $this->client->FEParamGetPtosVenta(
  			array('Auth' =>  array('Token'    => $this->TA->credentials->token,
  					'Sign'     => $this->TA->credentials->sign,
  					'Cuit'     => $this->cuitC)));
  					
  
  	//var_dump($results);
  	@$e = $this->_checkErrors($results, 'FEParamGetPtosVenta');
  	 
  	 
  	return $e == false ? $this->respXML($results) : false;
  }
  
  
  /*
   * Retorna el ultimo comprobante autorizado para el tipo de comprobante /cuit / punto de venta ingresado.
   */ 
  public function recuperaLastCMP ($ptovta, $tipoCmp)
  {
    $results = $this->client->FECompUltimoAutorizado(
     array('Auth' =>  array('Token'    => $this->TA->credentials->token,
                                'Sign'     => $this->TA->credentials->sign,
                                'Cuit'     => $this->cuitC),
             'PtoVta'   => $ptovta,
             'CbteTipo' => $tipoCmp));

    //var_dump($results);
    @$e = $this->_checkErrors($results, 'FECompUltimoAutorizado');

    return $e == false ? $this->respXML($results) : false;
  }
  
  /*
   * Obtiene los tipos de Documentos
   */
  public function getTiposDoc()
  {
    $params->Auth->Token = $this->TA->credentials->token;
    $params->Auth->Sign = $this->TA->credentials->sign;
    $params->Auth->Cuit = $this->cuitC;
    $results = $this->client->FEParamGetTiposDoc($params);
    @$e = $this->_checkErrors($results, 'FFEParamGetTiposDoc');
    return $e == false ? $this->respXML($results) : false;
    //print_r($results);
    //fclose($fh);
  }

  /*
   * Obtiene los tipos de Comprobante
   */
  public function getTiposCbte()
  {
  	@$params->Auth->Token = $this->TA->credentials->token;
    $params->Auth->Sign = $this->TA->credentials->sign;
    $params->Auth->Cuit = $this->cuitC;
    $results = $this->client->FEParamGetTiposCbte($params);
    @$e = $this->_checkErrors($results, 'FEParamGetTiposCbte');
    return $e == false ? $this->respXML($results) : false;
   
  }

  /*
   * Obtiene los tipos de Concepto
   */
  public function getTiposConcepto()
  {
    @$params->Auth->Token = $this->TA->credentials->token;
    $params->Auth->Sign = $this->TA->credentials->sign;
    $params->Auth->Cuit = $this->cuitC;
    $results = $this->client->FEParamGetTiposConcepto($params);
    @$e = $this->_checkErrors($results, 'FEParamGetTiposConcepto');
    return $e == false ? $this->respXML($results) : false;
    
  }
  
  /*
   * Obtiene los tipos de IVA
   */
  public function getTiposIva()
  {
    @$params->Auth->Token = $this->TA->credentials->token;
    $params->Auth->Sign = $this->TA->credentials->sign;
    $params->Auth->Cuit = $this->cuitC;
    $results = $this->client->FEParamGetTiposIva($params);
    @$e = $this->_checkErrors($results, 'FEParamGetTiposIva');
    return $e == false ? $this->respXML($results) : false;
  }
  
  /*
   * Obtiene los tipos de Monedas
   */
  public function getTiposMonedas()
  {
    @$params->Auth->Token = $this->TA->credentials->token;
    $params->Auth->Sign = $this->TA->credentials->sign;
    $params->Auth->Cuit = $this->cuitC;
    $results = $this->client->FEParamGetTiposMonedas($params);
    @$e = $this->_checkErrors($results, 'FFEParamGetTiposMonedas');
    return $e == false ? $this->respXML($results) : false;
  }
  /*
   * Obtiene los tipos de Tributos
   */
  public function getTiposTributos()
  {
    @$params->Auth->Token = $this->TA->credentials->token;
    $params->Auth->Sign = $this->TA->credentials->sign;
    $params->Auth->Cuit = $this->cuitC;
    $results = $this->client->FEParamGetTiposTributos($params);
    @$e = $this->_checkErrors($results, 'FEParamGetTiposTributos');
    return $e == false ? $this->respXML($results) : false;
  }

  /**
   * Setea el tipo de comprobante
   * A = 1
   * B = 6
   */
  public function setTipoCbte($tipo) 
  {
    switch($tipo) {
      case 'a': case 'A': case '1':
        $this->tipo_cbte = 1;
      break;
      
      case 'b': case 'B': case 'c': case 'C': case '6':
        $this->tipo_cbte = 6;
      break;
      
      default:
        return false;
    }

    return true;
  }

  // Dado un lote de comprobantes retorna el mismo autorizado con el CAE otorgado.
  public function aut($cantReg, $PtoVta, $cbteTipo, $comprobante)
  { 
  	$results = $this->client->FECAESolicitar(
  	 ['Auth' => [
  	 		'Token' => $this->TA->credentials->token,
  	 		'Sign'  => $this->TA->credentials->sign,
  	 		'Cuit'  => $this->cuitC
  			],
  			'FeCAEReq' => [
  			'FeCabReq' => [ 
  			'CantReg' => $cantReg,
  			'PtoVta' => $PtoVta,
  			'CbteTipo' => $cbteTipo,
  			],
  			'FeDetReq' => $comprobante 
  	        
  	 	 ]
  	     
  	     ]
  	);

  	
    @$e = $this->_checkErrors($results, 'FECAESolicitar');
    
    
    return $e == false ? $this->respXML($results) : false;
    
    
    
  }
  
  

} // class

?>