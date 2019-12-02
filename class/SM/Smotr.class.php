<?php
class SM_Smotr {

  public static function smotrHomeDir() {
      if ($_SERVER['SERVER_NAME']=='www2.smo.uhi.ac.uk') { return '/teanga/smotr_dev'; }
      return '/teanga/smotr';
  }
  public static function smotrUrl() {
      return 'https://' . $_SERVER['SERVER_NAME'] . self::smotrHomeDir();
  }

  public static function navbar($domhan='',$duilleagAghaidh=0,$str=0) {
      $smohl = SM_T::hl0();
      $smotrHomeDir = self::smotrHomeDir();
      $smotrUrl     = self::smotrUrl();
      $T = new SM_T('smotr/navbar');
      $T_SmotrTitle         = $T->h('SmotrTitle');
      $T_canan_eadarAghaidh = $T->h('canan_eadarAghaidh');
      $T_Log_air            = $T->h('Log_air');
      $T_Log_air_fios       = $T->h('Log_air_fios');
      $T_tr_fios            = $T->h('tr_fios');
      if ($duilleagAghaidh) { $SmotrCeangal = "<li><a href='/toisich/' title='Sabhal Mór Ostaig - prìomh dhuilleag (le dà briog)'>SMO</a>"; }
        else                { $SmotrCeangal = "<li><a href='$smotrHomeDir' title='$T_SmotrTitle'>Smotr</a>"; }
      $strCeangal = "$smotrHomeDir/tr.php?tra=[$str]";
      $strCeangal = ( $str ? "<li><a href='$strCeangal'>Sreang $str</a>" : '' );
      $myCLIL = SM_myCLIL::singleton();
      if ($myCLIL->cead(SM_myCLIL::LUCHD_EADARTHEANGACHAIDH) && !empty($domhan))
        { $trPutan = "\n<li class=deas><a href='/teanga/smotr/tr.php?domhan=$domhan' target='tr' title='$T_tr_fios'>tr</a>"; } else { $trPutan = ''; }
      $ceangalRiMoSMO = ( isset($myCLIL->id)
                        ? "<li class='deas'><a href='$smotrHomeDir/logout.php' title='Log out from myCLIL'>Logout</a></li>"
                        : "<li class='deas'><a href='$smotrHomeDir/login.php?till_gu=$smotrUrl/' title='$T_Log_air_fios'>$T_Log_air</a></li>"
                        );
      $hlArr = array(
          'br'=>'Brezhoneg',
          'da'=>'Dansk',
          'de'=>'Deutsch',
          'en'=>'English',
          'fr'=>'Français',
          'gd'=>'Gàidhlig',
          'it'=>'Italiano',
          'lt'=>'Lietuvių',
            '----2'=>'',  //Very partial translations
          'cy'=>'Cymraeg (anorffenedig)',
          'es'=>'Español (incompleto)',
          'ga'=>'Gaeilge (neamhiomlán)',
          'pt'=>'Portuguès (incompleto)',
          'bg'=>'Български (непълен)');
      $options = '';
      foreach ($hlArr as $hl=>$hlAinm) {
          if (substr($hl,0,4)=='----') { $options .= "<option value='' disabled>&nbsp;_{$hlAinm}_</option>/n"; }  //Divider in the list of select options
            else                       { $options .= "<option value='$hl|en'" . ( $hl==$smohl ? ' selected' : '' ) . ">$hlAinm</option>\n"; }
      }
      $selCanan = <<< END_selCanan
<script>
    function atharraichCanan(hl) {
        document.cookie='smohl='+hl;
        var paramstr = location.search;
        if (/Trident/.test(navigator.userAgent) || /MSIE/.test(navigator.userAgent)) {
          //Rud lag lag airson seann Internet Explorer, nach eil eòlach air URLSearchParams. Sguab ás nuair a bhios IE marbh.
            if (paramstr.length==6 && paramstr.substring(0,4)=='?hl=') { paramstr = ''; }
            paramstr = paramstr;
        } else {
            const params = new URLSearchParams(paramstr)
            params.delete('hl');
            paramstr = params.toString();
            if (paramstr!='') { paramstr = '?'+paramstr; }
        }
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
