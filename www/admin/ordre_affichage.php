<?php
include "./includes/header.php";

$referencepage="ordre_affichage";
$pagetitle = "Ordre d'affichage - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}
$scriptcourant = "categorie.php";
//$mode = "test_modules";

// AFFICHAGE ALERTES SUCCES ET ERREUR
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if(isset($action) && $action == "ajout"){
	if($admin_droit[$scriptcourant][ecriture]){
		$idArticle = explode(",", $ordre);
		$ordre = "";
		$first = true;
		for($i=0;$i<sizeof($idArticle);$i++){
			if($first){
				$ordre .= "'$idArticle[$i]'";
				$first = false;
			}else{
				$ordre .= ",'$idArticle[$i]'";
			}
		}
		$DB_site->query("DELETE FROM position WHERE catid = '$catid' AND artid IN ($ordre) ");
		for($i=0;$i<sizeof($idArticle);$i++){
			$position = $i+1;
			$DB_site->query("INSERT INTO position VALUES ('$idArticle[$i]','$catid','$position')");
		}
		
		header("location: categorie.php?catid=$parent");
	}else{
		header('location: categorie.php?erreurdroits=1');	
	}
}

if(!isset($action) || $action = ""){
	if(isset($catid)){
		$categorie = $DB_site->query_first("SELECT libelle FROM categorie_site WHERE catid='$catid'");
		$parent = $DB_site->query_first("SELECT parentid FROM categorie WHERE catid='$catid'");
		$libNavigSupp = $categorie[libelle];
		eval(charge_template($langue, $referencepage, "NavigSupp"));
		$ordre = "";
		$first = true;
		$articles = $DB_site->query("SELECT * FROM position INNER JOIN article_site USING (artid) WHERE catid = '$catid' AND siteid = '1' ORDER BY position");
		while($article = $DB_site->fetch_array($articles)){
			if($first){
				$ordre = $article[artid];
				$first = false;	
			}else{
				$ordre .= ",$article[artid]";
			}
			eval(charge_template($langue, $referencepage, "ListeArticlesBit"));
		}
	}
	
	
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