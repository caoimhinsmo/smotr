<?php
class SM_T {

  public $tArr, $domhan, $domhanArr;

  public function __construct($domhan='',$tLiosta='') {
      if (empty($tLiosta)) { $tLiosta = self::hl(); }
      $tArr = explode('|',$tLiosta);
      if (count($tArr)<2) { //Gun chànanan cul-taice
          $cultaic = array('gd','en','%');
          $tArr = array_merge($tArr,$cultaic);
      }
      $this->tArr   = $tArr;
      $mirean = explode ('/',trim($domhan,'/'));
      $this->domhan = '/';
      $this->domhanArr = [$this->domhan];
      foreach ($mirean as $mir) {
          $this->domhan .= "$mir/"; 
          array_unshift($this->domhanArr,$this->domhan);
      }
  }

  public function _($str,$opt='') {
      $tArr = $this->tArr;
      $trans = '';
      $Smotr = SM_SmotrPDO::singleton('rw');
      $stmtSEL = $Smotr->prepare('SELECT trtra.id, tra FROM trstr,trtra WHERE trstr.id=trtra.id AND trstr.str=:str AND trstr.domhan=:domhan AND trtra.t LIKE :t');
      foreach ($this->domhanArr as $domhan) {
          foreach ($tArr as $i=>$t) {
              $stmtSEL->execute([':str'=>$str,':domhan'=>$domhan,':t'=>$t]);
              if ($row = $stmtSEL->fetch(PDO::FETCH_ASSOC)) {
                  extract($row);
                  if ($i>0) { $tra = "¤$tra"; }
                  break;
              }
          }
          if (!empty($tra)) { break; }
      }
      if (!empty($tra)) { self::log($id,$t); }
        else            { $tra = "✕$str"; }
      if       ($opt=='hsc') { $tra = htmlspecialchars($tra,ENT_QUOTES); }
       else if ($opt=='eq')  { $tra = strtr($tra,["'"=>"\'", '"'=>'\"']); } //Escape quotes for use in Javascript
      return $tra;
  }
  public function h($str) {
      $str = self::_($str);
      return htmlspecialchars($str,ENT_QUOTES); //Escape special characters and quotes for use in HTML
  }
  public function j($str) {
      $str = self::_($str);
      $str = strtr( $str, ["\'"=>"\ '", '\"'=>'\ "'] ); //Paranoia. First zap any \' or \" escape sequences by inserting an intervening space
      return strtr( $str, ["'"=>"\'", '"'=>'\"'] ); //Escape quotes for use in Javascript strings
  }

  private static function log($id,$t) {
      $SmotrLog = SM_SmotrLogPDO::singleton('rw');
      $utime = time();

      $stmtINStrstr =$SmotrLog->prepare('INSERT IGNORE INTO trstr(id) VALUES (:id)');
      $stmtINStrstr->execute([':id'=>$id]);
      $stmtUPDtrstr = $SmotrLog->prepare('UPDATE trstr SET cunntas=cunntas+1, utime=:utime WHERE id=:id');
      $stmtUPDtrstr->execute([':utime'=>$utime,':id'=>$id]);

      $stmtINStrtra = $SmotrLog->prepare('INSERT IGNORE INTO trtra(id,t) VALUES (:id,:t)');
      $stmtINStrtra->execute([':id'=>$id,':t'=>$t]);
      $stmtUPDtrtra = $SmotrLog->prepare('UPDATE trtra SET cunntas=cunntas+1, utime=:utime WHERE id=:id AND t=:t');
      $stmtUPDtrtra->execute([':utime'=>$utime,':id'=>$id,':t'=>$t]);

      $frithealaiche = $_SERVER['SERVER_NAME'];
      $duilleag      = $_SERVER['PHP_SELF'];
      $ip            = $_SERVER['REMOTE_ADDR'];
      $query         = $_SERVER['QUERY_STRING'];
   #--- Feumach air seo an-dràsta air sgàth’s gur e 767 bytes am max key size ann an seann mySQL ann an Ubuntu --CPD,2019-11-06
   $frithealaiche = substr($frithealaiche,0,40);
   $duilleag      = substr($duilleag,0,40);
   #---
      $stmtINStrstrURL = $SmotrLog->prepare('INSERT IGNORE INTO trstrURL(id,frithealaiche,duilleag) VALUES(:id,:frithealaiche,:duilleag)');
      $stmtINStrstrURL->execute([':id'=>$id, ':frithealaiche'=>$frithealaiche, ':duilleag'=>$duilleag]);
      $stmtUPDtrstrURL = $SmotrLog->prepare('UPDATE trstrURL SET cunntas=cunntas+1,utime=:utime,ip=:ip,query=:query'
                                        .' WHERE id=:id AND frithealaiche=:frithealaiche AND duilleag=:duilleag');
      $stmtUPDtrstrURL->execute([':id'=>$id, ':frithealaiche'=>$frithealaiche, ':duilleag'=>$duilleag, ':utime'=>$utime, ':ip'=>$ip, ':query'=>$query]);
  }

