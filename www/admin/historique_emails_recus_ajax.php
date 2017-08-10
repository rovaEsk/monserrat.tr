<?php
	include "includes/header.php";
	
	$iDisplayLength = intval($length);
	$iDisplayStart = intval($start);

  	$records = array();
  	$records["aaData"] = array(); 

  	if(!isset($iDisplayStart))
  		$iDisplayStart = 0;
  	if(!isset($iDisplayLength))
  		$iDisplayLength = 10;
  	
  	switch($order[0][column]){
  		case "0" :
  			$orderby = "formulairereponseid";
  		break;
  		case "1" :
  			$orderby = "date";
  		break;
  		case "2" :
  			$orderby = "valeur";
  		break;
  		case "3" :
  			$orderby = "champ";
  		break;
  		case "4" :
  			$orderby = "valeur";
  		break;
  		default:
  			$orderby = "formulairereponseid";
  		break;
  	}
  	$sensorder = $order[0][dir];
  	$search[value] = securiserSql($search[value]);
	$where = "AND formulairereponseid LIKE '%$search[value]%'";
  	$where .= " OR champ LIKE '%$search[value]%' OR valeur LIKE '%$search[value]%'";
  	$mails = $DB_site->query("SELECT * FROM formulaire_reponse INNER JOIN formulaire_reponse_champ USING(formulairereponseid) WHERE formulaireid = '1' $where GROUP BY formulairereponseid ORDER BY $orderby $sensorder");
  	$mailcount = $DB_site->num_rows($mails);
  	
  	$limitlower = ($iDisplayLength != -1 ? $iDisplayStart : 0);
  	$perpage = ($iDisplayLength != -1 ? $iDisplayLength : $mailcount);
  	
  	if ($mailcount > 0) {
  		$mails = $DB_site->query("SELECT * FROM formulaire_reponse INNER JOIN formulaire_reponse_champ USING(formulairereponseid) WHERE formulaireid = '1' $where GROUP BY formulairereponseid ORDER BY $orderby $sensorder LIMIT $limitlower, $perpage");
  		while ($mail = $DB_site->fetch_array($mails)) {
  			$emails = $DB_site->query("SELECT * FROM formulaire_reponse_champ WHERE formulairereponseid = '$mail[formulairereponseid]'");
  			$tab = array();
  			while ($email = $DB_site->fetch_array($emails)){
  				$tab[$email[champ]] = $email[valeur];
  				if(preg_match('#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#', $email[valeur]))
  					$destinataire = $email[valeur];
  			}
  			$contenu = substr(strip_tags(stripslashes($tab[Message])), 0, 60) . '<a href="formulaires.php?action=reponseschamps&formulaireid=1&formulairereponseid=' . $mail[formulairereponseid] . '"><i> ' . $multilangue[lire_suite] . ' ...</i></a>';
  			$records["aaData"][] = array(
		      	"emailenvoyeid" => $mail[formulairereponseid],
		      	"dateline" => date("d/m/Y", strtotime($mail[date])) . ' ' . $mail[heure],
  				"destinataire" => $destinataire,
		      	"sujet" => $tab[Sujet],
		      	"contenu" => $contenu
	   		);
  		}
  	}

  	$records["iTotalRecords"] = $mailcount;
  	$records["iTotalDisplayRecords"] = $mailcount;
 
  	echo json_encode($records);
?>