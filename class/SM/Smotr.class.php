<?php
class SM_Smotr {

  public static function smotrHomeDir() {
      if ($_SERVER['SERVER_NAME']=='www2.smo.uhi.ac.uk') { return '/teanga/smotr_dev'; }
      return '/teanga/smotr';
  }

  public static function navbar($domhan='',$duilleagAghaidh=0,$str=0) {
      $smohl = SM_T::hl0();
      $smotrHome = self::smotrHomeDir();
      $smotrUrl = 'https://' . $_SERVER['SERVER_NAME'] . $smotrHome;
      $T = new SM_T('smotr/navbar');
      $T_SmotrTitle    = $T->_('SmotrTitle');
      $T_canan_eadarAghaidh = $T->_('canan_eadarAghaidh','hsc');
      $T_Log_air            = $T->_('Log_air','hsc');
      $T_Log_air_fios       = $T->_('Log_air_fios','hsc');
      $T_tr_fios            = $T->_('tr_fios','hsc');
      if ($duilleagAghaidh) { $SmotrCeangal = "<li><a href='/toisich/' title='Sabhal Mór Ostaig - prìomh dhuilleag (le dà briog)'>SMO</a>"; }
        else                { $SmotrCeangal = "<li><a href='$smotrHome' title='$T_SmotrTitle'>Smotr</a>"; }
      $strCeangal = "$smotrHome/tr.php?tra=[$str]";
      $strCeangal = ( $str ? "<li><a href='$strCeangal'>Sreang $str</a>" : '' );
      $myCLIL = SM_myCLIL::singleton();
      if ($myCLIL->cead(SM_myCLIL::LUCHD_EADARTHEANGACHAIDH) && !empty($domhan))
        { $trPutan = "\n<li class=deas><a href='/teanga/smotr/tr.php?domhan=$domhan' target='tr' title='$T_tr_fios'>tr</a>"; } else { $trPutan = ''; }
      $ceangalRiMoSMO = ( isset($myCLIL->id)
                        ? "<li class='deas'><a href='$smotrHome/logout.php' title='Log out from myCLIL'>Logout</a></li>"
                        : "<li class='deas'><a href='$smotrHome/login.php?till_gu=$smotrUrl/' title='$T_Log_air_fios'>$T_Log_air</a></li>"
                        );
      $hlArr = array(
          'br'=>'Brezhoneg',
          'de'=>'Deutsch',
          'en'=>'English',
          'fr'=>'Français',
          'ga'=>'Gaeilge',
          'gd'=>'Gàidhlig',
          'cy'=>'Cymraeg (anorffenedig)',
          'da'=>'Dansk (ufuldstændig)',
          'es'=>'Español (incompleto)',
          'it'=>'Italiano (incompleto)',
          'bg'=>'Български (непълен)');
      $options = '';
      foreach ($hlArr as $hl=>$hlAinm) { $options .= "<option value='$hl|en'" . ( $hl==$smohl ? ' selected' : '' ) . ">$hlAinm</option>\n"; }
      $selCanan = <<< END_selCanan
<script>
    function atharraichCanan(hl) {
        document.cookie='smohl='+hl;
        const params = new URLSearchParams(location.search)
        params.delete('hl');
        var paramstr = params.toString();
        if (paramstr!='') { paramstr = '?'+paramstr; }
        loc = window.location;
        location = loc.protocol + '//' + loc.hostname + loc.pathname + paramstr;
    }
</script>
<form>
<select name="hl" style="display:inline-block;background-color:#eef;margin:0 4px" onchange="atharraichCanan(this.options[this.selectedIndex].value)">
$options</select>
</form>
END_selCanan;
      $navbar = <<<EOD_NAVBAR
<ul class="smo-navlist">
$SmotrCeangal
$strCeangal
$ceangalRiMoSMO
<li style="float:right" title="$T_canan_eadarAghaidh">$selCanan$trPutan
</ul>
EOD_NAVBAR;
      return $navbar;
  }

}
?>
