<?php
include "./includes/header.php";

$referencepage="marques";
$pagetitle = "Gestion des marques - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}


// AFFICHAGE ALERTES SUCCES ET ERREUR
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

if($succes == 1){
	$mq = $DB_site->query_first("SELECT * from marque
						INNER JOIN marque_site USING(marqueid)
						WHERE marqueid = '$marqueid'
						AND siteid='1'");
	$texteSuccess = $multilangue[la_marque]." <strong>$mq[libelle]</strong> ".$multilangue[a_bien_ete_cree];
	eval(charge_template($langue,$referencepage,"Success"));
}

if($succes == 2){
	$mq = $DB_site->query_first("SELECT * from marque
			INNER JOIN marque_site USING(marqueid)
			WHERE marqueid = '$marqueid'
			AND siteid='1'");
	$texteSuccess =$multilangue[la_marque]." <strong>$marque[libelle]</strong> ".$multilangue[a_bien_ete_modifiee];
	eval(charge_template($langue,$referencepage,"Success"));
}

if($succes == 3){
	$mq = $DB_site->query_first("SELECT * from marque
			INNER JOIN marque_site USING(marqueid)
			WHERE marqueid = '$marqueid'
			AND siteid='1'");
	$texteSuccess = $multilangue[la_marque]." <strong>$mq[libelle]</strong> ".$multilangue[a_bien_ete_supprimee];
	eval(charge_template($langue,$referencepage,"Success"));
}

if($erreur == 1){	
	$texteErreur = $multilangue[la_marque_n_existe_plus];
	eval(charge_template($langue,$referencepage,"Erreur"));
}

if($action=="ordreArticles" && !empty($marqueid)){
	if($admin_droit[$scriptcourant][ecriture]){
		if (!empty($ordre)){
			$position = 0 ;
			$liste = explode(";", $ordre) ;
			foreach ($liste as $key => $value) {
				$position++ ;
				$DB_site->query("UPDATE article_marque SET position = '$position' WHERE artid = '$value' AND marqueid='$marqueid'");
			}
			
			$texteSuccess = $multilangue[l_ordre_d_affichage]." ".$multilangue[a_bien_ete_enregistre];
			eval(charge_template($langue,$referencepage,"Success"));
		}
		
		$marque = $DB_site->query_first("SELECT * FROM marque 
											INNER JOIN marque_site USING(marqueid)
											WHERE marqueid='$marqueid'
											AND siteid='1'");
	
		$articles=$DB_site->query("SELECT * FROM article a
									INNER JOIN article_site asite ON asite.artid=a.artid  
									INNER JOIN article_marque am ON a.artid=am.artid 
									WHERE am.marqueid = '$marqueid' 
									AND asite.siteid='1' 
									ORDER BY am.position");
									
		while ($article=$DB_site->fetch_array($articles)){
			if($article[image]){
				$art_image="<img src=\"/configurations/$host/images/produits/br/$article[artid].$article[image]\" height=\"50\">";
			}else{
				$art_image="";
			}
			
			$regleurlrewriteDefaut = $regleurlrewrite[1];
			$article[url] = url_rewrite($article[libelle]);
			$urlarticle="http://$host/".$regleurlrewriteDefaut[article]."-$article[url]-$article[artid].htm";
			$urlarticleV2="http://$host/V2/".$regleurlrewriteDefaut[article]."-$article[url]-$article[artid].htm";
			
			$article[prixachat]=formaterPrix($article[prixachat]);
			$article[prix]=formaterPrix($article[prix]);
			
			eval(charge_template($langue,$referencepage,"OrderArticlesBit"));
		}			
		eval(charge_template($langue,$referencepage,"OrderArticles"));
		
		$libNavigSupp="Liste des articles de la marque <i><b>\"$marque[libelle]\"</b></i>";
		eval(charge_template($langue,$referencepage,"NavigSupp"));
	}else{
		header('location: marques.php?erreurdroits=1');	
		exit();
	}
}

if ($action=="ref"){
	if($admin_droit[$scriptcourant][ecriture]){
		if ($pastille == "vert") 
			$cacher = 0 ; 
		else 
			$cacher = 1 ;
			
		$DB_site->query("UPDATE marque SET nofollow = '$cacher' WHERE marqueid = '$marque'");
		
		header("location: marques.php");
		exit();
	}else{
		header('location: marques.php?erreurdroits=1');	
		exit();
	}
}
if ($action=="visible_article"){
	if($admin_droit[$scriptcourant][ecriture]){
		if ($pastille == "vert") 
			$cacher = 0 ; 
		else 
			$cacher = 1 ;
			
		$DB_site->query("UPDATE marque_site SET visible_article = '$cacher' WHERE marqueid = '$marque'");
		
		header("location: marques.php");
		exit();
	}else{
		header('location: marques.php?erreurdroits=1');	
		exit();
	}
}


// MODIFIER UNE MARQUE (Enregistrement BDD)
if (isset($action) and $action == "modifier2"){	
	if($admin_droit[$scriptcourant][ecriture]){
		$erreur = "" ;
		
		if($marqueid == ""){			
			$DB_site->query("INSERT INTO marque(marqueid)VALUES ('')");
			$marqueid = $DB_site->insert_id();
			$nouvellemarque=1;
		}
		
		$sites = $DB_site->query("SELECT * FROM site");		
		while($site = $DB_site->fetch_array($sites)){
			
			if($nouvellemarque){		
				$DB_site->query("INSERT INTO marque_site (marqueid,siteid) VALUES ('$marqueid','$site[siteid]')");
			}
			
			$existe_site_marque = $DB_site->query_first("SELECT * FROM marque 
										INNER JOIN marque_site USING(marqueid)
										WHERE marqueid = '$marqueid' 
										AND siteid='$site[siteid]'");
			
			if($existe_site_marque[marqueid] == ""){	
				$DB_site->query("INSERT INTO marque_site (marqueid,siteid) VALUES ('$marqueid','$site[siteid]')");
			}
			
			$curentlibelle = "libelle".$site[siteid];
			$curentdescription = "description".$site[siteid];
			$curentpagetitle = "pagetitle".$site[siteid];
			$curentmetadescription = "metadescription".$site[siteid];
			$curentmetakeywords = "metakeywords".$site[siteid];
			
			$sql = "UPDATE marque_site SET libelle = '".addslashes(${$curentlibelle})."',
					description='".addslashes(${$curentdescription})."',
					pagetitle = '".addslashes(htmlentities(strip_tags(${$curentpagetitle})))."',				
					metadescription= '".addslashes(htmlentities(strip_tags(${$curentmetadescription})))."',
					metakeywords = '".addslashes(htmlentities(strip_tags(${$curentmetakeywords})))."'
					WHERE marqueid = '$marqueid'
					AND siteid = '$site[siteid]'";
				 
			//echo "<br>$sql";		
			$DB_site->query($sql);	 
		}
	
		if (!empty($_FILES['mq_image']['name'])) {
			$listeTypesAutorise = array("image/pjpeg","image/jpeg","image/gif");
			erreurUpload("mq_image",$listeTypesAutorise,1048576);
		}
		if ($erreur == "" && !empty($_FILES['mq_image']['name'])) {		
			$type_fichier=define_extention($_FILES['mq_image']['name']);
			$DB_site->query("UPDATE marque SET image = '$type_fichier' WHERE marqueid = '$marqueid'");
			$nom_fic=$rootpath."configurations/$host/images/marques/".$marqueid.".".$type_fichier;
			copier_image($nom_fic,'mq_image');
			$destination=$rootpath."configurations/$host/images/marques/br/".$marqueid.".".$type_fichier;
			redimentionner_image($nom_fic,$destination,$marque_largeur,$marque_hauteur);
			$destination2=$rootpath."configurations/$host/images/marques/br2/".$marqueid.".".$type_fichier;
			redimentionner_image($nom_fic,$destination2,$marque2_largeur,$marque2_hauteur);
		}
		
		
		if($nouvellemarque){
			header("location: marques.php?succes=1&marqueid=$marque[marqueid]");
			exit();
		}else{
			header("location: marques.php?succes=2&marqueid=$marque[marqueid]");
			exit();
		}
	}else{
		header('location: marques.php?erreurdroits=1');	
		exit();
	}
}


// AJOUTER OU MODIFIER UNE MARQUE
if (isset($action) and $action == "modifier") {
	
	$sitePrinc = $DB_site->query_first("SELECT * FROM site WHERE siteid='1'");
	
	if(isset($marqueid)){		
		$mq = $DB_site->query_first("SELECT * from marque 
									INNER JOIN marque_site USING(marqueid)
									WHERE marqueid = '$marqueid' 
									AND siteid='1'");
		$texte_entete="$multilangue[modif_marque] : $mq[libelle]";
		eval(charge_template($langue,$referencepage,"ModificationDefautBit"));
		
		$libNavigSupp="Modification de la marque <i><b>\"$mq[libelle]\"</b></i>";
		eval(charge_template($langue,$referencepage,"NavigSupp"));
	}else{
		$texte_entete="$multilangue[ajt_marque]";
		eval(charge_template($langue,$referencepage,"ModificationDefautBit"));
		
		$libNavigSupp="Ajout d'une nouvelle marque";
		eval(charge_template($langue,$referencepage,"NavigSupp"));
	}
	
	
	if ($action2 == "supprimage"){
		$mq=$DB_site->query_first("SELECT * from marque where marqueid = '$marqueid'");
		$fichier = $mq[marqueid].".".$mq[image] ;
		$folder = $rootpath."configurations/$host/images/marques";
		$folder2 = $rootpath."configurations/$host/images/marques/br";
		$folder3 = $rootpath."configurations/$host/images/marques/br2";
		if (file_exists($folder."/".$fichier)) {
			unlink($folder."/".$fichier);
			unlink($folder2."/".$fichier);
			unlink($folder3."/".$fichier);
			$DB_site->query("update marque set image = '' where marqueid = '$marqueid'");
		}
		$mq=$DB_site->query_first("SELECT * from marque where marqueid = '$marqueid'");
	}
	
	if ($mq[image] != "") {
		$fichier = $mq[marqueid].".".$mq[image] ;
		$folder = $rootpath."configurations/$host/images/marques";
		if (file_exists($folder."/".$fichier)) {
			$image = "<div data-provides=\"fileinput\" class=\"fileinput fileinput-new\">
							<div style=\"width: ".$marque_largeur."px; height: ".$marque_hauteur."px;\" data-trigger=\"fileinput\" class=\"fileinput-preview thumbnail\"><img src=\"/configurations/$host/images/marques/br/$fichier\"></div>
							<div>
								<span class=\"btn default btn-file\">
									<span class=\"fileinput-new\">$multilangue[selectionner]</span>
									<span class=\"fileinput-exists\">$multilangue[modification]</span>
									<input type=\"file\" name=\"mq_image\">
								</span>								
							</div>
						</div>
					 <a class=\"btn red\" href=\"marques.php?action=modifier&action2=supprimage&marqueid=$marqueid\" title=\"$multilangue[supprimer]\"><i class=\"fa fa-trash-o\"></i></a>" ;
		}else{
			$image = "
					<div data-provides=\"fileinput\" class=\"fileinput fileinput-new\">
						<div style=\"width: ".$marque_largeur."px; height: ".$marque_hauteur."px;\" data-trigger=\"fileinput\" class=\"fileinput-preview thumbnail\"><img src=\"$img\" alt=\"\"></div>
						<div>
							<span class=\"btn default btn-file\">
								<span class=\"fileinput-new\">$multilangue[selectionner]</span>
								<span class=\"fileinput-exists\">$multilangue[modification]</span>
								<input type=\"file\" name=\"mq_image\">
							</span>
						</div>
					</div>" ;
		}			
	} else {
		$image = "<input type=\"file\" class=\"form-control\" name=\"mq_image\">" ;
	}
	eval(charge_template($langue,$referencepage,"ModificationImage"));

	$sites = $DB_site->query("SELECT * FROM site WHERE siteid!='1'");		
	while($site = $DB_site->fetch_array($sites)){	
		$mqsite = $DB_site->query_first("SELECT * from marque 
									INNER JOIN marque_site USING(marqueid)
									WHERE marqueid = '$marqueid' 
									AND siteid='$site[siteid]'");
		eval(charge_template($langue,$referencepage,"ModificationSiteBit"));
	}
	
	eval(charge_template($langue,$referencepage,"Modification"));
}

// SUPPRIMER UNE MARQUE
if (isset($action) and $action == "supprimer2") {
	if($admin_droit[$scriptcourant][suppression]){
		$marque = $DB_site->query_first("SELECT * FROM marque
											INNER JOIN marque_site USING(marqueid)
											WHERE marqueid = '$marqueid'
											ORDER BY position");
		if ($marque[marqueid]!=""){
			$rq_positions_suivantes = $DB_site->query("SELECT marqueid, position FROM marque WHERE position > $marque[position]" );
			while ($rs_positions_suivantes=$DB_site->fetch_array($rq_positions_suivantes)) {
				$position_temp = $rs_positions_suivantes[position] - 1;
				$DB_site->query("UPDATE marque SET position = '$position_temp' WHERE marqueid = '$rs_positions_suivantes[marqueid]'");
			}
			if ($marque[image] != "") {
				@unlink($rootpath."configurations/$host/images/marques/$marqueid.$marque[image]");
				@unlink($rootpath."configurations/$host/images/marques/br/$marqueid.$marque[image]");
				@unlink($rootpath."configurations/$host/images/marques/br2/$marqueid.$marque[image]");
			}
			$suppr=$DB_site->query("delete from marque where marqueid = '$marqueid'");
			$suppr2=$DB_site->query("delete from article_marque where marqueid = '$marqueid'");
			$suppr3=$DB_site->query("delete from marque_site where marqueid = '$marqueid'");
			
			
			header("location: marques.php?succes=3&marqueid=$marque[marqueid]");
			exit();
		}else{		
			header("location: marques.php?erreur=1");
			exit();
		}
	}else{
		header('location: marques.php?erreurdroits=1');	
		exit();
	}
}

if (!isset($action) or $action == ""){
	$marques=$DB_site->query("SELECT * FROM marque 
								INNER JOIN marque_site USING(marqueid)
								WHERE siteid='1'
								ORDER BY position");
	$nb_marques=$DB_site->num_rows($marques) ;
	$i_marque = 0;
	while ($marque=$DB_site->fetch_array($marques)){
		$rowalt = "td_users".getrowbg();
		$i_marque++ ;
		$monter = "" ;
		$descendre = "" ;
		if ($marque[nofollow] == 1){
			$color_follow = "vert";
			$color2_follow = "green";
			$ico_follow = "fa-check-square-o";
			$tooltip_reference=$multilangue[passer_nonreference];
		}else{			
			$color_follow = "rouge";
			$color2_follow = "red";
			$ico_follow = "fa-square-o";
			$tooltip_reference=$multilangue[passer_reference];
		} 
			
		
		if($marque[visible_article]==1){
			$color_aff = "vert";
			$color2_aff = "green";
			$ico_aff = "fa-check-square-o";
			$tooltip_visible=$multilangue[passer_invisible];
		}else{
			$color_aff = "rouge";
			$color2_aff = "red";
			$ico_aff = "fa-square-o";
			$tooltip_visible=$multilangue[passer_visible];
		}
		eval(charge_template($langue,$referencepage,"ListeBit"));
	}
	eval(charge_template($langue,$referencepage,"Liste"));

	$libNavigSupp="Liste des marques";
	eval(charge_template($langue,$referencepage,"NavigSupp"));
}

$TemplateIncludejavascript = eval(charge_template($langue, $referencepage,"Includejavascript"));
eval(charge_template($langue,"commun","header"));
eval(charge_template($langue,"commun","footer"));
eval(charge_template($langue,$referencepage,"index"));

$nomtemplateindex="Template".ucfirst($referencepage)."Index";
echo ${$nomtemplateindex};

$DB_site->close();
flush();

?>