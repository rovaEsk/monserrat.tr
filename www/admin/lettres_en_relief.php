<?php

include "includes/header.php";

$referencepage="lettres_en_relief";
$pagetitle = "Gestion du module lettres_en_relief - $host - Admin Arobases";

$titrepage=$multilangue[gestion_lettres_en_relief];
$lienpagebase="lettres_en_relief.php";
$niveaunavigsup="";

$class_menu_gestion_modules_lettres_en_relief_active = "active";

if(!parse_template($referencepage, $langue)){
	echo "erreur de chargement de template";
}

if($_GET[alert] == 1){
	$texteSuccess=$multilangue[les_coordonnees_ont_ete_mises_a_jour];
	eval(charge_template($langue,$referencepage,"Success"));
}

if(isset($erreurdroits) and $erreurdroits == 1){
	$texteErreur = $multilangue[action_page_refuse];
	eval(charge_template($langue, $referencepage, "Erreur"));
}

$articleModuleId =  4 ;
$widthbg = "300px";
$heightbg = "200px"; 
/** init form module **/
if (!isset($action) or $action == ""){
    if($admin_droit[$scriptcourant][suppression]){
        $sitesParams = $DB_site->query("SELECT * FROM site");
    	while($sitesParam = $DB_site->fetch_array($sitesParams)){
			$devise_site_actuel = $tabsites[$sitesParam[siteid]][devise_complete];
		}
		/*
    	$sites = $DB_site->query("SELECT * FROM articlemodule WHERE articlemoduleid=$articleModuleId");  
        $pays = $DB_site->query_first("SELECT * FROM pays WHERE paysid = '57'");
    	$image_module =  "http://$host/admin/assets/img/modulehtml5/default-image.png";
        while ($site = $DB_site->fetch_array($sites)){
            $class_wysiwyg="editeur";
            $articleModuleId = var2html($site[articlemoduleid]);
        	$titre_module=var2html($site[libelle]);
        	$texte_module=var2html($site[textemodule]);
            $image_module_extension=var2html($site[imagemodule]);
            if($image_module_extension != null){
                $image_module= "http://$host/admin/assets/img/modulehtml5/".$articleModuleId.".".$image_module_extension;
            }
            $prixModules = $DB_site->query("SELECT prixmoduleid FROM articlemodule_prix WHERE articlemoduleid=$articleModuleId");
            if ($DB_site->num_rows($prixModules) > 0) { 
                while ($prixModule = $DB_site->fetch_array($prixModules)){
                    $idprixmodule = $prixModule[prixmoduleid];
                    $prixReglesTaxes = $DB_site->query("SELECT * FROM prixarticlemodule WHERE prixmoduleid=$idprixmodule"); 
                    if ($DB_site->num_rows($prixReglesTaxes) > 0){
                        while ($prixReglesTaxe = $DB_site->fetch_array($prixReglesTaxes)){
                            $moduleprixid = $prixReglesTaxe[prixmoduleid];
                            $tvatauxnormale = $pays[TVAtauxnormal];
                            $prixmoduleTTC =  number_format((float)( $prixReglesTaxe[prixmodule] + ( $prixReglesTaxe[prixmodule] * $tvatauxnormale ) / 100), 2, '.', '');
                            $prixmoduleHTC = number_format((float)($prixReglesTaxe[prixmodule]), 2, '.', '');
                            $sitesDevise = $devise_site_actuel;
                            eval(charge_template($langue,$referencepage,"modulePrixBit"));            
                        }
                    }
                }
            }
        	eval(charge_template($langue,$referencepage,"moduleBit"));
        }*/
		$site = $DB_site->query_first("SELECT * FROM articlemodule WHERE articlemoduleid=$articleModuleId");  
        $pays = $DB_site->query_first("SELECT * FROM pays WHERE paysid = '57'");
    	$image_module =  "http://$host/admin/assets/img/modulehtml5/default-image.png";
    	
		$class_wysiwyg="editeur";
		$articleModuleId = var2html($site[articlemoduleid]);
        $titre_module = var2html($site[libelle]);
        $texte_module = var2html($site[textemodule]);
		$image_module_extension = var2html($site[imagemodule]);
		
		if($image_module_extension != null){
			$image_module= "http://$host/admin/assets/img/modulehtml5/".$articleModuleId.".".$image_module_extension;
		}
		
		/*$prixModules = $DB_site->query("SELECT prixmoduleid FROM articlemodule_prix WHERE articlemoduleid=$articleModuleId");
            if ($DB_site->num_rows($prixModules) > 0) { 
                while ($prixModule = $DB_site->fetch_array($prixModules)){
                    $idprixmodule = $prixModule[prixmoduleid];*/
					$request = "SELECT el.id AS epaisseurLettreId, el.epaisseur AS epaisseur, el.prix AS prix, lm.id AS lettreMatiereId 
								FROM epaisseurs_lettres AS el
								INNER JOIN lettres_matieres AS lm
					    			ON el.id_matiere = lm.id" ;
                    //$prixReglesTaxes = $DB_site->query("SELECT * FROM epaisseurs_lettres");
                    $prixReglesTaxes = $DB_site->query($request); 
                    if ($DB_site->num_rows($prixReglesTaxes) > 0){
                        while ($prixReglesTaxe = $DB_site->fetch_array($prixReglesTaxes)){
                        	
                    		$lettresMatieres = $DB_site->query("SELECT * FROM lettres_matieres");
                        	
                        	$TemplateLettres_en_reliefModulePrixCategorieBit = "";
                            while ($lettreMatiere = $DB_site->fetch_array($lettresMatieres)){
                                    $lettreMatiereId = $lettreMatiere[id];
                                    $lettreMatiereNom = $lettreMatiere[nom];
                                    $selectedOption = "";
                                    if($lettreMatiereId == $prixReglesTaxe[lettreMatiereId]){
                                        $selectedOption ="selected";
                                    }
                                    eval(charge_template($langue,$referencepage,"modulePrixCategorieBit"));            
                            }
                            //$libelle = $prixReglesTaxe[nom];
                            
                            $moduleprixid = $prixReglesTaxe[epaisseurLettreId];
                            $epaisseur = $prixReglesTaxe[epaisseur];
                            $lettreMatiere = $DB_site->query_first("SELECT * FROM lettres_matieres WHERE id=$prixReglesTaxe[lettreMatiereId]"); 
                            $couleur = $lettreMatiere[couleur];
                            $nommatiere = $lettreMatiere[nom];
                            $tvatauxnormale = $pays[TVAtauxnormal];
                            $prixmoduleTTC =  number_format((float)( $prixReglesTaxe[prix] + ( $prixReglesTaxe[prix] * $tvatauxnormale ) / 100), 2, '.', '');
                            $prixmoduleHTC = number_format((float)($prixReglesTaxe[prix]), 2, '.', '');
                            $sitesDevise = $devise_site_actuel;
                            eval(charge_template($langue,$referencepage,"modulePrixBit"));            
                        }
                    }
                /*}
            }*/
            /** get all categorie matiere  **/
            $categoriesMatieres = $DB_site->query("SELECT * FROM lettres_matieres"); 
            //$TemplateLettres_en_reliefModulePrixCategorieAjoutBit = "";
			while ($categorieMatiere = $DB_site->fetch_array($categoriesMatieres)){
				$categorieMatiereId = $categorieMatiere[id];
				$categorieMatiereNom = $categorieMatiere[nom];
				eval(charge_template($langue,$referencepage,"modulePrixCategorieAjoutBit"));            
			}
            
        	/** get all categorie matiere **/
        eval(charge_template($langue,$referencepage,"moduleBit"));
        	
    }else{
       header('location: lettres_en_relief.php?erreurdroits=1'); 
    }	
}


