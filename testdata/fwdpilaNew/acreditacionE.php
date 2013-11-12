<?php
error_reporting(E_ALL ^ E_NOTICE);
include("pila.php");
$hoy = date('Ymd');
$rezagos=0;
echo "<h4>INICIANDO VALIDACION ARCHIVOS PILA ... ".date('h:i:s')." </h4> \n\n";
//actualizaciones generales se quitan del For **
//SANTI -OK $enc =pg_query($pila,"update enc_proc set tipo_aportante='1' where estado=0 and tipo_aportante=''"); 
//SANTI -OK  $enc =pg_query($pila,"update enc_proc set tipo_planilla='E'  where estado=0 and tipo_planilla=''");	
$enc =pg_query($pila,"update enc_proc set clase_aportante='B' where estado=0 and clase_aportante=''");
//SANTI -OK  $enc =pg_query($pila,"update enc_proc set presentacion='U' where estado=0 and presentacion not in ('U','C','D')");
//SANTI -OK  $enc =pg_query($pila,"update enc_proc set tipo_planilla='E' where estado=0 and tipo_planilla in ('T','F','P')");

echo "\n COMIENZA ACREDITACION \n".date('h:i:s')."<br>";
//$sql=pg_query($pila,"SELECT * FROM enc_proc WHERE estado=0 and tipo_planilla in ('E','I','M','N','Y') ORDER BY fechapago,sr");
$sql=pg_query($pila,"SELECT * FROM enc_proc WHERE estado=0 ORDER BY fechapago,sr");
$cont = 1;
$canregs = pg_num_rows($sql);
for($i=0;$i<$canregs;$i++){
	ini_set("max_execution_time","25");
	flush();
	$arr=pg_fetch_array($sql,$i);
	echo $cont." de $canregs ".$arr['sr']." sr, ".$arr['planilla']." planilla \n"."<br>";
	$cont++;
	foreach ($arr as $a1=>$v1){
		$arr[$a1] = utf8_encode($v1);
	}
	/* 
	SANTI OK
	$telefono = "";
	$telefono = $arr['telefono'];
	if($telefono==""){
		$telefono=0;
	}
	$fax = "";
	$fax = $arr['fax'];
	if($fax==""){
		$fax=0;
	}
	$direccion = "";
	$direccion = $arr['direccion'];
	if($direccion==""){
		$direccion="NO REPORTA";
	}
	*/

	//actualiza datos de estructura vieja
	/*
	SANTI OK
	$mod=substr($arr['nro_registro'],0,1);
	$detalle=pg_query($pila,"select count(*) as cantidad from det_proc where sr='$arr[sr]'");
	$cant=pg_fetch_result($detalle, 0, "cantidad");	
	$enc =pg_query($pila,"update enc_proc set registros_tipo2 ='$cant' where sr='$arr[sr]'");
	*/
	
	/* de aqui se quitaron las actualizaciones -- el 'where sr='$arr[sr]' and' sin la variable estado */
	
	/*
	SANTI OK
	//valida el tipo de cotizante y lo cambia a voluntario  
	if($arr['registros_tipo2']==1){ //si es un solo trabajador
		$detalle=pg_query($pila,"select id_afiliado,cotizacion_voluntaria_aportante,cotizacion_voluntaria_af,ing,ret 
			from det_proc where sr='$arr[sr]'");
		if((pg_num_rows($detalle)==1)&&(pg_fetch_result($detalle,0,'id_afiliado')==$arr['id_empresa'])){
			$enc=pg_query($pila,"update enc_proc set tipo_aportante='2',tipo_planilla='I',clase_aportante='I' where sr='$arr[sr]'");
			$detalle1=pg_query($pila,"update det_proc set tipo_cotizante='3' where sr='$arr[sr]'");
			if (pg_fetch_result($detalle,0,'cotizacion_voluntaria_aportante')>0){
				$sumacot=pg_fetch_result($detalle,0,'cotizacion_voluntaria_aportante')+pg_fetch_result($detalle,0,'cotizacion_voluntaria_af');
				$aporteaf=pg_fetch_result($detalle,0,'cotizacion_voluntaria_af');
				$detalle1=pg_query($pila,"update det_proc set cotizacion_voluntaria_aportante=0,cotizacion_voluntaria_af=$aporteaf+$sumacot where sr='$arr[sr]'");
			}
			if (pg_fetch_result($detalle,0,'ing')=='X')
				 $detalle1=pg_query($pila,"update det_proc set ing='' where sr='$arr[sr]'");
            if (pg_fetch_result($detalle,0,'ret')=='X')
				 $detalle1=pg_query($pila,"update det_proc set ret='' where sr='$arr[sr]'");						 	 
		}
	}
	*/

	$marca=0;
	//--------------------------------Validacion encabezado Totales--------------------------------
//	$sal=pg_query($pila,"SELECT * FROM salario_minimo");
	
	/*
	OK SANTI

	if($arr['tipoid_empresa']=='NI'){
		$tipoe='NIT';
	}elseif($arr['tipoid_empresa']=='CC'){
		$tipoe='CC';
	} elseif($arr['tipoid_empresa']=='CE'){
		$tipoe='CE';
	} elseif($arr['tipoid_empresa']=='TI'){
		$tipoe='TI';
	} else{
		$tipoe='PA';
	}
	if ($arr['tipo_planilla']=='R'){
		$rs2=pg_query($pila,"INSERT INTO rezago (planilla,linea_det,tipoid_empresa,id_empresa,tipoid_afiliado,id_afiliado,fechapago,codigo_rezago,periodo,
			usuario,operador,sr) VALUES($arr[planilla],0,'$arr[tipoid_empresa]','$arr[id_empresa]','$arr[tipoid_empresa]','$arr[id_empresa]',
			$arr[fechapago],10,$arr[periodo],'sistema','$arr[operador]','$arr[sr]')");
		$marca=$marca+1;
	}
	if ($arr['tipo_planilla']=='L'){
		$rs2=pg_query($pila,"INSERT INTO rezago (planilla,linea_det,tipoid_empresa,id_empresa,tipoid_afiliado,id_afiliado,fechapago,codigo_rezago,periodo,
			usuario,operador,sr) VALUES($arr[planilla],0,'$arr[tipoid_empresa]','$arr[id_empresa]','$arr[tipoid_empresa]','$arr[id_empresa]',
			$arr[fechapago],11,$arr[periodo],'sistema','$arr[operador]','$arr[sr]')");
		$marca=$marca+1;
	}

	
	if ($arr['tipo_aportante']!='Y'){
		$sql1=pg_query($pila,"SELECT * FROM tblempleadoresliq where identificacion='$arr[id_empresa]' AND tipoidentif='$tipoe'");
		if(pg_num_rows($sql1)==0){
			$rs2=pg_query($pila,"INSERT INTO rezago(planilla,linea_det,tipoid_empresa,id_empresa,tipoid_afiliado,id_afiliado,fechapago,codigo_rezago,periodo,usuario,operador,sr) 
			VALUES($arr[planilla],0,'$arr[tipoid_empresa]','$arr[id_empresa]','$arr[tipoid_empresa]','$arr[id_empresa]',$arr[fechapago],1,$arr[periodo],'sistema','$arr[operador]','$arr[sr]')");
			$marca=$marca+1;
		}else{
			$arme=pg_fetch_array($sql1);
			//esta en espera si se va  acatualizar la tabla de tblempleadoresliq, falta actualizar el update
			//$rs3=pg_query($pila,"update tblempleadoresliq set razonsocial='$arr[razon_social]',direccion='$arr[direccion]', codigo_ciudad='$arr[cod_ciudad]',codigo_departamento='$arr[cod_departamento]',telefono='$telefono',clase_aportante='$arr[clase_aportante]',forma_presentacion='$arr[presentacion]' where id_empresa='$arr[id_empresa]' AND tipoid_empresa='$tipoe'");
		}
	}else{
		//vaidar con allan la tabla de empresas de terceros
		
	}

	*/

	//planilla errada
	/* OK SANTI
	if(($arr['tipoid_empresa']=='')||($arr['presentacion']=='')||($arr['cod_ciudad']=='')||($arr['cod_departamento']=='')){	
		$rs2=pg_query($pila,"INSERT INTO rezago(planilla,linea_det,tipoid_empresa,id_empresa,tipoid_afiliado,id_afiliado,fechapago,codigo_rezago,periodo,usuario,operador,sr)
		VALUES($arr[planilla],0,'$arr[tipoid_empresa]','$arr[id_empresa]','$arr[tipoid_empresa]','$arr[id_empresa]',$arr[fechapago],20,$arr[periodo],'sistema','$arr[operador]','$arr[sr]')");
		$marca=$marca+1;
	}
	*/
    //ERROR DE PERIODO O VALOR NULO
	
	/* OK SANTI
	if($arr['periodo']==''){
			$rs2=pg_query($pila,"INSERT INTO rezago(planilla,linea_det,tipoid_empresa,id_empresa,tipoid_afiliado,id_afiliado,fechapago,codigo_rezago,periodo,usuario,operador,sr)
			VALUES($arr[planilla],0,'$arr[tipoid_empresa]','$arr[id_empresa]','$arr[tipoid_empresa]','$arr[id_empresa]',$arr[fechapago],25,$arr[periodo],'sistema','$arr[operador]','$arr[sr]')");
			$marca=$marca+1;
	}
	*/


	/* OK SANTI
	//valida telefono, fax y direccion
	if($telefono==0)	$pre=pg_query($pila,"update enc_proc set telefono='$telefono' where sr='$arr[sr]'"); 
	if($fax==0)		$pre=pg_query($pila,"update enc_proc set fax='$fax' where sr='$arr[sr]'"); 
	if($direccion=="NO REPORTA")	$pre=pg_query($pila,"update enc_proc set direccion='$direccion' where sr='$arr[sr]'");  
    */


	$sql1=pg_query($pila,"SELECT total_cot,fsp,fspsub FROM total_proc WHERE sr='$arr[sr]' AND linea_total='39'");
	//echo $arr['planilla']."   ".$i."\n";
	$arrt=pg_fetch_array($sql1);
	$total=$arrt['total_cot']+$arrt['fsp']+$arrt['fspsub'];
	//Valida Conciliacion
	//que coincidan todos los campos
	$sql2=pg_query($pila,"SELECT * FROM tbllogbancario WHERE documentoid='$arr[id_empresa]' AND valortotal='$total' AND fechapago='$arr[fechapago]' and radicado='$arr[planilla]' AND conciliado='0' and codigobanco = '007' and oficina = '9999'");
	if(pg_num_rows($sql2)==0){
		$plani=$arr['operador'].$arr['planilla'];
		//se valida el like del nit
		$rs4  = pg_query($pila,"select * from tbllogbancario where documentoid like '$arr[id_empresa]%' and valortotal='$total' and fechapago='$arr[fechapago]' and radicado='$arr[planilla]' and conciliado='0' and codigobanco = '007' and oficina = '9999'");
		if(pg_num_rows($rs4)==0){
			//se valida sin el nit mas planilla y operador
			$sql3=pg_query($pila,"SELECT * FROM tbllogbancario WHERE radicado='$plani' AND valortotal='$total' AND fechapago='$arr[fechapago]' AND conciliado='0' and codigobanco = '007' and oficina = '9999'");        	  
			if(pg_num_rows($sql3)==0){
				//se valida sin el nit mas planilla
				$sql3=pg_query($pila,"SELECT * FROM tbllogbancario WHERE radicado='$arr[planilla]' AND valortotal='$total' AND fechapago='$arr[fechapago]' AND conciliado='0' and codigobanco = '007' and oficina = '9999'");	
				if(pg_num_rows($sql3)==0){
					//sin fecha de pago y planilla
					$rs4  = pg_query($pila,"select * from tbllogbancario where documentoid like '$arr[id_empresa]%' and radicado='$arr[planilla]' and valortotal='$total' and conciliado='0' and codigobanco = '007' and  oficina = '9999'");
					 if(pg_num_rows($rs4)==0){
						//sin fecha de pago y planilla y operador 
							$rs5  = pg_query($pila,"select * from tbllogbancario where documentoid like '$arr[id_empresa]%' and radicado='$plani' and valortotal='$total' and conciliado='0' and codigobanco = '007' and  oficina = '9999'");
						if(pg_num_rows($rs5)==0){
							// planilla y operador, valor,fehapago banco
							$rs6  = pg_query($pila,"select * from tbllogbancario where radicado='$plani' and valortotal='$total' and conciliado='0' and codigobanco = '007' and fechapago='$arr[fechapago]'");
							if(pg_num_rows($rs6)==0){
								$rsy=pg_query($pila,"INSERT INTO rezago(planilla,linea_det,tipoid_empresa,id_empresa,tipoid_afiliado,id_afiliado,fechapago,codigo_rezago,periodo,usuario,operador,sr)
								VALUES('$arr[planilla]',0,'$arr[tipoid_empresa]','$arr[id_empresa]','$arr[tipoid_empresa]','$arr[id_empresa]','$arr[fechapago]',18,'$arr[periodo]','sistema','$arr[operador]','$arr[sr]')");				
								$marca=$marca+1;	
							}elseif(pg_num_rows($rs6)==1){
								$na=pg_fetch_result($rs6,0,'na'); 
									$fpago=pg_fetch_result($rs6,0,'fechapago');
								$encd=pg_query($pila,"update enc_proc set fechapago='$fpago' where sr='$arr[sr]'");
								$sql2=pg_query($pila,"UPDATE tbllogbancario SET sr='$arr[sr]',documentoid='$arr[id_empresa]',planilla='$arr[planilla]',conciliado=1,lote=1,fechaconc='$hoy',usuarioconc='sistema' WHERE na='$na'");
							}
						}elseif(pg_num_rows($rs5)==1){
							$na=pg_fetch_result($rs5,0,'na'); 
								$fpago=pg_fetch_result($rs5,0,'fechapago');
							$encd=pg_query($pila,"update enc_proc set fechapago='$fpago' where sr='$arr[sr]'");
							$sql2=pg_query($pila,"UPDATE tbllogbancario SET sr='$arr[sr]',documentoid='$arr[id_empresa]',planilla='$arr[planilla]',conciliado=1,lote=1,fechaconc='$hoy',usuarioconc='sistema' WHERE na='$na'");
						}
					}elseif(pg_num_rows($rs4)==1){
						$na=pg_fetch_result($rs4,0,'na');
						$fpago=pg_fetch_result($rs4,0,'fechapago');
						$encd=pg_query($pila,"update enc_proc set fechapago='$fpago' where sr='$arr[sr]'");
						$sql2=pg_query($pila,"UPDATE tbllogbancario SET sr='$arr[sr]',documentoid='$arr[id_empresa]',planilla='$arr[planilla]',conciliado=1,lote=1,fechaconc='$hoy',usuarioconc='sistema' WHERE na='$na'");
					}
				}elseif(pg_num_rows($sql3)==1){
					$nite=pg_fetch_result($sql3,0,'documentoid');
					$na=pg_fetch_result($sql3,0,'na');
					$sql2=pg_query($pila,"UPDATE tbllogbancario SET sr='$arr[sr]',documentoid='$arr[id_empresa]',planilla='$arr[planilla]',conciliado=1,lote=1,fechaconc='$hoy',usuarioconc='sistema' WHERE na='$na'");
				}
			}elseif(pg_num_rows($sql3)==1){
				$nite=pg_fetch_result($sql3,0,'documentoid');
				$na=pg_fetch_result($sql3,0,'na');
				$sql2=pg_query($pila,"UPDATE tbllogbancario SET sr='$arr[sr]',documentoid='$arr[id_empresa]',planilla='$arr[planilla]',conciliado=1,lote=1,fechaconc='$hoy',usuarioconc='sistema' WHERE na='$na'");
			}
		}elseif(pg_num_rows($rs4)==1){
			$na=pg_fetch_result($rs4,0,'na');
			$sql2=pg_query($pila,"UPDATE tbllogbancario SET sr='$arr[sr]',planilla='$arr[planilla]',conciliado=1, lote=1,fechaconc='$hoy',usuarioconc='sistema' WHERE na='$na'");
		
		}
	}elseif(pg_num_rows($sql2)==1){
		$na=pg_fetch_result($sql2,0,'na');
		$sql2=pg_query($pila,"UPDATE tbllogbancario SET sr='$arr[sr]',planilla='$arr[planilla]',conciliado=1, lote=1,fechaconc='$hoy',usuarioconc='sistema' WHERE na='$na'");
	} 
	//valida la planilla tiopo N los campos fecha_pago_asociada y planilla_asociada
	//validar el tipo planill =N
	/* SANTI OK --- FALTAN DETALLES, VER @TODO EN EL CODIGO
	if($arr['tipo_planilla']=='N' && ($arr['fecha_pago_asociada']=='')||($arr['planilla_asociada']=='')){
		if($arr['fecha_pago_asociada']==''){
			$rs5  = pg_query($pila,"update enc_proc set fecha_pago_asociada='$arr[fechapago]' where sr='$arr[sr]'");
			$error_N = '19';
		}
		if ($arr['planilla_asociada']==''){
			$rs5  = pg_query($pila,"update enc_proc set planilla_asociada='$arr[planilla]' where sr='$arr[sr]'");
			$error_N = '15';
		}
		$rs2=pg_query($pila,"INSERT INTO rezago(planilla,linea_det,tipoid_empresa,id_empresa,tipoid_afiliado,id_afiliado,fechapago,codigo_rezago,periodo,usuario,operador,sr)
		VALUES($arr[planilla],0,'$arr[tipoid_empresa]','$arr[id_empresa]','$arr[tipoid_empresa]','$arr[id_empresa]',$arr[fechapago],$error_N,$arr[periodo],'sistema','$arr[operador]','$arr[sr]')");
		$marca=$marca+1;
		if ($error_N == '19'){//solo faltó la fecha de pago asociada queda de una vez solucionado el rezago
			$rs5  = pg_query($pila,"update rezago set estado='2',fechaok='$hoy',usuario='$user' where sr='$arr[sr]' and linea_det=0 and tiporezago='0' and estado in ('0') and codigo_rezago='$error_N'");
			$marca=$marca-1;
		}
		
	}
	*/
		
	//------------------------------Validacion Detalles---------------------------------------
	
	//unificación de registros
	if ($arr['tipo_planilla']!='N'){ //Demás planillas diferentes a Corrección
		//tener en cuenta en la unificación de las cédulas las novedades SLN
		$sql1=pg_query($pila,"SELECT DISTINCT id_afiliado, Sr FROM det_proc WHERE id_afiliado In (SELECT id_afiliado FROM det_proc As Tmp where sr='$arr[sr]' GROUP BY id_afiliado HAVING Count(*)>1 ) ORDER BY id_afiliado");
		for($j=0;$j<pg_num_rows($sql1);$j++){
			$arrd=pg_fetch_array($sql1,$j);
			$sqldet1=pg_query($pila,"SELECT linea_det,ing,ret,tdp,tap,vsp,vte,vst,sln,ige,lma,vac,avp,dias,ibc,cotizacion_obli,cotizacion_voluntaria_af,
				cotizacion_voluntaria_aportante,total_cot,fsp,afsp,valor_no_retenido FROM det_proc WHERE sr='$arr[sr]' and 
				id_afiliado='$arrd[id_afiliado]' ORDER BY linea_det");
			$arrdet=pg_fetch_array($sqldet1);
			$lineadet1=$arrdet['linea_det']; //registro 1 se va a actualizar
			$dias = 0;
			$ibc    = 0;
			$cotizacion_obli= 0;
			$cotizacion_voluntaria_af= 0;
			$cotizacion_voluntaria_aportante= 0;
			$total_cot= 0;
			$fsp= 0;
			$afsp= 0;
			$valor_no_retenido= 0;
			for($d=0;$d<pg_num_rows($sqldet1);$d++){
				$arrdet1=pg_fetch_array($sqldet1,$d);
				if ($arrdet1['ing']=='X')       $ing='X';
				if ($arrdet1['ret']=='X')       $ret='X';
				if ($arrdet1['tdp']=='X')       $tdp='X';
				if ($arrdet1['tap']=='X')       $tap='X';
				if ($arrdet1['vsp']=='X')       $vsp='X';
				if ($arrdet1['vte']=='X')       $vte='X';
				if ($arrdet1['vst']=='X')       $vst='X';
				if ($arrdet1['sln']=='X')       $sln='X';
				if ($arrdet1['ige']=='X')       $ige='X';
				if ($arrdet1['lma']=='X')       $lma='X';
				if ($arrdet1['vac']=='X')       $vac='X';
				if ($arrdet1['avp']=='X')       $avp='X';
				$dias=$arrdet1['dias']+$dias;
				if ($dias>30)                   $dias=30;
				$ibc    = $arrdet1['ibc']+$ibc;
				$cotizacion_obli= $arrdet1['cotizacion_obli']+$cotizacion_obli;
				$cotizacion_voluntaria_af= $arrdet1['cotizacion_voluntaria_af']+$cotizacion_voluntaria_af;
				$cotizacion_voluntaria_aportante= $arrdet1['cotizacion_voluntaria_aportante']+$cotizacion_voluntaria_aportante;
				$total_cot= $arrdet1['total_cot']+$total_cot;
				$fsp= $arrdet1['fsp']+$fsp;
				$afsp= $arrdet1['afsp']+$afsp;
				$valor_no_retenido= $arrdet1['valor_no_retenido']+$valor_no_retenido;

				if($arrdet1['linea_det'] != $lineadet1){ //se borra la linea
					$sqldel=pg_query($pila,"delete from det_proc where sr='$arr[sr]' and id_afiliado='$arrd[id_afiliado]' 
						and linea_det='$arrdet1[linea_det]'");
				}
			}
			$sqlupd=pg_query($pila,"update det_proc set ing='$ing',ret='$ret',tdp='$tdp',tap='$tap',vsp='$vsp',vte='$vte',vst='$vst',sln='$sln',ige='$ige',lma='$lma',vac='$vac',avp='$avp',dias='$dias',ibc='$ibc',cotizacion_obli='$cotizacion_obli',cotizacion_voluntaria_af='$cotizacion_voluntaria_af',cotizacion_voluntaria_aportante='$cotizacion_voluntaria_aportante',total_cot='$total_cot',fsp='$fsp',afsp='$afsp',valor_no_retenido='$valor_no_retenido' where sr='$arr[sr]' and id_afiliado='$arrd[id_afiliado]' and linea_det='$lineadet1'");
		}
		$detalle=pg_query($pila,"select count(*) as cantidad from det_proc where sr='$arr[sr]'");
		$cant=pg_fetch_result($detalle, 0, "cantidad");
		$enc =pg_query($pila,"update enc_proc set registros_tipo2 ='$cant' where sr='$arr[sr]'");
	}
	/* SANTI OK
	if ($arr['tipo_planilla']=='N'){ //planilla de corrección
		$sql1=pg_query($pila,"SELECT DISTINCT id_afiliado, Sr FROM det_proc WHERE id_afiliado 
			In (SELECT id_afiliado FROM det_proc As Tmp where sr='$arr[sr]' GROUP BY id_afiliado HAVING Count(*)>1 ) ORDER BY id_afiliado");
		for($j=0;$j<pg_num_rows($sql1);$j++){
			$arrd=pg_fetch_array($sql1,$j);
			$sqldet1=pg_query($pila,"SELECT * FROM det_proc WHERE sr='$arr[sr]' and id_afiliado='$arrd[id_afiliado]' and correcciones = 'C'");
			$arrdet1=pg_fetch_array($sqldet1);
			
			$sqldet2=pg_query($pila,"SELECT * FROM det_proc WHERE sr=$arr[sr] and id_afiliado='$arrd[id_afiliado]' and correcciones = 'A'");
			$arrdet2=pg_fetch_array($sqldet2);	
			$dias=$arrdet1['dias']-$arrdet2['dias'];
			if ($dias<=0)                   $dias=$arrdet1['dias'];
			$ibc    = $arrdet1['ibc']-$arrdet2['ibc'];
			$cotizacion_obli= $arrdet1['cotizacion_obli']-$arrdet2['cotizacion_obli'];
			$cotizacion_voluntaria_af= $arrdet1['cotizacion_voluntaria_af']-$arrdet2['cotizacion_voluntaria_af'];
			$cotizacion_voluntaria_aportante= $arrdet1['cotizacion_voluntaria_aportante']-$arrdet2['cotizacion_voluntaria_aportante'];
			$total_cot= $arrdet1['total_cot']-$arrdet2['total_cot'];
			$fsp= $arrdet1['fsp']-$arrdet2['fsp'];
			$afsp= $arrdet1['afsp']-$arrdet2['afsp'];
			$valor_no_retenido= $arrdet1['valor_no_retenido']-$arrdet2['valor_no_retenido'];
			$sqldel=pg_query($pila,"delete from det_proc where sr = '$arr[sr]' and id_afiliado = '$arrd[id_afiliado]' and correcciones = 'C'");
			$sqlupd=pg_query($pila,"update det_proc set dias=$dias, ibc=$ibc, cotizacion_obli=$cotizacion_obli,
				cotizacion_voluntaria_af=$cotizacion_voluntaria_af, cotizacion_voluntaria_aportante=$cotizacion_voluntaria_aportante,
				total_cot=$total_cot, fsp=$fsp, afsp=$afsp, valor_no_retenido=$valor_no_retenido where sr=$arr[sr] and id_afiliado='$arrd[id_afiliado]' 
				and correcciones = 'A'");
		}
		$detalle=pg_query($pila,"select count(*) as cantidad from det_proc where sr='$arr[sr]'");
		$cant=pg_fetch_result($detalle, 0, "cantidad");
		$enc =pg_query($pila,"update enc_proc set registros_tipo2 ='$cant' where sr='$arr[sr]'");
	}
	*/
	
	$sql1=pg_query($pila,"SELECT * FROM det_proc WHERE sr=$arr[sr] ORDER BY linea_det");
	for($j=0;$j<pg_num_rows($sql1);$j++){
		$arrd=pg_fetch_array($sql1,$j);
		//dias menores a 30 sin novedad
		/* OK SANTI
		if((($arrd['ing']=='')&&($arrd['ret']=='')&&($arrd['tdp']=='')&&($arrd['tap']=='')&&($arrd['vsp']=='')&&($arrd['vte']=='')&&($arrd['vst']=='')&&($arrd['sln']=='')&&($arrd['ige']=='')&&($arrd['lma']=='')&&($arrd['vac']=='')&&($arrd['avp']==''))&&($arrd['dias']<30)){
			$rs2=pg_query($pila,"INSERT INTO rezago (planilla,linea_det,tipoid_empresa,id_empresa,tipoid_afiliado,id_afiliado,
			fechapago,codigo_rezago,periodo,usuario,operador,sr) VALUES($arrd[planilla],$arrd[linea_det],'$arr[tipoid_empresa]','$arr[id_empresa]',
			'$arrd[tipoid_afiliado]','$arrd[id_afiliado]',$arr[fechapago],12,$arr[periodo],'sistema','$arr[operador]','$arr[sr]')");
			$marca=$marca+1;
		}
		*/


        /* OK SANTI
		//validar salario ibc
		$tarifa = $arrd['tarifa']/100000;
		if (($tarifa=='0.16')||($tarifa=='0.25')){
		}else{
			//valideaciones que va a mandar proteccion
		}	
		
		if($tarifa!=0){
			$ibc_cal=($arrd['cotizacion_obli']/$tarifa);
		}else{
			$ibc_cal=0;
		}
		if($arrd['ibc']>=$ibc_cal-1000 and $arrd['ibc']<=$ibc_cal+1000){
		}else{
			$rs2=pg_query($pila,"INSERT INTO  rezago(planilla,linea_det,tipoid_empresa,id_empresa,tipoid_afiliado,id_afiliado,fechapago,codigo_rezago, periodo,usuario,operador,sr) VALUES($arrd[planilla],$arrd[linea_det],'$arr[tipoid_empresa]','$arr[id_empresa]','$arrd[tipoid_afiliado]',
			'$arrd[id_afiliado]',$arr[fechapago],14,$arr[periodo],'sistema','$arr[operador]','$arr[sr]')");			
			$marca=$marca+1;
		}

		/* OK SANTI
		//valida cotizacion
		$cotiz  = $arrd['ibc']*$tarifa;
		$cotiz  = $cotiz+$arrd['cotizacion_voluntaria_af']+$arrd['cotizacion_voluntaria_aportante'];
		$total  = $arrd['total_cot'];
		if ($total>=$cotiz-100 and $total<=$cotiz+100){
			// ENCONTRADO OK EN EL RANGO 
		}else{
			$rs2=pg_query($pila,"INSERT INTO rezago(planilla,linea_det,tipoid_empresa,id_empresa,tipoid_afiliado,id_afiliado,fechapago,codigo_rezago,periodo,usuario,operador,sr)
			VALUES($arrd[planilla],$arrd[linea_det],'$arr[tipoid_empresa]','$arr[id_empresa]','$arrd[tipoid_afiliado]','$arrd[id_afiliado]',$arr[fechapago],17,$arr[periodo],'sistema','$arr[operador]','$arr[sr]')");
			$marca=$marca+1;
		} 
		*/ 
		if ($arrd['subtipo_cotizante'] == '18'){
			//SE INSERTA EN REZAGO 13 Y GENERAR REPORTE
			$rs2=pg_query($pila,"INSERT INTO rezago(planilla,linea_det,tipoid_empresa,id_empresa,tipoid_afiliado,id_afiliado,fechapago,codigo_rezago,periodo,usuario,operador,sr)
			VALUES($arrd[planilla],$arrd[linea_det],'$arr[tipoid_empresa]','$arr[id_empresa]','$arrd[tipoid_afiliado]','$arrd[id_afiliado]',$arr[fechapago],13,$arr[periodo],'sistema','$arr[operador]','$arr[sr]')");
			$marca=$marca+1;
		}
		*/
	}

	/* SANTI OK
	//valores en cero de los detalles 
	//insert into en rezagos 35
	$rsvalcero=pg_query($pila,"INSERT INTO rezago (planilla,linea_det,tipoid_empresa,id_empresa,tipoid_afiliado,id_afiliado,
			fechapago,codigo_rezago,periodo,usuario,operador,sr) 
			SELECT $arr[planilla],linea_det,'$arr[tipoid_empresa]','$arr[id_empresa]',tipoid_afiliado,id_afiliado,$arr[fechapago],35,
			$arr[periodo],'sistema','$arr[operador]','$arr[sr]' FROM det_proc WHERE sr=$arr[sr]
			 and dias=0 and 
			 valor_neto=0 and 
			 ibc=0 and 
			 cotizacion_obli=0 and 
			 cotizacion_voluntaria_af=0 and
			 cotizacion_voluntaria_aportante=0 and 
			 total_cot=0 and 
			 fsp=0 and 
			 afsp=0 and 
			 (sln='' or sln=' ')");
	//$marca=$marca+1;
	//borra registro de detalles
	$sql1=pg_query($pila,"DELETE FROM det_proc WHERE sr=$arr[sr] and dias=0 and valor_neto=0 and 
			ibc=0 and cotizacion_obli=0 and cotizacion_voluntaria_af=0 and cotizacion_voluntaria_aportante=0 and total_cot=0 
			and fsp=0 and afsp=0 and (sln='' or sln=' ')");
	$detalle=pg_query($pila,"select count(*) as cantidad from det_proc where sr='$arr[sr]'");
	$cant=pg_fetch_result($detalle, 0, "cantidad");	
	$enc =pg_query($pila,"update enc_proc set registros_tipo2 ='$cant' where sr='$arr[sr]'");
	*/
	
	/* SANTI OK

	//comparación de totales detalles reg 31 totales
	$sql1=pg_query($pila,"SELECT 
		sum(ibc) as sibc,
		sum(cotizacion_obli) as scotizacion_obli,
		sum(cotizacion_voluntaria_af) as scotizacion_voluntaria_af,
		sum(cotizacion_voluntaria_aportante) as scotizacion_voluntaria_aportante, 
		sum(total_cot) as stotal_cot, 
		sum(fsp) as sfsp, 
		sum(afsp) as safsp
		FROM det_proc WHERE sr=$arr[sr]");
	$arrtd=pg_fetch_array($sql1);
	$sql2=pg_query($pila,"SELECT * FROM total_proc WHERE sr=$arr[sr] and linea_total = '31'");
	$arrtp=pg_fetch_array($sql2);
	if ($arrtd[sibc] != $arrtp[ibc]){
		$sqlup=pg_query($pila,"update total_proc set ibc = $arrtd[sibc] WHERE sr=$arr[sr] and linea_total = '31'");
	}
	if ($arrtd[scotizacion_obli] != $arrtp[cotizacion_obli]){
		$sqlup=pg_query($pila,"update total_proc set cotizacion_obli = $arrtd[scotizacion_obli] WHERE sr=$arr[sr] and linea_total = '31'");
	}
	if ($arrtd[scotizacion_voluntaria_af] != $arrtp[cotizacion_voluntaria_af]){
		$sqlup=pg_query($pila,"update total_proc set cotizacion_voluntaria_af = $arrtd[scotizacion_voluntaria_af] WHERE sr=$arr[sr] and linea_total = '31'");
	}
	if ($arrtd[scotizacion_voluntaria_aportante] != $arrtp[cotizacion_voluntaria_aportante]){
		$sqlup=pg_query($pila,"update total_proc set cotizacion_voluntaria_aportante = $arrtd[scotizacion_voluntaria_aportante] 
			WHERE sr=$arr[sr] and linea_total = '31'");
	}
	if ($arrtd[stotal_cot] != $arrtp[total_cot]){
		$sqlup=pg_query($pila,"update total_proc set total_cot = $arrtd[stotal_cot] WHERE sr=$arr[sr] and linea_total = '31'");
	}
	if ($arrtd[sfsp] != $arrtp[fsp]){
		$sqlup=pg_query($pila,"update total_proc set fsp = $arrtd[sfsp] WHERE sr=$arr[sr] and linea_total = '31'");
	}
	if ($arrtd[safsp] != $arrtp[fspsub]){
		$sqlup=pg_query($pila,"update total_proc set fspsub = $arrtd[safsp] WHERE sr=$arr[sr] and linea_total = '31'");
	}
	
	*/
	//comparación de totales reg 31-36 con reg 39
	$sql2=pg_query($pila,"SELECT cotizacion_obli,fsp,fspsub FROM total_proc WHERE sr=$arr[sr] and linea_total = '31'");
	$arrt2=pg_fetch_array($sql2);
	$sql3=pg_query($pila,"SELECT mora_cotizaciones,mora_fsp,mora_fspsub FROM total_proc WHERE sr=$arr[sr] and linea_total = '36'");
	$arrt3=pg_fetch_array($sql3);
	
	$tot_cto = $arrt2[cotizacion_obli] + $arrt3[mora_cotizaciones];
	$tot_fsp = $arrt2[fsp] + $arrt3[mora_fsp];
	$tot_fspsub = $arrt2[fspsub] + $arrt3[mora_fspsub];
	
	$sql4=pg_query($pila,"SELECT total_cot,fsp,fspsub FROM total_proc WHERE sr=$arr[sr] and linea_total = '39'");
	$arrt4=pg_fetch_array($sql4);
	if ($arrt4[total_cot] != $tot_cto){
		$sqlup=pg_query($pila,"update total_proc set total_cot = $arrt4[total_cot] WHERE sr=$arr[sr] and linea_total = '39'");
	}
	if ($arrt4[fsp] != $tot_fsp){
		$sqlup=pg_query($pila,"update total_proc set fsp = $arrt4[fsp] WHERE sr=$arr[sr] and linea_total = '39'");
	}
	if ($arrt4[fspsub] != $tot_fspsub){
		$sqlup=pg_query($pila,"update total_proc set fspsub = $arrt4[fspsub] WHERE sr=$arr[sr] and linea_total = '39'");
	}
	
	
	//TRaslado registros de la planilla validada de procesadas a devoluciones
	if($marca>0){
		$sql1=pg_query($pila,"INSERT INTO enc_dev (planilla,nro_registro,tipo_registro,cod_formato,razon_social_afp,num_id_afp,dv_afp,razon_social,tipoid_empresa,id_empresa,dv_aportante,clase_aportante,direccion,ciudad,cod_ciudad,departamento,cod_departamento,telefono,fax,mail,periodo,fechapago,presentacion,cod_sucursal,nombre_sucursal,empleados,afiliados,operador,usuario,estado,sr,tipo_aportante,codigo_arp,tipo_planilla,fecha_pago_asociada,planilla_asociada,modalidad,dias_mora,registros_tipo2)
		SELECT planilla,nro_registro,tipo_registro,cod_formato,razon_social_afp,num_id_afp,dv_afp,razon_social,tipoid_empresa,id_empresa,dv_aportante,clase_aportante,direccion,ciudad,cod_ciudad,departamento,cod_departamento,telefono,fax,mail,periodo,fechapago,presentacion,cod_sucursal,nombre_sucursal,empleados,afiliados,operador,usuario,estado,sr,tipo_aportante,codigo_arp,tipo_planilla,fecha_pago_asociada,planilla_asociada,modalidad,dias_mora,registros_tipo2 FROM enc_proc WHERE sr='$arr[sr]'");
		$sql1=pg_query($pila,"INSERT INTO det_dev (planilla,linea_det,tipo_registro,tipoid_afiliado,id_afiliado,tipo_cotizante,subtipo_cotizante,extranjero,col_exterior,cod_departamento,cod_municipio,primer_apellido,segundo_apellido,primer_nombre,segundo_nombre,ing,ret,tdp,tap,vsp,vte,vst,sln,ige,lma,vac,avp,dias,salario,valor_neto,ibc,tarifa,cotizacion_obli,cotizacion_voluntaria_af,cotizacion_voluntaria_aportante,total_cot,fsp,afsp,valor_no_retenido,sr,correcciones,salario_integral)
		SELECT planilla,linea_det,tipo_registro,tipoid_afiliado,id_afiliado,tipo_cotizante,subtipo_cotizante,extranjero,col_exterior,cod_departamento,cod_municipio,primer_apellido,segundo_apellido,primer_nombre,segundo_nombre,ing,ret,tdp,tap,vsp,vte,vst,sln,ige,lma,vac,avp,dias,salario,valor_neto,ibc,tarifa,cotizacion_obli,cotizacion_voluntaria_af,cotizacion_voluntaria_aportante,total_cot,fsp,afsp,valor_no_retenido,sr,correcciones,salario_integral FROM det_proc WHERE sr=$arr[sr]");
		$sql1=pg_query($pila,"INSERT INTO total_dev (planilla,linea_total,total_aportes,tipo_registro,ibc,cotizacion_obli,cotizacion_voluntaria_af,cotizacion_voluntaria_aportante,total_cot,fsp,fspsub,sr
		,dias_mora,mora_cotizaciones,mora_fsp,mora_fspsub)
		SELECT planilla,linea_total,total_aportes,tipo_registro,ibc,cotizacion_obli,cotizacion_voluntaria_af,cotizacion_voluntaria_aportante,total_cot,fsp,fspsub,sr,dias_mora,mora_cotizaciones,mora_fsp,mora_fspsub FROM total_proc WHERE sr=$arr[sr]");
		$sql1=pg_query($pila,"DELETE FROM total_proc WHERE sr=$arr[sr]");
		$sql1=pg_query($pila,"DELETE FROM det_proc WHERE sr=$arr[sr]");
		$sql1=pg_query($pila,"DELETE FROM enc_proc WHERE sr=$arr[sr]");
		$sql1=pg_query($pila,"UPDATE radicado SET estado_planilla=1 WHERE sr='$arr[sr]'");
		$rezagos=$rezagos+1;
	}else{
		$sql1=pg_query($pila,"UPDATE enc_proc SET estado=1 WHERE sr='$arr[sr]'");
	}
}

//$nombre_archivo="resultadopldes.txt";
//@$gestor=fopen($nombre_archivo,'w+');
//@fwrite($gestor,$txterr);
//@fclose($gestor);
//echo "<a href='resultadopldes.txt'>Informe de Resultadooooo</a>";
$tipoDato=$_POST['d'];
if($tipoDato=='gen') { //echo 'Se realizo validacion...!!!'."\n"."\n";
 echo "<h5>Procesadas ".pg_num_rows($sql)." Planillas, ".$rezagos." en rezago.</h5>\n";
 echo "\n <h4>TERMINO VALIDACION ARCHIVOS PILA COMIENZAN INCLUDES ...</h4>\n";
}
echo date('h:i:s')."inica masivos..."."<br>";
include("C:\AppServ\www\pila\masivorezagoopc1.php");//empresa no existe
include("C:\AppServ\www\pila\masivorezagoopc12.php");//retorna DIAS MENORES A 30 SIN NOVEDAD
//include("srv/www/htdocs/new2/pila/masivorezagoopc1.php");//empresa no existe
//include("/srv/www/htdocs/new2/pila/masivorezagoopc12.php");//retorna DIAS MENORES A 30 SIN NOVEDAD
echo date('h:i:s')."termina masivos..."."<br>";
//include("/var/www/ing/pila/rezago/masivorezagoopc18.php");//retorna masivo de no concilia
//include("/var/www/ing/pila/rezago/masivorezagoopc_18_1.php");//retorna masivo de no concilia
/*
include("/var/www/ing/pila/rezago/masivorezagoopc2.php");//retorna masivo no existe afiliado
include("/var/www/ing/pila/rezago/masivorezagoopc2_afp.php");//retorna masivo no existe afiliado afp
include("/var/www/ing/pila/rezago/masivorezagoopc3.php");//retorna masivo no coincide identificacion con nombres
include("/var/www/ing/pila/rezago/masivorezagoopc16.php");//retorna masivo afiliado no empleador
include("/var/www/ing/pila/rezago/masivorezagoopc_afi.php");//retorna masivo de afiliado no exsite
include("/var/www/ing/pila/rezago/masivorezagoopc_radicado3.php");//retorna radicado estado_planilla=3
include("/var/www/ing/pila/rezago/masivorezagoopc12.php");//retorna DIAS MENORES A 30 SIN NOVEDAD
include("/var/www/ing/pila/rezago/masivorezagoopc14.php");//retorna IBC DIFERENTE AL CALCULADO
include("/var/www/ing/pila/rezago/masivorezagoopc17.php");//retorna COTIZACION DIFERENTE AL CALCULADO
include("/var/www/ing/pila/rezago/masivorezagoopc24.php");//retorna SALARIO MINIMO
*/
//include("/var/www/ing/pila/conciliacion/arregla.php");//valida los caracteres Ñ en los detalles

//update empresas aportantes 44 y 45 aplica el 20120525
/*
echo "\n\n INICIA SEGUNDA VALIDACION DE REGISTROS \n\n";
$cd = "SELECT count(*) as tot from enc_dev where substr(fechainsert,1,10)='$fec'";
$rd = pg_query($pila,$cd);
$vd = pg_fetch_array($rd);
$totalDev = $vd['tot'];
$ce = "SELECT count(*) as tot from enc_proc where substr(fechainsert,1,10)='$fec'";
$re = pg_query($pila,$ce);
$ve = pg_fetch_array($re);
$totalEnc = $ve['tot'];
$totPla = $totalEnc + $totalDev;
if($totalRad!=$totPla){
	die("Error!!!!!!!====>Revise la cantidad de archivos en radicado y en enc_proc y enc_dev no coincide \n\n");
}
echo "\n\n FINALIZA SEGUNDA VALIDACION DE REGISTROS \n\n";
*/
?>


