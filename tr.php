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
    $bunPhasgan = $_SERVER['SCRIPT_NAME'];
    $bunPhasgan = substr ( $bunPhasgan, 0, strrpos($bunPhasgan,'/')  );

    $HTML = $domhanFhtml = $trFhtml = $suas = $needle = $plusStarHtml = '';
    $idLorg = 0; //Mar default, chan eilear a’ lorg str le id sonraichte
    $strArr = [];

    function glanSaoragan (&$param, &$paramHtml) {
        $param = strtr($param, ['\_'=>'\¤¤', '\%'=>'\¤¤', '\?'=>'\¤¤', '\*'=>'\¤¤']); //Caomhnaich escaped characters
        $param = strtr($param,'*?','%_');
        $param = strtr($param, ['%%%'=>'%','%%'=>'%','%_'=>'%','_%'=>'%']);
        $paramHtml = htmlspecialchars(strtr($param,'%_','*?'));
        $param     = strtr($param,     ['\¤¤'=>'\_', '\¤¤'=>'\%', '\¤¤'=>'\?', '\¤¤'=>'\*']); //Aisig escaped characters
        $paramHtml = strtr($paramHtml, ['\¤¤'=>'\_', '\¤¤'=>'\%', '\¤¤'=>'\?', '\¤¤'=>'\*']); //Aisig escaped characters
        if ($paramHtml=='*') { $paramHtml = ''; }
    }

    function soillsich ($haystack, $needle) {
        if (empty($needle)) { return $haystack; }
        return str_replace($needle, "<span class=soillsich>$needle</span>", $haystack);
    }

    $T = new SM_T('smotr/tr');
    $hl0 = $T->hl0();
    $T_Deasaich_eadar_theangachaidhean = $T->h('Deasaich_eadar_theangachaidhean');
    $T_Canan                           = $T->h('Language');
    $T_Criathair                       = $T->h('Criathair');
    $T_Criathraich                     = $T->h('Criathraich');
    $T_Domhan                          = $T->h('Domhan');
    $T_Domhan_eadar_theangachaidh      = $T->h('Domhan_eadar_theangachaidh');
    $T_Suas_aon_ire                    = $T->h('Suas_aon_ire');
    $T_Sgriobh_gus_gach_domhan_ranns   = $T->h('Sgriobh_gus_gach_domhan_ranns');
    $T_Lorg_eadar_theangachadh         = $T->h('Lorg_eadar_theangachadh');
    $T_Eadar_theangachadh_a_dhith      = $T->h('Eadar_theangachadh_a_dhith');
    $T_CananFios                       = $T->h('CananFios');
    $T_CananFios2                      = $T->h('CananFios2');
    $T_traFios                         = $T->h('traFios');
    $T_traPlaceholderPairt             = $T->h('traPlaceholderPairt');
    $T_traPlaceholderSlan              = $T->h('traPlaceholderSlan');
    $T_Sguab_as                        = $T->h('Sguab às');
    $T_barr                            = $T->h('barr');
    $T_pairt                           = $T->h('pairt');
    $T_slan                            = $T->h('slan');
    $T_pairtFios                       = $T->h('pairtFios');
    $T_slanFios                        = $T->h('slanFios');
    $T_Deasaich                        = $T->h('Deasaich');
    $T_Dublaich                        = $T->h('Dùblaich');
    $T_DeasaichFios                    = $T->h('DeasaichFios');
    $T_DublaichFios                    = $T->h('DublaichFios');
    $T_Fosgail_textarea                = $T->h('Fosgail_textarea');
    $T_Duin_textarea                   = $T->h('Duin_textarea');

    $T_Dublachadh_soirbheachail        = $T->j('Dublachadh_soirbheachail');
    $T_Dublachadh_mu_thrath            = $T->j('Dublachadh_mu_thrath');
    $T_Cod_canain_mi_iom               = $T->j('Cod_canain_mi_iom');
    $T_Error_in                        = $T->j('Error_in');
    $T_Sguab_as_tra_an_darireabh       = $T->j('Sguab_as_tra_an_darireabh');

    $T_Soillsich                       = $T->h('Soillsich');
    $T_SoillsichFios                   = $T->h('SoillsichFios');
    $T_Duilleagan_str_cleachdadh       = $T->h('Duilleagan_str_cleachdadh');

    $navbar = SM_Smotr::navbar($T->domhan);

    $domhanF = $_REQUEST['domhan'] ?? '';
    if (substr($domhanF,-1)=='/') {
        $domhanSaorag = 0;
    } else {
        $domhanSaorag = 1;
        $dFlastchar = substr($domhanF,-1); if ($dFlastchar=='*' || $dFlastchar=='%' ) { $domhanF = substr($domhanF,0,-1); }
        $dFlastchar = substr($domhanF,-1); if ($dFlastchar!='/') { $domhanF .= '/'; }
    }
    $domhanFhtml = htmlspecialchars($domhanF);
    if ($domhanSaorag) { $domhanFhtml .= '*'; }

    $tF      = ( !empty($_REQUEST['t'])      ? $_REQUEST['t']       : '%' ); glanSaoragan($tF,$tFhtml);
    $slan = $_REQUEST['slan'] ?? 0;
    $T_traPlaceholder = ( $slan ? $T_traPlaceholderSlan : $T_traPlaceholderPairt );
    if (empty($_REQUEST['tra'])) {
        $traF = '%';
        $traFhtml = '';
    } else {
        $traF = trim($_REQUEST['tra']);
        glanSaoragan($traF,$traFhtml);
        if (preg_match('|^\[(\d+)\]$|',$traF,$matches)) {
            $idLorg = $matches[1];
            $domhanF = '/%';
        } elseif ($traF!='[NULL]' && $slan==0) {
            $traF = "%$traF%";
        }
    }
    $slanToggleRange = "<input id=slanrange name=slan type=range min=0 max=1 step=1 value=$slan onchange=submitSlan() style=width:3em;margin:0;padding:0>";
    if ($slan) { $slanToggleHtml = "<a title='Lorg pàirt de dh’eadar-theangachadh' onclick=toggleSlan()>$T_pairt</a>$slanToggleRange<b>$T_slan</b>"; }
          else { $slanToggleHtml = "<b>$T_pairt</b>$slanToggleRange<a title='Lorg eadar-theangachadh slàn' onclick=toggleSlan()>$T_slan</a>"; }
    $slanToggleHtml = "<span class=toggle>$slanToggleHtml</span>";

    $cead2 = $myCLIL->cead(SM_myCLIL::LUCHD_EADARTHEANGACHAIDH2);  //Cead nas adhartaiche gus na sreangan fhéin atharrachadh
    if ($cead2) { $plusStarHtml = "<a href='trstr.php?id=0&amp;domhan=$domhanF' target=_blank>"
                                . "<img src='/icons-smo/plusStar.png' alt='Cruthaich sreang ùr' title='Cruthaich sreang ùr'></a>"; }
    $soillsichCanan = $_COOKIE['soillsichCanan'] ?? 'null';
    $stmtSELcanain = $Smotr->prepare('SELECT DISTINCT t FROM trtra ORDER BY t');
    $stmtSELcanain->execute();
    $soillsichCananOptArr = array_merge( [''], $stmtSELcanain->fetchAll(PDO::FETCH_COLUMN,0) );
    foreach ($soillsichCananOptArr as &$opt) {
        $selected = ( $opt==$soillsichCanan ? ' selected' : '');
        $opt = "<option$selected>$opt</option>";
    }
    $soillsichCananOptions = implode("\n",$soillsichCananOptArr);
    $HTML .= <<<EOD_ceann
