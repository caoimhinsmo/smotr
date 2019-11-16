<?php
  if (!include('autoload.inc.php'))
    header("Location:http://claran.smo.uhi.ac.uk/mearachd/include_a_dhith/?faidhle=autoload.inc.php");
  header('Cache-Control:max-age=0');

  $T = new SM_T('smotr/index');
  $hl0 = $T->hl0();
  $T_SmotrTitle                      = $T->_('SmotrTitle');
  $T_Deasaich_eadar_theangachaidhean = $T->_('Deasaich_eadar_theangachaidhean');
  $T_As_phortaich                    = $T->_('As_phortaich');
  $T_Cruthaich_sreang_ur             = $T->_('Cruthaich_sreang_ur');
  $T_Aireamhan                       = $T->_('Aireamhan');

  $navbar = SM_Smotr::navbar($T->domhan,1);

  try {
    $myCLIL = SM_myCLIL::singleton();
    $ceadaichte  = SM_myCLIL::LUCHD_EADARTHEANGACHAIDH;
    $ceadaichte2 = SM_myCLIL::LUCHD_EADARTHEANGACHAIDH2;
    if (!$myCLIL->cead($ceadaichte)) { $myCLIL->diultadh(''); }
    $myCLIL->dearbhaich();
    $Smotr = SM_SmotrPDOedit::singleton('rw');

    $ceanglaichean = <<<EOD_ceanglaichean
<li><a href="tr.php">$T_Deasaich_eadar_theangachaidhean</a>
<li><a href="aireamhan.php">$T_Aireamhan</a>
<li><a href="xliff.php">$T_As_phortaich XLIFF</a>
EOD_ceanglaichean;

    if ($myCLIL->cead($ceadaichte2)) { $ceanglaichean .= "\n<li><a href=trstr.php?id=0>$T_Cruthaich_sreang_ur</a>"; }

    $HTML = <<<EOD_HTML
<h1 class=smo>$T_SmotrTitle</h1>

<ul id=priomhUL>
$ceanglaichean
</ul>
EOD_HTML;
    

  } catch (Exception $e) { $HTML = $e; }

  echo <<<END_duilleag
<!DOCTYPE html>
<html lang="$hl0">
<head>
    <meta charset="UTF-8">
    <title>$T_SmotrTitle</title>
    <link rel="StyleSheet" href="/css/smo.css">
    <style>
        ul#priomhUL li { margin-top:1em; }
    </style>
</head>
<body>

$navbar
<div class="smo-body-indent">

$HTML

</div>
$navbar

<div class="smo-latha">2019-10-26 <a href="http://www.smo.uhi.ac.uk/~caoimhin/cpd.html">CPD</a></div>
</body>
</html>
END_duilleag;

?>
