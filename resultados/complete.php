<?php
session_start();
if(!isset($_SESSION['login'])) {
	header('LOCATION:index.php'); die();
}

include_once "connection.php";
$id = $_GET['id'];
$con = new mysqli($host, $user, $pass, $db);
$con ->set_charset('utf8');
if(mysqli_connect_errno()){
	echo json_encode(array('success'=>false));
	exit;
}
$group_result = mysqli_query($con, "SELECT * FROM search_group WHERE id = '$id' ") or die(mysqli_error($con));

$professor_result = mysqli_query($con, "SELECT * FROM professor WHERE search_group_id = '$id' ") or die(mysqli_error($con));
$professors = Array();
while($professor = mysqli_fetch_assoc($professor_result)){
        array_push($professors, $professor);
}

$insertion_result = mysqli_query($con, "SELECT * FROM insertion WHERE professor_search_group_id = '$id' ") or die(mysqli_error($con));
$insertions = Array();
while($insertion = mysqli_fetch_assoc($insertion_result)){
        array_push($insertions, $insertion);
}

$groups = Array();
while($group = mysqli_fetch_assoc($group_result)){
	$group['professors'] = Array();
	foreach($professors as $professor){
		$professor['insertions'] = Array();
		foreach($insertions as $insertion){
			if($insertion['professor_id'] == $professor['id']){
				array_push($professor['insertions'], $insertion);
			}
		}
		usort($professor['insertions'], 'sortByType');
		array_push($group['professors'], $professor);
	}
	array_push($groups, $group);
}

function sortByType($a, $b)
{
    $a = $a['criterion'];
    $b = $b['criterion'];

    if ($a == $b) return 0;
    return ($a < $b) ? -1 : 1;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<title>Respostas do formulário</title>
</head>
<link rel="stylesheet" href="https://unpkg.com/purecss@1.0.0/build/pure-min.css" integrity="sha384-nn4HPE8lTHyVtfCBi5yW9d20FjT8BJwUXyWZT9InLYax14RDjBj46LmSztkmNP9w" crossorigin="anonymous">
<body style="margin: 0 auto; width: 900px;"><br>
	<center>
	 <b>CENTRO DE CIÊNCIAS SOCIAIS APLICADAS (CCSA/UFRN)</b>
	</center><br>
	<h4>Resultados retornados</h4>
	<?php foreach($groups as $group): ?>
        <table class="pure-table pure-table-bordered <?php echo $group['id']; ?>" width="100%">
		<thead>
			<tr>
                        <td colspan=2><b>Informações pessoais</b>
                        </td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td width="20%">Nome</td>
                                <td><?php echo $group['name']; ?></td>
			</tr>
			<tr>
				<td>Coordenador</td>
                                <td><?php echo $group['coordinator']; ?></td>
			</tr>
			<tr>
				<td>Ano</td>
                                <td><?php echo $group['year']; ?></td>
			</tr>
			<tr>
				<td>Número de Alunos da Graduação</td>
				<td><?php echo $group['numGrad']; ?></td>
			</tr>
			<tr>
				<td>Número de Alunos da Pós-Graduação</td>
				<td><?php echo $group['numPosGrad']; ?></td>
			</tr>
		</tbody>
	</table>
        <table class="pure-table pure-table-bordered <?php echo $group['id']; ?>" width="100%">
		<thead>
			<tr>
				<td colspan=2><b>Objetivos</b></td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
                                   <?php echo $group['goals']?><span> </span>
				</td>
			</tr>
		</tbody>
	</table>
	<table class="pure-table pure-table-bordered <?php echo $group['id']; ?>" width="100%">
		<thead>
			<tr>
				<td colspan=2><b>Lista de professores</b></td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<?php foreach($group['professors'] as $professor): ?>
					<p><?php echo $professor['name']; ?></p>
					<?php endforeach; ?>
				</td>
			</tr>
		</tbody>
	</table>
	<?php foreach($group['professors'] as $professor): ?>
	</br>
        <table class="pure-table pure-table-bordered <?php echo $group['id']; ?>" width="100%">
		<thead>
			<tr>
				<td colspan=2><b><?php echo $professor['name']; ?></b></td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<input type="checkbox" <?=($professor['ccsa'] == 1) ? "checked" : ""?>  disabled> Membro do CCSA
					<input type="checkbox" <?=($professor['master_phd'] == 1) ? "checked" : ""?> disabled> Possui Pós-Graduação 
				</td>
			</tr>
		</tbody>
	</table>
	<?php foreach($professor['insertions'] as $insertion): ?>
        <table class="pure-table pure-table-bordered <?php echo $group['id']; ?>" width="100%">
		<thead>
			<tr class="<?php echo $insertion['id']; ?>">
				<td colspan=2><b><?php echo $insertion['criterion'] + 4; ?></b></td>
				<td colspan=2><b><?php echo $insertion['type']; ?></b></td>
				<td colspan=2><b><?php echo $insertion['year']; ?></b></td>
 				<td><button style="font-size: 10px;" type="button" onClick='removeGroup("<?= $insertion["id"]; ?>")' class="btn btn-default <?= $group['id'] ?>">Esconder</button></td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class=" <?php echo $insertion['id']; ?>"><p style="text-align: justify;"><?php echo $insertion['content']; ?></p></td>
			</tr>
		</tbody>
	</table>
	<?php endforeach; ?>
	<?php endforeach; ?>
	<?php endforeach; ?>
<script type="text/javascript">
    function removeGroup(formClass) {
        var elem = document.getElementsByClassName(formClass);
        for(x = 0; x < elem.length; x++){
            elem[x].style.display = 'none';
        }
    } 
    function removeBut(formClass) {
        var elem = document.getElementsByClassName("pure-table");
        var className = " " + formClass + " ";
        for(x = 0; x < elem.length; x++){
            if ( !((" " + elem[x].className + " ").replace(/[\n\t\r]/g, " ").indexOf(className) > -1) && !((" " + elem[x].className + " ").replace(/[\n\t\r]/g, " ").indexOf(" principal ") > -1)) {
                elem[x].style.display = 'none';
            }
        }
    }
</script>
</body>
</html>
