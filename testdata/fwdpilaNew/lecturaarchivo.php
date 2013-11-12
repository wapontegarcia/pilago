<?php
ini_set('session.cache_expire','3000');
include("pila.php");

$txterr="";
$rsh=pg_query($pila,"select to_char(now(),'YYYYMMDD') as hoy");
$hoy=pg_fetch_result($rsh, 0, "hoy");

$disco1 = '/app/nss3/IMG6/img26';

function comprobar_email($email){
	$mail_correcto = 0;
	//compruebo unas cosas primeras
	if ((strlen($email) >= 6) && (substr_count($email,"@") == 1) && (substr($email,0,1) != "@") && (substr($email,strlen($email)-1,1) != "@")){
		if ((!strstr($email,"'")) && (!strstr($email,"\"")) && (!strstr($email,"\\")) && (!strstr($email,"\$")) && (!strstr($email," "))) {
		//miro si tiene caracter .
			if (substr_count($email,".")>= 1){
				//obtengo la terminacion del dominio
				$term_dom = substr(strrchr ($email, '.'),1);
				//compruebo que la terminación del dominio sea correcta
				if (strlen($term_dom)>1 && strlen($term_dom)<5 && (!strstr($term_dom,"@")) ){
					//compruebo que lo de antes del dominio sea correcto
					$antes_dom = substr($email,0,strlen($email) - strlen($term_dom) - 1);
					$caracter_ult = substr($antes_dom,strlen($antes_dom)-1,1);
					if ($caracter_ult != "@" && $caracter_ult != "."){
						$mail_correcto = 1;
					}
				}
			}
		}
	}
	if ($mail_correcto) return 0;
	else return 1;
}

$cnt=0;
$cnt1=0;
//$user='sistema';
$fechasystem = date('Ymd');


$dir ='C:/AppServ/www/tmp/pila/';
$dir1='C:/AppServ/www/tmp/pila/';

