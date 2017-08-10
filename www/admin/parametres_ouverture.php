<?php
set_time_limit(0) ; 

include "./includes/header.php";

$referencepage="parametres_ouverture";
$pagetitle = "Paramètres d'ouverture - $host - Admin Arobases";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

if($_GET[alert] == 1){
	$texteSuccess= "Les modifications ont bien été efectuées";
	eval(charge_template($langue,$referencepage,"Success"));
}
if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

//Redimensionnement des images articles
if( isset($action) and $action=="redimensionner_article" ) {
	//Articles
	$full_path = $rootpath."configurations/$host/images/produits/";
	$scenes_hauteur = $scenes_article_hauteur;
	$scenes_largeur = $scenes_article_largeur;
	$br_hauteur = $hauteurimages;
	$br_largeur = $largeurimages;
	$last_article=0;
	// $last_article=999999999999999;
	if ($_POST[lastartid]){
		$last_article = $_POST[lastartid];
	}
	$articles = $DB_site->query("SELECT artid, image FROM article WHERE image != '' AND artid >'".mysql_real_escape_string($last_article)."' ORDER BY artid LIMIT 5");
	// $articles = $DB_site->query("SELECT artid, image FROM article WHERE image != '' AND artid <'".mysql_real_escape_string($last_article)."' ORDER BY artid DESC LIMIT 5");
	while ($article=$DB_site->fetch_array($articles)){
		$img = $article[artid].'.'.$article[image];
		if ($handle = opendir($full_path)) {
			while (false !== ($file = readdir($handle))) {
				if(is_dir($full_path."/".$file) and $file!=".." and $file!="." and $file!="old" and $file!="3D"){// un répertoire de miniature ....
					// if (${$file} == "on"){//Si le dossier est coché
					if ($_POST[$file] == "on"){//Si le dossier est coché
						// echo $file." -> ".${$file."_hauteur"}." ".${$file."_largeur"}."<br>";
						$full_path_file = $rootpath."configurations/$host/images/produits/$file/";
						if ($handle_file = opendir($full_path_file)) {
							$fichier=$rootpath."configurations/".$host."/images/produits/".$img ;
							$destination=$rootpath."configurations/".$host."/images/produits/$file/".$img ;
							if (file_exists($fichier)){
								redimentionner_image($fichier,$destination,${$file."_largeur"},${$file."_hauteur"},$couleurfondimages);
								// echo $fichier.'<br>';
							}
							$article_photos = $DB_site->query("SELECT articlephotoid, image FROM articlephoto WHERE artid = '$article[artid]' ORDER BY articlephotoid");
							while ($article_photo=$DB_site->fetch_array($article_photos)){
								$img2 = $article[artid].'_'.$article_photo[articlephotoid].'.'.$article_photo[image];
								$fichier2=$rootpath."configurations/".$host."/images/produits/".$img2 ;
								$destination2=$rootpath."configurations/".$host."/images/produits/$file/".$img2 ;
								if (file_exists($fichier2)){
									redimentionner_image($fichier2,$destination2,${$file."_largeur"},${$file."_hauteur"},$couleurfondimages);
									// echo $fichier2.'<br>';
								}
							}
						}
					}
				}
			}
		}
		$last_article = $article[artid];
		eval(charge_template($langue,$referencepage,"ImagesArticleFormSucces"));
	}
	if ($last_article && $last_article != $_POST[lastartid]){
		foreach ($_POST as $dossier => $val){
			if ($val == 'on'){
				eval(charge_template($langue,$referencepage,"ImagesArticleFormSuivantOption"));
			}	
		}
		eval(charge_template($langue,$referencepage,"ImagesArticleFormSuivant"));
	}
	eval(charge_template($langue,$referencepage,"ImagesArticleForm"));
}	

//Redimensionnement des images articles
if( isset($action) and $action=="redimensionner" ) {
	$sites = $DB_site->query("SELECT * FROM site");
	while($site = $DB_site->fetch_array($sites)){
		$full_path = "../configurations/$host/images/categories/$site[siteid]/";
		$categorie_br_hauteur = $categorie_description_hauteur;
		$categorie_br_largeur = $categorie_description_largeur;
		if ($handle = opendir($full_path)) {
			while (false !== ($file = readdir($handle))) {
				if(is_dir($full_path."/".$file) and $file!=".." and $file!="."){
					// un répertoire de miniature ....
					$full_path_file = "../configurations/$host/images/categories/$site[siteid]/$file/";
					if ($handle_file = opendir($full_path)) {
						while (false !== ($img = readdir($handle_file))) {
							if(is_file($full_path.$img) && !is_dir($full_path.$img) && $full_path.$img!=".." && $full_path.$img!="."){
								$fichier=$rootpath."configurations/".$host."/images/categories/$site[siteid]/".$img ;
								$destination=$rootpath."configurations/".$host."/images/categories/$site[siteid]/$file/".$img ;
								if (file_exists($fichier)){
									redimentionner_image($fichier,$destination,${"categorie_".$file."_largeur"},${"categorie_".$file."_hauteur"},$couleurfondimages);
								}
							}
						}
					}
				}
			}
		}
	}
	header("Location: parametres_ouverture.php?alert=1");
}	


