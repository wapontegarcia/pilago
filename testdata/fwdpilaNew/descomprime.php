<?php
error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);
ini_set('session.cache_expire','3000');
include("pila.php");

$hoy=date('Ymd');

echo date('h:i:s')."<h5>Inicia proceso de descomprime ...</h5>"."\n";
$path='C:/AppServ/www/tmp/pila/pila_'.$user.'/';

if($gestor=opendir($path)){
	while(false!==($archivo=readdir($gestor))){
		if(($archivo != ".")&&($archivo != "..")){
			//system("/usr/bin/unzip ".$path."/".$archivo ." -d  ".$path);
		}
	}
}
$droptb=pg_query($pila,"DROP TABLE plano2".$user."");
echo date('h:i:s')."<h5>Termina proceso de descomprime ...</h5>"."\n";
#agregamos funcion que recorre subdirectorios para traer los archivos
#contenidos dentro de ellos.
$ruta = "C:/AppServ/www/tmp/pila/pila_".$user."/";
$carpetas = verificaCarpetas($ruta);
if(count($carpetas)!=0){
	if(recorreCarpetas($carpetas,$ruta)){
		echo "Los archivos han sido movidos exitosamente";
	}
}else{
	echo "El directorio ".$ruta."/ no tiene subdirectorios\n";
}

system("/bin/chmod -R 777 /tmp/pila/pila_".$user."/");
echo date('h:i:s')."<h5>Inicia proceso borrado de archivos ...</h5>"."\n";
if($gestor=opendir($path)){
	while(false!==($archivo=readdir($gestor))){
		if(($archivo!=".")&&($archivo!="..")){
			if((substr($archivo,strlen($archivo)-5,1)=="A")||(substr($archivo,strlen($archivo)-6,1)=="A")||(substr($archivo,strlen($archivo)-7,1)=="A")){
				system("/bin/rm -f ".$path."/".$archivo);
			}
			if((substr($archivo,strlen($archivo)-13,1)=="A")||(substr($archivo,strlen($archivo)-14,1)=="A")){
				system("/bin/rm -f ".$path."/".$archivo);
			}
			if((substr($archivo,strlen($archivo)-3,3)=="zip")||(substr($archivo,strlen($archivo)-3,3)=="ZIP")){
				system("/bin/rm -f ".$path."/".$archivo);
			}
			if(substr($archivo,strlen($archivo)-14,2)=="IP"){
				$origen="C:/AppServ/www/tmp/pila/pila_".$user."";
				$destino="C:/AppServ/www/tmp/pila/pilamalos/pensionados/$archivo";
				$crea="cp '".$origen."' '".$destino."'";
				system($crea);
				system("/bin/rm -f ".$path."/".$archivo);
			}
		}
	}
}
echo date('h:i:s')."<h5>Termina proceso borrado de archivos ...</h5>"."\n";

$path='C:/AppServ/www/tmp/pila/pila_'.$user.'/';
$path1='C:/AppServ/www/tmp/pila/pila_'.$user.'/';

$sqlc = pg_query($pila,"CREATE TABLE plano2".$user." (
  path text,
  sr numeric(12,0) DEFAULT 0
);
ALTER TABLE plano2".$user." OWNER TO postgres;
CREATE INDEX idx_plano2".$user."  ON plano2".$user."
  USING btree (sr);
  ");
