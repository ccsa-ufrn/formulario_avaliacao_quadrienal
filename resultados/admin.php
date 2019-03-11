<?php
session_start();
if(!isset($_SESSION['login'])) {
	header('LOCATION:index.php'); die();
}

include_once "connection.php";

$con = new mysqli($host, $user, $pass, $db);
$con->set_charset('utf8');
if(mysqli_connect_errno()){
    echo json_encode(array('success'=>false));
    exit;
}
$professors_result = mysqli_query($con, "SELECT * FROM professor") or die(mysqli_error($con));
$professors = array();
$number_professors = array();
while($professor = mysqli_fetch_assoc($professors_result)){
    $temp_professor = array();
    $temp_professor = array("id" => $professor["id"], "search_group_id" => $professor["search_group_id"], "name" => $professor["name"], "ccsa" => $professor["ccsa"], "master_phd" => $professor["master_phd"], "pointsEachInsertion" => Array("3" => 0, "4" => 0, "5" => 0, "6" => 0, "7" => 0, "8" => 0, "9" => 0, "10" => 0));
    array_push($professors, $temp_professor);
    array_push($number_professors, $professor['name']);
}

$insertions_result = mysqli_query($con, "SELECT * FROM insertion") or die(mysqli_error($con));
$insertions = array();
while($insertion = mysqli_fetch_array($insertions_result)){
    $temp_insertion = array();
    $temp_insertion = array("id" => $insertion["id"], "professor_search_group_id" => $insertion["professor_search_group_id"], "criterion" => $insertion["criterion"], "type" => $insertion["type"], "content" => $insertion["content"], "professor_id" => $insertion["professor_id"]);
    array_push($insertions, $temp_insertion);
}

