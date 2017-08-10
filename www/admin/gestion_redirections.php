<?php
include "./includes/header.php";

$referencepage="gestion_redirections";
$pagetitle = "Gestion des redirections - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

//$mode = "test_modules";
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if(isset($action) && $action == "doSupprMultiple"){
	if($admin_droit[$scriptcourant][suppression]){
		$tabids = explode(',', $id);
		foreach ($tabids as $value){
			$DB_site->query("DELETE FROM redirections WHERE id = '$value'");
		}
		header("location: gestion_redirections.php");
	}else{
		header('location: gestion_redirections.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "supprMultiple"){
	if($admin_droit[$scriptcourant][suppression]){
		if(sizeof($chk) > 0){
			$ids = "";
			foreach ($chk as $key => $value){
				$url = $DB_site->query_first("SELECT * FROM redirections WHERE id = '$key'");
				$ids .= "$key,";
				eval(charge_template($langue, $referencepage, "ModalSupprInfos"));
			}
			$ids = substr($ids, 0, -1);
			eval(charge_template($langue, $referencepage, "ModalSupprMultiple"));
			$action = "";
		}
	}else{
		header('location: gestion_redirections.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "supprimer"){
	if($admin_droit[$scriptcourant][suppression]){
		$DB_site->query("DELETE FROM redirections WHERE id = '$id'");
		header("location: gestion_redirections.php");
	}else{
		header('location: gestion_redirections.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "modifier"){
	if($admin_droit[$scriptcourant][ecriture]){
		$DB_site->query("UPDATE redirections SET old_url = '$ancienneURL', new_url = '$nouvelleURL', code = '$type' WHERE id = '$id'");
		header("location: gestion_redirections.php");
	}else{
		header('location: gestion_redirections.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "ajout"){
	if($admin_droit[$scriptcourant][ecriture]){
		$DB_site->query("INSERT INTO redirections (old_url, new_url, code) VALUES ('/$ancienneURL','/$nouvelleURL','$type')");
		header("location: gestion_redirections.php");
	}else{
		header('location: gestion_redirections.php?erreurdroits=1');	
	}
}

if(!isset($action) || $action == "" ){	
	eval(charge_template($langue, $referencepage, "Liste"));
}

$TemplateIncludejavascript = eval(charge_template($langue, $referencepage, "Includejavascript"));
eval(charge_template($langue, "commun", "header"));
eval(charge_template($langue, "commun", "footer"));
eval(charge_template($langue, $referencepage, "index"));

$nomtemplateindex="Template".ucfirst($referencepage)."Index";
echo ${$nomtemplateindex};

$DB_site->close();
flush();

?>