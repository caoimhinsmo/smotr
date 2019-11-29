<?php
//Cruthaich eadar-theangachadh

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
  if ( $t==strtoupper($t)                                      //Ceartaich daoine a sgrìobhas cód cànain le litrichean móra
      && !$myCLIL->cead(SM_myCLIL::LUCHD_EADARTHEANGACHAIDH2)) //ach leig do na h-urramaich rudan leithid 'TEST' a sgrìobhadh
      { $t = strtolower($t); }
  $tra = urldecode($_REQUEST['tra']);
  $Smotr = SM_SmotrPDOedit::singleton('rw');
  $stmt = $Smotr->prepare('INSERT INTO trtra(id,t,tra,csmid,ctime,msmid,mtime) VALUES(:id,:t,:tra,:smid,:utime,:smid,:utime)');
  $stmt->execute([':id'=>$id,':t'=>$t,':tra'=>$tra,':smid'=>$smid,':utime'=>$utime]);
  if ($stmt->rowCount()<>1) { die('failed to create new translation'); }
  echo 'OK';

?>