if($gestor=opendir($path)){
	while(false!==($archivo=readdir($gestor))){
		if(($archivo != ".")&&($archivo != "..")){
			if(strlen($archivo)>15){
				ini_set("max_execution_time","25");
				flush();
				//echo $archivo."\n"; 
				$planilla=explode("_",$archivo);
				$sqlc = pg_query($pila,"SELECT nextval('recaudo_encabezado_sr_seq') as val");
				$sr   = pg_fetch_result($sqlc,0,'val');
				//$sqlcopia="SET client_encoding = 'LATIN1';COPY plano2(path) FROM '$path1$archivo'";	
				$sqlcopia="COPY plano2".$user." (path) FROM '$path1$archivo'";	
				$rs=pg_query($pila,$sqlcopia);
				$sqlc = pg_query($pila,"update plano2".$user." set sr='$sr' where sr=0");
				$estr=0;
				$totalfinales=0;
				$est=pg_query($pila,"select * FROM plano2".$user." WHERE (substr(path,9,9)='800224827' or substr(path,9,9)='800229739') and substr(path,6,1)='1' and sr='$sr'");
				if(pg_num_rows($est)!=0){
					//valida la estructura tipo 2
					$rspu=pg_query($pila,"select * FROM plano2".$user." WHERE substr(path,168,1)<>'.' and substr(path,6,1)<>'1' and substr(path,6,1)<>'3' and sr='$sr'");
					if(pg_num_rows($rspu)!=0){
						$err = 'inconsistencia detalles';
						$estr=0;
					}else{
						$tot31=pg_query($pila,"SELECT * FROM plano2".$user." WHERE substr(path,6,1)='3' AND (substr(path,1,5)='00031' or substr(path,1,5)='00036' or substr(path,1,5)='00039') and sr='$sr'");
						if(pg_num_rows($tot31)==3){
							$estr=1;
						} else{
							$err = 'inconsistencia totales';
							$estr=0;
							$origen="C:/AppServ/www/tmp/pila/pila_".$user."/$archivo";
							$destino="C:/AppServ/www/tmp/pilamalos/estructura/$archivo";
							$crea="cp '".$origen."' '".$destino."'";
							system($crea);
							$borrar="rm -f C:/AppServ/www/tmp/pila/pila_".$user."/$archivo";
							system($borrar);
						}
					}
				} else{
					$err = 'inconsistencia encabezados';
					$estr=0; 
				}
				if($estr==1){
					$enc=pg_query($pila,"SELECT substr(path,226,2) as tipocc,substr(path,228,16) as id_empresa,substr(path,371,7) as periodo,substr(path,395,10) as fechapago, substr(path,415,10) as pl,substr(path,486,2) as ope FROM plano2".$user." WHERE substr(path,6,1)='1' and sr='$sr'");
					if(pg_num_rows($enc)!=0){
						$tot=pg_query($pila,"SELECT substr(path,1,5) as total_pagar,substr(path,6,1) as tipo_registro,substr(path,7,10) as total_cot,substr(path,17,10) as fsp,substr(path,27,10) as fspsub FROM plano2".$user." WHERE substr(path,6,1)='3' AND substr(path,1,5)='00039' and sr='$sr'");
						$arrenc=pg_fetch_array($enc);
						$nit = trim(pg_fetch_result($enc,0,'id_empresa'))*1;
						$pla = trim(pg_fetch_result($enc,0,'ope')).trim(pg_fetch_result($enc,0,'pl'));
						$pla = $pla * 1;
						$plann = trim(pg_fetch_result($enc,0,'pl'));
						$plann = $plann * 1;
						$per = substr(pg_fetch_result($enc,0,'periodo'),0,4).substr(pg_fetch_result($enc,0,'periodo'),5,2);           
						$fechap=substr(pg_fetch_result($enc,0,'fechapago'),0,4).substr(pg_fetch_result($enc,0,'fechapago'),5,2).substr(pg_fetch_result($enc,0,'fechapago'),8,2);   	
						$totalc=pg_fetch_result($tot,0,'total_cot') * 1;
						$fsp=pg_fetch_result($tot,0,'fsp') * 1;
						$fspsub=pg_fetch_result($tot,0,'fspsub') * 1;
						
					    //echo $nit."====".$pla."====".$plann."====".$per."====".$fechap."====".$totalc."====".$fsp."====".$fspsub."====";

						$radi=pg_query($pila,"select * from radicado where id_empresa='$nit' and periodo='$per' and fechapago='$fechap' and total_cot='$totalc' and fsp='$fsp' and fspsub='$fspsub' and planilla='$plann'");
						if(pg_num_rows($radi)==0){
							$ope  = $arrenc['ope']; //trim(pg_fetch_result($enc,0,'ope'));
							//Es la tabla donde se inserta cuando la estructura esta defectuosa
							$devv=pg_query($pila,"select * from pila_devueltas where id_empresa='$nit' * 1 and fechapago='$fechap' and estado='D' and planilla='$plann' and operador='$ope'");
							if(pg_num_rows($devv)!=0){
								$devv=pg_query($pila,"update pila_devueltas set estado='C',fechacargue='$hoy' where id_empresa='$nit' and fechapago='$fechap' and estado='D' and planilla='$plann' and operador='$ope'");
							}
							$ori="$path$archivo";
							$des="$path".$sr.".txt";
							$reemplazar=rename($ori,$des); 
							system ($reemplazar);
						}else{// el archivo ya se encuentra en radicado
							echo ("validar como se borra el archivo el archivo ya existe -- select * from radicado where id_empresa='$nit' and periodo='$per' and fechapago='$fechap' and total_cot='$totalc' and fsp='$fsp' and fspsub='$fspsub' and planilla='$plann'");
							//$borrar="/bin/rm -f /tmp/pila/$archivo";
							//system($borrar);
						}
					}
				}else{//$estr = 0
					$planilla=explode("_",$archivo);
					$ope=$planilla['6'];
					$pl=$planilla['2'];
					$fpag=explode("-",$archivo);
					$fp=$planilla['0']; 
					$fp = str_replace("-","",$fp);
					$plani=$ope.$pl;
					$nit=$planilla['4'];              
					$totalc=0;
					if($nit>0){
						$my_file = "%path$archivo";
						$file_size = filesize($my_file);
						if ($file_size== 0) {
							$des='Archivo vacio';
						}else{
							if ($err!='')
								$des=$err;
							else			
							$des='Error estructura encabezado';
						}
						$dv=pg_query($pila,"select * from pila_devueltas where planilla='$pl' and estado='D' and fechapago='$fp' and operador='$ope' and sr='0'");
						if(pg_num_rows($dv)==0){
							$dev=pg_query($pila,"insert into pila_devueltas(planilla,estado,fechapago,operador,descripcion,sr,id_empresa,valor) values('$pl','D','$fp','$ope','$des','0','$nit','$totalc')");

							$origen="C:/AppServ/www/tmp/pila/$archivo";
							$destino="C:/AppServ/www/tmp/pilamalos/estructura/$archivo";							
							die("validar como se hace le copy del archivo 1 -- select * from pila_devueltas where planilla='$pl' and estado='D' and fechapago='$fp' and operador='$ope' and sr='0'");
							//$crea="cp '".$origen."' '".$destino."'";
							//system($crea);
							//$borrar="/bin/rm -f /tmp/pila/$archivo";
							//system($borrar);
						}else{                  
							$origen="C:/AppServ/www/tmp/pila/$archivo";
							$destino="C:/AppServ/www/tmp/pilamalos/estructura/$archivo";							
							die("validar como se hace le copy del archivo 2 -- select * from pila_devueltas where planilla='$pl' and estado='D' and fechapago='$fp' and operador='$ope' and sr='0'");
							//$crea="cp '".$origen."' '".$destino."'";
							//system($crea);
							//$borrar="/bin/rm -f /tmp/pila/$archivo";
							//system($borrar);
						}
					}
				}
			}
		}
	}
}