/** edit form module **/
if(isset($action) && $action == "modifmodule"){
	/*
    if($admin_droit[$scriptcourant][ecriture]){
        $articlemoduleid   = securiserSql($_POST['moduleid']);
        $titremodule    = securiserSql($_POST['titremodule']);
        $textmodule     = securiserSql($_POST['textemodule']);
        
        $DB_site->query("UPDATE articlemodule SET libelle = '$titremodule', textemodule = '$textmodule' WHERE articlemoduleid = '$articlemoduleid'");
        $prixModules = $DB_site->query("SELECT prixmoduleid FROM articlemodule_prix WHERE articlemoduleid=$articleModuleId");
        while($prixModule = $DB_site->fetch_array($prixModules)){
            $idprixmodule = $prixModule[prixmoduleid];
            $newprixmodule = $_POST['prixmodulehtc_'.$idprixmodule];
            $DB_site->query("UPDATE prixarticlemodule SET prixmodule = $newprixmodule WHERE prixmoduleid = '$idprixmodule'");
        }      
        if(!empty($_FILES['imageModule']['name'])){
    			$listeTypesAutorise = array("image/pjpeg", "image/jpeg", "image/gif", "image/png");
    			erreurUpload("imageModule", $listeTypesAutorise, 5048576);
    			if ($erreur == "" && !empty($_FILES['imageModule']['name'])){
    				$type_fichier = define_extention($_FILES['imageModule']['name']);
                   // p($type_fichier , true);
    				$DB_site->query("UPDATE articlemodule SET imagemodule = '$type_fichier' WHERE articlemoduleid = '$articlemoduleid'");
    				$nom_fichier = $rootpath."admin/assets/img/modulehtml5/".$articlemoduleid.".".$type_fichier;
    				copier_image($nom_fichier, 'imageModule');
    			}
    	}
        header('location: lettres_en_relief.php');   
    }else{
        header('location: lettres_en_relief.php?erreurdroits=1');	
    }
    */
	
	if($admin_droit[$scriptcourant][ecriture]){
        $articlemoduleid   = securiserSql($_POST['moduleid']);
        $titremodule    = securiserSql($_POST['titremodule']);
        $textmodule     = securiserSql($_POST['textemodule']);
        
        $DB_site->query("UPDATE articlemodule SET libelle = '$titremodule', textemodule = '$textmodule' WHERE articlemoduleid = '$articlemoduleid'");
        
        $epaisseursLettres = $DB_site->query("SELECT id FROM epaisseurs_lettres");
        while($epaisseurLettre = $DB_site->fetch_array($epaisseursLettres)){
            $id = $epaisseurLettre[id];
            $newlettrematiere = $_POST['categorieregleprix_'.$id];
            $newprix = $_POST['prixmodulehtc_'.$id];
            $newepaisseur = $_POST['epaisseur_'.$id];
            $lettreMatiere = $DB_site->query_first("SELECT couleur FROM lettres_matieres WHERE id=$newlettrematiere"); 
            $newcouleur = $lettreMatiere[couleur];
            //$newnom = "$newepaisseur mm";
            $DB_site->query("UPDATE epaisseurs_lettres SET id_matiere = $newlettrematiere, epaisseur = $newepaisseur, prix = $newprix, nom = $newepaisseur, couleurs = $newcouleur WHERE id = '$id'");
        }      
        if(!empty($_FILES['imageModule']['name'])){
    			$listeTypesAutorise = array("image/pjpeg", "image/jpeg", "image/gif", "image/png");
    			erreurUpload("imageModule", $listeTypesAutorise, 5048576);
    			if ($erreur == "" && !empty($_FILES['imageModule']['name'])){
    				$type_fichier = define_extention($_FILES['imageModule']['name']);
                   // p($type_fichier , true);
    				$DB_site->query("UPDATE articlemodule SET imagemodule = '$type_fichier' WHERE articlemoduleid = '$articlemoduleid'");
    				$nom_fichier = $rootpath."admin/assets/img/modulehtml5/".$articlemoduleid.".".$type_fichier;
    				copier_image($nom_fichier, 'imageModule');
    			}
    	}
        header('location: lettres_en_relief.php');   
    }else{
        header('location: lettres_en_relief.php?erreurdroits=1');	
    }
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
