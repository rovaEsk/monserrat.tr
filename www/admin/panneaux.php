<?php

include "includes/header.php";

$referencepage="panneaux";
$pagetitle = "Gestion du module panneaux - $host - Admin Arobases";

$titrepage=$multilangue[gestion_panneaux];
$lienpagebase="panneaux.php";
$niveaunavigsup="";

$class_menu_gestion_modules_panneaux_active = "active";

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

$articleModuleId =  6 ; 
$widthbg = "300px";
$heightbg = "200px";
/** init form module **/
if (!isset($action) or $action == ""){
    if($admin_droit[$scriptcourant][suppression]){
        $sitesParams = $DB_site->query("SELECT * FROM site");
    	while($sitesParam = $DB_site->fetch_array($sitesParams)){
			$devise_site_actuel = $tabsites[$sitesParam[siteid]][devise_complete];
		}
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
            /*
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
                            $largeur = $prixReglesTaxe[largeur];
                            $hauteur = $prixReglesTaxe[hauteur];
                            $sitesDevise = $devise_site_actuel;
                            eval(charge_template($langue,$referencepage,"modulePrixBit"));            
                        }
                    }
                }
            }
            */
        	$panneauxEpaisseurs = $DB_site->query("SELECT * FROM panneaux_epaisseurs");
            if ($DB_site->num_rows($panneauxEpaisseurs) > 0) { 
                while ($panneauxEpaisseur = $DB_site->fetch_array($panneauxEpaisseurs)){
                    $panneauxEpaisseurId = $panneauxEpaisseur[id];
                    $panneauMatiere = $DB_site->query_first("SELECT * FROM panneaux_matieres WHERE id=$panneauxEpaisseur[id_panneau_matiere]");

                    $nomPanneauMatiere = $panneauMatiere[nom];
                    $nomPanneauEpaisseur = $panneauxEpaisseur[nom];
                    $prix1a5000 = $panneauxEpaisseur[prix_1_5000];
                    $prix5000a20000 = $panneauxEpaisseur[prix_5000_20000];
                    $prix20000 = $panneauxEpaisseur[prix_20000];
                    $prixOptionImage = $panneauxEpaisseur[prix_option_image];
                    
                    /*
                    $tvatauxnormale = $pays[TVAtauxnormal];
                    $prixmoduleTTC =  number_format((float)( $prixReglesTaxe[prixmodule] + ( $prixReglesTaxe[prixmodule] * $tvatauxnormale ) / 100), 2, '.', '');
                    $prixmoduleHTC = number_format((float)($prixReglesTaxe[prixmodule]), 2, '.', '');
                    $largeur = $prixReglesTaxe[largeur];
                    $hauteur = $prixReglesTaxe[hauteur];
                    */
                    $sitesDevise = $devise_site_actuel;
                    eval(charge_template($langue,$referencepage,"modulePrixBit"));

                }
            }
        	eval(charge_template($langue,$referencepage,"moduleBit"));
        }
    }else{
       header('location: panneaux.php?erreurdroits=1'); 
    }	
}
/** edit form module **/
if(isset($action) && $action == "modifmodule"){

    if($admin_droit[$scriptcourant][ecriture]){
        /*
    	$articlemoduleid   = securiserSql($_POST['moduleid']);
        $titremodule    = securiserSql($_POST['titremodule']);
        $textmodule     = securiserSql($_POST['textemodule']);
        
        $DB_site->query("UPDATE articlemodule SET libelle = '$titremodule', textemodule = '$textmodule' WHERE articlemoduleid = '$articlemoduleid'");
        $prixModules = $DB_site->query("SELECT prixmoduleid FROM articlemodule_prix WHERE articlemoduleid=$articleModuleId");
        while($prixModule = $DB_site->fetch_array($prixModules)){
            $idprixmodule = $prixModule[prixmoduleid];
            $newprixmodule = $_POST['prixmodulehtc_'.$idprixmodule];
            $largeur = $_POST['largeur_'.$idprixmodule];
            $hauteur = $_POST['hauteur_'.$idprixmodule];
            $DB_site->query("UPDATE prixarticlemodule SET prixmodule = $newprixmodule, largeur = $largeur, hauteur = $hauteur WHERE prixmoduleid = '$idprixmodule'");        }      
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
        header('location: panneaux.php');   
        */
    	$articlemoduleid   = securiserSql($_POST['moduleid']);
        $titremodule    = securiserSql($_POST['titremodule']);
        $textmodule     = securiserSql($_POST['textemodule']);
        
        $DB_site->query("UPDATE articlemodule SET libelle = '$titremodule', textemodule = '$textmodule' WHERE articlemoduleid = '$articlemoduleid'");
        $panneauxEpaisseurs = $DB_site->query("SELECT * FROM panneaux_epaisseurs");
        $prix='';
        while($panneauxEpaisseur = $DB_site->fetch_array($panneauxEpaisseurs)){
            $panneauEpaisseurId = $panneauxEpaisseur[id];
            $newprix1 = $_POST['prixmodulehtc1_'.$panneauEpaisseurId];
            $newprix5000 = $_POST['prixmodulehtc5000_'.$panneauEpaisseurId];
            $newprix20000 = $_POST['prixmodulehtc20000_'.$panneauEpaisseurId];
            $newprixImage = $_POST['prixmodulehtcimage_'.$panneauEpaisseurId];
            $DB_site->query("UPDATE panneaux_epaisseurs SET prix_1_5000 = $newprix1, prix_5000_20000 = $newprix5000, prix_20000 = $newprix20000, prix_option_image = $newprixImage WHERE id = '$panneauEpaisseurId'");        
			$prix=$prix.'-'.$newprixImage;
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
        header('location: panneaux.php'); 
    }else{
        header('location: panneaux.php?erreurdroits=1');	
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
