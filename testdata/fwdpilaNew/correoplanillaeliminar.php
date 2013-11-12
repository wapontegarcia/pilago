<?php
include("pila.php");
$enviados = 0;
$noenviados = 0;
$correo = '';

$rsh=pg_query($pila,"select to_char(now(),'YYYYMMDD') as hoy");
$hoy=pg_fetch_result($rsh, 0, "hoy");

$disco = '/app/nss3/IMG6/img26';
@mkdir("/$disco/cpu/",0777);
@mkdir("/$disco/cpu/$hoy",0777);
@mkdir("/$disco/cpu/$hoy/00000001/",0777);

$fecha = pg_query($pila,"select current_date as A");
$fecha = pg_fetch_result($fecha,0,'A');
$fecha = explode("-",$fecha);
$fecha = $fecha[0].$fecha[1].$fecha[2];

$rs_mail= pg_query($pila,"select * from pagounificadomail where fechaenvio='0' and medio=1 and gabinete=6 order by na desc");
//$rs_mail= pg_query($pila,"select * from pagounificadomail where  na=4216676");
$num  = pg_num_rows($rs_mail);
for($i=0;$i<$num;$i++){
	echo $i."\n";
	$planilla=trim(pg_fetch_result($rs_mail,$i,'planilla'));
	$nit = trim(pg_fetch_result($rs_mail,$i,'id_empresa')).'.pdf';
	$na = trim(pg_fetch_result($rs_mail,$i,'na'));
	$archivo=  $planilla.'-'.$nit;
	$path= "/mnt/firmas/".$archivo;
	if(file_exists($path)){
		$path=$disco."/cpu/".$hoy."/00000001/".$planilla."-".$nit;
		$rsmail=pg_query($pila,"update pagounificadomail SET fechaenvio = $fecha,path='$path',path1='' WHERE na='$na'");
		$origen="/mnt/firmas/".$planilla."-".$nit;
		$destino="/$disco/cpu/$hoy/00000001/$planilla-$nit";
		$crea="cp $origen $destino";
		system($crea);
		@unlink($origen);
		$enviados = $enviados + 1;
	}
}
//valido que el correo sea xxx@xxx.ciom
$sq = pg_query($pila,"select correo from correosdefectuosos");
for($j=0;$j<pg_num_rows($sq);$j++){
    $correodefec = pg_fetch_result($sq,$j,'correo');
    $rs_mail= pg_query($pila,"select * from pagounificadomail where fechaenvio='0' and medio=0 and gabinete=6  and mail='$correodefec' order by na");
    for($i=0;$i<pg_num_rows($rs_mail);$i++){
        echo $i."\n";
        $planilla=trim(pg_fetch_result($rs_mail,$i,'planilla'));
        $nit = trim(pg_fetch_result($rs_mail,$i,'id_empresa')).'.pdf';
        $na = trim(pg_fetch_result($rs_mail,$i,'na'));
        $archivo=  $planilla.'-'.$nit;
        $path= "/mnt/firmas/".$archivo;
        if(file_exists($path)){
            $path=$disco."/cpu/".$hoy."/00000001/".$planilla."-".$nit;
            $rsmail=pg_query($pila,"update pagounificadomail SET fechaenvio = $fecha,path='$path',path1='' WHERE na='$na'");
			$origen="/mnt/firmas/".$planilla."-".$nit;
			$destino="/$disco/cpu/$hoy/00000001/$planilla-$nit";
			$crea="cp $origen $destino";
			system($crea);
			@unlink($origen);
            $enviados = $enviados + 1;
         }
    }
}
$correook = "se actualizaron ".$enviados." correos"."\n";
echo $correook ;
?>
