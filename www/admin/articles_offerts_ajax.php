<?php
	include "includes/header.php";
	
	if (isset($action) && $action == "parametre"){
		$first = true;
		foreach($_POST as $key => $value){
			if(!$first){
				$parametre = $key;
				if($value == "false"){
					$valeur=0;
				}else{
					$valeur=1;
				}
				$DB_site->query("UPDATE parametre SET valeur = '$valeur' WHERE parametre = '$parametre'");
				//echo "UPDATE parametre SET valeur = '$valeur' WHERE parametre = '$parametre'";
			}	
			$first=false;
		}
	}
	
	if (isset($action) && $action == "active"){
		$active = ($color == "green" ? 0 : 1);
		$DB_site->query("UPDATE articleoffert SET active = '$active' WHERE articleoffertid = '$articleoffertid'");
		$color = ($color == "green" ? "red" : "green");
		$tooltipvisible = ($color == "green" ? $multilangue[desactiver] : $multilangue[activer]);
	
		echo $action . "_" . $articleoffertid . "_" . $color . "_" . $tooltipvisible;
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
		$categories = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE siteid = '1' ORDER BY position");
		while ($categorie = $DB_site->fetch_array($categories)){
			$records[] = array(
					"id" => $categorie[catid],
					"parent" => $categorie[parentid],
					"text" => $categorie[libelle]/*,
					"state" => array("selected" => ($position[catid] ? true : false))*/
			);
		}
		echo json_encode($records);
	}
	
	if (isset($action) and $action == "jstreearticles"){
		header('Content-Type: application/json');
		$records = array();
		$records[] = array(
				"id" => "cat0",
				"parent" => "#",
				"text" => $titleFR,
				"state" => array("opened" => true)
		);
		$categories = $DB_site->query("SELECT * FROM categorie INNER JOIN categorie_site USING(catid) WHERE siteid = '1' ORDER BY position");
		$arr_articles = array();
		while ($categorie = $DB_site->fetch_array($categories)){
			$records[] = array(
					"id" =>"cat$categorie[catid]",
					"parent" =>"cat$categorie[parentid]",
					"text" => $categorie[libelle]
			);
			
			$articles = $DB_site->query("SELECT * FROM article AS a
											INNER JOIN article_site AS asite USING(artid)
											WHERE siteid = '1'
											AND catid = '$categorie[catid]'
											ORDER BY asite.libelle");
			
			while ($article = $DB_site->fetch_array($articles)){
				$records[] = array(
					"id" => "art$article[artid]",
					"parent" => "cat$categorie[catid]",
					"text" => $article[libelle],
					"icon" => "fa fa-inbox"
				);
			}
		
		}
		$articles_niveau0 = $DB_site->query("SELECT * FROM article AS a INNER JOIN article_site AS asite USING(artid) WHERE asite.siteid = '1'  AND a.catid = '0' ORDER BY asite.libelle");
		while($article_niveau0 = $DB_site->fetch_array($articles_niveau0)){
			$records[] = array(
					"id" => "art$article_niveau0[artid]",
					"parent" => "cat0",
					"text" => $article_niveau0[libelle],
					"icon" => "fa fa-inbox"
			);
		}
		echo json_encode($records);
	}
	
?>