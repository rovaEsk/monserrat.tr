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
  			$orderby = "emailenvoyeid";
  		break;
  		case "1" :
  			$orderby = "dateline";
  		break;
  		case "2" :
  			$orderby = "destinataire";
  		break;
  		case "3" :
  			$orderby = "sujet";
  		break;
  		case "4" :
  			$orderby = "contenu";
  		break;
  		default:
  			$orderby = "emailenvoyeid";
  		break;
  	}
  	$sensorder = $order[0][dir];
  	$search[value] = securiserSql($search[value]);
	$where = "emailenvoyeid LIKE '%$search[value]%'";
  	$where .= " OR destinataire LIKE '%$search[value]%'";
  	$where .= " OR sujet LIKE '%$search[value]%'";
  	$where .= " OR contenu LIKE '%$search[value]%'";
  	$mails = $DB_site->query("SELECT * FROM emails_envoyes WHERE $where ORDER BY $orderby $sensorder");
  	$mailcount = $DB_site->num_rows($mails);
  	
  	$limitlower = ($iDisplayLength != -1 ? $iDisplayStart : 0);
  	$perpage = ($iDisplayLength != -1 ? $iDisplayLength : $mailcount);
  	
  	if ($mailcount > 0) {
  		$mails = $DB_site->query("SELECT * FROM emails_envoyes WHERE $where ORDER BY $orderby $sensorder LIMIT $limitlower, $perpage");
  		while ($mail = $DB_site->fetch_array($mails)) {
  			$contenu = substr(strip_tags(gzuncompress($mail[contenu])), 0, 60) . '<a href="#myModal' . $mail[emailenvoyeid] . '" data-toggle="modal"><i> ' . $multilangue[lire_suite] . ' ...</i></a>';
  			$contenu .= '<div aria-hidden="true" aria-labelledby="myModalLabe' . $mail[emailenvoyeid] . '" role="dialog" tabindex="-1" class="modal fade" id="myModal' . $mail[emailenvoyeid] . '" style="display: none;">';
  			$contenu .= '<div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button aria-hidden="true" data-dismiss="modal" class="close" type="button"></button>';
  			$contenu .= '<h4 class="modal-title">Email envoyé à "' . $mail[destinataire] . '"</h4></div>';
  			$contenu .= '<div class="modal-body"><b>' . $multilangue[date] . ' : </b>' . date("d/m/Y H:i:s", $mail[dateline]) . '<br><b>' . $multilangue[sujet] . ' : </b>' . $mail[sujet];
  			$contenu .= '<br><br><br>' . gzuncompress($mail[contenu]) . '</div></div></div></div>';
  			$records["aaData"][] = array(
		      	"emailenvoyeid" => $mail[emailenvoyeid],
		      	"dateline" => date("d/m/Y H:i:s", $mail[dateline]),
  				"destinataire" => $mail[destinataire],
		      	"sujet" => $mail[sujet],
		      	"contenu" => $contenu
	   		);
  		}
  	}

  	$records["iTotalRecords"] = $mailcount;
  	$records["iTotalDisplayRecords"] = $mailcount;
 
  	echo json_encode($records);
?>