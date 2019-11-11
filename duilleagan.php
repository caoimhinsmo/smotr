<?php
  if (!include('autoload.inc.php'))
    header("Location:http://claran.smo.uhi.ac.uk/mearachd/include_a_dhith/?faidhle=autoload.inc.php");
  header('Cache-Control:max-age=0');

  try {
    $myCLIL = SM_myCLIL::singleton();
    $ceadaichte = SM_myCLIL::LUCHD_EADARTHEANGACHAIDH;
    if (!$myCLIL->cead($ceadaichte)) { $myCLIL->diultadh(''); }
    $myCLIL->dearbhaich();
    $Smotr = SM_SmotrPDOedit::singleton('r');

    $T = new SM_T('smotr/trstr');
    $hl0 = $T->hl0();
    $T_Sreang              = $T->_('Sreang');
    $T_domhan              = $T->_('domhan');
    $T_sreang              = $T->_('sreang');
    $T_fiosrachadh         = $T->_('fiosrachadh');
    $T_Cunntas             = $T->_('Cunntas');
    $T_Ceann_latha         = $T->_('Ceann_latha');
    $T_Parameter_p_a_dhith = $T->_('Parameter_p_a_dhith');
    $T_Chaidh_sreang_id_sguabadh     = $T->_('Chaidh_sreang_id_sguabadh');
    $T_Chaidh_sreang_id_dheasachadh  = $T->_('Chaidh_sreang_id_dheasachadh');
    $T_Chaidh_sreang_id_chruthachadh = $T->_('Chaidh_sreang_id_chruthachadh');
    $T_Duilleagan_str_cleachdadh     = $T->_('Duilleagan_str_cleachdadh');

    $HTML = $duilleaganHtml = '';

    if (!isset($_REQUEST['id'])) { throw new Exception(sprintf($T_Parameter_p_a_dhith,'id')); }
    $id = $_REQUEST['id'];
    $navbar = SM_Smotr::navbar($T->domhan,0,$id);
    
    $stmtSELfios = $Smotr->prepare('SELECT * FROM trstr WHERE id=:id');
    $stmtSELfios->execute([':id'=>$id]);
    $row = $stmtSELfios->fetch(PDO::FETCH_ASSOC);
    extract($row);
    $domhanHtml = htmlspecialchars($domhan);
    $strHtml    = htmlspecialchars($str);
    $fiosHtml   = htmlspecialchars($fios);

    $h1 = "$T_Sreang $id: &ldquo;$str&rdquo;”";
    $HTML .= "<h1 class=smo>$h1</h1>\n";

    $HTML .= <<<END_fiosrachadh
<p style='font-size:90%;color:grey'>
$T_domhan:$domhanHtml<br>
$T_fiosrachadh:$fiosHtml
</p>
END_fiosrachadh;

    $stmtSELduilleagan = $Smotr->prepare('SELECT frithealaiche,duilleag,cunntas,utime,query FROM smotrLog.trstrURL WHERE id=:id ORDER BY frithealaiche,duilleag');
    $stmtSELduilleagan->execute([':id'=>$id]);
    $duilleaganArr = $stmtSELduilleagan->fetchAll(PDO::FETCH_ASSOC);
    foreach ($duilleaganArr as $duilleagArr) {
        extract($duilleagArr);
        $url = htmlspecialchars("https://$frithealaiche$duilleag");
        $dateTimeObj = new DateTime("@$utime");
        $dateTime = $dateTimeObj->format('Y-m-d');
        $dateTime = "<span title='" . $dateTimeObj->format(' H:i:s') . "'>$dateTime</span>";
        $queryHtml = htmlspecialchars($query);
        if ($queryHtml) { $queryHtml = "<a href=\"$url?$queryHtml\">?$queryHtml</a>"; }
        $duilleaganHtml .= "<tr><td>$cunntas</td><td><a href='$url'>$url</a></td><td>$queryHtml</td><td>$dateTime</td></tr>\n";
    }
    if ($duilleaganHtml) { $HTML .= <<<END_duilleaganHtml
<p style="margin-bottom:0.2em">$T_Duilleagan_str_cleachdadh</p>
<table id=tortab>
<tr class=ceann><td>$T_Cunntas</td><td>URL</td><td>Query</td><td>$T_Ceann_latha</td></tr>
$duilleaganHtml
</table>
END_duilleaganHtml;
    }

  } catch (Exception $e) { $HTML = $e; }

  echo <<<END_duilleag
<!DOCTYPE html>
<html lang="$hl0">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex,nofollow">
    <title>Smotr: $h1</title>
    <link rel="StyleSheet" href="/css/smo.css">
    <style>
        table#tortab { border-collapse:collapse; margin:0 0 1em 0; border:1px solid grey; }
        table#tortab tr.ceann { background-color:grey; color:white; }
        table#tortab td { padding:2px 8px; }
        table#tortab td:nth-child(1) { text-align:right; }
        table#tortab td:nth-child(3) { font-size:70%; }
        table#tortab td:nth-child(4) { font-size:70%; }
    </style>
</head>
<body>

$navbar
<div class="smo-body-indent">

$HTML

</div>
$navbar

<div class="smo-latha">2019-10-25 <a href="http://www.smo.uhi.ac.uk/~caoimhin/cpd.html">CPD</a></div>
</body>
</html>
END_duilleag;

?>
