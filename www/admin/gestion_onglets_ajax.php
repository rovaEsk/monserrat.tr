<?php
include "includes/header.php";

clearDir($GLOBALS[rootpath]."configurations/$GLOBALS[host]/cache/onglets/");

$scriptcourant = "gestion_onglets.php";

if(isset($action) && $action == "org"){
	if($admin_droit[$scriptcourant][ecriture]){
		$DB_site->query("UPDATE categorie_site SET onglet = '0', positiononglet='0' WHERE siteid='$idsite'");
		$ordre = explode("|", $ordre);
		for($i=0;$i<sizeof($ordre);$i++){
			$id = explode("t",$ordre[$i]);
			$catid[$i] = $id[1];
		}
		
		for($i=0;$i<sizeof($catid);$i++){
			$position = $i+1;
			$DB_site->query("UPDATE categorie_site SET onglet = '1', positiononglet='$position' WHERE siteid='$idsite' AND catid='$catid[$i]'");
			echo "$catid[$i]_$position|";
		}
	}else{
		header("HTTP/1.1 503 $multilangue[action_page_refuse]");
		exit;
	}
}

if(isset($action) && $action == "change"){
	$categories = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE siteid = '$idsite' AND parentid = '0' ORDER BY position");
	while($categorie = $DB_site->fetch_array($categories)){
		echo "$categorie[catid]_$categorie[libelle]";
	}
}

if (isset($action) and $action == "jstreecateg"){
	header('Content-Type: application/json');
	$records = array();
	$records[] = array(
			"id" => 0,
			"parent" => "#",
			"text" => $titleFR,
			"state" => array("opened" => true)
	);
	$categories = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE siteid = '$idsite' AND parentid = '0' ORDER BY position");
	while ($categorie = $DB_site->fetch_array($categories)){
		$records[] = array(
				"id" => $categorie[catid],
				"parent" => $categorie[parentid],
				"text" => $categorie[libelle],
				"li_attr" => array("data-value" => $categorie[positiononglet]),
				"state" => array("selected" => ($categorie[onglet] ? true : false))
		);
	}
	echo json_encode($records);
}

?>