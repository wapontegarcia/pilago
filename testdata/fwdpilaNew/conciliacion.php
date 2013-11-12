<?php
error_reporting(0);
ini_set('display_errors', 0);
include("pila.php");

$rs  = pg_query($pila,"delete from plano1");
echo date('h:i:s')."inicia cargaC ...>"."<br>";
$dir = "C:/AppServ/www/tmp/conciliacion/1.txt";
$sql = "SET client_encoding = 'LATIN1';copy plano1 from '$dir'";
$rs  = pg_query($pila,$sql);

$rs  = pg_query($pila,"delete from plano1 where substr(path,1,1)='5'");
$rs  = pg_query($pila,"delete from plano1 where substr(path,1,1)='8'");
$rs  = pg_query($pila,"delete from plano1 where substr(path,1,1)='9'");

$sql=pg_query($pila,"SELECT substr(path,2,8) as fec FROM plano1 where substr(path,1,1)='1'");
$fecha=pg_fetch_result($sql, 0, "fec");
$rs  = pg_query($pila,"delete from plano1 where substr(path,1,1)='1'");

$sql=pg_query($pila,"SELECT * FROM plano1");

for($i=0;$i<pg_num_rows($sql);$i++){
	echo $i."=="."<br>";
	$arr = pg_fetch_array($sql,$i);
	$nit = substr($arr['path'],1,16) * 1;
	$operador=substr($arr['path'],70,2);
	$planilla = substr($arr['path'],41,15) * 1;
	$valor = substr($arr['path'],72,16) * 1;
	$va=$va+$valor;
	$periodo = substr($arr['path'],56,6);
	$conce = substr($arr['path'],95,5) * 1;
	$cuenta = '9999';
	$oficina = '9999'; 

	$rs=@pg_query($pila,"SET client_encoding = 'LATIN1';INSERT INTO tbllogbancario (codigobanco,cuenta,documentoid,fechapago,valorefectivo,codoperador,radicado,conciliado,valortotal,oficina,periodo,consecutivo,planilla) 
	values ('007',$cuenta,$nit,$fecha,$valor,$operador,$planilla,0,$valor,$oficina,$periodo,$conce,0)");
}
echo date('h:i:s')."termina carga ...>"."<br>";



create table tbllogbancario (
	id serial primary key,
	codigobanco character varying(4)
	cuenta numeric,
	documentoid numeric,
	fechapago numeric,
	valorefectivo numeric,
	codoperador numeric,
	radicado numeric,
	conciliado numeric,
	valortotal numeric,
	oficina numeric,
	periodo numeric,
	consecutivo numeric,
	planilla numeric) 