//Regénération des PDF
if (isset($action) && $action == "genererPDF") {
	if (count($checkbox_pdf)) {
		foreach ($checkbox_pdf as $dossier => $value) {
			$full_path = $rootpath."configurations/$host/factures/pdf/$dossier/";
			if ($handle_file = opendir($full_path)) {
				while (false !== ($fichier = readdir($handle_file))) {
					if(is_file($full_path.$fichier) && !is_dir($full_path.$fichier) && $full_path.$fichier!=".." && $full_path.$fichier!="."){
						$extension = substr($full_path.$fichier, -3);
						if ($extension == "pdf") {
							@unlink($full_path.$fichier);
						}
					}
				}
			}
		} 
	}
	header("Location: parametres_ouverture.php?alert=1");
}

if (!isset($action) or $action == ""){
	// Redimensionnement images articles
	$full_path = $rootpath."configurations/$host/images/produits/";
	if ($handle = opendir($full_path)) {
		$repertoires = array();
		while (false !== ($repertoire = readdir($handle))) {
			if(is_dir($full_path."/".$repertoire) and $repertoire!=".." and $repertoire!="." and $repertoire!="old" and $repertoire!="3d"){
				// un répertoire de miniature ....
				$repertoires[] = $repertoire;
			}
		}
		sort($repertoires);
		foreach($repertoires as $repertoire){
			eval(charge_template($langue,$referencepage,"ImagesArticleBit"));
		}
	}
	eval(charge_template($langue,$referencepage,"ImagesArticle"));
	
	// Redimensionnement images catégorie
	$full_path = $rootpath."configurations/$host/images/categories/1/";
	if ($handle = opendir($full_path)) {
		$repertoires = array();
		while (false !== ($repertoire = readdir($handle))) {
			if(is_dir($full_path."/".$repertoire) and $repertoire!=".." and $repertoire!="."){
				// un répertoire de miniature ....
				$repertoires[] = $repertoire;
			}
		}
		sort($repertoires);
		foreach($repertoires as $repertoire){
			eval(charge_template($langue,$referencepage,"ImagesCategorieBit"));
		}
	}
	eval(charge_template($langue,$referencepage,"ImagesCategorie"));
	// Redimensionnement images produits_caractvals
	if(in_array(5941,$modules)){
		$full_path = $rootpath."configurations/$host/images/produits_caractvals/";
		if ($handle = opendir($full_path)) {
			$repertoires = array();
			while (false !== ($repertoire = readdir($handle))) {
				if(is_dir($full_path."/".$repertoire) and $repertoire!=".." and $repertoire!="."){
					// un répertoire de miniature ....
					$repertoires[] = $repertoire;
				}
			}
			sort($repertoires);
			foreach($repertoires as $repertoire){
				eval(charge_template($langue,$referencepage,"ImagesCaractvalBit"));
			}
		}
		eval(charge_template($langue,$referencepage,"ImagesCaractval"));
	}
	eval(charge_template($langue,$referencepage,"ImagesAutre"));

	// Regénération PDF
	$full_path = $rootpath."configurations/$host/factures/pdf/";
	if ($handle = opendir($full_path)) {
		$repertoires = array();
		while (false !== ($repertoire = readdir($handle))) {
			if(is_dir($full_path."/".$repertoire) and $repertoire!=".." and $repertoire!="."){
				// un répertoire de miniature ....
				$repertoires[] = $repertoire;
			}
		}
		sort($repertoires);
		foreach($repertoires as $repertoire){
			eval(charge_template($langue,$referencepage,"RegenerationPDFBit"));
		}
	}
	eval(charge_template($langue,$referencepage,"RegenerationPDF"));
}

//include "./includes/footer.php";
eval(charge_template($langue,"commun","header"));
eval(charge_template($langue,"commun","footer"));
eval(charge_template($langue,$referencepage,"index"));

$nomtemplateindex="Template".ucfirst($referencepage)."Index";
echo ${$nomtemplateindex};

$DB_site->close();
flush();
?>