<div style="margin-bottom:1em">
<h1 style='display:inline;font-size:110%;margin:0 0 3px 0'>$T_Deasaich_eadar_theangachaidhean</h1>
$plusStarHtml
<span style="padding-left:6em;font-size:80%" title="$T_SoillsichFios">
$T_Soillsich
<select id=soillsichCanan onchange="soillsichCanan()">
$soillsichCananOptions
</select>
</span>
</div>
EOD_ceann;

    $domhanFArr =[];
    if (!empty($_REQUEST)) {
        $mirean = explode('/',$domhanF);
        array_pop($mirean);
        while ($mirean) {
            $domhanFArr[] =  implode('/',$mirean) . '/';
            array_pop($mirean);
        } 
    }
    if ($domhanSaorag) { $domhanFArr[0] .= '%'; }
    if (!empty($domhanFArr[1])) {
        $domhanSuas = $domhanFArr[1];
    } else {
        $domhanSuas = '';
    }
    $suas = "<a href='tr.php?domhan=$domhanSuas' title='$T_Suas_aon_ire'>⬆</a>";

    $HTML .= <<<END_foirm
<fieldset id=lorgFS>
<legend>$T_Criathair</legend>
<form id=formLorg>
<span style="font-size:80%;font-weight:bold">$T_Domhan</span> <input name=domhan value="$domhanFhtml" title="$T_Domhan_eadar_theangachaidh" style="font-size:75%;font-weight:bold"> $suas
<span class=fios>($T_Sgriobh_gus_gach_domhan_ranns)</span>
<table id=formtab>
<tr style="font-size:75%;font-weight:bold"><td>$T_Canan</td><td style="padding-left:0.6em">$T_Lorg_eadar_theangachadh /
<a onclick="document.getElementById('tra').value='[NULL]'" style='text-decoration:underline'>$T_Eadar_theangachadh_a_dhith</a></td></tr>
<tr>
<td><input name=t id=t value="$tFhtml" title="$T_CananFios2" style="width:3em;text-align:center"></td>
<td style="padding-left:0.3em"><input name=tra id=tra value="$traFhtml" title="$T_traFios" placeholder="$T_traPlaceholder" style="width:70%"></td>
<tr><td></td><td style="padding:3px 0 0 4px">$slanToggleHtml</td></tr>
</tr>
</table>
<input name="Lorg" type="submit" value="$T_Criathraich">
</form>
</fieldset>
END_foirm;

    $stmtSELstr1 = $Smotr->prepare(
        'SELECT id, domhan, str, fios FROM trstr WHERE id=:id');
    $stmtSELstr2 = $Smotr->prepare(
        'SELECT DISTINCT trstr.id, domhan, str, fios FROM trstr LEFT JOIN trtra ON trstr.id=trtra.id'
       .' WHERE domhan LIKE :domhan AND (str LIKE :tra OR tra LIKE :tra)'
       .' ORDER BY domhan DESC,str');
    $stmtSELstr3 = $Smotr->prepare(
        'SELECT id, domhan, str, fios FROM trstr WHERE domhan LIKE :domhan AND str LIKE :tra'
       .' ORDER BY domhan DESC,str');
    $stmtSELstr4 = $Smotr->prepare(
        'SELECT DISTINCT trstr.id, domhan, str, fios FROM trstr LEFT JOIN trtra ON trstr.id=trtra.id AND t RLIKE :t '
       .' WHERE domhan LIKE :domhan AND tra IS NULL'
       .' ORDER BY domhan DESC,str');
    $stmtSELstr5 = $Smotr->prepare(
        'SELECT DISTINCT trstr.id, domhan, str, fios FROM trstr, trtra'
       .' WHERE trstr.id=trtra.id AND domhan LIKE :domhan AND t RLIKE :t AND tra LIKE :tra'
       .' ORDER BY domhan DESC,str');
    foreach ($domhanFArr as $ire=>$domhanF) {
       //Cruthaich clàr strArr de na str ri’ taisbeanadh
        if ($idLorg) { //Sonraichte: a’ lorg str le id fa leth
            $stmtSELstr = $stmtSELstr1;
            $stmtSELstr->execute([':id'=>$idLorg]);
        } elseif ($tF=='%') {
            $tF = '.*';
            $stmtSELstr = $stmtSELstr2;
            $stmtSELstr->execute([':domhan'=>$domhanF, ':tra'=>$traF]);
        } elseif ($tF=='[str]') {
            $stmtSELstr = $stmtSELstr3;
            $stmtSELstr->execute([':domhan'=>$domhanF, ':tra'=>$traF]);
        } elseif ($traF=='[NULL]') {
            $tF2 = '^(' . $tF . ')$';
            $stmtSELstr = $stmtSELstr4;
            $stmtSELstr->execute([':domhan'=>$domhanF, ':t'=>$tF2]);
        } else {
            $tF2 = '^(' . $tF . ')$';
            $stmtSELstr = $stmtSELstr5;
            $stmtSELstr->execute([':domhan'=>$domhanF, ':t'=>$tF2, ':tra'=>$traF]);
        }
        $strArr = $stmtSELstr->fetchAll(PDO::FETCH_ASSOC);

        if (substr($traF,0,1)=='%' && substr($traF,-1)=='%') { $needle = substr($traF,1,-1); }

        $stmtSELtra = $Smotr->prepare('SELECT t, tra, (t RLIKE :t AND tra LIKE :tra) AS soills FROM trtra WHERE id=:id ORDER BY t');
        foreach ($strArr as $strRow) {
            extract($strRow);
            $strHtml = $str;
            if ($fios) { $fios = " <img src=/icons-smo/info.gif title='$fios' alt=''>"; }
            if ($tF=='.*' || $tF=='[str]') { $strHtml = soillsich($str,$needle); }
            $stmtSELtra->execute([':id'=>$id, ':t'=>$tF, ':tra'=>$traF]);
            $traArr = $stmtSELtra->fetchAll(PDO::FETCH_ASSOC);
            $HTML .= "<div class=trid$ire id=tr$id>\n";
            $HTML .= "<div class=domhan title='$T_Domhan_eadar_theangachaidh'>$T_Domhan: " . ($domhan ? $domhan : "<i>($T_barr)</i>") . "</div>\n";
            $deasaichHtml = '';
            $deasaichHtml = <<<EOD_deasaichHtml
<a href='trstr.php?id=$id'><img src='/icons-smo/peann.png' alt='$T_Deasaich str' title='$T_DeasaichFios'></a>
<img src='/icons-smo/dublaich.png' onclick=dublaichStr($id) alt='$T_Dublaich str' title='$T_DublaichFios'>
EOD_deasaichHtml;
            $duilleaganHtml = <<<EOD_duilleaganHtml
<a href='duilleagan.php?id=$id' target='trduilleagan' title="$T_Duilleagan_str_cleachdadh"><img src='/icons-smo/td.gif' alt='duilleagan' style='padding:0 1px'></a>
(<a href='//www3.smo.uhi.ac.uk/teanga/smotr/duilleagan.php?id=$id' target='trduilleagan' title="www3.smo.uhi.ac.uk"><img src='/icons-smo/td.gif' style='padding:0 1px'></a>
<a href='//www2.smo.uhi.ac.uk/teanga/smotr_dev/duilleagan.php?id=$id' target='trduilleagan' title="www2.smo.uhi.ac.uk"><img src='/icons-smo/td.gif' style='padding:0 1px'></a>)
EOD_duilleaganHtml;
            $HTML .= "<div class=str>$strHtml$fios $duilleaganHtml $deasaichHtml</div>\n";
            $HTML .= "<table class='tratab'>\n";
            foreach ($traArr as $traRow) {
                extract($traRow);
                $traHtml = htmlspecialchars($tra);
                $soillsStyle = '';
                if ($soills && ($tF<>'.*' || $traF<>'%')) { $soillsStyle = ' style=background-color:yellow'; }
                $soillsichCananStyle = ( $t==$soillsichCanan ? ' style=font-weight:bold' : '' );
                $HTML .= "<tr class=inp><td><span id=\"$id-$t-changed\" class=change>✔<span></td>"
                       . "<td$soillsStyle>$t</td>"
                       . "<td lang=\"$t\">"
                       .   "<input$soillsichCananStyle class=inp value=\"$traHtml\" onchange=\"atharraichTra('$id','$t',this.value)\">"
                       .   "<textarea$soillsichCananStyle class=txa onchange=\"atharraichTra('$id','$t',this.value)\">$traHtml</textarea>"
                       . "</td>"
                       . "<td onclick='toggleInpTxa(this)'><a class=inp title='$T_Fosgail_textarea'>▼</a><a class=txa title='$T_Duin_textarea'>▲</a></td>"
                       . "<td><a onclick=sguabTra('$id','$t') title='$T_Sguab_as'>✘</a></td>"
                       . "</tr>\n";
            }
            $HTML .= "<tr id='$id-tur-row' class=inp><td><span id='$id-tur-changed' class=change>✔<span></td>"
                   . "<td><input id='$id-tur-t' value='' pattern='[-A-Za-z]+' style='width:3em' onchange=cruthaichTra('$id') title=\"$T_CananFios\"></td>"
                   . "<td>"
                   .   "<input class=inp onchange=cruthaichTra('$id')>"
                   .   "<textarea class=txa onchange=cruthaichTra('$id')></textarea>"
                   . "</td>"
                   . "<td onclick='toggleInpTxa(this)'><a class=inp title='$T_Fosgail_textarea'>▼</a><a class=txa title='$T_Duin_textarea'>▲</a></td>"
                   . "<td></td></tr>\n";
            $HTML .= "</table>\n";
            $HTML .= "</div>\n";
        }
    }

  } catch (Exception $e) { $HTML = $e; }

  echo <<<END_duilleag
