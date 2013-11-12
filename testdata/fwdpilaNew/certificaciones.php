<?php
include("pila.php");
require('pdf/fpdf/fpdf.php');

include('eliminacorreos.php');
$rsh=pg_query($pila,"select to_char(now(),'YYYYMMDD') as hoy");
$hoy=pg_fetch_result($rsh, 0, "hoy");

$disco = '/app/nss3/IMG6/img26';
@mkdir("/$disco/cpu/",0777);
@mkdir("/$disco/cpu/$hoy",0777);
@mkdir("/$disco/cpu/$hoy/00000001/",0777);

error_reporting(E_ALL ^ E_NOTICE);
//error_reporting(E_ALL);
class PDF extends FPDF {
	function PDF($orientation='P',$unit='mm',$format='Letter'){
		//Llama al constructor de la clase padre
		$this->FPDF($orientation,$unit,$format);
		//Iniciación de variables
		$this->B=0;
		$this->I=0;
		$this->U=0;
		$this->HREF='';
	}
	function Header(){
		//Logo
		$this->Image('/srv/www/htdocs/new2/pila/pdf/proteccion.jpg',10,4,33);
	}

	function Footer(){
		$this->SetY(-15);
		$this->SetFillColor(120,120,120);
		$this->SetTextColor(255,255,255);
		$this->SetFont('Courier','B',5.5);
		$this->Cell(200,8,'Linea de Servicio Proteccion 1 8000 52 8000 - Bogota 744 44 64 - Medellin y Cali: 510 90 99 Barranquilla: 319 79 99 - Cartagena: 642 49 99 www.proteccion.com',0,0,'J',1);
	}
}
$rsh=pg_query($pila,"select to_char(now(),'YYYYMMDD') as hoy");
$hoy=pg_fetch_result($rsh, 0, "hoy");

