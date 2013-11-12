<?
include("pila.php");

$sq = pg_query($pila,"select * from correosdefectuosos");

for($i=0;$i<pg_num_rows($sq);$i++){
	$correo = pg_fetch_result($sq,$i,'correo');
      	$sq1 = pg_query($pila,"select * from pila_correos where mail = '$correo'");
	if(pg_num_rows($sq1)!=0){
		pg_query($img009,"delete from pila_correos where mail = '$correo'");
	}
}
?>