<!DOCTYPE html>
<html lang="$hl0">
<head>
    <meta charset="UTF-8">
    <title>Deasaich eadar-theangachaidhean a tha gan cleachdadh ann am prògraman</title>
    <link rel="StyleSheet" href="/css/smo.css">
    <style>
        fieldset#lorgFS { margin-bottom:2em; background-color:#ddf; padding:3px 8px; border:2px solid #5ad; border-radius:0.5em; }
        fieldset#lorgFS legend { color:white; background-color:#5ad; padding:0 0.6em; font-size:95%; }
        input { margin:1px; padding:0; border:1px solid grey; }
        input[type=submit] { padding:2px 7px; border-radius:0.9em; color:white; background-color:#5ad; font-weight:bold; }
        input[type=submit]:hover { background-color:blue; }
        div.trid0,
        div.trid1,
        div.trid2,
        div.trid3,
        div.trid5 { margin:0.8em 0.4em; padding:0.2em; border:1px solid; border-radius:0.6em; background-color:#ccc; }
        div.trid1 { background-color:#fca; }
        div.trid2 { background-color:#fbb; }
        div.trid3 { background-color:#f99; }
        div.trid4 { background-color:#f88; }
        div.trid5 { background-color:#f77; }
        div.domhan { font-weight:bold; font-size:60%; color:red; }
        div.trid0 div.domhan { color:black; }
        div.trid1 div.domhan { color:brown; }
        div.str    { margin-left:6em; font-weight:bold; font-family:monospace; }
        table#formtab { margin:0.6em 0 0.6em 0.5em; width:100%; border-collapse:collapse; }
        table#formtab tr td:nth-child(1) { width:3em; text-align:center; }
        table#formtab input { margin:0; padding:0; border:0; }
        table.tratab { width:100%; border-collapse:collapse; }
        table.tratab input { margin:0; padding:1px 0; border:0; }
        table.tratab tr { vertical-align:top; }
        table.tratab td { margin:0 1px; padding:1px 0; }
        table.tratab tr:hover { background-color:#fdd; }
        table.tratab tr td:nth-child(1) { width:0.9em; text-align:center; }
        table.tratab tr td:nth-child(2) { width:3em; text-align:center; }
        table.tratab tr td:nth-child(2) input { text-align:center; }
        table.tratab tr td:nth-child(3) { width:100em; }
        table.tratab tr td:nth-child(3) input { width:100%; }
        table.tratab tr td:nth-child(3) input:hover,
        table.tratab tr td:nth-child(3) textarea:hover { background-color:#fea; }
        table.tratab tr td:nth-child(3) input:focus,
        table.tratab tr td:nth-child(3) textarea:focus { background-color:#fd9; }
        table.tratab tr td:nth-child(5) { width:1em; text-align:center; color:red; }
        table.tratab tr.inp .txa { display:none; }
        table.tratab tr.txa .inp { display:none; }
        table.tratab textarea { width:100%; font-family:Verdana,Arial,Helvetica,sans-serif; resize:vertical; }
        div.error { margin:0.5em; padding:0.5em; border:1px solid red; background-color:pink; color:red; font-weight:bold; }
        span.soillsich { background-color:yellow; }
        span.fios { color:green; font-size:70%; }
        span.change { opacity:0; color:white; }
        span.change.changed { color:green; animation:appearFade 5s; }
        @keyframes appearFade { from { opacity:1; background-color:yellow; } 20% { opacity:0.8; background-color:transparent; } to { opacity:0; } }
        span.toggle { margin:0 0 0 0.5em; border-radius:0.3em; padding:0.1em 0.3em 0 0.3em; background-color:#75c8fb; color:white; font-size:90%; }
        span.toggle b { color:yellow; }
        span.toggle input { font-size:90%; vertical-align:bottom; }
        select#soillsichCanan option { padding:0; }
    </style>
    <script>
        function soillsichCanan () {
            var canan = document.getElementById('soillsichCanan').value;
            document.cookie = 'soillsichCanan='+canan;
            location.reload();
        }
        function atharraichTra(id,t,tra) {
            tra = encodeURIComponent(tra).replace(/'/g,'%27');
            var xhttp = new XMLHttpRequest();
            xhttp.onload = function() {
                if (this.status!=200) { alert('$T_Error_in atharraichTra:'+this.status); return; }
                var el = document.getElementById(id+'-'+t+'-changed');
                el.classList.remove('changed'); //Remove the class (if required) and add again after a tiny delay, to restart the animation
                setTimeout(function(){el.classList.add('changed');},50);
            }
            var url = window.location.origin + '$bunPhasgan' + '/ajax/atharraichTra.php?id=';
            var params = 'id=' + id + '&t=' + t + '&tra=' + tra;
            xhttp.open('POST',url,true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send(params);
        }
        function cruthaichTra(id) {
            var tEl = document.getElementById(id+'-tur-t');
            var rowEl = tEl.parentNode.parentNode;
            var txaEl = rowEl.getElementsByTagName('textarea')[0];
            var traTd = txaEl.parentNode;
            var inpEl = traTd.getElementsByTagName('input')[0];
            var traEl;
            if (rowEl.className=='inp') { traEl = inpEl; } else { traEl = txaEl; }
            var t   = tEl.value;
            var tra = traEl.value;
            traTd.setAttribute('lang',t);
            if (t=='') {
                if (tra!='') { tEl.focus(); }
                return;
            }
            if (t=='')   { tEl.focus();   return; }
            if (tra=='') { traEl.focus(); return; }
            var regex = new RegExp("^[-A-Za-z]+$");
            if (!regex.test(t)) { alert('$T_Cod_canain_mi_iom'); tEl.focus(); return; }
            var row = document.getElementById(id+'-tur-row');
            var rows = row.parentNode.children;
            var i, tPrev;;
            tEl.style.color='inherit';
            for (i=0; i<rows.length-1; i++) {
                tPrev = rows[i].children[1].innerHTML;
                if (t==tPrev) { tEl.style.color='red'; alert('Tha an cànan sin ann mu-thràth'); return; }
            };
            traenc = encodeURIComponent(tra).replace(/'/g,'%27');
            t   = encodeURIComponent(t);
            var xhttp = new XMLHttpRequest();
            xhttp.onload = function() {
                if (this.status!=200) { alert('$T_Error_in cruthaichTra:'+this.status); return;}
                var tickEl = document.getElementById(id+'-tur-changed');
                tickEl.classList.remove('changed'); //Remove the class (if required) and add again after a tiny delay, to restart the animation
                setTimeout(function(){tickEl.classList.add('changed');},50);
                var newRow = document.createElement('tr');
                newRow.className = row.className;
                var newTd1 = document.createElement('td');
                var newTd2 = document.createElement('td');
                var newTd3 = document.createElement('td');
                var newTd4 = document.createElement('td');
                var newTd5 = document.createElement('td');
                newTd1.innerHTML = "<span id="+id+"-"+t+"-changed class=change>✔</span>";
                newTd2.innerHTML = t;
                var newInput    = document.createElement('input');
                var newTextarea = document.createElement('textarea');
                newInput.style = newTextarea.style = 'color:#090;font-weight:bold';
                newInput.value = newTextarea.value = tra;
                newInput.addEventListener('change',    function() { atharraichTra(id,t,this.value); });
                newTextarea.addEventListener('change', function() { atharraichTra(id,t,this.value); });
                newInput.setAttribute('lang',t);
                newTextarea.setAttribute('lang',t);
                newInput.className    = 'inp';
                newTextarea.className = 'txa';
                newTd3.appendChild(newInput);
                newTd3.appendChild(newTextarea);
                newTd4.innerHTML = "<a class=inp title='$T_Fosgail_textarea'>▼</a><a class=txa title='$T_Duin_textarea'>▲</a>"
                newTd4.addEventListener('click', function() { toggleInpTxa(this); });
                newTd5.innerHTML = "<a onclick=sguabTra("+id+",'"+t+"') title='$T_Sguab_as'>✘</a></td>"
                newRow.appendChild(newTd1);
                newRow.appendChild(newTd2);
                newRow.appendChild(newTd3);
                newRow.appendChild(newTd4);
                newRow.appendChild(newTd5);
                row.parentNode.insertBefore(newRow,row);
                tEl.value = traEl.value = '';
            }
            var url = window.location.origin + '$bunPhasgan' + '/ajax/cruthaichTra.php?id=';
            var params = 'id=' + id + '&t=' + t + '&tra=' + traenc;
            xhttp.open('POST',url,true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send(params);
        }
        function sguabTra(id,t) {
            if (!confirm('$T_Sguab_as_tra_an_darireabh')) { return; }
            var xhttp = new XMLHttpRequest();
            xhttp.onload = function() {
                if (this.status!=200) { alert('$T_Error_in sguabTra:'+this.status); return; }
                location.reload();
            }
            var url = window.location.origin + '$bunPhasgan' + '/ajax/sguabTra.php?id=' + id + '&t=' + t; 
            xhttp.open('GET',url,true);
            xhttp.send();
        }
        function toggleInpTxa(el) {
            var tr = el.parentNode;
            var txaEl = tr.getElementsByTagName('textarea')[0];
            var inpEl = txaEl.parentNode.getElementsByTagName('input')[0];
            if (tr.className=='inp') {
                txaEl.value = inpEl.value;
                tr.className = 'txa';
            } else {
                inpEl.value = txaEl.value;
                tr.className = 'inp';
            }
        }
        function toggleSlan() {
            var el = document.getElementById('slanrange');
            el.value = 1-el.value;
            submitSlan();
        }
        function submitSlan() {
            document.getElementById('formLorg').submit();
        }
        function dublaichStr(id) {
            if (!/^\d+$/.test(id)) { alert('$T_Error_in dublaichStr:\\n\\nNon-numeric parameter '+id); return; }
            var xhttp = new XMLHttpRequest();
            xhttp.onload = function() {
                if (this.status!=200) { alert('$T_Error_in dublaichStr:\\n\\n'+this.status); return; }
                var resp = this.responseText;
                if       (resp=='OK') { alert('$T_Dublachadh_soirbheachail'); }
                 else if (resp=='KO') { alert('$T_Dublachadh_mu_thrath'); }
                 else                 { alert('$T_Error_in dublaichStr:\\n\\n' + resp); }

            }
            var url = window.location.origin + '$bunPhasgan' + '/ajax/dublaichStr.php?id=' + id;
            xhttp.open('GET',url,true);
            xhttp.send();
        }
    </script>
</head>
<body spellcheck=true>

$navbar
<div class="smo-body-indent">

$HTML

</div>
$navbar

<div class="smo-latha">2019_08_29 <a href="http://www.smo.uhi.ac.uk/~caoimhin/cpd.html">CPD</a></div>
</body>
</html>
END_duilleag;

?>
