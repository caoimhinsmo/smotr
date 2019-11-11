<?php
  if (!include('autoload.inc.php'))
    header("Location:http://claran.smo.uhi.ac.uk/mearachd/include_a_dhith/?faidhle=autoload.inc.php");
  header('Cache-Control:max-age=0');

  try {
    function mix ($rgb1,$rgb2,$proportion2) {
       $proportion1 = 1.0 - $proportion2;
       for ($i=0;$i<3;$i++) { $rgb[$i] = round( $proportion1*$rgb1[$i] + $proportion2*$rgb2[$i] ); }
       $rgbMix = implode(',',$rgb);
       return "rgb($rgbMix)";
    }

    $myCLIL = SM_myCLIL::singleton();
    $ceadaichte = SM_myCLIL::LUCHD_EADARTHEANGACHAIDH;
    if (!$myCLIL->cead($ceadaichte)) { $myCLIL->diultadh(''); }
    $myCLIL->dearbhaich();
    $Smotr = SM_SmotrPDOedit::singleton('r');

    $HTML = $tableHTML = '';

    $T = new SM_T('smotr/aireamhan');
    $hl0 =$T->hl0();
    $T_Aireamhan   = $T->_('Aireamhan');
    $T_Domhan      = $T->_('Domhan');
    $T_Iomlan      = $T->_('Iomlan');
    $T_ri_dheanamh = $T->_('ri_dheanamh');

    $navbar = SM_Smotr::navbar($T->domhan);

    $h1 = "Smotr: $T_Aireamhan";

    $stmtSELt = $Smotr->prepare('SELECT DISTINCT t FROM trtra ORDER BY t');
    $stmtSELt->execute();
    $tArr = $stmtSELt->fetchAll(PDO::FETCH_COLUMN);
    $tTitles = implode('</td><td>',$tArr);
    $titleRow = "<tr class=titleRow><td>$T_Domhan</td><td>$T_Iomlan</td><td>$tTitles</td></tr>";

    $stmtSELdomhan = $Smotr->prepare('SELECT domhan, COUNT(1) AS iomlan FROM trstr GROUP BY domhan ORDER BY domhan');
    $stmtSELdomhan->execute();
    $domhanArr = $stmtSELdomhan->fetchAll(PDO::FETCH_ASSOC);

    $aireamhan = $aireamhanB = [];
    $zeroArr = ['iomlan'=>0];
    foreach ($tArr as $t) { $zeroArr[$t] = 0; }

    foreach ($domhanArr as $domhanRow) {
        extract($domhanRow);
        $aireamhan[$domhan] = $zeroArr;
        $aireamhan[$domhan]['iomlan'] = $iomlan;
        $mirean = explode('/',substr($domhan,0,-1));
        while ($mirean) {
            array_pop($mirean);
            $domhanB = implode('/',$mirean);
            $aireamhanB[$domhanB] = $zeroArr;
        }
    }
    ksort($aireamhan);

    $stmtSELtcunntas = $Smotr->prepare('SELECT domhan, t, COUNT(1) AS cunntas FROM trstr, trtra WHERE trstr.id=trtra.id GROUP BY domhan,t ORDER BY domhan,t');
    $stmtSELtcunntas->execute();
    $tcunntasArr = $stmtSELtcunntas->fetchAll(PDO::FETCH_ASSOC); 
    foreach ($tcunntasArr as $tcunntasRow) {
        extract($tcunntasRow);
        $aireamhan[$domhan][$t] = $cunntas;
        $mirean = explode('/',substr($domhan,0,-1));
    }

    foreach ($aireamhan as $domhan=>$dRow) {
        $mirean = explode('/',substr($domhan,0,-1));
        while ($mirean) {
            $domhanB = implode('/',$mirean);
            if (isset($aireamhanB[$domhanB])) {
                foreach ($dRow as $key=>$value) {
                    $aireamhanB[$domhanB][$key] += $value;
                }
            }
            array_pop($mirean);
        }
    }

    foreach ($aireamhanB as $domhanB=>$value) { $aireamhanB[$domhanB]['ireB'] = substr_count($domhanB,'/'); }
    $aireamhan = array_merge($aireamhan,$aireamhanB);
    ksort($aireamhan);

    foreach ($aireamhan as $domhan=>$domhanRow) {
        $iomlan = $domhanRow['iomlan'];
        $tCellsHtml = '';
        foreach ($tArr as $t) {
            $cunntas = $cunntasHtml = $aireamhan[$domhan][$t];
            $riDheanamh = $aireamhan[$domhan]['iomlan'] - $cunntas;
            $proportion = $cunntas/$aireamhan[$domhan]['iomlan'];
            $percent = 100*$proportion;
            $cellTitle = sprintf("%.1f%%",$percent);
            if ($riDheanamh) { $cellTitle = "$cellTitle\n$riDheanamh $T_ri_dheanamh"; }
            if ($cunntas==0) {
                $cellStyle = 'color:#ccc';
                $cunntasHtml = '-';
            } elseif ($percent==100.0) {
                $cellStyle = 'background-color:rgb(0,170,0);color:black';
            } else {
                $dath = ( $proportion < 0.7
                        ? mix( [255,255,255], [255,175, 30], $proportion/0.7 )
                        : mix( [255,175, 30], [  0,170,  0], ($proportion-0.7)/0.3 )
                        );
                $cellStyle = "background-color:$dath;color:blue";
            }
            $cunntasHtml = "<a href='tr.php?domhan=$domhan&amp;t=$t&amp;tra=[NULL]'>$cunntasHtml</a>";
            $tCellsHtml .= "<td title='$cellTitle' style='$cellStyle'>$cunntasHtml</td>";
        }
        $rowClass = '';
        if (isset($domhanRow['ireB'])) {
            $ireB = $domhanRow['ireB'];
            $rowClass = " class=ire$ireB";
            if ($ireB==1) $tableHTML .= $titleRow;
        }
        $domhanHtml = ( $domhan ? "<a href='tr.php?domhan=$domhan'>$domhan</a>" : '' );
        $iomlanHtml = "<a href='tr.php?domhan=$domhan'>$iomlan</a>";
        $tableHTML .= <<<END_tableRow
<tr$rowClass>
<td>$domhanHtml</td>
<td>$iomlanHtml</td>
$tCellsHtml</tr>
END_tableRow;
    }

    $HTML .= <<<END_HTML
<h1>$h1</h1>

<table id=priomh>
$titleRow
$tableHTML
$titleRow
</table>
END_HTML;

  } catch (Exception $e) { $HTML = $e; }

  echo <<<END_duilleag