echo date('h:i:s')."Procesando Archivos ..."."\n";
$cont=0;
$sqlenc=pg_query($pila,"SELECT sr FROM plano2".$user." group by sr");
$droptb=@pg_query($pila,"CREATE TEMPORARY TABLE recaudo_encabezado".$user."
(
  sr numeric,
  nro_registro numeric(5,0),
  tipo_registro numeric(1,0),
  cod_formato numeric(2,0),
  razon_social_afp character varying(40),
  num_id_afp numeric(16,0),
  dv_afp numeric(1,0),
  razon_social character varying(200),
  tipo_id_emp character varying(2),
  num_id_aportante character varying(16),
  dv_aportante character varying(1) DEFAULT 0,
  clase_aportante character varying(1),
  direccion character varying(40),
  ciudad character varying(15),
  cod_ciudad numeric(3,0),
  departamento character varying(15),
  cod_departamento numeric(2,0),
  telefono character varying(10),
  fax character varying(10),
  mail character varying(60),
  periodo character varying(7),
  fecha_pago character varying(10),
  planilla character varying(15),
  presentacion character varying(1),
  cod_sucursal character varying(10),
  nombre_sucursal character varying(40),
  empleados numeric(5,0),
  afiliados numeric(5,0),
  operador numeric(2,0),
  tipo_aportante character varying(1),
  codigo_arp character varying(6),
  tipo_planilla character varying(1),
  fecha_pago_asociada character varying(10),
  planilla_asociada character varying(10),
  modalidad numeric(1,0),
  dias_mora numeric(4,0),
  registros_tipo2 numeric(8,0)
);
ALTER TABLE recaudo_encabezado".$user." OWNER TO postgres;");
	$droptb=@pg_query($pila,"CREATE TEMPORARY TABLE recaudo_detalles".$user."
(
  sr numeric,
  nro_registro numeric(5,0),
  tipo_registro numeric(1,0),
  tipo_id character varying(2),
  cedula character varying(16),
  tipo_cotizante numeric(2,0),
  subtipo_cotizante numeric(2,0),
  extranjero character varying(1),
  col_exterior character varying(1),
  cod_departamento numeric(2,0),
  cod_municipio numeric(3,0),
  primer_apellido character varying(20),
  segundo_apellido character varying(30),
  primer_nombre character varying(20),
  segundo_nombre character varying(30),
  ing character varying(1),
  ret character varying(1),
  tdp character varying(1),
  tap character varying(1),
  vsp character varying(1),
  vte character varying(1),
  vst character varying(1),
  sln character varying(1),
  ige character varying(1),
  lma character varying(1),
  vac character varying(1),
  avp character varying(1),
  dias numeric(2,0),
  salario numeric(9,0),
  valor_neto numeric(9,0),
  ibc numeric(9,0),
  tarifa numeric(7,0),
  cotizacion_obli numeric(9,0),
  cotizacion_voluntaria_af numeric(9,0),
  cotizacion_voluntaria_aportante numeric(9,0),
  total_cot numeric(9,0),
  fsp numeric(9,0),
  afsp numeric(9,0),
  valor_no_retenido numeric(9,0),
  correcciones character varying(1),
  salario_integral character varying(1)
);
ALTER TABLE recaudo_detalles".$user." OWNER TO postgres;");
	$droptb=@pg_query($pila,"CREATE TEMPORARY TABLE recaudo_detalles_31".$user."
(
  sr numeric,
  total_aportes numeric(5,0),
  tipo_registro numeric(1,0),
  ibc numeric(10,0),
  cotizacion_obli numeric(10,0),
  cotizacion_voluntaria_af numeric(10,0),
  cotizacion_voluntaria_aportante numeric(10,0),
  total_cot numeric(10,0),
  fsp numeric(10,0),
  fspsub numeric(10,0),
  dias_mora numeric(4,0),
  mora_cotizaciones numeric(10,0),
  mora_fsp numeric(10,0),
  mora_fspsub numeric(10,0)
);
ALTER TABLE recaudo_detalles_31".$user." OWNER TO postgres;
");
	$droptb=@pg_query($pila,"CREATE TEMPORARY TABLE recaudo_detalles_36".$user."
(
  sr numeric,
  intereses numeric(5,0),
  tipo_registro numeric(1,0),
  ibc numeric(10,0),
  cotizacion_obli numeric(10,0),
  cotizacion_voluntaria_af numeric(10,0),
  cotizacion_voluntaria_aportante numeric(10,0),
  total_cot numeric(10,0),
  fsp numeric(10,0),
  fspsub numeric(10,0),
  dias_mora numeric(4,0),
  mora_cotizaciones numeric(10,0),
  mora_fsp numeric(10,0),
  mora_fspsub numeric(10,0)
);
ALTER TABLE recaudo_detalles_36".$user." OWNER TO postgres;
");
	$droptb=@pg_query($pila,"CREATE TEMPORARY TABLE recaudo_detalles_39".$user."
(
  sr numeric,
  total_apagar numeric(5,0),
  tipo_registro numeric(1,0),
  ibc numeric(10,0),
  cotizacion_obli numeric(10,0),
  cotizacion_voluntaria_af numeric(10,0),
  cotizacion_voluntaria_aportante numeric(10,0),
  total_cot numeric(10,0),
  fsp numeric(10,0),
  fspsub numeric(10,0)
);
ALTER TABLE recaudo_detalles_39".$user." OWNER TO postgres;
");
//$sqlenc=pg_query($pila,"SELECT sr FROM plano2 where sr ='4548' group by sr");
for($i=0;$i<pg_num_rows($sqlenc);$i++){
	ini_set("max_execution_time","25");
	flush();
	$canregs=pg_num_rows($sqlenc);
	echo $cont." de ". $canregs."<br>";;
	$cont++;

	$arr=pg_fetch_array($sqlenc,$i);	
	//echo date('h:i:s')."Crea tablas ...>"."\n";
	$droptb=@pg_query($pila,"truncate recaudo_encabezado".$user."");
	$droptb=@pg_query($pila,"Dtruncate recaudo_detalles".$user."");
	$droptb=@pg_query($pila,"truncate recaudo_detalles_31".$user."");
	$droptb=@pg_query($pila,"truncate recaudo_detalles_36".$user."");
	$droptb=@pg_query($pila,"truncate recaudo_detalles_39".$user."");
	
	//echo "inicio  ".date('h:i:s')." archivo oo oo ==>>>> \n";
	$enc=pg_query($pila,"SELECT substr(path,226,2) as tipocc,substr(path,228,16) as id_empresa,substr(path,371,7) as periodo,substr(path,395,10) as fechapago, substr(path,415,10) as pl,substr(path,486,2) as ope FROM plano2".$user." WHERE substr(path,6,1)='1' and sr='$arr[sr]'");
	if (pg_num_rows($enc)==1){
		//echo date('h:i:s')."into a las tablas locales ...>"."\n";
		$rs=pg_query($pila,"INSERT INTO recaudo_encabezado".$user."  (sr,nro_registro,tipo_registro,cod_formato,num_id_afp,dv_afp,razon_social,tipo_id_emp,			num_id_aportante,dv_aportante,direccion,cod_ciudad,cod_departamento,telefono,fax,mail,periodo,fecha_pago,planilla,presentacion,cod_sucursal,nombre_sucursal,empleados,afiliados,operador,tipo_aportante,codigo_arp,tipo_planilla,fecha_pago_asociada,planilla_asociada,dias_mora,modalidad,registros_tipo2)
		select '$arr[sr]',substr(path,1,5)::numeric as nro_registro,substr(path,6,1)::numeric as tipo_registro,substr(path,7,2)::numeric as cod_formato,substr(path,9,16)::numeric as num_id_afp,substr(path,25,1)::numeric as digito,substr(path,26,200) as razon_social,substr(path,226,2) as tipo_identificacion,trim(substr(path,228,16)) as identificacion_aportante,trim(substr(path,244,1)) as digito_aportante,substr(path,246,40) as direccion,substr(path,286,3)::numeric as cod_ciudad,substr(path,289,2)::numeric as cod_departamento,substr(path,291,10) as telefono,substr(path,301,10) as fax,substr(path,311,60) as mail,substr(path,371,7) as periodo,substr(path,395,10) as fecha_pago,trim(substr(path,415,10)) as planilla,substr(path,425,1) as forma_presentacion,substr(path,426,10) as codigo_sucursal,substr(path,436,40) as nombre_sucursal,substr(path,476,5)::numeric as total_empleados,substr(path,481,5)::numeric as afiliados,substr(path,486,2)::numeric as operador,substr(path,245,1) as tipo_aportante,substr(path,378,6) as arp,substr(path,384,1) as tipo_planilla,substr(path,385,10) as fecha_pago_asociada,substr(path,405,10) as planilla_asociada,substr(path,489,4)::numeric as dias_mora,substr(path,488,1)::numeric as modalidad,substr(path,493,8)::numeric as registros_tipo2 from plano2".$user." WHERE substr(path,6,1)='1' and sr='$arr[sr]'");
		//tipo 2
		$rs=pg_query($pila,"INSERT INTO recaudo_detalles".$user."  (sr,nro_registro,tipo_registro,tipo_id,cedula,tipo_cotizante,subtipo_cotizante,extranjero,col_exterior,cod_departamento,cod_municipio,primer_apellido,segundo_apellido,primer_nombre,segundo_nombre,ing,ret,tdp,tap,vsp,vst,sln,ige,lma,vac,avp,dias,salario,valor_neto,ibc,tarifa,cotizacion_obli,cotizacion_voluntaria_af,cotizacion_voluntaria_aportante,total_cot,fsp,afsp,	valor_no_retenido,correcciones,salario_integral)
		select '$arr[sr]',substr(path,1,5)::numeric as nro_registro,substr(path,6,1)::numeric as tipo_registro,trim(substr(path,7,2)) as tipo_documento,trim(substr(path,9,16)) as id_afiliado,substr(path,25,2)::numeric as tipo_cotizante,substr(path,27,2)::numeric as subtipo_cotizante,trim(substr(path,29,1)) as extanjero,trim(substr(path,30,1)) as col_exterior,substr(path,31,2)::numeric as cod_departamento,substr(path,33,3)::numeric as cod_municipio,upper(trim(substr(path,36,20))) as primer_apellido,upper(trim(substr(path,56,30))) as segundo_apellido,upper(trim(substr(path,86,20))) as primer_nombre,upper(substr(path,106,30)) as segundo_nombre,upper(trim(substr(path,136,1))) as ing,upper(trim(substr(path,137,1))) as ret,upper(trim(substr(path,138,1))) as tdp,upper(trim(substr(path,139,1))) as tap,upper(trim(substr(path,140,1))) as vsp,upper(trim(substr(path,141,1))) as vst,upper(trim(substr(path,142,1))) as sln,upper(trim(substr(path,143,1))) as ige,upper(trim(substr(path,144,1))) as lma,upper(trim(substr(path,145,1))) as vac,upper(trim(substr(path,146,1))) as avp,substr(path,147,2)::numeric as dias,substr(path,149,9)::numeric as salario,0,substr(path,158,9)::numeric as ibc,substr(path,169,5)::numeric as tarifa,substr(path,174,9)::numeric as cotizacion_obligatoria,substr(path,183,9)::numeric as cotizacion_voluntaria_af,substr(path,192,9)::numeric as cotizacion_voluntaria_aportante,substr(path,201,9)::numeric as total_cot,substr(path,210,9)::numeric as fsp,substr(path,219,9)::numeric as afsp,substr(path,228,9)::numeric as valor_no_retenido,substr(path,237,1) as correcciones,substr(path,238,1) as salario_integral FROM plano2".$user." WHERE substr(path,6,1)='2' and sr='$arr[sr]'");
		//tipo 31
		$rs=pg_query($pila,"INSERT INTO recaudo_detalles_31".$user."  (sr,total_aportes,tipo_registro,ibc,cotizacion_obli,cotizacion_voluntaria_af,cotizacion_voluntaria_aportante,total_cot,fsp,fspsub)
		SELECT '$arr[sr]',substr(path,1,5)::numeric,substr(path,6,1)::numeric,substr(path,7,10)::numeric,substr(path,17,10)::numeric,substr(path,27,10)::numeric,substr(path,37,10)::numeric,substr(path,47,10)::numeric,substr(path,57,10)::numeric,substr(path,67,10)::numeric FROM plano2".$user."  WHERE substr(path,6,1)='3' AND substr(path,1,5)='00031' and sr='$arr[sr]'");
		//tipo 36
		$rs=pg_query($pila,"INSERT INTO recaudo_detalles_36".$user."   (sr,intereses,tipo_registro,ibc,cotizacion_obli,cotizacion_voluntaria_af,cotizacion_voluntaria_aportante,total_cot,fsp,fspsub,dias_mora,mora_cotizaciones,mora_fsp,mora_fspsub)
		SELECT '$arr[sr]',substr(path,1,5)::numeric as intereses,substr(path,6,1)::numeric as tipo_registro,0,0,0,0,0,0,0,substr(path,7,4)::numeric as dias_mora,substr(path,11,10)::numeric as mora_cotizaciones,substr(path,21,10)::numeric as mora_fsp,substr(path,31,10)::numeric as morafspsub FROM plano2".$user." WHERE substr(path,6,1)='3' AND substr(path,1,5)='00036' and sr='$arr[sr]'");
		//tipo 39
		$rs=pg_query($pila,"INSERT INTO recaudo_detalles_39".$user." (sr,total_apagar,tipo_registro,ibc,cotizacion_obli,cotizacion_voluntaria_af,			cotizacion_voluntaria_aportante,total_cot,fsp,fspsub)
		SELECT '$arr[sr]',substr(path,1,5)::numeric as total_pagar,substr(path,6,1)::numeric as tipo_registro,0,0,0,0,substr(path,7,10)::numeric as total_cot,substr(path,17,10)::numeric as fsp,substr(path,27,10)::numeric as fspsub FROM plano2".$user." WHERE substr(path,6,1)='3' AND substr(path,1,5)='00039' and sr='$arr[sr]'");
		//INSERTA REGISTRO EN TABLAS PRINCIPALES
		//echo date('h:i:s')."<h5>Inicia select e into en radicado  ...</h5>"."\n";	
		$rs=pg_query($pila,"SELECT e.planilla,e.afiliados,e.num_id_aportante,e.tipo_id_emp,e.periodo,t.total_cot,t.fsp,t.fspsub,e.fecha_pago,e.sr,e.tipo_planilla FROM recaudo_encabezado".$user." e, recaudo_detalles_39".$user." t WHERE e.sr=t.sr");
		//echo date('h:i:s')."<h5>Termina select  ...</h5>"."\n";
		$arrrs=pg_fetch_array($rs);
		$periodo=substr($arrrs['periodo'],0,4).substr($arrrs['periodo'],5,2);
		$fechapago=substr($arrrs['fecha_pago'],0,4).substr($arrrs['fecha_pago'],5,2).substr($arrrs['fecha_pago'],8,2);
		//Inserta Registro en Tabla de Control RADICADO
		//echo date('h:i:s')."<h5>Inicia Into radicado ...</h5>"."\n";
		$radi=pg_query($pila,"select * from radicado where id_empresa='$arrrs[2]' and periodo='$periodo' and fechapago='$fechapago' and total_cot='$arrrs[5]' and fsp='$arrrs[6]' and fspsub='$arrrs[7]' and planilla=$arrrs[0]");
		if(pg_num_rows($radi)==0){
			$rs1=pg_query($pila,"INSERT INTO radicado(planilla,no_afiliados,id_empresa,tipoid_empresa,periodo,total_cot,fsp,fspsub,fechapago,sr,tipo_planilla)
			VALUES($arrrs[0],$arrrs[1],'$arrrs[2]','$arrrs[3]',$periodo,'$arrrs[5]','$arrrs[6]','$arrrs[7]',$fechapago,'$arrrs[9]','$arrrs[10]')");
		}else{
			echo "Ya registrada en radicado, planilla: ". $arrrs[0]. " id_empresa: ".$arrrs[2];
		}
		//echo date('h:i:s')."<h5>Termina select  e Into en radicado ...</h5>"."\n";
		//Inserta Encabezado
		$rs=pg_query($pila,"SELECT * FROM recaudo_encabezado".$user." ORDER BY sr");
		for($j=0;$j<pg_num_rows($rs);$j++){
			$arrenc=pg_fetch_array($rs,$j);
			//$periodo=substr($arr['periodo'],0,4).substr($arr['periodo'],5,2);
			//$fechapago=substr($arr['fecha_pago'],0,4).substr($arr['fecha_pago'],5,2).substr($arr['fecha_pago'],8,2);
			//echo date('h:i:s')."<h5>Inicia Into enc_proc ...</h5>"."\n";	      
			$sql1=pg_query($pila,"INSERT INTO enc_proc(planilla,nro_registro,tipo_registro,cod_formato,razon_social_afp,num_id_afp,dv_afp,razon_social,tipoid_empresa,id_empresa,dv_aportante,clase_aportante,direccion,ciudad,cod_ciudad,departamento,cod_departamento,telefono,fax,mail,periodo,fechapago,presentacion,cod_sucursal,nombre_sucursal,empleados,afiliados,operador,usuario,estado,sr,tipo_aportante,codigo_arp,tipo_planilla,fecha_pago_asociada,planilla_asociada,modalidad,dias_mora,registros_tipo2)
			VALUES($arrenc[planilla],$arrenc[nro_registro],$arrenc[tipo_registro],$arrenc[cod_formato],'$arrenc[razon_social_afp]',$arrenc[num_id_afp],$arrenc[dv_afp],'$arrenc[razon_social]','$arrenc[tipo_id_emp]','$arrenc[num_id_aportante]',$arrenc[dv_aportante],'$arrenc[clase_aportante]','$arrenc[direccion]','$arrenc[ciudad]',$arrenc[cod_ciudad],'$arrenc[departamento]',$arrenc[cod_departamento],'$arrenc[telefono]','$arrenc[fax]','$arrenc[mail]','$periodo',$fechapago,'$arrenc[presentacion]','$arrenc[cod_sucursal]','$arrenc[nombre_sucursal]',$arrenc[empleados],$arrenc[afiliados],$arrenc[operador],'sistema',0,$arrenc[sr],'$arrenc[tipo_aportante]','$arrenc[codigo_arp]','$arrenc[tipo_planilla]','$arrenc[fecha_pago_asociada]','$arrenc[planilla_asociada]','$arrenc[modalidad]','$arrenc[dias_mora]','$arrenc[registros_tipo2]')");
			//echo date('h:i:s')."<h5>Termina Into enc_proc ...</h5>"."\n";	      
			//Inserta Detalles
			//echo date('h:i:s')."<h5>Inicia select det_proc ...</h5>"."\n";
			$sqd=pg_query($pila,"SELECT * FROM recaudo_detalles".$user." WHERE sr='$arr[sr]' and total_cot=0 ORDER BY sr,nro_registro");
			$totaldetalle=0;
			for($d=0;$d<pg_num_rows($sqd);$d++){
				echo "entro a total_cot sr" . $arr['sr'];
				$arrrdet=pg_fetch_array($sqd,$d);
				$totaldetalle=$arrrdet['cotizacion_obli']+$arrrdet['cotizacion_voluntaria_af']+$arrrdet['cotizacion_voluntaria_aportante'];
				$sqdeta=pg_query($pila,"update recaudo_detalles".$user." set total_cot=$totaldetalle where sr='$arr[sr]' and nro_registro='$arrrdet[nro_registro]'");
			}
			//echo date('h:i:s')."<h5>Termina selectc det_proc ...</h5>"."\n";
			//echo date('h:i:s')."<h5>Inicia Into det_proc ...</h5>"."\n";
			$sql1=pg_query($pila,"SELECT * FROM recaudo_detalles".$user." WHERE sr='$arr[sr]' ORDER BY sr,nro_registro");
			for($j=0;$j<pg_num_rows($sql1);$j++){
				$arr1=pg_fetch_array($sql1,$j);
				$sql2=pg_query($pila,"INSERT INTO det_proc(planilla,linea_det,tipo_registro,tipoid_afiliado,id_afiliado,tipo_cotizante,subtipo_cotizante,extranjero,col_exterior,cod_departamento,cod_municipio,primer_apellido,segundo_apellido,primer_nombre,segundo_nombre,ing,ret,tdp,tap,vsp,vte,vst,sln,ige,lma,vac,avp,dias,salario,valor_neto,ibc,tarifa,cotizacion_obli,cotizacion_voluntaria_af,cotizacion_voluntaria_aportante,total_cot,fsp,afsp,valor_no_retenido,sr,correcciones,salario_integral)
				VALUES($arrenc[planilla],$arr1[nro_registro],$arr1[tipo_registro],'$arr1[tipo_id]','$arr1[cedula]',$arr1[tipo_cotizante],$arr1[subtipo_cotizante],'$arr1[extranjero]','$arr1[col_exterior]',$arr1[cod_departamento],$arr1[cod_municipio],'$arr1[primer_apellido]','$arr1[segundo_apellido]','$arr1[primer_nombre]','$arr1[segundo_nombre]','$arr1[ing]','$arr1[ret]','$arr1[tdp]','$arr1[tap]','$arr1[vsp]','$arr1[vte]','$arr1[vst]','$arr1[sln]','$arr1[ige]','$arr1[lma]','$arr1[vac]','$arr1[avp]',$arr1[dias],$arr1[salario],'$arr1[valor_neto]','$arr1[ibc]',$arr1[tarifa],$arr1[cotizacion_obli],$arr1[cotizacion_voluntaria_af],$arr1[cotizacion_voluntaria_aportante],$arr1[total_cot],$arr1[fsp],$arr1[afsp],$arr1[valor_no_retenido],$arr1[sr],'$arr1[correcciones]','$arr1[salario_integral]')");
			}
			//echo date('h:i:s')."<h5>Termina Into det_proc ...</h5>"."\n";	      
			//echo date('h:i:s')."<h5>Inicia Into recaudo_detalles_31".$user." ...</h5>"."\n";
			//Inserta Totales
			$sql1=pg_query($pila,"SELECT * FROM recaudo_detalles_31".$user." WHERE sr='$arr[sr]' ORDER BY sr");
			$arr1=pg_fetch_array($sql1);
			$sql2=pg_query($pila,"INSERT INTO total_proc(planilla,linea_total,total_aportes,tipo_registro,ibc,cotizacion_obli,cotizacion_voluntaria_af,cotizacion_voluntaria_aportante,total_cot,fsp,fspsub,sr)
			VALUES($arrenc[planilla],31,$arr1[total_aportes],$arr1[tipo_registro],$arr1[ibc],$arr1[cotizacion_obli],$arr1[cotizacion_voluntaria_af],$arr1[cotizacion_voluntaria_aportante],$arr1[total_cot],$arr1[fsp],$arr1[fspsub],$arr1[sr])");
			//echo date('h:i:s')."<h5>Termina Into recaudo_detalles_31".$user." ...</h5>"."\n";	      
			//echo date('h:i:s')."<h5>Inicia Into recaudo_detalles_36".$user." ...</h5>"."\n";
			$sql1=pg_query($pila,"SELECT * FROM recaudo_detalles_36".$user." WHERE sr='$arr[sr]' ORDER BY sr");
			$arr1=pg_fetch_array($sql1);
			$sql2=pg_query($pila,"INSERT INTO total_proc(planilla,linea_total,total_aportes,tipo_registro,ibc,cotizacion_obli,cotizacion_voluntaria_af,cotizacion_voluntaria_aportante,total_cot,fsp,fspsub,sr,dias_mora,mora_cotizaciones,mora_fsp,mora_fspsub)
			VALUES('$arrenc[planilla]',36,'$arr1[intereses]','$arr1[tipo_registro]','$arr1[ibc]','$arr1[cotizacion_obli]','$arr1[cotizacion_voluntaria_af]','$arr1[cotizacion_voluntaria_aportante]','$arr1[total_cot]','$arr1[fsp]','$arr1[fspsub]','$arr1[sr]','$arr1[dias_mora]','$arr1[mora_cotizaciones]','$arr1[mora_fsp]','$arr1[mora_fspsub]')");
			//echo date('h:i:s')."<h5>Termina Into recaudo_detalles_36".$user." ...</h5>"."\n";
			//echo date('h:i:s')."<h5>Inicia Into recaudo_detalles_39".$user." ...</h5>"."\n";
			$sql1=pg_query($pila,"SELECT * FROM recaudo_detalles_39".$user." WHERE sr='$arr[sr]' ORDER BY sr");
			$arr1=pg_fetch_array($sql1);
			$sql2=pg_query($pila,"INSERT INTO total_proc(planilla,linea_total,total_aportes,tipo_registro,ibc,cotizacion_obli,cotizacion_voluntaria_af,cotizacion_voluntaria_aportante,total_cot,fsp,fspsub,sr)
			VALUES('$arrenc[planilla]',39,'$arr1[total_apagar]','$arr1[tipo_registro]','$arr1[ibc]','$arr1[cotizacion_obli]',
			'$arr1[cotizacion_voluntaria_af]','$arr1[cotizacion_voluntaria_aportante]','$arr1[total_cot]','$arr1[fsp]','$arr1[fspsub]','$arr1[sr]')");
			//echo date('h:i:s')."<h5>Termina Into recaudo_detalles_39".$user." ...</h5>"."\n";	      
			//Inserta registro en Imagenes 
			$path='https://imagine/imagen26/pia/'.$hoy.'/00000001/'.$arr['sr'].'.txt';
			//echo date('h:i:s')."<h5>Inicia into imagenes ...</h5>"."\n";	      
			$rs=pg_query($pila,"INSERT INTO pp(cg,pn,ei,fp,ip,lo,lt,gf,pi)
			VALUES('55',$arr[sr],'$arrenc[num_id_aportante]','$hoy','$arrenc[tipo_id_emp]','Archivo txt planilla PO','1','55','$path')");
			//echo date('h:i:s')."<h5>Ternima into imagenes ...</h5>"."\n";	      
			$planilla1=trim($arr['sr']).".txt";
			/*
			//echo date('h:i:s')."<h5>Inicia copia imagenes ...</h5>"."\n";	      
			$origen="/tmp/pila/$planilla1";
			$destino="/$disco1/pia/$hoy/00000001/$planilla1";
			$crea="cp $origen $destino";
			system($crea);
			$borrar="/bin/rm -f /tmp/pila/$planilla1";
			system($borrar);
			*/
			//echo date('h:i:s')."<h5>Termina copia imagenes ...</h5>"."\n";	      
			//Inserta Registro en tabla de correo para envio de notificaciones
			$medio=comprobar_email(trim($arrenc['mail']));
			if($medio==0){
				$mailcorreo=trim($arrenc['mail']);
				$sql1=pg_query($pila,"delete from pila_correos WHERE tipoid_empresa='$arrenc[tipo_id_emp]' AND id_empresa='$arrenc[num_id_aportante]' ");
				//$sql1=pg_query($pila,"SELECT * FROM pila_correos WHERE tipoid_empresa='$arr[tipo_id_emp]' AND id_empresa='$arr[num_id_aportante]' AND mail='$mailcorreo' AND gabinete=2");
				//if(pg_num_rows($sql1)==0){
				//echo date('h:i:s')."<h5>Inicia INTO pila_correos ...</h5>"."\n";		
				$sql1=pg_query($pila,"INSERT INTO pila_correos(tipoid_empresa,id_empresa,mail,direccion,telefono,fax)
				VALUES('$arrenc[tipo_id_emp]','$arrenc[num_id_aportante]','$mailcorreo','$arrenc[direccion]','$arrenc[telefono]','$arrenc[fax]')");
				//echo date('h:i:s')."<h5>Termina INTO pila_correos ...</h5>"."\n";		  
				//}
			}
		}
	    echo "fin  ".date('h:i:s')." - \n";
	}
}
//validar esto
//$sql=pg_query($pila,"delete from pila_correos where trim(mail)=''");
echo date('h:i:s')."TERMINO PROCESAMIENTO ARCHIVOS PILA..."."\n";
//$nombre_archivo="resultado.txt";
//$gestor=@fopen($nombre_archivo,'w+');
//@fwrite($gestor,$txterr);
//@fclose($gestor);
//include("acreditacion.php");
?>
