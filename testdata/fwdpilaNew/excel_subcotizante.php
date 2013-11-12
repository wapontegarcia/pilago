<?php
include_once("pila.php");
$query=pg_query($pila,"select sr from rezago where codigo_rezago='13' and estado='0' order by sr");
if(pg_num_rows($query)!=0){
	$archivo = 'Sub Cotizante.xls';
	header("Content-Type: application/vnd.ms-excel");
	header("Content-Disposition: attachment;filename=$archivo");
	$abrir=fopen("$archivo", "r");
	fpassthru($abrir);
	?>
	<html>
	<body class='fondo'>
	<table  align='center'>
		<tr>
			<td class='celdat' align='center'>Tipo id Afiliado</td>
			<td class='celdat' align='center'>id Afiliado</td>
			<td class='celdat' align='center'>primer_apellido</td>
			<td class='celdat' align='center'>segundo_apellido</td> 
			<td class='celdat' align='center'>primer_nombre</td>
			<td class='celdat' align='center'>segundo_nombre</td>
			<td class='celdat' align='center'>IBC</td>
			<td class='celdat' align='center'>periodo</td> 
			
		</tr>
	<?php	
	for($i=0;$i<pg_num_rows($query);$i++){		
		$sr = pg_fetch_result($query,$i,'sr');
		$periodo= pg_fetch_result($query,$i,'periodo');
		$querydev=pg_query($pila,"select tipoid_empresa,id_empresa,razon_social,telefono,direccion,cod_ciudad,cod_departamento from enc_dev where sr='$sr'");
		$arr=pg_fetch_array($querydev);
	?>
		<tr>
			<td><?php echo $arr['tipoid_afiliado'];?></td>  
			<td><?php echo $arr['id_afiliado'];?></td>
			<td><?php echo $arr['primer_apellido'];?></td>
			<td><?php echo $arr['segundo_apellido'];?></td>
			<td><?php echo $arr['primer_nombre'];?></td>
			<td><?php echo $arr['segundo_nombre'];?></td>
			<td><?php echo $arr['ibc'];?></td>
			<td><?php echo $periodo;?></td>
		</tr>
	<?php		
	}
	?>
	</body>
	<html>
<?php
}
?>