  public static function setcookieThl($hl) {
     // Cleachd heuristics gus $cookiepath a thomhais, agus cleachd sin airson cookie a chur air dòigh dhan chànan eadar-aghaidh.
     // Tha na heuristics a’ crochadh air liosta de apps is aithne dhuinn a tha a’ dèanamh feum de Smotr.
     // Chan eil sin ro mhath idir, ach chan eil fhios agam dé eile is urrainn dhomh a dhèanamh.  --CPD, 2019-12-07
     // (B'urrainn dhuinn '/' a chleachdadh fad na h-ùine mura be gu bheil diofar cànanan eadar-aghaidh ri fhaighinn aig diofar apps)
      $cookiepath = '/'; //default
      if  ( (strpos($_SERVER['SERVER_NAME'],'multidict.')===false)      // Fàg aig '/' e airson multidict.*
         && (strpos($_SERVER['SERVER_NAME'],'clilstore.eu')===false) )  // agus cuideachd airson clilstore.eu
      { 
          $apps = ['smotr','smotr_dev','bunadas','sruth'];
          $dirs = explode( '/', trim($_SERVER['SCRIPT_NAME'],'/') );
          $foundi = -1;
          foreach ($dirs as $i=>$dir) {
              foreach($apps as $app) {
                  if ($app==$dir) { $foundi=$i; break 2; }
              }
          }
          if ($foundi>-1) {
              $dirs = array_slice($dirs,0,$foundi+1);
              $cookiepath = implode('/',$dirs);
              $cookiepath = "/$cookiepath/";
              if ($cookiepath=='//') { $cookiepath = '/'; }
          }
      }
      setcookie ( 'Thl', $hl, ['path'=>$cookiepath,'samesite'=>'Lax','expires'=>time()+5000000] ); //expiry 2 mhìos
  }

  public static function hl() {
      if (isset($_REQUEST['hl'])) {
          $hl = $_REQUEST['hl'];
          self::setcookieThl($hl);
          return $hl;
      }
      if (!empty($_COOKIE['Thl'])) { return $_COOKIE['Thl']; }
     //Mura bheil hl sna request variables, no Thl ann an cookie, cleachd na h-accept-languages bhon bhrabhsair
      $http2 = new HTTP2();
      $negotLang = $http2->negotiateLanguage(['gd'=>true,'gd-GB'=>true]);
      if (substr($_SERVER['SERVER_NAME'],-13)=='smo.uhi.ac.uk' && $negotLang<>'gd' && $negotLang<>'gd-GB') {
          $negotLang = 'gd';  //Air frithealaichean smo.uhi.ac.uk, mura bheil Gàidhlig sna h-accept-languages idir, ’s e Gàidhlig a gheibhear!!
      } else {
          $cananCeadaichteArr =
            [ 'en', 'en-GB', 'en-US',
              'gd', 'gd-GB',
              'ga', 'ga-IE',
              'br', 'br-FR',
              'fr', 'fr-FR',
              'de', 'de-DE',
              'da', 'da-DK',
              'es', 'es-ES',
              'it', 'it-IT',
              'lt', 'lt-LT',
              'bg', 'bg-BG',
              'cy', 'cy-GB' ];
          $supported = [];
          foreach ($cananCeadaichteArr as $canan) { $supported[$canan] = true; }
          $negotLang = $http2->negotiateLanguage($supported);
      }
      $negotLang = explode('-',$negotLang)[0]; //Tilg a-mach region sam bith agus gléidh an cód cànain a-mhàin
      if ($negotLang<>'en') { $negotLang .= '|en'; }
      self::setcookieThl($negotLang);
      return $negotLang;
  }

  public static function hl0() {
      $hl = self::hl();
      return explode('|',$hl)[0];
  }

  public static function uru($facal) {
      $litir1 = substr($facal,0,1);
      $trArray = [
          'b'=>'mb',  'B'=>'mB',
          'c'=>'gc',  'C'=>'gC',
          'd'=>'nd',  'D'=>'nD',
          'f'=>'bhf', 'F'=>'bhF',
          'g'=>'ng',  'G'=>'nG',
          'p'=>'bp',  'P'=>'bP',
          't'=>'dt',  'T'=>'dT',
          'a'=>'n-a', 'A'=>'nA',
          'e'=>'n-e', 'E'=>'nE',
          'i'=>'n-i', 'I'=>'nI',
          'o'=>'n-o', 'O'=>'nO',
          'u'=>'n-u', 'U'=>'nU',
          'á'=>'n-á', 'Á'=>'nÁ',
          'é'=>'n-é', 'É'=>'nÉ',
          'í'=>'n-í', 'Í'=>'nÍ',
          'ó'=>'n-ó', 'Ó'=>'nÓ',
          'ú'=>'n-ú', 'Ú'=>'nÚ' ];
      return strtr($litir1,$trArray).substr($facal,1);
  }

  public static function seimheachadh($facal) {
      $litir1 = substr($facal,0,1);
      $litir2 = substr($facal,1,1);
      $litir1lc = strtolower($litir1);
      $litir2lc = strtolower($litir2);
      if (!in_array($litir1lc,['b','c','d','f','g','m','p','s'])) return $facal;
      if ($litir1lc=='s' && in_array($litir2lc,['c','d','f','g','m','p','t','v'])) return $facal;
      return $litir1.'h'.substr($facal,1);
  }

  public static function cunntasLom($n,$singilte,$iolra) {
  // Cleachd $singilte no $iolra a réir cànan agus $n
  // Dèan séimheachadh/ùradh
  // Na nochd $n
      $hl0 = self::hl0();
      if ($hl0=='ga') {
         if ($n<1 || $n>19) return $singilte;
         if (in_array($n,[7,8,9,10,17,18,19])) return self::uru($singilte);
         return self::seimheachadh($singilte);
      } elseif ($hl0=='gd') {
         if (in_array($n,[1,2,11,12])) return self::seimheachadh($singilte);
         if (in_array($n,[20,100,1000])) return $singilte;
         return $iolra;
      } else {
         if ($n==1) return $singilte;
         return $iolra;
      }      
  }

  public static function cunntas($n,$singilte,$iolra) {
      return "$n ".self::cunntasLom($n,$singilte,$iolra);
  }

}
?>
