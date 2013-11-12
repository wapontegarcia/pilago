<?php
error_reporting(E_ALL ^ E_NOTICE);
include("pila.php");

$hoy = date('Ymd');

function formato_num($texto,$long){
	$tlng=strlen(trim($texto));
	$cmp=$long-$tlng;
	if($cmp<=0){
		if($long==1) $txt=substr(trim($texto),0,1);
			else $txt=substr(trim($texto),0,$long);
		}else{
		$txt='';
		for($ft=0;$ft<$cmp;$ft++){
			$txt=$txt."0";
		}
		$txt=strtoupper($txt.trim($texto));
	}  
	return $txt;
}

function formato_alf($texto,$long){
	$tlng=strlen(trim($texto));
	$cmp=$long-$tlng;
	if($cmp<=0){
		if($long==1) $txt=substr(trim($texto),0,1);
		else $txt=substr(trim($texto),0,$long);
	} else {
		$txt='';
		for($ft=0;$ft<$cmp;$ft++){
			$txt=$txt." ";
		}
		$txt=strtoupper(trim($texto).$txt);
	}  
return $txt;
}

function formato_fec($texto){
	$tlng=strlen(trim($texto));
	if($tlng==8){
		$txt=substr($texto,0,4)."-".substr($texto,4,2)."-".substr($texto,6,2);
	} elseif($tlng==6){
		$txt=substr($texto,0,4)."-".substr($texto,4,2);
	} else {
	$txt=$texto; 
	}
	return $txt;
}


echo "INICIANDO GENERACION DE ARCHIVOS PLANOS AS400 ...\n\n\n";