echo date('h:i:s')."<h5>Termina proceso renombrar archivos ...</h5>"."\n";
//$destino="/tmp/pila/*";
//system("/bin/chmod -R 777 /tmp/pila");
echo "Comienza Lectura Archivo \n";
//include("lecturaarchivo.php");


function permisos($ruta){
	$cmd = "/bin/chmod 777 ".$ruta."/";
	//echo $cmd."<br><br>";
	system($cmd);
}
function borraCarpeta($ruta){
	$cmd = "/bin/rm -rf ".$ruta."/";
	//echo $cmd."<br>";
	system($cmd);
}

function mueveArchivos($org,$des){
	$cmd="mv -f ".$org." ".$des."/";
	//echo $cmd."<br><br>";
	system($cmd);
}

function verificaCarpetas($ruta){
	if($pila=opendir($ruta)){
	while(false!==($archvo=readdir($pila))){
		if($archvo!="." && $archvo!=".."){
			if(is_dir($ruta."/".$archvo)){
				$carpetas[] = $ruta."/".$archvo; 
			}
		}
	}
	return $carpetas;
}
}
function recorreCarpetas($carpetas,$destino){
	for($a=0;$a<count($carpetas);$a++){
		#asignamos permisos al subdirectorio
		permisos($carpetas[$a]);
		//die("'(");
		#recorremos carpeta y extraemos archivos de la misma
		if($recor=opendir($carpetas[$a])){
			//echo "<hr>La carpeta <b>".$carpetas[$a]."</b> tiene los siguientes archivos:<hr>";
			while(false!==($files=readdir($recor))){
				if($files!="." && $files != ".."){
					if(!is_dir($carpetas[$a]."/".$files)){
						#movemos los archivos a la ruta deseada
						$origen = $carpetas[$a]."/".$files;
						mueveArchivos($origen,$destino);
					}
				}
			}
		}
		#borramos el subdirectorio
		borraCarpeta($carpetas[$a]);
	}
	return true;
}
?>
