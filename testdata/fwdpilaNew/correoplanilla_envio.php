<?php
include("pila.php");

$rsh=pg_query($pila,"select to_char(now(),'YYYYMMDD') as hoy");
$hoy=pg_fetch_result($rsh, 0, "hoy");

$disco = '/app/nss3/IMG6/img26';

$enviados = 0;
$noenviados = 0;
$correo = '';

@pg_query($pila,"drop table enviotemp");
$sq = pg_query($pila,"
create table enviotemp (
   correo character varying(50),
   cnt integer,
   CONSTRAINT pk_enviotemp_correo PRIMARY KEY (correo)
)
");
$rs_mail= pg_query($pila,"select * from pagounificadomail where sr in ('7516658')");
//$rs_mail = pg_query($pila,"select * from pagounificadomail where fechaenvio='0' and medio=0 and gabinete=6 order by na desc ");
$num     = pg_num_rows($rs_mail);
$per     = date('Ym');
$mes     = substr($per,4,2);
if($mes == '01') $m = 'Enero';
if($mes == '02') $m = 'Febrero';
if($mes == '03') $m = 'Marzo';
if($mes == '04') $m = 'Abril';
if($mes == '05') $m = 'Mayo';
if($mes == '06') $m = 'Junio';
if($mes == '07') $m = 'Julio';
if($mes == '08') $m = 'Agosto';
if($mes == '09') $m = 'Septiembre';
if($mes == '10') $m = 'Octubre';
if($mes == '11') $m = 'Noviembre';
if($mes == '12') $m = 'Diciembre';
$periodo = $m .' del '.$ano;

$mail = new phpmailer();
$mail->Mailer = "smtp";
$mail->Host = "10.11.230.126";//"10.12.200.200";
$mail->SMTPAuth = false;
$mail->Username = "Facturacion";
$mail->Password = "";
$mail->From = "aportesbogota@proteccion.com.co";
$mail->FromName = "Certificado Planila Unica Pensiones y Cesantias Proteccion";
$mail->Timeout=120;

$contador = 0;
for($i=0;$i<$num;$i++){
	$planilla=trim(pg_fetch_result($rs_mail,$i,'planilla'));
	$correo = trim(pg_fetch_result($rs_mail,$i,'mail'));
	$nit = trim(pg_fetch_result($rs_mail,$i,'id_empresa')).'.pdf';
	$na = trim(pg_fetch_result($rs_mail,$i,'na'));
	$archivo=  $planilla.'-'.$nit;
	$path= "/mnt/firmas/".$archivo;
	//No enviar a la misma direccion de correo mas de 10 veces
	$sq = pg_query($pila,"select * from enviotemp where correo = '$correo'");
	if(pg_num_rows($sq)==0){
		pg_query($pila,"insert into enviotemp (correo,cnt) values ('$correo',0)");
		$sw = 0;
	}else{
		$cnt = pg_fetch_result($sq,0,'cnt');
		if($cnt==10){
			$sw = 1;       
		}else{
			$cnt = $cnt + 1;
			pg_query($pila,"update enviotemp set cnt = '$cnt' where correo = '$correo'");
			$sw = 0;
		}
	}
	if($sw == 0){
		$mail->ClearAddresses($correo);
		$mail->ClearReplyTos();
		$mail->ClearCCs();
		$mail->ClearAllRecipients();
		$mail->ClearAttachments();
		if(file_exists($path)){		
			$mail->AddAddress($correo);
			//$mail->AddCC($cc);
			//$cco='Jenry.Henao@ing.com.co';
			//$mail->AddBCC($cco);
			$mail->Subject = "Certificado Planila Unica Pensiones y Cesantias Proteccion";
			$mail->Body = '
			<body topmargin=6 marginheight=6 leftmargin=55 marginwidth=45 rightmargin=35 bottommargin=5>
			<div style=font-size:10pt;font-family:arial><br><br>
			<img border="0" width="949" height="72"
			src="http://www.ing.com.co/pragma/documenta/documentos/4955/informacion/cabezote_ING.jpg">
			<div style=text-align:left;>Bogotá D.C.,  '.$periodo.'</div><br><br><br>
			<div style=text-align:left;>Apreciado Cliente:</div><br><br>
			<div style=text-align:justify;>Adjunto estamos remitiendo certificación de planilla úunica en los términos y condiciones de la Resolución 634 de 2006 del Ministerio de Protección Social.<br>
			Atenderemos sus consultas en cualquiera de nuestros canales o a través de nuestro correo electrónico <a href="mailto:planillaunica@ing.com.co">planillaunica@ing.com.co</a> </div><br>
			<div style=text-align:left;>En Pensiones y Cesantías, <b>el mundo confía en ING.</b></div><br>
			<div style=text-align:left;>Cordialmente,</div><br><br><br><br><br><br>
			<div style=text-align:left;><b>Blanca Cecilia Castañeda</b></div>
			<div style=text-align:left;>Directora de Monetarios</div>
			<div style=text-align:left;>ING Pensiones y Cesantías</div><br></div><br><br><br><br><br>
			<div style=text-align:justify;font-size:0.53em;font-family:arial>
			* Si la información aquí presentada no corresponde a lo esperado por usted, le agradecemos informarnos a <a href="mailto:scliente@ing.com.co"> scliente@ing.com.co</a>,  Linea de Servicio Proteccion 1 8000 52 8000 - Bogota 744 44 64 - Medellin y Cali: 510 90 99 Barranquilla: 319 79 99 - Cartagena: 642 49 99 www.iproteccion.com. La información contenida en este mensaje y en los archivos electrónicos adjuntos es 			confidencial y reservada, conforme a lo previsto en la Constitución y está dirigida exclusivamente a su destinatario, sin la intención de que sea revelada o divulgada a otras personas. <br>
			<img border="0" width="949" height="72" src="http://www.ing.com.co/pragma/documenta/documentos/4955/informacion/LINE.JPG"><br>
			<b>Call Center ING: Linea Gratuita Nacional  1 8000 52 8000 - Bogota 744 44 64 - Medellin y Cali: 510 90 99 Barranquilla: 319 79 99 - Cartagena: 642 49 99	-	Página Web ING: www.proteccion.com</b></div></body>';
			
			$path2="/mnt/firmas/".$archivo;

			$mail->AddAttachment($path2,$nit);
			$mail->IsHTML(true);
			// $mail->UpdateParams($na,'na','logmail','pagounificadomail','pila');
			/*	'na'        => $na valor,
			'field'     => nombre column de valor na
			'column'    => nombre de la coluna donde se guarda el resultado del log
			'table'     => $table,
			'db'        => $db
			*/
			//$mail->UpdateParams($na,'na','logmail','pagounificadomail','pila');
			$exito = $mail->Send();
			if ($mail->ErrorInfo=="SMTP Error: Data not accepted"){
				$exito=true;
			}
			if(!$exito){
				echo "Problemas enviando correo electronico ".$email;
				echo "<br/>".$mail->ErrorInfo;
				$sw = 0;
			}else{
				$path=$disco."/cpu/".$hoy."/00000001/".$planilla."-".$nit;
				$rsmail=pg_query($pila,"update pagounificadomail SET fechaenvio = $hoy,path='$path',path1='' WHERE na='$na'");
				$ruta="/mnt/firmas/".$planilla."-".$nit;
				
				$origen=$ruta;
				$destino="/$disco/cpu/$hoy/00000001/$planilla-$nit";
				$crea="cp $origen $destino";
				system($crea);
				//@unlink($path2);
				$enviados = $enviados + 1;
				sleep(2);
			}
			if($contador<10000){
				$contador++;
				echo $contador;
			}else{
				break;
			}			
		}
		else{
			$rs_update=pg_query($pila,"UPDATE pagounificadomail SET fechaenvio = 0 WHERE na='$na'");
			$noenviados = $noenviados + 1;
		}
	}
}
//Borra la tabla temporal
pg_query($pila,"drop table enviotemp");
$correook = "se enviaron ".$enviados." correos"."\n";
echo $correook;
?>
