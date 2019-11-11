<?php
//Sguab às eadar-theangachadh

  if (!include('autoload.inc.php')) { die('include autoload failed'); }
  $myCLIL = SM_myCLIL::singleton();
  if (!$myCLIL->cead(SM_myCLIL::LUCHD_EADARTHEANGACHAIDH)) { die('You do not have permission'); }
  if (!isset($_REQUEST['id']))  { die('id is not set'); }
  if (!isset($_REQUEST['t']))   { die('t is not set'); }
  $id  = $_REQUEST['id'];
  $t   = $_REQUEST['t'];
  $Smotr = SM_SmotrPDOedit::singleton('rw');
  $stmt = $Smotr->prepare('DELETE FROM trtra WHERE id=:id AND t=:t');
  $stmt->execute([':id'=>$id,':t'=>$t]);
  echo 'OK';

?>
