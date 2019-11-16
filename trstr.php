<?php
  if (!include('autoload.inc.php'))
    header("Location:http://claran.smo.uhi.ac.uk/mearachd/include_a_dhith/?faidhle=autoload.inc.php");
  header('Cache-Control:max-age=0');

  try {
    $myCLIL = SM_myCLIL::singleton();
    $ceadaichte = SM_myCLIL::LUCHD_EADARTHEANGACHAIDH2;
    if (!$myCLIL->cead($ceadaichte)) { $myCLIL->diultadh(''); }
    $myCLIL->dearbhaich();
    $Smotr = SM_SmotrPDOedit::singleton('rw');

    $T = new SM_T('smotr/trstr');
    $hl0 = $T->hl0();
    $T_Sabhail             = $T->_('Sàbhail');
    $T_domhan              = $T->_('domhan');
    $T_sreang              = $T->_('sreang');
    $T_fiosrachadh         = $T->_('fiosrachadh');
    $T_Cruthaich_sreang_ur = $T->_('Cruthaich_sreang_ur');
    $T_Deasaich_sreang     = $T->_('Deasaich_sreang');
    $T_Parameter_p_a_dhith = $T->_('Parameter_p_a_dhith');
    $T_Sguab_as            = $T->_('Sguab às');
    $T_Rabhadh             = $T->_('Rabhadh');
    $T_Sguab_as_Bac_title  = $T->_('Sguab_as_Bac_title','eq');
    $T_Chaidh_sreang_id_sguabadh     = $T->_('Chaidh_sreang_id_sguabadh');
    $T_Chaidh_sreang_id_dheasachadh  = $T->_('Chaidh_sreang_id_dheasachadh');
    $T_Chaidh_sreang_id_chruthachadh = $T->_('Chaidh_sreang_id_chruthachadh');

    $HTML = $croisHtml = $athsheol = '';

    if (!isset($_REQUEST['id'])) { throw new Exception(sprintf($T_Parameter_p_a_dhith,'id')); }
    $id = $_REQUEST['id'];
    $domhan = $_REQUEST['domhan'] ?? '';
    $str    = $_REQUEST['str']    ?? '';
    $fios   = $_REQUEST['fios']   ?? '';
    $h1 = ( $id==0 ? $T_Cruthaich_sreang_ur
                   : "$T_Deasaich_sreang $id" );
    $HTML .= "<h1 class=smo>$h1</h1>\n";
    
    $navbar = SM_Smotr::navbar($T->domhan,0,$id);

    if (isset($_REQUEST['sguab'])) {
        $stmtDEL = $Smotr->prepare('DELETE FROM trstr WHERE id=:id');
        $stmtDEL->execute([':id'=>$id]);
        $T_Chaidh_sreang_id_sguabadh = sprintf($T_Chaidh_sreang_id_sguabadh,$id);
        $HTML = "<p>$T_Chaidh_sreang_id_sguabadh</p>\n";
    } else {
        if (isset($_REQUEST['sabhail'])) {
            if (empty($str))    { throw new Exception(sprintf($T_Parameter_p_a_dhith,'str')); }
            if ($id==0) {
                $stmtINS = $Smotr->prepare('INSERT INTO trstr (domhan,str,fios) VALUES (:domhan,:str,:fios)');
                $stmtINS->execute([':domhan'=>$domhan, ':str'=>$str, ':fios'=>$fios]);
                $id = $Smotr->lastInsertId();
                $T_Chaidh_sreang_id_chruthachadh = sprintf($T_Chaidh_sreang_id_chruthachadh,$id);
                $HTML = "<p>$T_Chaidh_sreang_id_chruthachadh</p>\n";
                $athsheolURL = SM_Smotr::smotrHome() . "trstr.php?id=$id";
                $athsheol = "\n<meta http-equiv='refresh' content='1;URL=$athsheolURL'>"; 
            } else {
                $stmtUPD = $Smotr->prepare('UPDATE trstr SET domhan=:domhan, str=:str, fios=:fios WHERE id=:id');
                $stmtUPD->execute([':domhan'=>$domhan, ':str'=>$str, ':id'=>$id, ':fios'=>$fios]);
                $T_Chaidh_sreang_id_dheasachadh = sprintf($T_Chaidh_sreang_id_dheasachadh,$id);
                $HTML = "<p>$T_Chaidh_sreang_id_dheasachadh</p>\n";
            }
        }
        if ($id<>0) {
            $stmtSEL = $Smotr->prepare('SELECT * FROM trstr WHERE id=:id');
            $stmtSEL->execute([':id'=>$id]);
            $row = $stmtSEL->fetch(PDO::FETCH_ASSOC);
            extract($row);
            $stmtSELtra = $Smotr->prepare('SELECT tra FROM trtra WHERE id=:id');
            $stmtSELtra->execute([':id'=>$id]);
            $croisHtml = ( $stmtSELtra->fetch()
                         ? "<span title='$T_Sguab_as_Bac_title'>✘</span>"
                          : "<a href='trstr.php?id=$id&amp;sguab' style='color:red' title='$T_Sguab_as'>✘</a>" );
        }
        $domhanHtml = htmlspecialchars($domhan);
        $strHtml    = htmlspecialchars($str);
        $fiosHtml   = htmlspecialchars($fios);
        $HTML .= <<<ENDform
<p style="color:red;font-weight:bold">$T_Rabhadh</p>
<form>
<input name=id type=hidden value='$id'>
<table id=formtab>
<tr><td>$T_domhan</td><td><input name=domhan value='$domhanHtml' pattern="^\/.*\/$|^\/$" style='width:30em;font-size:60%;font-weight:bold'></td></tr>
<tr><td>$T_sreang</td><td><input name=str value='$strHtml' style='width:35%;font-weight:bold'>$croisHtml</td><tr>
<tr><td>$T_fiosrachadh</td><td><input name=fios value='$fiosHtml' style='width:60em'></td></tr>
<tr><td></td><td><input name=sabhail type=submit value='$T_Sabhail'></td></tr>
</table>
</form>
ENDform;
    }

  } catch (Exception $e) { $HTML = $e; }

  echo <<<END_duilleag
<!DOCTYPE html>
<html lang="$hl0">
<head>
    <meta charset="UTF-8">$athsheol
    <title>$h1</title>
    <link rel="StyleSheet" href="/css/smo.css">
    <style>
        form input { margin:1px; padding:0; border:1px solid grey; }
        form input[type=submit] { padding;2px 5px; border-radius:5px; background-color:#ffd; }
        form input[type=submit]:hover { background-color:#fe6; }
        div.str    { font-weight:bold; font-size:120%; }
        div.error { margin:0.5em; padding:0.5em; border:1px solid red; background-color:pink; color:red; font-weight:bold; }
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
