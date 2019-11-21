<?php
  if (!include('autoload.inc.php'))
    header("Location:http://claran.smo.uhi.ac.uk/mearachd/include_a_dhith/?faidhle=autoload.inc.php");
  header('Cache-Control:max-age=0');

  try {
    $myCLIL = SM_myCLIL::singleton();
    $ceadaichte = SM_myCLIL::LUCHD_EADARTHEANGACHAIDH;
    if (!$myCLIL->cead($ceadaichte)) { $myCLIL->diultadh(''); }
    $myCLIL->dearbhaich();
    $Smotr = SM_SmotrPDOedit::singleton('rw');

    $HTML = $xliff = $selected1 = $selected2 = '';

    $T = new SM_T('smotr/xliff');
    $hl0 = $T->hl0();
    $T_As_phortaich               = $T->_('As_phortaich');
    $T_Seall                      = $T->_('Seall');
    $T_Luchdaich_anuas            = $T->_('Luchdaich_anuas');
    $T_Domhan                     = $T->_('Domhan');
    $T_Domhan_eadar_theangachaidh = $T->_('Domhan_eadar_theangachaidh');
    $T_CananFios                  = $T->_('CananFios');
    $T_Tus_chanan                 = $T->_('Tus_chanan');
    $T_Canan_targaid              = $T->_('Canan_targaid');
    $T_Cruth                      = $T->_('Cruth');

    $navbar = SM_Smotr::navbar($T->domhan);

    $domhanF   = $_REQUEST['domhan'] ?? '';
    $source    = $_REQUEST['source'] ?? '';
    $target    = $_REQUEST['target'] ?? '';
    $format    = $_REQUEST['format'] ?? 'XLIFF1';
    $domhanF = trim($domhanF);
    $source  = trim($source);
    $target  = trim($target);
    $domhanF = strtr($domhanF,'%*_?','');
    $domhanF = trim($domhanF,'/');
    $domhanF = "/$domhanF/";
    if ($domhanF=='//') { $domhanF = '/'; }
    $domhanHSC = htmlspecialchars($domhanF);
    $sourceHSC = htmlspecialchars($source);
    $targetHSC = htmlspecialchars($target);
    $utime = time();
    $domhanF .= '%';
    if ($format=='XLIFF1') {
        $selected1 = ' selected';
        $xliffHead = "<xliff version=\"1.2\"><header><note>timestamp:$utime</note></header>";
        $fileHead1 = '<file id="';
        $fileHead2 = '" source-language="'.$source.'" target-language="'.$target.'"><body>';
        $fileFoot = '</body></file>';
        $segment = 'trans-unit';
        $ContentType = "Content-type:application/x-xliff+xml";
    } else {
        $selected2 = ' selected';
        $xliffHead = "<xliff xmlns=\"urn:oasis:names:tc:xliff:document:2.0\" version=\"2.0\" srcLang=\"$source\" trgLang=\"$target\">";
        $fileHead1 = '<file id="';
        $fileHead2 = '">';
        $fileFoot = '</file>';
        $segment = 'segment';
        $ContentType = "Content-type:application/xliff+xml";
    }
    $h1 = "$T_As_phortaich XLIFF";

    $domhanFArr = [];
    if ($source<>'' && $target<>'') {
        $domhanF2 = $domhanF;
        while(1) {
            $domhanFArr[] = $domhanF2;
            $slashpos = strrpos($domhanF2,'/');
           if ($slashpos===FALSE) break;
            $domhanF2 = substr($domhanF2,0,$slashpos);
        }
    }

    $HTML .= <<<END_foirm
<div style="margin-bottom:1em">
<h1 style='font-size:110%'>$h1</h1>

<fieldset id=lorgFS>
<form id=formLorg>
<table id=formtab>
<tr><td>$T_Domhan</td><td><input name=domhan value="$domhanHSC" title="$T_Domhan_eadar_theangachaidh" style="font-size:75%;font-weight:bold"></td></tr>
<tr><td>$T_Tus_chanan</td><td><input name=source required value="$sourceHSC" title="$T_CananFios" style="width:3em;text-align:center"></td></tr>
<tr><td>$T_Canan_targaid</td><td><input name=target required value="$targetHSC" title="$T_CananFios" style="width:3em;text-align:center"></td></tr>
<tr><td>$T_Cruth</td><td>
<select name=format>
<option$selected1 value=XLIFF1>XLIFF 1.2</option>
<option$selected2 value=XLIFF2>XLIFF 2.0</option>
</select></td></tr>
</table>
<input name="Seall" type="submit" value="$T_Seall">
<input name="Faidhle" type="submit" value="$T_Luchdaich_anuas">
</form>
</fieldset>
END_foirm;

    $stmtSELstr = $Smotr->prepare(
        'SELECT domhan, trstr.id, str, tra AS traSource, trstr.fios FROM trstr, trtra'
       .' WHERE trstr.id=trtra.id AND domhan LIKE :domhan AND t=:t'
       .' ORDER BY domhan,str');
    foreach ($domhanFArr as $domhanF) {
       //Cruthaich clàr strArr de na str ri’ taisbeanadh
        $stmtSELstr->execute([':domhan'=>$domhanF, ':t'=>$source]);
        $strDomhanArr = $stmtSELstr->fetchAll(PDO::FETCH_GROUP);
        $xliffIre = '';
        foreach ($strDomhanArr as $domhan=>$strArr) {
            $xliffDomhan = '';
            $stmtSELtra = $Smotr->prepare('SELECT tra AS traTarget FROM trtra WHERE id=:id AND t=:t');
            foreach ($strArr as $strRow) {
                extract($strRow);
                $stmtSELtra->execute([':id'=>$id,':t'=>$target]);
                $result = $stmtSELtra->fetch(PDO::FETCH_ASSOC);
                $traTarget = $result['traTarget'] ?? '';
                $strHSC    = htmlspecialchars($str);
                $traSourceHSC = htmlspecialchars($traSource);
                $traTargetHSC = htmlspecialchars($traTarget);
                $fiosHSC      = htmlspecialchars($fios);
                $xliffDomhan .= <<<EOD_Unit
    <unit id="$id">
      <$segment>
        <source>$traSourceHSC</source>
        <target>$traTargetHSC</target>
        <note>$fiosHSC</note>
      </$segment>
    </unit>

EOD_Unit;
            }
            $xliffIre .= <<<EOD_xliffDomhan
  $fileHead1$domhan$fileHead2
$xliffDomhan
  $fileFoot

EOD_xliffDomhan;
        }
        $xliff .= $xliffIre;
    }
    if ($xliff) { $xliff = <<<EOD_xliffHead
<?xml version="1.0" encoding="UTF-8"?>
$xliffHead
$xliff
</xliff>
EOD_xliffHead;
    }
    if (isset($_REQUEST['Faidhle'])) {
        header("$ContentType");
        header("Content-disposition:filename=\"smotr.xlf\"");
        echo $xliff;
        exit;
    }

    $xliff = htmlspecialchars($xliff);
    $HTML .= <<<EOD_xliff
<pre>
$xliff
</pre>
EOD_xliff;

  } catch (Exception $e) { $HTML = $e; }

  echo <<<END_duilleag
