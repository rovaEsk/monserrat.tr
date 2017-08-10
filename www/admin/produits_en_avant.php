<?php
include "./includes/header.php";

$referencepage="produits_en_avant";
$pagetitle = "Produits mis en avant - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

//$mode = "test_modules";

// AFFICHAGE ALERTES SUCCES ET ERREUR
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if(isset($action) && $action == "add" ){
	if($admin_droit[$scriptcourant][ecriture]){
		$date_debut = "0";
		$date_fin = "0";
		if ($datedebut != "") {
			list($jour, $mois, $annee) = explode('/', $datedebut);
			list($heure, $min, $sec) = explode(':', $timedebut);
			$date_debut = mktime($heure, $min, $sec, $mois, $jour, $annee);
		}
		if ($datefin != "") {
			list($jour, $mois, $annee) = explode('/', $datefin);
			list($heure, $min, $sec) = explode(':', $timefin);
			$date_fin = mktime($heure, $min, $sec, $mois, $jour, $annee);
		}
		$date_saisie = time();
		
		if(isset($pourcentage)){
			$pctpromo = $pourcentage;
		}else{
			$pctpromo = "";
		}
		$nb_promo_ajoute = 0;
		$site = explode(',', $idsite);
		$tabarticle = explode(',', $articles);
		for($i=0;$i<sizeof($site);$i++){
			for($j=0;$j<sizeof($tabarticle);$j++){
				$article = explode('t', $tabarticle[$j]);
				if($article[0] == 'ar'){
					$prix_article = $DB_site->query_first("SELECT prix FROM article_site WHERE artid = '$article[1]' AND siteid = '$site[$i]'");
					if($pctpromo == ""){
						if($prix_article[prix] == "0" || $prix_article[prix] == ""){
							$pctpromo = 0;
						}else{
							$nouveau_prix = floatval($prix_article[prix]) - floatval($montant);
							$pctpromo = ( ( floatval($prix_article[prix]) - $nouveau_prix ) / floatval($prix_article[prix]) ) * 100;
						}
					}
					$DB_site->query("INSERT INTO article_promo_site (artid, siteid, pctpromo, datedebut, datefin, datesaisie) VALUES ('$article[1]','$site[$i]','$pctpromo','$date_debut','$date_fin','$date_saisie')");
					$promoid = $DB_site->insert_id();
					$DB_site->query("INSERT INTO article_historique_prix_site (siteid, artid, prix, pctpromo, datesaisie, datedebut, datefin, promoid) VALUES ('$site[$i]','$article[1]','$prix_article[prix]','$pctpromo','$date_saisie','$date_debut','$date_fin','$promoid')");
					$nb_promo_ajoute++;			
					clearDir($GLOBALS[rootpath]."configurations/".$GLOBALS[host]."/cache/articles/".$article[1]);				
				}
			}
		}
		if($nb_promo_ajoute > 0){
			header("location: produits_en_avant.php?succes=1#promotions");
		}else{
			header("location: produits_en_avant.php?erreur=1#promotions");
		}
	}else{
		header('location: produits_en_avant.php?erreurdroits=1');	
	}
}

if(isset($action) && $action == "ajouter" ){
	$libNavigSupp = $multilangue[ajt_promotion];
	eval(charge_template($langue,$referencepage,"NavigSupp"));
	
	$sites = $DB_site->query("SELECT * FROM site");
	while($site = $DB_site->fetch_array($sites)){
		eval(charge_template($langue,$referencepage,"CheckboxSiteBit"));
	}
	eval(charge_template($langue,$referencepage,"AjoutPromotion"));
}


if(isset($action) && $action == "supprimer" ){
	if($admin_droit[$scriptcourant][suppression]){
		$date_historique = $DB_site->query_first("SELECT datefin, datedebut FROM article_promo_site WHERE promoid = '$promoid'");
		if($date_historique[datefin] > time() && $date_historique[datedebut] < time()){
			$DB_site->query("UPDATE article_historique_prix_site SET datefin = '".time()."' WHERE promoid = '$promoid'");
		}
		
		if($date_historique[datefin] > time() && $date_historique[datedebut] > time()){
			$DB_site->query("DELETE FROM article_historique_prix_site WHERE promoid = '$promoid'");	
		}
		$DB_site->query("DELETE FROM article_promo_site WHERE promoid = '$promoid'");
		header("location: produits_en_avant.php#promotions");
	}else{
		header('location: produits_en_avant.php?erreurdroits=1');	
	}
}


