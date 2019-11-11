<?php
//Atharraich eadar-theangachadh

  if (!include('autoload.inc.php')) { die('include autoload failed'); }
  $myCLIL = SM_myCLIL::singleton();
  if (!$myCLIL->cead(SM_myCLIL::LUCHD_EADARTHEANGACHAIDH)) { die('You do not have permission'); }
  $smid = $myCLIL->id;
  $utime = time();
  if (!isset($_REQUEST['id']))  { die('id is not set'); }
  if (!isset($_REQUEST['t']))   { die('t is not set'); }
  if (!isset($_REQUEST['tra'])) { die('tra is not set'); }
  $id  = $_REQUEST['id'];
  $t   = $_REQUEST['t'];
  $tra = urldecode($_REQUEST['tra']);
  $Smotr = SM_SmotrPDOedit::singleton('rw');
  $stmt = $Smotr->prepare('UPDATE trtra SET tra=:tra, msmid=:smid, mtime=:utime WHERE id=:id AND t=:t');
  $stmt->execute([':id'=>$id,':t'=>$t,':tra'=>$tra,':smid'=>$smid,':utime'=>$utime]);
  echo 'OK';

?>