<!DOCTYPE html>
<html lang="$hl0">
<head>
    <meta charset="UTF-8">
    <title>$h1</title>
    <link rel="StyleSheet" href="/css/smo.css">
    <style>
        table#priomh { border-collapse:collapse; border:2px solid grey; margin:1em 0.3em; }
        table#priomh tr.titleRow { background-color:grey; color:white; }
        table#priomh tr.titleRow td { text-align:center; }
        table#priomh tr.titleRow td:first-child { text-align:left; padding-left:2em; }
        table#priomh tr.ire0,
        table#priomh tr.ire1,
        table#priomh tr.ire2,
        table#priomh tr.ire3,
        table#priomh tr.ire4 { font-style:italic; font-weight:bold; }
        table#priomh tr.ire2 { border-top:1px solid #bbb; }
        table#priomh tr { border-top:1px solid #bbb; }
        table#priomh td { text-align:right; padding:1px 10px; border-left:1px solid #bbb; border-right:1px solid #bbb; }
        table#priomh td:nth-child(1) { text-align:left; border-right:1px solid black; }
        table#priomh td:nth-child(2) { border-right:1px solid black; }
        table#priomh a { color:inherit; }
        table#priomh a:hover { color:inherit; background-color:inherit; }
    </style>
</head>
<body spellcheck=true>

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