if(!isset($action) || $action = "" ){	
	if(isset($succes) && $succes == "1"){
		eval(charge_template($langue,$referencepage,"Succes"));
	}
	
	if(isset($erreur) && $erreur == "1"){
		$texteErreur = $multilangue[erreur_ajout_promotions] ;
		eval(charge_template($langue,$referencepage,"Erreur"));
	}
	
	if(!isset($idsite)){
		$idsite = "1";
	}
	
	if(isset($top_defaut)){
		$DB_site->query("UPDATE parametre SET valeur = '".$top_defaut."' WHERE parametre = 'top_index' AND siteid = '".$idsite."' ");
	}
	
	$sites = $DB_site->query("SELECT * FROM site ORDER BY siteid");
	while($site = $DB_site->fetch_array($sites)){
		$selected_site = "";
		if($site[siteid] == $idsite){
			$selected_site="selected=\"selected\"";
		}
		eval(charge_template($langue,$referencepage,"ListeSiteBit"));
	}
	/*MODIF ADP*/
	$tab_tops = array("top_ventes", "nouveautes", "promotions", "coups_de_coeur");
	$top_index = $DB_site->query_first("SELECT valeur FROM parametre WHERE parametre = 'top_index' AND siteid = '".$idsite."' ");
	foreach($tab_tops as $tab_top){
		$selected_top = "";
		if($tab_top == $top_index[0]){
			$selected_top="selected=\"selected\"";
		}
		eval(charge_template($langue,$referencepage,"ListeTopBit"));
	}
	/*MODIF ADP*/
	
	$ordreCoupsCoeur = "";
	$ordreNouveautes = "";
	$ordreTopVentes = "";
	
	$first = true;
	$coupscoeur = $DB_site->query("SELECT * FROM topcoupdecoeur INNER JOIN article_site USING(artid) INNER JOIN article USING(artid) WHERE topcoupdecoeur.siteid = '$idsite' AND topcoupdecoeur.siteid = article_site.siteid ORDER BY position");
	while($coupcoeur = $DB_site->fetch_array($coupscoeur)){
		if($first){
			$ordreCoupsCoeur .= "$coupcoeur[artid]";
			$first = false;	
		}else{
			$ordreCoupsCoeur .= "|$coupcoeur[artid]";
		}
		
		if(strlen($coupcoeur[libelle])>30){
			$texte_coup_coeur = substr($coupcoeur[libelle], 0, 30);	
		}else{
			$texte_coup_coeur = $coupcoeur[libelle];	
		}
		$texte_coup_coeur .= " ($coupcoeur[artcode])";
		
		if($coupcoeur[image] != ""){
			$image_article="<img src='http://$host/br-a-$coupcoeur[artid].$coupcoeur[image]' style='max-width:140px;max-height:100px;'>";
		}else{
			$image_article="";
		}
		
		eval(charge_template($langue,$referencepage,"CoupsCoeur"));
		eval(charge_template($langue,$referencepage,"CoupsCoeurBoitesBit"));
	}
	
	$first = true;
	$nouveautes = $DB_site->query("SELECT * FROM topnouveaute INNER JOIN article_site USING(artid) INNER JOIN article USING(artid) WHERE topnouveaute.siteid='$idsite' AND topnouveaute.siteid = article_site.siteid ORDER BY position");
	while($nouveaute = $DB_site->fetch_array($nouveautes)){
		if($first){
			$ordreNouveautes .= "$nouveaute[artid]";
			$first = false;
		}else{
			$ordreNouveautes .= "|$nouveaute[artid]";
		}
		
		if(strlen($nouveaute[libelle])>30){
			$texte_nouveaute = substr($nouveaute[libelle], 0, 30);
		}else{
			$texte_nouveaute = $nouveaute[libelle];
		}
		$texte_nouveaute .= " ($nouveaute[artcode])";
		
		if($nouveaute[image] != ""){
			$image_article="<img src='http://$host/br-a-$nouveaute[artid].$nouveaute[image]' style='max-width:140px;max-height:100px;'>";
		}else{
			$image_article="";
		}
		
		eval(charge_template($langue,$referencepage,"Nouveautes"));
		eval(charge_template($langue,$referencepage,"NouveautesBoitesBit"));
	}
	
	$first = true;
	$topventes = $DB_site->query("SELECT * FROM topvente INNER JOIN article_site USING(artid) INNER JOIN article USING(artid) WHERE topvente.siteid='$idsite' AND topvente.siteid = article_site.siteid ORDER BY position");
	while($topvente = $DB_site->fetch_array($topventes)){
		if($first){
			$ordreTopVentes .= "$topvente[artid]";
			$first = false;
		}else{
			$ordreTopVentes .= "|$topvente[artid]";
		}
		
		if(strlen($topvente[libelle])>30){
			$texte_top_vente = substr($topvente[libelle], 0, 30);
		}else{
			$texte_top_vente = $topvente[libelle];
		}
		$texte_top_vente .= " ($topvente[artcode])";
		
		if($topvente[image] != ""){
			$image_article="<img src='http://$host/br-a-$topvente[artid].$topvente[image]' style='max-width:140px;max-height:100px;'>";
		}else{
			$image_article="";
		}
		
		eval(charge_template($langue,$referencepage,"TopVentes"));
		eval(charge_template($langue,$referencepage,"TopVentesBoitesBit"));
	}
	
	
	$first = true;
	$avantpremieres = $DB_site->query("SELECT * FROM topavantpremiere 
										INNER JOIN article_site USING(artid) 
										INNER JOIN article USING(artid) 
										WHERE topavantpremiere.siteid='$idsite' 
										AND topavantpremiere.siteid = article_site.siteid 
										ORDER BY position");
	while($avantpremiere = $DB_site->fetch_array($avantpremieres)){
		if($first){
			$ordreAvantPremieres .= "$avantpremiere[artid]";
			$first = false;
		}else{
			$ordreAvantPremieres .= "|$avantpremiere[artid]";
		}
	
		if(strlen($avantpremiere[libelle])>30){
			$texte_avant_premiere = substr($avantpremiere[libelle], 0, 30);
		}else{
			$texte_avant_premiere = $avantpremiere[libelle];
		}
		$texte_avant_premiere .= " ($avantpremiere[artcode])";
	
		if($avantpremiere[image] != ""){
			$image_article="<img src='http://$host/br-a-$avantpremiere[artid].$avantpremiere[image]' style='max-width:140px;max-height:100px;'>";
		}else{
			$image_article="";
		}
		
		eval(charge_template($langue,$referencepage,"AvantPremiere"));
		eval(charge_template($langue,$referencepage,"AvantPremiereBoitesBit"));
	}
	eval(charge_template($langue,$referencepage,"Tabs"));
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