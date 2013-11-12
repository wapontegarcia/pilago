<?php
include("pila.php");
echo "inicia 1 de 2";
$user='sistema';
$hoy=date('Ymd');
$opc=1;//empresa no existe
$cnt=0;

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

$rs=pg_query($pila,"select sr from rezago where tiporezago='0' and estado in ('0') and codigo_rezago='$opc' group by sr");
for($i=0;$i<pg_num_rows($rs);$i++){
	ini_set("max_execution_time","25");
	flush();
	$canregs=pg_num_rows($rs);
    echo $cont." de $canregs"."<br>";;
	$cont++;
	
	$arrd = pg_fetch_array($rs,$i);
	$rs2=pg_query($pila,"select * from rezago where tiporezago='0' and estado='0' and sr='$arrd[sr]' and codigo_rezago='$opc'");
	for($j=0;$j<pg_num_rows($rs2);$j++){
		$arrc = pg_fetch_array($rs2,$j);
		echo $arrd['sr']."\n"; 
       	 	$querydev=pg_query($pila,"select * from enc_dev where sr='$arrc[sr]'");
        	$arr=pg_fetch_array($querydev);
		if ($arr['tipo_planilla']=='I')
			$tipo='2';
		else
			$tipo='1';
		if($arr['tipoid_empresa']=='NI') 	
			$tipoem='NIT';
		else
			$tipoem=$arr['tipoid_empresa'];
		$ciu=formato_num($arr['cod_departamento'],2).formato_num($arr['cod_ciudad'],3);
		$dep=formato_num($arr['cod_departamento'],2);
		$raz=trim($arr['razon_social']);
		$dir=trim($arr['direccion']);
		$rs3  = pg_query($pila,"insert into tblempleadoresliq (tipoidentif,identificacion,razonsocial,claseaportante,formapresent,totalafiliados,totalempleados,tipoempleador,dir,tel,fax,ciudad,departamento,numerodirecciones)
        values ('$tipoem','$arr[id_empresa]','$raz','$arr[clase_aportante]','$arr[presentacion]','$arr[afiliados]','$arr[empleados]','$tipo','$dir','$arr[telefono]','$arr[fax]','$ciu','$dep','1')");
		$cnt=$cnt+1;
	
		$rs5  = pg_query($pila,"update rezago set estado='2',fechaok='$hoy',usuario='$user' where sr='$arrc[sr]' and linea_det=$arrc[linea_det] and tiporezago='0' and estado in ('0','3') and codigo_rezago='$opc'");  
		
		$rs1=pg_query($pila,"select * from rezago where tiporezago='0' and estado in('1','0','3') and sr='$arrd[sr]'");
		if(pg_num_rows($rs1)==0){
			$cnt=$cnt+1;
			$rst = pg_query($pila,"update enc_dev set estado='1' where sr='$arrc[sr]'");
			$rst = pg_query($pila,"insert into enc_proc select * from enc_dev where sr='$arrc[sr]'");
			$rst = pg_query($pila,"insert into det_proc select * from det_dev where sr='$arrc[sr]'");
			$rst = pg_query($pila,"insert into total_proc select * from total_dev where sr='$arrc[sr]'");
			$rst = pg_query($pila,"delete from total_dev where sr='$arrc[sr]'");
			$rst = pg_query($pila,"delete from det_dev where sr='$arrc[sr]'");
			$rst = pg_query($pila,"delete from enc_dev where sr='$arrc[sr]'");
		}		
	}
}
?>
<br>
<?php
echo "FINALIZO MASIVO DE EMPLEADOR NO EXISTE";  
?>
