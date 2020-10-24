<?php
require 'exceptionhandler.php';
require 'wsaa.class.php';
require 'wsaaPadron.class.php';
require 'wspadron.class.php';
require 'wsfe.class.php';
require 'factura.class.php';


class ijnWebService {
	private $cuit = '';
	private $wsaa;
	private $wsfe;
	private $path;
	public $factura;
	public $comprobante=null;
	public $errores=null;
	
	public function __construct($MiCuit,$P_o_T){
		switch ($P_o_T) {
			case "p":
			case "P":
				$this->path="TA_Produccion/";
			break;
			case "t":
			case "T":
				$this->path="TA_Test/";
				break;
		}
		$this->cuit=$MiCuit;
		$this->wsaa = new WSAA($this->path."",$P_o_T);
		$this->wsaaTA();
				
		$this->wsfe = new WSFE($this->path."",$this->cuit, $P_o_T);
		if($this->wsfe->openTA())
		{
		//	echo "TA cargado";
		}
		else
		{
			//echo "TA no cargado";
		}
		$this->factura = new ijnFactura();
	}
	
	//Genera TA autorizacion
	private function wsaaTA(){
		if(strtotime($this->wsaa->get_expiration()) < strtotime(date("Y-m-d H:m:i"))) {
					
			if ($this->wsaa->generar_TA()) {
				//echo 'obtenido nuevo TA';
			} else {
				echo 'error al obtener el TA';
			}	
		
	    } else {
	    	//echo 'TA ya generado';
	    }
	}
	////////////////////////
	
	public function crearComprobante(){
		$cmp = $this->wsfe->aut($this->factura->cantidadRegistros,
				$this->factura->puntoVenta,
				$this->factura->codigoComprobante,
				$this->factura->parseFactura());
		
		$cmp1 = new SimpleXMLElement($cmp);
				
		$this->comprobante =
		[
		'Resultado' => $cmp1->FECAESolicitarResult->FeCabResp->Resultado,		
		'CAE' => $cmp1->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CAE,
		'CAEVto' => $cmp1->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CAEFchVto		
	    ];
		
		if ($cmp1->FECAESolicitarResult->Errors->Err->Code!=null){
		$this->errores =
		[
		'ErrorCodigo' => $cmp1->FECAESolicitarResult->Errors->Err->Code,
		'ErrorMensaje' => $cmp1->FECAESolicitarResult->Errors->Err->Msg
		];
		$cmp=null;
		}
		
		if ($cmp1->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Observaciones->Obs->item0->Code!=null){
		$this->errores =
		[
		'ErrorCodigo' => $cmp1->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Observaciones->Obs->item0->Code,
		'ErrorMensaje' => $cmp1->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Observaciones->Obs->item0->Msg
		];
		$cmp=null;
		}
		
		if ($cmp1->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Observaciones->Obs->Code!=null){
			$this->errores =
			[
			'ErrorCodigo' => $cmp1->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Observaciones->Obs->Code,
			'ErrorMensaje' => $cmp1->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Observaciones->Obs->Msg
			];
			$cmp=null;
		}
		
		if ($cmp1->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Observaciones->Obs->item0->Code!=null){
			$this->errores =
			[
			'ErrorCodigo' => $cmp1->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Observaciones->Obs->item0->Code,
			'ErrorMensaje' => $cmp1->FECAESolicitarResult->FeDetResp->FECAEDetResponse->Observaciones->Obs->item0->Msg
			];
			$cmp=null;
		}
		
		return $cmp;		
	}
	
	//Ultimo comprobante
	public function UltimoCmp($ptoV, $tipoCmp){
		$cmp = $this->wsfe->recuperaLastCMP($ptoV, $tipoCmp);
		$nro = new SimpleXMLElement($cmp);
		return $nro->FECompUltimoAutorizadoResult->CbteNro;
	}
	
	public function ConsultarComprobante($ptovta, $tipoCmp, $numero){
		$cmp = $this->wsfe->ConsultarComprobanteEmitido($ptovta, $tipoCmp, $numero);
		$cmp1 = new SimpleXMLElement($cmp);
		$this->comprobante =
		[
		'Resultado' => $cmp1->FECompConsultarResult->ResultGet->Resultado,
		'CAE' => $cmp1->FECompConsultarResult->ResultGet->CodAutorizacion,
		'CAEVto' => $cmp1->FECompConsultarResult->ResultGet->FchVto
		];
		
		if ($cmp1->FECompConsultarResult->Errors->Err->Code!=null){
			$this->errores =
			[
			'ErrorCodigo' => $cmp1->FECompConsultarResult->Errors->Err->Code,
			'ErrorMensaje' => $cmp1->FECompConsultarResult->Errors->Err->Msg
			];
			$cmp=null;
		}
		
		return $cmp;
	}
	
	public function TiposDeComprobantes(){
		$cmp = $this->wsfe->getTiposCbte();
		return $cmp;
	}
	
}


?>