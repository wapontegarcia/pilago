<?php
include_once("pila.php");
$query=pg_query($pila,"select sr from rezago where codigo_rezago='1' and estado='0' order by sr");
if(pg_num_rows($query)!=0){
	$archivo = 'Empresa No Existe.xls';
	header("Content-Type: application/vnd.ms-excel");
	header("Content-Disposition: attachment;filename=$archivo");
	$abrir=fopen("$archivo", "r");
	fpassthru($abrir);
	?>
	<html>
	<body class='fondo'>
	<table  align='center'>
		<tr>
			<td class='celdat' align='center'>Tipos id Empresa</td>
			<td class='celdat' align='center'>id Empresa</td>
			<td class='celdat' align='center'>Razon Social</td>
			<td class='celdat' align='center'>Telefono</td> 
			<td class='celdat' align='center'>Direccion</td>
			<td class='celdat' align='center'>Ciudad</td>
			<td class='celdat' align='center'>Departamento</td> 
		</tr>
	<?php	
	for($i=0;$i<pg_num_rows($query);$i++){		
		$sr = pg_fetch_result($query,$i,'sr');
		$querydev=pg_query($pila,"select tipoid_empresa,id_empresa,razon_social,telefono,direccion,cod_ciudad,cod_departamento from enc_dev where sr='$sr'");
		$arr=pg_fetch_array($querydev);
	?>
		<tr>
			<td><?php echo $arr['tipoid_empresa'];?></td>  
			<td><?php echo $arr['id_empresa'];?></td>
			<td><?php echo $arr['razon_social'];?></td>
			<td><?php echo $arr['telefono'];?></td>
			<td><?php echo $arr['direccion'];?></td>
			<td><?php echo $arr['cod_ciudad'];?></td>
			<td><?php echo $arr['cod_departamento'];?></td>
		</tr>
	<?php		
	}
	?>
	</body>
	<html>
<?php
}
?>
