<?php

require 'resultPXML.php';

class WS_PADRON {

  const CUIT = "27066479701";                 # CUIT del emisor de las facturas
  //const TA =    "xmlgeneradosPadron/TA.xml";        # Archivo con el Token y Sign
  const WSDL = "wsdl/homo/ws_sr_padron_a5.wsdl";                   # The WSDL corresponding to WSFE
  const PROXY_ENABLE = false;
  const LOG_XMLS = false;                     # For debugging purposes
  //const WSFEURL = "https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA5"; // testing
  const WSFEURL = "https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA5";
  
  //const WSFEURL = "?????????????????"; // produccion  

  
  /*
   * el path relativo, terminado en /
   */
  private $path = './';
  private $path1 ="";
  private $TAs = "";
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
  private $TA;
  
  public $resp;
  public $respFac;
  

  
  /**
   * tipo_cbte defije si es factura A = 1 o B = 6
   */
  private $tipo_cbte = '1';
  
  /*
   * Constructor
   */
  public function __construct($pt, $cuitCc) 
  {
    $this->path = './';
    $this->path1=$pt;
    $this->TAs=$pt."/TA.xml";
    $this->cuitC=$cuitCc;
    // seteos en php
    ini_set("soap.wsdl_cache_enabled", "0");    
    
    // validar archivos necesarios
    if (!file_exists($this->path.self::WSDL)) $this->error .= " Failed to open ".self::WSDL;
    
    if(!empty($this->error)) {
      throw new Exception('WSFE class. Faltan archivos necesarios para el funcionamiento');
    }        
    
    $this->client = new SoapClient($this->path.self::WSDL, array( 
              'soap_version' => SOAP_1_1,
              'location'     => self::WSFEURL,
              'exceptions'   => 0,
              'trace'        => 1)
    );
    
  }
  
  
  
  
  private function respXML($resultado){
  	$arrCae=objectToArrayp($resultado);
  	$xml = new SimpleXMLElement('<root/>');
  	array2XMLp($xml, $arrCae);
  	$xmlCae=htmlentities($xml->asXML());
  	return $xmlCae;
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
      throw new Exception('WSPADRON class. FaultString: ' . $results->faultcode.' '.$results->faultstring);
    }
    
    if ($method == 'dummy') {return;}
    
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
  
   
 
 public function Dummy()
  {
  	$results = $this->client->dummy();
  	@$e = $this->_checkErrors($results, 'dummy');
  	return $e == false ? $this->respXML($results) : false;
  }
  
   
  
  /*
   * Retorna el ultimo comprobante autorizado para el tipo de comprobante /cuit / punto de venta ingresado.
   */ 
  public function getContribuyente ($id)
  {
  
    $results = $this->client->getPersona(
     array(
    		'token' 			=> $this->TA->credentials->token,
    		'sign' 				=> $this->TA->credentials->sign,
    		'cuitRepresentada' 	=> $this->cuitC+0,
    		'idPersona' 		=> $id+0
    ));

    //var_dump($results);
    @$e = $this->_checkErrors($results, 'getPersona');

    //return $e == false ? $results->getPersonaResponse->CbteNro : false;
    return $e == false ? $this->respXML($results) : false;
  }
  
  public function getDatos ($xmlres)
  {
  	$myVar =htmlspecialchars_decode($xmlres); 
  	
  	$file = fopen($this->path1."padron.txt", "w");
  	fwrite($file, $myVar);
  	fclose($file);
  	
  	$doc = new DOMDocument();
  	$doc->loadXML($myVar);
  	$datos = $doc->getElementsByTagName("datosGenerales");
  	
  	foreach($datos as $dato) {
  		if($dato->getElementsByTagName("tipoPersona")->item(0)->nodeValue=="FISICA"){
  			$apellido=$dato->getElementsByTagName("apellido")->item(0)->nodeValue." ".$dato->getElementsByTagName("nombre")->item(0)->nodeValue;
  			$domicilio=$dato->getElementsByTagName("direccion")->item(0)->nodeValue;
  			$provincia=$dato->getElementsByTagName("descripcionProvincia")->item(0)->nodeValue;
  		}
  		if($dato->getElementsByTagName("tipoPersona")->item(0)->nodeValue=="JURIDICA"){
  			$apellido=$dato->getElementsByTagName("razonSocial")->item(0)->nodeValue." ".$dato->getElementsByTagName("nombre")->item(0)->nodeValue;
  			$domicilio=$dato->getElementsByTagName("direccion")->item(0)->nodeValue;
  			$provincia=$dato->getElementsByTagName("descripcionProvincia")->item(0)->nodeValue;
  		}
  		
  	}
  	
  	$datos = $doc->getElementsByTagName("item1");
  	foreach($datos as $dato) {
  		if($dato->getElementsByTagName("idImpuesto")->item(0)->nodeValue=="30"){
  			$iva="Responsable Inscripto";
  		}
  		if($dato->getElementsByTagName("idImpuesto")->item(0)->nodeValue=="32"){
  			$iva="EXENTO";
  		}	
  	}
  	
  	$datos = $doc->getElementsByTagName("categoriaMonotributo");
  	foreach($datos as $dato) {
  		if($dato->getElementsByTagName("idImpuesto")->item(0)->nodeValue=="20"){
  			$iva="Monotributo";
  		}
  	}
  	
  	$datos = $doc->getElementsByTagName("errorConstancia");
  	foreach($datos as $dato) {
  		$apellido=$dato->getElementsByTagName("error")->item(0)->nodeValue;
  		
  	}
  	  	
  	return "<?xml version='1.0' encoding='UTF-8'?><root><datos><nombre>".$apellido."</nombre><domicilio>".$domicilio."</domicilio><provincia>".$provincia."</provincia><iva>".$iva."</iva></datos></root>";
  }
 
  

} // class

?>