$groups_result = mysqli_query($con, "SELECT * FROM search_group") or die (mysqli_error($con));
$groups = array();
while($group = mysqli_fetch_array($groups_result)){
    $temp_group = array();
    $points_for_rooms = $group['numGrad'] * 4 + $group['numPosGrad'] * 4;
    $temp_group = array("id" => $group["id"], "name" => $group['name'], "coordinator" => $group['coordinator'], "year" => $group['year'], "numGrad" => $group['numGrad'], "numPosGrad" => $group['numPosGrad'], "goals" => $group['goals'], "pointsRooms" => $points_for_rooms, "professors" => array());
    foreach($professors as $professor){
        if($professor['search_group_id'] == $temp_group['id'] && $professor['ccsa'] == 1){
            $prof_points_rooms = 0;
            $prof_points_four_years = 0;
            if($professor['master_phd'] == 1){
                $prof_points_rooms += 4;
                $professor["pointsEachInsertion"]["3"] += 4;
            }
            foreach($insertions as $insertion){
                if($insertion['professor_id'] == $professor['id'] && $insertion['content'] != "" && $insertion['content'] != " "){
                    if($insertion['criterion'] == 0){
                        $prof_points_rooms += 3;
                        $prof_points_four_years += 3;
                        $professor["pointsEachInsertion"]["4"] += 3;
                    }
                    if($insertion['criterion'] == 1){
                        $prof_points_rooms += 5;
                        $prof_points_four_years += 5;
                        $professor["pointsEachInsertion"]["5"] += 5;
                    }
                    if($insertion['criterion'] == 2){
                        $prof_points_rooms += 4;
                        $prof_points_four_years += 4;
                        $professor["pointsEachInsertion"]["6"] += 4;
                        }
                        if($insertion['criterion'] == 3){
                            $prof_points_rooms += 3;
                            $prof_points_four_years += 3;
                            $professor["pointsEachInsertion"]["7"] += 3;
                        }
                        if($insertion['criterion'] == 4){
                            $prof_points_rooms += 4;
                            $prof_points_four_years += 4;
                            $professor["pointsEachInsertion"]["8"] += 4;
                        }
                        if($insertion['criterion'] == 5){
                            $prof_points_rooms += 5;
                            $prof_points_four_years += 5;
                            $professor["pointsEachInsertion"]["9"] += 5;
                        }
                        if($insertion['criterion'] == 6){
                            $prof_points_rooms += 3;
                            $prof_points_four_years += 3;
                            $professor["pointsEachInsertion"]["10"] += 3;
                        }
                    }
                }
                unset($insertion);
                array_push($temp_group['professors'], $professor);
                $temp_group['pointsRooms'] += $prof_points_rooms;
            }
        }
        unset($professor);
        array_push($groups, $temp_group);
    }
    $con->close();
    ?>
    <?php
    function array_icount_values($array) {
        $ret_array = array();
        foreach($array as $value) {
            foreach($ret_array as $key2 => $value2) {
                if(strtolower($key2) == strtolower($value)) {
                    $ret_array[$key2]++;
                    continue 2;
                }
            }
            $ret_array[$value] = 1;
        }
        return $ret_array;
    }
    ?>
    <!DOCTYPE html>
    <html>
       <head>
         <meta http-equiv='content-type' content='text/html;charset=utf-8' />
         <title>Formula</title>
         <meta charset="utf-8">
         <meta name="viewport" content="width=device-width, initial-scale=1">
         <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.0/build/pure-min.css" integrity="sha384-nn4HPE8lTHyVtfCBi5yW9d20FjT8BJwUXyWZT9InLYax14RDjBj46LmSztkmNP9w" crossorigin="anonymous">
       </head>
       <style>
        tr.collapse.in {
            display:table-row; !important
        }
       </style>
    <body style="margin: 0 auto; width: 90%"><br>
      <nav class="navbar navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="#">
                    <img src="../ccsa.png" width="300" class="d-inline-block align-top" alt=""/>
                </a>
            </div>
        </nav>
        <div class="container"> 
            <div style="margin-top: 20px"></div>
            <div class="row">
            <div class="col-md-12">
                    <div class="panel panel-default">
                            <div class="panel-heading text-center">
                                    <h4>Dados(Mostrando apenas professores com vínculo ao CCSA</h4>
                            </div>
			<?php foreach($groups as $group): ?>
			<table style="width: 100%" class="<?= $group['id'] ?> pure-table pure-table-bordered">
				<thead>
					<th>Nome</th>
					<th>Coordenador</th>
					<th>Pontos</th>
					<th>Esconder</th>
					<th>Relatório Completo</th>
				</thead>
                                <tr class=" <?= $group['id'] ?>">
					<td><?= $group['name'] ?></td>
					<td><?= $group['coordinator'] ?></td>
					<td><?= $group['pointsRooms'] ?></td>
 					<td><button style="font-size: 10px;" type="button" onClick='removeGroup("<?= $group["id"]; ?>")' class="btn btn-default <?= $group['id'] ?>">Esconder</button></td>
					<td><button type="button" style="font-size: 10px;" onClick="location.href = 'http:\\ccsa.ufrn.br/avaliacao_quadrienal/resultados/complete.php?id=<?= $group["id"]; ?>';" class="btn btn-default <?= $group['id'] ?>">Detalhes completos</button></td>
                                    </tr>
                                    <td colspan="5" class=" <?= $group['id'] ?>">
                                        <table style="width: 100%" class="pure-table pure-table-bordered">
                                            <thead>
                                                <th>Nome</th>
                                                <th>Crit 1</th>
                                                <th>Crit 4</th>
                                                <th>Crit 5</th>
                                                <th>Crit 6</th>
                                                <th>Crit 7</th>
                                                <th>Crit 8</th>
                                                <th>Crit 9</th>
                                                <th>Crit 10</th>
                                                <th>Total</th>
                                            </thead>
                                        <?php foreach($group['professors'] as $professor): ?>
                                            <tr>
                                                <td><?= $professor['name']?></td>
                                                <td><?= $professor['pointsEachInsertion']['3'] ?></td>
                                                <td><?= $professor['pointsEachInsertion']['4'] ?></td>
                                                <td><?= $professor['pointsEachInsertion']['5'] ?></td>
                                                <td><?= $professor['pointsEachInsertion']['6'] ?></td>
                                                <td><?= $professor['pointsEachInsertion']['7'] ?></td>
                                                <td><?= $professor['pointsEachInsertion']['8'] ?></td>
                                                <td><?= $professor['pointsEachInsertion']['9'] ?></td>
                                                <td><?= $professor['pointsEachInsertion']['10'] ?></td>
                                                <td><?= $professor['pointsEachInsertion']['3'] + $professor['pointsEachInsertion']['4'] + $professor['pointsEachInsertion']['5'] + $professor['pointsEachInsertion']['6'] + $professor['pointsEachInsertion']['7'] + $professor['pointsEachInsertion']['8'] + $professor['pointsEachInsertion']['9'] + $professor['pointsEachInsertion']['10']?></td>
                                            <tr>
                                        <?php endforeach; ?>
                                        </table>
                                    </td>
			</table>
                        </br>
			<?php endforeach; ?>
		</div>
            </div>
        </div>
     </div>
  </div>
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
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
   <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
</body>
</html>