<!DOCTYPE html>
<html lang="$hl0">
<head>
    <meta charset="UTF-8">
    <title>$h1</title>
    <link rel="StyleSheet" href="/css/smo.css">
    <style>
        fieldset#lorgFS { margin-bottom:2em; background-color:#ddf; padding:3px 8px; border:2px solid #5ad; border-radius:0.5em; }
        fieldset#lorgFS legend { color:white; background-color:#5ad; padding:0 0.6em; font-size:95%; }
        input { margin:1px; padding:0; border:1px solid grey; }
        input[type=submit] { padding:2px 7px; border-radius:0.9em; color:white; background-color:#5ad; font-weight:bold; }
        input[type=submit]:hover { background-color:blue; }
        div.trid { margin:0.8em 0.4em; padding:0.2em; border:1px solid; border-radius:0.6em; background-color:#ddd; }
        div.domhan { font-weight:bold; font-size:60%; color:red; }
        div.trid div.domhan { color:black; }
        div.str    { margin-left:6em; font-weight:bold; font-family:monospace; }
        table#formtab { margin:0.6em 0 0.6em 0.5em; border-collapse:collapse; }
        table#formtab input { margin:0; padding:0; border:0; }
    </style>
</head>
<body>

$navbar
<div class="smo-body-indent">

$HTML

</div>
$navbar

<div class="smo-latha">2019_10-26 <a href="http://www.smo.uhi.ac.uk/~caoimhin/cpd.html">CPD</a></div>
</body>
</html>
END_duilleag;

?>
