<?php
include("pila.php");
echo "inicia 2 de 2";
$hoy = date('Ymd');
$cnt = 0;
$opc = 12;
$rs=pg_query($pila,"select sr from rezago where tiporezago='0' and estado in ('0') and codigo_rezago in($opc) group by sr");
for($i=0;$i<pg_num_rows($rs);$i++){
	ini_set("max_execution_time","25");
	flush();
	$canregs=pg_num_rows($rs);
    echo $cont." de $canregs"."<br>";;
	$cont++;
	$arrd = pg_fetch_array($rs,$i);
	$rs2=pg_query($pila,"select * from rezago where tiporezago='0' and estado='0' and sr='$arrd[sr]' and codigo_rezago='$opc' ");
	for($j=0;$j<pg_num_rows($rs2);$j++){
		$arrc = pg_fetch_array($rs2,$j);
		$rs5  = pg_query($pila,"update rezago set estado='2',modifica='1',fechaok='$hoy',usuario='sistema' where sr='$arrc[sr]' and tiporezago='0' and estado in ('0') and codigo_rezago='$opc'");
		$cnt=$cnt+1;
	}
	$rs1=pg_query($pila,"select * from rezago where tiporezago='0' and modifica=0 and estado in('0','1','3') and sr='$arrd[sr]'");
	if(pg_num_rows($rs1)==0){ 
		$rst = pg_query($pila,"update enc_dev set estado='1' where sr='$arrc[sr]'");
		$rst = pg_query($pila,"insert into enc_proc select * from enc_dev where sr='$arrc[sr]'");
		$rst = pg_query($pila,"insert into det_proc select * from det_dev where sr='$arrc[sr]'");
		$rst = pg_query($pila,"insert into total_proc select * from total_dev where sr='$arrc[sr]'");
		$rst = pg_query($pila,"delete from total_dev where sr='$arrc[sr]'");
		$rst = pg_query($pila,"delete from det_dev where sr='$arrc[sr]'");
		$rst = pg_query($pila,"delete from enc_dev where sr='$arrc[sr]'");
	} 
}
?>
<br>
<?php
echo "FINALIZO MASIVO DE REZAGO $opc, $cnt REGISTROS"."\n"; ?>
