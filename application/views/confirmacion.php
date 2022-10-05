<td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
 
 <table border="1" cellpadding="0" cellspacing="0" width="100%">
 
 <tr>

 </tr>
 <tr class="fitness" style="background:#e4381c; color:#FFFFFF; font-family: Oswald,sans-serif; text-align:center;">
  <td class="content">	
	<center></center>
	<h2 >CITY FITNESS WORLD</h2>
 </td>
</tr>

<tr>
  <td class="content">
	<strong>NOMBRE:</strong>	<?php echo 	$usuario?>
 </td>
</tr>

<tr>
 <td class="content">
 <strong>CURSO:</strong> <?php echo $clase->clas_nombre;?>
 </td>
</tr>
 
<tr>
 <td class="content">
 <strong>	TIPO:</strong> <?php echo $tipoclase;?>
 </td>
</tr>

<?php 
if($clase->clas_tipo==1){
?>
<tr>
 <td class="content">
 <strong>ENLACE: </strong> <?php echo $clase->clas_enlace;?>
 </td>
</tr>

<tr>
 <td class="content">
 <strong>ISNTRUCTOR: </strong> <?php echo $clase->ins_nombre.' '.$clase->ins_apellido;?>
 </td>
</tr>

<tr>
 <td class="content">
 <strong>FECHA: </strong> <?php echo $clase->clas_fecha_inicio;?>
 </td>
</tr>

<tr>
 <td class="content">
 <strong>HORA: </strong> <?php echo $clase->ins_nombre.' '.$clase->clas_hora;?>
 </td>
</tr>
<?php
}else if($clase->clas_tipo==0){
?>
<tr>
 <td class="content">
	<strong>POR FAVOR AGENDAR SU CITA COMUNICANDOSE AL 3006667694</strong>
 </td>
</tr>

<?php } ?>
 
 </table>
 
</td>

<style>
.content{
	text-align:center;
}
</style>
