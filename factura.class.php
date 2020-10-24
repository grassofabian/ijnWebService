<?php
class ijnFactura {
	public $cantidadRegistros;
	public $puntoVenta;
	public $codigoComprobante;
	public $codigoConcepto;
	public $codigoDocumento;
	public $numeroDocumento;
	public $numeroComprobante;
	public $fecha;
	public $FchServDesde;
	public $FchServHasta;
	public $FchVtoPago;
	public $importeTotal="0.00";
	public $importeNoGravado="0.00";
	public $importeNeto="0.00";
	public $importeExento="0.00";
	public $importeOtrosTributos="0.00";
	public $importeIVA="0.00";
	public $codigoMoneda;
	public $cotizacionMoneda="1.00";
	
	public $arrayComprobantesAsociados = null;	
	public $arrayOtrosTributos=null;
	public $ivacod;
	public $ivabase;
	public $ivaimp;
	public $ivas;
	public $tributos;
	public $arrtributos;
	public $arraySubtotalesIVA;
	
	public $tribId;
	public $tribDesc;
	public $tribBaseImp="0.00";
	public $tribAlic;
	public $tribImporte="0.00";
	
	public $comproAsoc=null;
	public $arrcomproAsoc;
	public $compAsocTipo;
	public $compAsocPtoVta;
	public $compAsocNro;
	
	
	public function agregarIvas($i){
		$i=$i-1;
		
		
		$this->ivas[$i][0]=$this->ivacod;
		$this->ivas[$i][1]=$this->ivabase;
		$this->ivas[$i][2]=$this->ivaimp;
		
	}
	
	public function agregarTributos($i){
		$i=$i-1;
		
		$this->tributos[$i][0]=$this->tribId;
		$this->tributos[$i][1]=$this->tribDesc;
		$this->tributos[$i][2]=$this->tribBaseImp;
		$this->tributos[$i][3]=$this->tribAlic;
		$this->tributos[$i][4]=$this->tribImporte;
	
	}
	
	public function agregarCompAsoc($i){
		$i=$i-1;
	
		$this->arrcomproAsoc[$i][0]=$this->compAsocTipo;
		$this->arrcomproAsoc[$i][1]=$this->compAsocPtoVta;
		$this->arrcomproAsoc[$i][2]=$this->compAsocNro;
	
	}
	
	
	
	public function parseFactura(){
		$iiva=null;
		$tributos=null;
		$comproAsoc=null;
		
		if (isset($this->ivas)) {
			for ($x = 0; $x < count($this->ivas); $x++){
				
				$arraySubtotalesIVA[$x] = [
				'Id' =>  $this->ivas[$x][0],
				'BaseImp' =>  $this->ivas[$x][1],
				'Importe' =>  $this->ivas[$x][2],
				];
			}
			$iiva = $arraySubtotalesIVA;
		    }
		
		    if (isset($this->tributos)) {
		    	for ($x = 0; $x < count($this->tributos); $x++){
		    	
		    		$arraySubtotalestrib[$x] = [
		    		'Id' =>  $this->tributos[$x][0],
		    		'Desc' =>  $this->tributos[$x][1],
		    		'BaseImp' =>  $this->tributos[$x][2],
		    		'Alic' =>  $this->tributos[$x][3],
		    		'Importe' =>  $this->tributos[$x][4],
		    		];
		    	}
		    		    	
		     $arrtributos=$arraySubtotalestrib;
		    }    
		
		    if (isset($this->arrcomproAsoc)) {
		    for ($x = 0; $x < count($this->arrcomproAsoc); $x++){
		    	
		    		$arraycomproAsoc[$x] = [
		    		'Tipo' =>  $this->arrcomproAsoc[$x][0],
		    		'PtoVta' =>  $this->arrcomproAsoc[$x][1],
		    		'Nro' =>  $this->arrcomproAsoc[$x][2],
		    		];
		    	}
		    	$comproAsoc=$arraycomproAsoc;
		    }
		
		if ($this->tributos<>null){
		$comprobante = [
		[
		'Concepto' => $this->codigoConcepto,
		'DocTipo' => $this->codigoDocumento,
		'DocNro' => $this->numeroDocumento+0,
		'CbteDesde' => $this->numeroComprobante, // todo: depende de la cantidad de fact enviadas
		'CbteHasta' => $this->numeroComprobante,
		'CbteFch' => $this->fecha,
		'ImpTotal' => $this->importeTotal,
		'ImpTotConc' => $this->importeNoGravado,
		'ImpNeto' => $this->importeNeto,
		'ImpOpEx' => $this->importeExento,
		'ImpTrib' => $this->importeOtrosTributos,
		'ImpIVA' => $this->importeIVA,
		'FchServDesde' => $this->FchServDesde,
		'FchServHasta' => $this->FchServHasta,
		'FchVtoPago' => $this->FchVtoPago,
		'MonId' => $this->codigoMoneda,
		'MonCotiz' => $this->cotizacionMoneda,
		'CbtesAsoc' => $comproAsoc,
		'Iva' => $iiva,
		'Tributos' => $arrtributos
		
		
        ]
		
		];
		} else {
			$comprobante = [
			[
			'Concepto' => $this->codigoConcepto,
			'DocTipo' => $this->codigoDocumento,
			'DocNro' => $this->numeroDocumento+0,
			'CbteDesde' => $this->numeroComprobante, // todo: depende de la cantidad de fact enviadas
			'CbteHasta' => $this->numeroComprobante,
			'CbteFch' => $this->fecha,
			'ImpTotal' => $this->importeTotal,
			'ImpTotConc' => $this->importeNoGravado,
			'ImpNeto' => $this->importeNeto,
			'ImpOpEx' => $this->importeExento,
			'ImpTrib' => $this->importeOtrosTributos,
			'ImpIVA' => $this->importeIVA,
			'FchServDesde' => $this->FchServDesde,
		    'FchServHasta' => $this->FchServHasta,
	     	'FchVtoPago' => $this->FchVtoPago,
			'MonId' => $this->codigoMoneda,
			'MonCotiz' => $this->cotizacionMoneda,
			'CbtesAsoc' => $comproAsoc,
			'Iva' => $iiva
			
			
			]
			
			];
		}
		$comprobante = json_decode(json_encode($comprobante));
		//print_r($comprobante);
		 
		
		 
		
		 
		
		return $comprobante;
	}
	
	
}