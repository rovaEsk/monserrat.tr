<?
list($usec, $sec) = explode(" ", microtime());
$tempdebutlancementpage=((float)$usec + (float)$sec);
if (!$rootpath)
	$rootpath = $_SERVER['DOCUMENT_ROOT'] ;
	
require_once($rootpath."admin/includes/admin_global.php");
header('Content-Type: application/json');

$tab_result = array ();
if (strlen($term)>1) {
	$term = utf8_decode(html_entity_decode(addslashes($term)));
	$i=0;
	
	//le premier champ du select doit �tre l'id et deuxieme le libell� � afficher
	//in il y a un trois�me champ il serra ajout� au libelle
	switch ($type) {
		case "marque":
			$req=$DB_site->query("SELECT marqueid, libelle FROM marque INNER JOIN marque_site USING(marqueid) WHERE libelle LIKE '%".$term."%' AND siteid = '1' ORDER BY libelle LIMIT 50");
		break;
		case "fournisseur":
			$req=$DB_site->query("SELECT fournisseurid, libelle FROM fournisseur WHERE libelle LIKE '%".$term."%' ORDER BY libelle asc LIMIT 50");
		break;
		case "bundle":
			$req = $DB_site->query("SELECT artid, $libelle, artcode FROM article WHERE ($libelle LIKE '%".$term."%' OR artcode LIKE '%".$term."%') AND artid != '$artid' ORDER BY $libelle LIMIT 50"); 
		break;
		case "article":
			$req = $DB_site->query("SELECT artid, libelle, artcode FROM article 
									INNER JOIN article_site AS asite USING(artid)
									WHERE (libelle LIKE '%".$term."%' 
									OR artcode LIKE '%".$term."%' 
									OR code_EAN LIKE '%".$term."%' 
									OR reference_fabricant LIKE '%".$term."%') 
									AND asite.siteid='1'
									ORDER BY libelle LIMIT 50"); 
		break;
		case "articleSeek":
			$req = $DB_site->query("SELECT DISTINCT(a.artid), a.$libelle FROM article a LEFT OUTER JOIN article_marque am ON a.artid=am.artid LEFT OUTER JOIN marque m ON am.marqueid = m.marqueid LEFT OUTER JOIN pays py ON a.pays_origine=py.paysid 
			WHERE a.$libelle LIKE '%".$term."%' OR a.$titre2 LIKE '%".$term."%' OR a.artcode LIKE '%".$term."%' OR a.code_EAN LIKE '%".$term."%' OR a.reference_fabricant LIKE '%".$term."%' OR m.$libelle LIKE '%".$term."%' OR py.libelle LIKE '%".$term."%' ORDER BY a.$libelle LIMIT 50"); 
			
			if($DB_site->num_rows($req)==0){ //Ajout benj si aucun r�sultat, on regarde dans stocks pour les variantes de r�f�rences...
				$req = $DB_site->query("SELECT DISTINCT(a.artid), a.$libelle FROM article a INNER JOIN stocks s ON (a.artid=s.artid) WHERE s.reference LIKE '%".$term."%' OR s.code_EAN LIKE '%".$term."%' OR s.reference_fabricant LIKE '%".$term."%' ORDER BY a.$libelle LIMIT 50");
			}
		
		break;
		case "articleSeekZone":
			$req = $DB_site->query("SELECT DISTINCT ss.zonestockage, s.zonestockage FROM stocks ss, stock s WHERE s.zonestockage LIKE '%".$term."%' OR ss.zonestockage LIKE '%".$term."%' ORDER BY ss.zonestockage LIMIT 50"); 
		break;
		case "villes":
			$req=$DB_site->query("SELECT id, nom FROM villes WHERE nom LIKE '%".$term."%' OR codepostal LIKE '%".$term."%' ORDER BY nom LIMIT 10");
		break;
		case "archives":
			$req=$DB_site->query("SELECT artid, $libelle, artcode FROM archive_article WHERE $libelle LIKE '%".$term."%' OR artcode LIKE '%".$term."%' ORDER BY $libelle LIMIT 50");
		break;
		case "tags":
			$req=$DB_site->query("SELECT tagid, $tag FROM tag WHERE $tag LIKE '%".$term."%' ORDER BY $tag LIMIT 50");
		break;
		case "categorie":
			$req = $DB_site->query("SELECT catid, libelle FROM categorie
									INNER JOIN categorie_site USING(catid)
									WHERE libelle LIKE '%".$term."%' AND siteid = '1'
									ORDER BY libelle LIMIT 50");
		break;
		case "client":
			$req=$DB_site->query("SELECT userid, CONCAT(nom, ' ', prenom, ' (', mail, ') ', raisonsociale) AS client FROM utilisateur WHERE nom LIKE '%".$term."%' OR prenom LIKE '%".$term."%' OR mail LIKE '%".$term."%' OR raisonsociale LIKE '%".$term."%' ORDER BY userid DESC LIMIT 50");
		break;
		case "cadeau":
			$req = $DB_site->query("SELECT cadeauid, code FROM cadeau WHERE code LIKE '%".$term."%' ORDER BY code LIMIT 50"); 
		break;
		case "referent":
			$req = $DB_site->query("SELECT groupe_referentid,libelle FROM groupe_referents WHERE libelle LIKE '%".$term."%' ORDER BY libelle LIMIT 50");
		break;
		case "clientFull":
			$req = $DB_site->query("SELECT userid, CONCAT(nom,' ',prenom) AS client FROM utilisateur WHERE nom LIKE '%".$term."%' OR prenom LIKE '%".$term."%' OR mail LIKE '%".$term."%' OR userid = '".$term."' ORDER BY userid DESC LIMIT 50");
		break;
		default:
		break;
	}
	while ($arr=$DB_site->fetch_array($req)) {
		$i++;
		switch ($type) {
			//cas sp�cial
			case "bundle":
				// On s�lectionne un produit et une combinaison (si l'article a des caract�ristiques)
				$lignesStocks = $DB_site->query("SELECT * FROM stocks WHERE artid = '$arr[artid]'");
				if ($DB_site->num_rows($lignesStocks)) {
					while ($ligneStocks = $DB_site->fetch_array($lignesStocks)) {
						$libArt = html_entity_decode($arr[$libelle]);
						$idArt = $arr[artid];
						$caractvals = $DB_site->query("SELECT cv.$libelle, cv.caractvalid FROM caracteristiquevaleur cv INNER JOIN stocks_caractval sc ON (sc.caractvalid = cv.caractvalid) WHERE sc.stockid = '$ligneStocks[stockid]'");
						while ($caractval = $DB_site->fetch_array($caractvals)) {
							$libArt .= " ".stripslashes($caractval[$libelle]);
							$idArt .= "_".$caractval[caractvalid];
						}
						$row_array['id'] = $idArt;
						if($ligneStocks[reference] != "") {
							$libArt.= " (".$ligneStocks[reference].")";
						}
						$row_array['value'] = html_entity_decode($libArt);		
						array_push($tab_result, $row_array);
					}
				} else {
					$row_array['id'] = $arr[artid];
					if($arr[artcode] != "") {
						$arr[$libelle].= " (".$arr[artcode].")";
					}
					$row_array['value'] = html_entity_decode($arr[$libelle]);
					array_push($tab_result, $row_array);				
				}
			break;
			case "articleSeekZone":
				
				if($arr[0] == "") {
					$zone = $arr[1];
				}else{
					$zone = $arr[0];
				}
				$row_array['id'] = $zone;
				$row_array['value'] = html_entity_decode($zone);
				array_push($tab_result, $row_array);
			break;
			default:
				$row_array['id'] = $arr[0];
				if($arr[2] != "") {
					$arr[1].= " (".$arr[2].")";
				}	
				$row_array['value'] = html_entity_decode($arr[1]);
				array_push($tab_result, $row_array);
			break;
		}
		
	}
	if ($i == 0) {
		$row_array['id'] = "";
        $row_array['value'] = html_entity_decode($multilangue[pas_de_reponse]);
		array_push($tab_result, $row_array);
	}else{
		if ($i == 50) {
			$row_array['id'] = "";
			$row_array['value'] = html_entity_decode("...");
			array_push($tab_result, $row_array);
		}
	}
}else{
	$row_array['id'] = "";
	$row_array['value'] = html_entity_decode($multilangue[saisissez_2_caracteres_minmum]);
	array_push($tab_result, $row_array);
}

//deconnexion
$DB_site->close();
//envoi de la r�ponse
echo json_encode($tab_result);
?>