//$sql=pg_query($pila,"SELECT * from radicado where estado_cert='0' order by sr ");
//$sql=pg_query($pila,"SELECT * from radicado where estado_cert='0' and fechapago between 20110701 and 20110723 order by sr ");
$sql=pg_query($pila,"SELECT * from radicado where sr in(1037)");
for($i=0;$i<pg_num_rows($sql);$i++){
	$totalco=0;
	$totalcva=0;
	$totalcve=0;
	$totalt=0;
	$fsptotal1=0;
	$arr=pg_fetch_array($sql,$i);
	$sql2=pg_query($pila,"SET client_encoding = 'LATIN1';SELECT * FROM det_proc WHERE sr='$arr[sr]' ORDER BY linea_det");
	if(pg_num_rows($sql2)==0){ 
		$sql2  =pg_query($pila,"SET client_encoding = 'LATIN1';SELECT * FROM det_dev WHERE sr='$arr[sr]' ORDER BY linea_det");
		$sqlenc=pg_query($pila,"SET client_encoding = 'LATIN1';SELECT razon_social,direccion,cod_ciudad,cod_departamento FROM enc_dev WHERE sr='$arr[sr]'");
	}else{
		$sqlenc=pg_query($pila,"SET client_encoding = 'LATIN1';SELECT razon_social,direccion,cod_ciudad,cod_departamento FROM enc_proc WHERE sr='$arr[sr]'");
	}
	if(pg_num_rows($sqlenc)!=0){
		//echo $i."\n";
		$arrenc = pg_fetch_array($sqlenc);
		//$rsciu  = pg_query($ing,"select ciudad,departamento from ciudades where codmun = '$arrenc[cod_ciudad]' and coddep = '$arrenc[cod_departamento]'");
		//$arrciu = pg_fetch_array($rsciu);

		$pdf=new PDF();
		$pdf->AddPage();
		$pdf->Ln(5);
		$pdf->SetLeftMargin(35);
		$pdf->SettopMargin(20);
		//Colores, ancho de línea y fuente en negrita
		$pdf->SetFillColor(255,0,0);
		$pdf->SetTextColor(255);
		$pdf->SetDrawColor(0,0,0);  //pinta la linea
		$pdf->SetLineWidth(.8);
		$pdf->SetFont('Arial','',17);
		$pdf->Line('10','15','208','15');
		//Restauración de colores y fuentes
		$pdf->SetFillColor(224,235,255);
		$pdf->SetTextColor(0);
		$pdf->SetDrawColor(0,0,0);
		$pdf->SetLineWidth(.3);
		$pdf->ln(33);
		$pdf->SetFont('Arial','',9);
		$pdf->Cell(45,4,'Razon Social:',0,0);
		$pdf->Cell(40,4,$arrenc['razon_social'],0,1);
		$pdf->Cell(45,4,'Dirección:',0,0);
		$pdf->Cell(40,4,$arrenc['direccion'],0,1);
		//$pdf->Cell(45,4,'Ciudad:',0,0);
		//$pdf->Cell(40,4,$arrciu['ciudad'],0,1);
		//$pdf->Cell(45,4,'Departamento:',0,0);
		//$pdf->Cell(40,4,$arrciu['departamento'],0,1);    
		$pdf->SetLeftMargin(10);
		$pdf->ln(10);
		$pdf->SetFont('Arial','',9);
		$pdf->MultiCell(197,4,'En cumplimiento de la Resolución 0634 de 2006 expedida por el Ministerio de la Protección Social en cuanto a la implementación de mecanismos de confirmación a los aportantes de la recepción y conciliación de la información y de los recursos correspondientes, dentro del sistema del formulario único o planilla integrada de liquidación de aportes,',0,'J',0); 
		$pdf->ln(5);
		$totalll=$arr['total_cot']+$arr['fsp']+$arr['fspsub']; 
		$pdf->MultiCell(197,4,'PROTECCIONS, CERTIFICA que ha recibido del aportante '. trim(strtoupper($arrenc['razon_social'])).', la suma de '.number_format($totalll).', el día '.$arr['fechapago'].', a la Hora 12:00, respecto de '.$arr['no_afiliados'].' afiliados, por el período '.$arr['periodo'].', correspondiente al formulario único o planilla integrada de liquidación de aportes No '.$arr['planilla'].'.',0,'J',0);
		$pdf->ln(5);
		$pdf->SetFont('Arial','',7);
		$pdf->Cell(5,3,'',1,0);
		$pdf->Cell(10,3,'Tipo',1,0,'C');
		$pdf->Cell(20,3,'No.',1,0,'C');
		$pdf->Cell(53,3,'Nombres.',1,0,'C');
		$pdf->Cell(16,3,'',1,0);
		$pdf->Cell(13,3,'',1,0);
		$pdf->Cell(16,3,'',1,0);
		$pdf->Cell(42,3,'Aporte',1,0,'C');
		$pdf->Cell(18,3,'',1,1);
		$pdf->Cell(5,3,'',1,0);
		$pdf->Cell(10,3,'identifi',1,0,'C');
		$pdf->Cell(20,3,'identificacion',1,0,'C');
		$pdf->Cell(53,3,'y',1,0,'C');
		$pdf->Cell(16,3,'IBC',1,0,'C');
		$pdf->Cell(13,3,'Tarifa',1,0,'C');
		$pdf->Cell(16,3,'C.O',1,0,'C');
		$pdf->Cell(12,3,'C.V.A',1,0,'C');
		$pdf->Cell(12,3,'C.V.E',1,0,'C');
		$pdf->Cell(18,3,'Cotización',1,0,'C');
		$pdf->Cell(18,3,'FSP',1,1,'C');
		$pdf->Cell(5,3,'',1,0);
		$pdf->Cell(10,3,'cacion',1,0,'C');
		$pdf->Cell(20,3,'',1,0);
		$pdf->Cell(53,3,'Apellidos',1,0,'C');
		$pdf->Cell(16,3,'',1,0);
		$pdf->Cell(13,3,'',1,0);
		$pdf->Cell(16,3,'',1,0);
		$pdf->Cell(12,3,'',1,0);
		$pdf->Cell(12,3,'',1,0);
		$pdf->Cell(18,3,'',1,0);
		$pdf->Cell(18,3,'',1,1);
		$pdf->ln(2);
		for($j=0;$j<pg_num_rows($sql2);$j++){
			$arr2=pg_fetch_array($sql2,$j);
			$k=$j+1;
			$pdf->SetFont('Arial','',6);
			$pdf->Cell(5,4,$k,1,0);
			$pdf->SetFont('Arial','',7);
			$pdf->Cell(10,4,$arr2['tipoid_afiliado'],1,0);
			$pdf->Cell(20,4,$arr2['id_afiliado'],1,0);
			$pdf->Cell(53,4,strtolower($arr2['primer_nombre'].' '.$arr2['segundo_nombre'].' '.$arr2['primer_apellido'].' '.$arr2['segundo_apellido']),1,0);
			$pdf->Cell(16,4,number_format($arr2['ibc']),1,0,'R');
			$tari=$arr2['tarifa']/1000;
			$pdf->Cell(13,4,$tari. '%',1,0,'R');
			$pdf->Cell(16,4,number_format($arr2['cotizacion_obli']),1,0,'R');
			$pdf->Cell(12,4,number_format($arr2['cotizacion_voluntaria_af']),1,0,'R');
			$pdf->Cell(12,4,number_format($arr2['cotizacion_voluntaria_aportante']),1,0,'R');
			$tari1=$arr2['cotizacion_obli'];
			$pdf->Cell(18,4,number_format($tari1),1,0,'R');
			$fsptotal=$arr2['fsp']+$arr2['afsp'];
			$pdf->Cell(18,4,number_format($fsptotal),1,1,'R');
			$totalco=$totalco+$arr2['cotizacion_obli'];
			$totalcva=$totalcva+$arr2['cotizacion_voluntaria_af'];
			$totalcve=$totalcve+$arr2['cotizacion_voluntaria_aportante'];
			$totalt=$totalt+$tari1;
			$fsptotal1=$fsptotal1+$fsptotal;
		}
		$pdf->Cell(117,4,'',1,0);
		$pdf->Cell(16,4,number_format($totalco),1,0,'R');
		$pdf->Cell(12,4,number_format($totalcva),1,0,'R');
		$pdf->Cell(12,4,number_format($totalcve),1,0,'R');
		$pdf->Cell(18,4,number_format($totalt),1,0,'R');
		$pdf->Cell(18,4,number_format($fsptotal1),1,1,'R');
		$pdf->ln(5);
		$pdf->MultiCell(197,4,'Nota: la información que sirve de base a la presente certificación ha sido tomada del formulario único o planilla integrada de liquidación de aportes diligenciada por el aportante en concordancia con el reporte de archivo de salida generado por el operador del sistema',0,'J',0);
		$pdf->SetY(-30);
		$pdf->SetFont('Arial','B',8);
		$pdf->Image('/srv/www/htdocs/new2/pila/pdf/proteccion.jpg',20,235,33);
		$pdf->Cell(10,3,'',0,0);
		$pdf->Cell(10,3,'FRANCISCO CUBILLOS ANGEL',0,1,'L');
		$pdf->Cell(10,3,'',0,0);
		$pdf->Cell(10,3,'Gerente de Operaciones',0,1,'L');
				
		$path = '/app/nss3/IMG6/img26/cpu/'.$hoy.'/00000001/'.$arr['planilla'].'-'.$arr['id_empresa'].'.pdf';
		echo $path;
		$path1=$arr['planilla']."-".$arr['id_empresa'].".pdf";
		//$ruta="/mnt/disco_respaldo/".$arr['planilla']."-".$arr['id_empresa'].".pdf";
		$ruta=$path;
		$pdf->Output($ruta,F);
		
		$rsmail=pg_query($pila,"select mail from pila_correos where id_empresa='$arr[id_empresa]' AND tipoid_empresa='$arr[tipoid_empresa]'");
		$path1=str_replace("/","\\\\",$path);
		if(pg_num_rows($rsmail)!=0){
			$mail=pg_fetch_result($rsmail,0,'mail');
			$mail=trim($mail);
			$rs=pg_query($pila,"INSERT INTO pagounificadomail(mail,id_empresa,planilla,path1,medio,gabinete,sr,fecha)
			VALUES('$mail','$arr[id_empresa]','$arr[planilla]','$ruta',0,6,'$arr[sr]','$arr[fechapago]')");
		}else{
			$rs=pg_query($pila,"INSERT INTO pagounificadomail(id_empresa,planilla,path1,medio,gabinete,sr,fecha)
			VALUES('$arr[id_empresa]','$arr[planilla]','$ruta',1,6,'$arr[sr]','$arr[fechapago]')");
		} 
		$sql1=pg_query($pila,"UPDATE radicado SET estado_cert=1 WHERE sr=$arr[sr]");
	}
	else{
		echo "No hay encabezado\n";
	}
}
?>