$plan=pg_query($pila,"delete from planoas400");
$tipoa='-Dependientes';//tipo de archivo				
//para Dependientes	
$cont=1;
$sql = pg_query($pila,"SELECT * FROM enc_proc WHERE estado='1' order by sr");
for($i=0;$i<pg_num_rows($sql);$i++){
	ini_set("max_execution_time","25");
	flush();
	$canregs=pg_num_rows($sql);
	echo $cont." de $canregs"."<br>";;
	$cont++;
			
	$arr = pg_fetch_array($sql,$i); 
	$conta=0;
	$enct=pg_query($pila,"select * from enc_proc where sr='$arr[sr]'");
	if(pg_num_rows($enct)!=0)
		$conta=$conta+1;	
	$enct1=pg_query($pila,"select * from det_proc where sr='$arr[sr]'");
	if(pg_num_rows($enct1)!=0)
		$conta=$conta+1;
	$enct2=pg_query($pila,"select * from total_proc where sr='$arr[sr]'");
	if(pg_num_rows($enct2)!=0)
		$conta=$conta+1;
	$rs  = pg_query($pila,"SELECT no_afiliados FROM radicado WHERE sr='$arr[sr]'");
	$sql1=pg_query($pila,"SELECT count(*) AS afi FROM det_proc WHERE sr=$arr[sr]");
	if($conta<3){
		$rs=pg_query($pila,"UPDATE radicado SET estado_planilla=5 WHERE sr='$arr[sr]'");
		$rs=pg_query($pila,"UPDATE enc_proc SET estado=5 WHERE sr='$arr[sr]'");
		echo "NO SE GENERA PLANO DEL sr $arr[sr], DETALLES Y TOTALES PENDIENTES \n\n";
	}elseif(pg_fetch_result($sql1,0,'afi')!=pg_fetch_result($enct,0,'registros_tipo2')){
		$rs = pg_query($pila,"UPDATE radicado SET estado_planilla=3 WHERE sr='$arr[sr]'");
		$rs = pg_query($pila,"UPDATE enc_proc SET estado=3 WHERE sr='$arr[sr]'");
		echo "NO SE GENERA PLANO DEL SR $arr[sr], PERDIDA DE REGISTROS DEL ARCHIVO\n\n";
	}else{
		//inserta empresa en la em
	        if($arr['tipoid_empresa']=='CC'){
	            $tipoe='C';
	        } elseif($arr['tipoid_empresa']=='CE'){
	            $tipoe='E';
	        } elseif($arr['tipoid_empresa']=='TI'){
	            $tipoe='T';
	        } elseif($arr['tipoid_empresa']=='PA'){
				$tipoe='P';
	        } else{
				$tipoe='N';																		        
			}																			
		$rsem=pg_query($pila,"select * from em where ei='$arr[id_empresa]' and ti='$arr[tipoid_empresa]'");
		if(pg_num_rows($enct1)==0)
			$rsem=pg_query($pila,"insert into em (ei,ti,ds) values('$arr[id_empresa]','$arr[tipoid_empresa]','$arr[razon_social]')");
		//GENERA PLANO EMPRESAS
		$enc   = '00000'.formato_num($arr['tipo_registro'],1).formato_num($arr['cod_formato'],2).formato_alf($arr['num_id_afp'],16).formato_num($arr['dv_afp'],1).formato_alf($arr['razon_social'],200).formato_alf($arr['tipoid_empresa'],2).formato_alf($arr['id_empresa'],16).formato_num($arr['dv_aportante'],1).formato_alf($arr['tipo_aportante'],1).formato_alf($arr['direccion'],40).formato_num($arr['cod_ciudad'],3).formato_num($arr['cod_departamento'],2).formato_alf($arr['telefono'],10).formato_alf($arr['fax'],10).formato_alf($arr['mail'],60).formato_alf(formato_fec($arr['periodo']),7).formato_alf($arr['codigo_arp'],6).formato_alf($arr['tipo_planilla'],1).formato_alf($arr['fecha_pago_asociada'],10).formato_alf(formato_fec($arr['fechapago']),10).formato_alf(formato_fec($arr['planilla_asociada']),10).formato_alf($arr['planilla'],10).formato_alf($arr['presentacion'],1).'                                                  '.formato_num($arr['empleados'],5).formato_num($arr['afiliados'],5).formato_num($arr['operador'],2).formato_num($arr['modalidad'],1).formato_num($arr['dias_mora'],4).formato_num($arr['registros_tipo2'],8);
		$enc = strtoupper($enc);
		//echo $enc."==".$arr['sr']."\n";
		//echo $arr['sr']."\n";
		$rs  = pg_query($pila,"INSERT INTO planoas400(texto,tipo,planilla) VALUES('$enc',1,$arr[sr])");
		//Generacion Detalles
		$sql1=pg_query($pila,"SELECT * FROM det_proc WHERE sr=$arr[sr] ORDER BY linea_det");
		for($j=0;$j<pg_num_rows($sql1);$j++){
			$det  = "";
			$arrd = pg_fetch_array($sql1,$j);
			$arrd['tarifa']= number_format(($arrd['tarifa']/100000), 5, '.','');
			$det= formato_num($arrd['linea_det'],5).formato_num($arrd['tipo_registro'],1).formato_alf($arrd['tipoid_afiliado'],2).formato_alf($arrd['id_afiliado'],16).formato_num($arrd['tipo_cotizante'],2).formato_num($arrd['subtipo_cotizante'],2).formato_alf($arrd['extranjero'],1).formato_alf($arrd['col_exterior'],1).formato_num($arrd['cod_departamento'],2).formato_num($arrd['cod_municipio'],3).formato_alf($arrd['primer_apellido'],20).formato_alf($arrd['segundo_apellido'],30).formato_alf($arrd['primer_nombre'],20).formato_alf($arrd['segundo_nombre'],30).formato_alf($arrd['ing'],1).formato_alf($arrd['ret'],1).formato_alf($arrd['tdp'],1).formato_alf($arrd['tap'],1).formato_alf($arrd['vsp'],1).formato_alf($arrd['vst'],1).formato_alf($arrd['sln'],1).formato_alf($arrd['ige'],1).formato_alf($arrd['lma'],1).formato_alf($arrd['vac'],1).formato_alf($arrd['avp'],1).formato_num($arrd['dias'],2).formato_num($arrd['salario'],9).formato_num($arrd['ibc'],9).formato_num($arrd['tarifa'],7).formato_num($arrd['cotizacion_obli'],9).formato_num($arrd['cotizacion_voluntaria_af'],9).formato_num($arrd['cotizacion_voluntaria_aportante'],9).formato_num($arrd['total_cot'],9).formato_num($arrd['fsp'],9).formato_num($arrd['afsp'],9).formato_num($arrd['valor_no_retenido'],9).formato_alf($arrd['correcciones'],1).formato_alf($arrd['salario_integral'],1);
			$det = strtoupper($det);
			$rs=pg_query($pila,"INSERT INTO planoas400(texto,tipo,planilla) VALUES('$det',2,$arr[sr])");
		}
		//Generacion Totales
		$sql1=pg_query($pila,"SELECT * FROM total_proc WHERE sr=$arr[sr] ORDER BY linea_total");
		for($j=0;$j<pg_num_rows($sql1);$j++){
			$tot  = "";
			$arrt = pg_fetch_array($sql1,$j);
			if($arrt['total_aportes']==31)  
				$tot  = formato_num($arrt['total_aportes'],5).formato_num($arrt['tipo_registro'],1).formato_num($arrt['ibc'],10).formato_num($arrt['cotizacion_obli'],10).formato_num($arrt['cotizacion_voluntaria_af'],10).formato_num($arrt['cotizacion_voluntaria_aportante'],10).formato_num($arrt['total_cot'],10).formato_num($arrt['fsp'],10).formato_num($arrt['fspsub'],10);
			elseif($arrt['total_aportes']==36)
				$tot  = formato_num($arrt['total_aportes'],5).formato_num($arrt['tipo_registro'],1).formato_num($arrt['dias_mora'],4).formato_num($arrt['mora_cotizaciones'],10).formato_num($arrt['mora_fsp'],10).formato_num($arrt['mora_fspsub'],10);
			elseif($arrt['total_aportes']==39)
				$tot  = formato_num($arrt['total_aportes'],5).formato_num($arrt['tipo_registro'],1).formato_num($arrt['total_cot'],10).formato_num($arrt['fsp'],10).formato_num($arrt['fspsub'],10);
			$rs=pg_query($pila,"INSERT INTO planoas400(texto,tipo,planilla) VALUES('$tot',$arrt[linea_total],$arr[sr])");
		}
		
		$rs=pg_query($pila,"UPDATE radicado SET estado_planilla=2 WHERE sr='$arr[sr]'");
		$rs=pg_query($pila,"UPDATE enc_proc SET estado=2,fechacargue=$hoy WHERE sr='$arr[sr]'");

        $sr  = $arr['sr'];
		$txtfec=substr($arr['fechapago'],0,4)."-".substr($arr['fechapago'],4,2)."-".substr($arr['fechapago'],6,2);
		$txtper=substr($arr['periodo'],0,4)."-".substr($arr['periodo'],4,2);
		
	    $arc = 'IMA-'.$txtfec.'_1_'.$arr['planilla']._.$arr['tipoid_empresa'].'_'.$arr['id_empresa'].'_230201_'.$arr['operador'].'_I_'.$txtper.'.TXT';
		$rsh=pg_query($pila,"select to_char(now(),'YYYYMMDD') as hoy");
		$hoy=pg_fetch_result($rsh,0,"hoy");
		$txtplano='';	
		$sqlplano=pg_query($pila,"SELECT * FROM planoas400 WHERE estado=0 ORDER BY na");
		$nombre_archivo="C:/AppServ/www/tmp/planos/".$arc;
		$gestor=fopen($nombre_archivo,'a');
		for($j=0;$j<pg_num_rows($sqlplano);$j++){
			$arrplano=pg_fetch_array($sqlplano,$j);
			$txtplano.=$arrplano['texto']."\r\n";
		}
		fwrite($gestor,$txtplano);
		fclose($gestor);
		$sqlplano1=pg_query($pila,"UPDATE planoas400 SET estado='3' WHERE planilla='$sr'");
		$sqlplano1=pg_query($pila,"INSERT INTO seg_planos(archivo,sr) VALUES('$arc',$sr)");
		$sqlplano1=pg_query($pila,"INSERT INTO planoas400_old select * from planoas400");
		$sqlplano1=pg_query($pila,"truncate table planoas400");
	}
}		

?>
