<?php

require 'exceptionhandler.php';
require 'wsaa.class.php';
require 'wsaaP.class.php';
require 'wsaaPadron.class.php';
require 'wspadron.class.php';
require ('wsfe.class.php');
require ('wsfeP.class.php');
require 'factura.class.php';
require('conexion.php');

session_start();
$inactividad = 1800;
// Comprobar si $_SESSION["timeout"] estï¿½ establecida
if(isset($_SESSION["timeout"])){
	// Calcular el tiempo de vida de la sesiï¿½n (TTL = Time To Live)
	$sessionTTL = time() - $_SESSION["timeout"];
	if($sessionTTL > $inactividad){
		header("Location: logout.php");
	}
}
$_SESSION['timeout'] = time();

$v_servicio = $_POST["servicio"];
$v_mensaje = $_POST["mensaje"];
$v_mensaje1 = $_POST["mensaje1"];
$v_mensaje2 = $_POST["mensaje2"];
$v_autorizacion = $_POST["autorizacion"];


if(empty($_POST)){
	print_r("Logueese antes por favor!!!");
}
 else {
 	
 	$cmp = "";
 	
 	$sql = "SELECT Id, email, Password, Cuit FROM users WHERE Id = '$v_autorizacion'";
 	$result=$mysqli->query($sql);
 	$rows = $result->num_rows;
 	//si existe en bd
 	if($rows > 0) {
 		$row = $result->fetch_assoc();
 		
/**********************
 * Ejemplo WSAA
 * ********************/

switch ($row['email']){

case "demoInscripto@demo.com":
 $wsaa = new WSAA("ta_prueba/".""); 

  if(strtotime($wsaa->get_expiration()) < strtotime(date("Y-m-d H:m:i"))) {
	
  if ($wsaa->generar_TA()) {
    //echo 'obtenido nuevo TA';  
  } else {
    echo 'error al obtener el TA';
  }
  } else {
  
  }
  break;
case "demoMonotributo@demo.com":
  	$wsaa = new WSAA("ta_prueba/"."");
  
  	if(strtotime($wsaa->get_expiration()) < strtotime(date("Y-m-d H:m:i"))) {
  
  		if ($wsaa->generar_TA()) {
  			//echo 'obtenido nuevo TA';
  		} else {
  			echo 'error al obtener el TA';
  		}
  	} else {
  
  	}
break;

 case "grassofabian@yahoo.com.ar":
  	$wsaa = new WSAA("ta_prueba/"."");
  
  	if(strtotime($wsaa->get_expiration()) < strtotime(date("Y-m-d H:m:i"))) {
  
  		if ($wsaa->generar_TA()) {
  			//echo 'obtenido nuevo TA';
  		} else {
  			echo 'error al obtener el TA';
  		}
  	} else {
  
  	}
 break;

default:
	//$wsaa = new WSAAp($_SESSION["directorioC"]."",$_SESSION["CuitMio"]);
	$wsaa = new WSAAp("ta/","");
	
	if(strtotime($wsaa->get_expiration()) < strtotime(date("Y-m-d H:m:i"))) {
	
		if ($wsaa->generar_TA()) {
			//echo 'obtenido nuevo TA';
		} else {
			echo 'error al obtener el TA';
		}
	} else {
		
	}
}

/**********************
 * Ejemplo WSAA PADRON
* ********************/

$wsaaPadron = new WSAAPadron("ta/xmlgeneradosPadron","ta/"."");

if(strtotime($wsaaPadron->get_expiration()) < strtotime(date("Y-m-d H:m:i"))) {
	if ($wsaaPadron->generar_TA()) {
		//echo 'obtenido nuevo TA';
	} else {
		echo 'error al obtener el TA';
	}
} else {
	//echo $wsaa->get_expiration();
}
$wspadron = new WS_PADRON("ta/xmlgeneradosPadron","20224335238");

if($wspadron->openTA())
 echo "";
else 
 echo "";
	
/**********************
 * Ejemplo WSFE
 * ********************
 */
//
switch ($row['email']){

case "demoInscripto@demo.com":
  $wsfe = new WSFE("ta_prueba/"."","20224335238");
break;
case "demoMonotributo@demo.com":
	$wsfe = new WSFE("ta_prueba/"."","20224335238");
break;
case "grassofabian@yahoo.com.ar":
	$wsfe = new WSFE("ta_prueba/"."","20224335238");
	break;
default:
	//$wsfe = new WSFEp("ta/"."",$_SESSION['CuitMio']);	
	$wsfe = new WSFEp("ta/"."",$row['Cuit']);
}

$factura = new ijnFactura();

// Carga el archivo TA.xml
if($wsfe->openTA())
	echo "";
else
	echo "";


switch ($v_servicio) {
	case "fecha":
		print_r(date('Ymd'));
		break;
	case "cae":
		$factura_xml = new SimpleXMLElement($v_mensaje);
		$factura->cantidadRegistros=$factura_xml->Factura->CantReg->__toString();		
		$factura->puntoVenta=$factura_xml->Factura->PtoVta->__toString();				
		$factura->codigoComprobante=$factura_xml->Factura->CbteTipo->__toString();
		$factura->codigoConcepto=$factura_xml->Factura->Concepto->__toString();
		$factura->codigoDocumento=$factura_xml->Factura->DocTipo->__toString();				
		$factura->numeroDocumento=$factura_xml->Factura->DocNro->__toString();
		$factura->numeroComprobante=$factura_xml->Factura->CbteDesde->__toString();
		$factura->fecha=$factura_xml->Factura->CbteFch->__toString();
		$factura->importeTotal=$factura_xml->Factura->ImpTotal->__toString();
		$factura->importeNoGravado=$factura_xml->Factura->ImpNoGravado->__toString();
		$factura->importeNeto=$factura_xml->Factura->ImpNeto->__toString();
		$factura->importeExento=$factura_xml->Factura->ImpOpEx->__toString();
		$factura->importeOtrosTributos=$factura_xml->Factura->ImpTrib->__toString();
		$factura->importeIVA=$factura_xml->Factura->ImpIVA->__toString();
		$factura->FchServDesde=$factura_xml->Factura->FchServDesde->__toString();
		$factura->FchServHasta=$factura_xml->Factura->FchServHasta->__toString();
		$factura->FchVtoPago=$factura_xml->Factura->FchVtoPago->__toString();
		$factura->codigoMoneda=$factura_xml->Factura->CodigoMoneda->__toString();
		$factura->cotizacionMoneda=$factura_xml->Factura->MonCotiz->__toString();
		
		
        
		if ($factura_xml->Factura->Iva->AlicIva<>null){
		$contIva=1;
		foreach ($factura_xml->Factura->Iva->AlicIva as $AlicIva){
			$factura->ivacod=$AlicIva->Id->__toString();
			$factura->ivabase=$AlicIva->BaseImp->__toString();
			$factura->ivaimp=$AlicIva->Importe->__toString();
			$factura->agregarIvas($contIva);
			$contIva=$contIva+1;
		}
		}
	
		
		if ($factura_xml->Factura->Tributos->Tributo<>null){
		$contTrib=1;
		foreach ($factura_xml->Factura->Tributos->Tributo as $Tributo){
			$factura->tribId=$Tributo->Id->__toString();
            $factura->tribDesc=$Tributo->Desc->__toString();
            $factura->tribBaseImp=$Tributo->BaseImp->__toString();
            $factura->tribAlic=$Tributo->Alic->__toString();
            $factura->tribImporte=$Tributo->Importe->__toString();
            $factura->agregarTributos($contTrib);
			$contTrib=$contTrib+1;
		}
		}
		if ($factura_xml->Factura->Cabecera->compAsoc<>null){
		   $cod="";
		   if ($factura_xml->Factura->Cabecera->compAsoc->__toString()=="Factura A"){$cod="1";}
		   if ($factura_xml->Factura->Cabecera->compAsoc->__toString()=="Factura M"){$cod="51";}
		   if ($factura_xml->Factura->Cabecera->compAsoc->__toString()=="Factura B"){$cod="6";}
		   if ($factura_xml->Factura->Cabecera->compAsoc->__toString()=="Recibo A"){$cod="4";}
		   if ($factura_xml->Factura->Cabecera->compAsoc->__toString()=="Recibo M"){$cod="54";}
		   if ($factura_xml->Factura->Cabecera->compAsoc->__toString()=="Recibo B"){$cod="9";}
		   if ($factura_xml->Factura->Cabecera->compAsoc->__toString()=="Factura C"){$cod="11";}
		   if ($factura_xml->Factura->Cabecera->compAsoc->__toString()=="Recibo C"){$cod="15";}
		   if ($factura_xml->Factura->Cabecera->compAsoc->__toString()=="Nota de Credito A"){$cod="3";}
		   if ($factura_xml->Factura->Cabecera->compAsoc->__toString()=="Nota de Credito M"){$cod="53";}
		   if ($factura_xml->Factura->Cabecera->compAsoc->__toString()=="Nota de Credito B"){$cod="8";}
		   if ($factura_xml->Factura->Cabecera->compAsoc->__toString()=="Nota de Debito A"){$cod="2";}
		   if ($factura_xml->Factura->Cabecera->compAsoc->__toString()=="Nota de Debito M"){$cod="52";}
		   if ($factura_xml->Factura->Cabecera->compAsoc->__toString()=="Nota de Debito B"){$cod="7";}
		   if ($factura_xml->Factura->Cabecera->compAsoc->__toString()=="Nota de Credito C"){$cod="13";}
		   if ($factura_xml->Factura->Cabecera->compAsoc->__toString()=="Nota de Debito C"){$cod="12";}		
		   $cm=$factura_xml->Factura->Cabecera->compAsoc->__toString();
		   if ($cm!="Remito" and $cm!=""){
		     $factura->compAsocTipo=$cod;
		     $factura->compAsocPtoVta=$factura_xml->Factura->Cabecera->RemSuc->__toString();
		     $factura->compAsocNro=$factura_xml->Factura->Cabecera->RemNro->__toString();
		     $factura->agregarCompAsoc(1);
		   }
		   
		}
		
		$cae = $wsfe->aut($factura->cantidadRegistros,
				$factura->puntoVenta,
				$factura->codigoComprobante,
				$factura->parseFactura());
		print_r($cae);
		
		break;
	case "ultCmp":
		$cmp = $wsfe->recuperaLastCMP($_POST["mensaje"], $_POST["mensaje1"]);
		print_r($cmp);
		break;
	case "consCmp":
		$cmp = $wsfe->ConsultarComprobanteEmitido($_POST["mensaje"], $_POST["mensaje1"], $_POST["mensaje2"]);
		print_r($cmp);
		break;
	case "ptoVenta":		
		$cmp = $wsfe->ConsultarPtoVenta();
		print_r($cmp);
		break;
	case "tpoComp":
			$cmp = $wsfe->getTiposCbte();
			
			print_r($cmp);
	break;
	case "padron":
		$cmp = $wspadron->getContribuyente($v_mensaje);
		$apellido=$wspadron->getDatos($cmp);
		//print_r($cmp);
		print_r($apellido);
		
		break;
	case "test":
		$cmp = $wsfe->Dymmy();
		print_r($cmp);
	break;
}
} else {
	echo "usuario no existe en la base de datos";
}

 }
 
?>