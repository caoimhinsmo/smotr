<?php
// Dùblaich sreang (agus gach eadar-theangachadh a th’aice)
// Till ‘OK’ ma tha sin soirbheachail, 'KO’ ma tha dùblachadh ann mu-thràth

  if (!include('autoload.inc.php')) { die('include autoload failed'); }
  $myCLIL = SM_myCLIL::singleton();
  if (!$myCLIL->cead(SM_myCLIL::LUCHD_EADARTHEANGACHAIDH2)) { die('You do not have permission'); }
  $smid = $myCLIL->id;
  $utime = time();
  if (!isset($_REQUEST['id']))  { die('id is not set'); }
  $id  = $_REQUEST['id'];
  $Smotr = SM_SmotrPDOedit::singleton('rw');

  $stmt = $Smotr->prepare('SELECT domhan,str,fios FROM trstr WHERE id=:id');
  $stmt->execute([':id'=>$id]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  extract($row);

  $str2 = $str . '_×2';
  $stmt = $Smotr->prepare('SELECT 1 FROM trstr WHERE domhan=:domhan AND str=:str');
  $stmt->execute([':domhan'=>$domhan,':str'=>$str2]);
  if ($stmt->fetch()) { echo 'KO'; return; }

  $stmt = $Smotr->prepare('INSERT INTO trstr (domhan, str, fios) VALUES (:domhan, :str, :fios)');
  $stmt->execute([':domhan'=>$domhan,':str'=>$str2,':fios'=>$fios]);
  $id2 = $Smotr->lastInsertId();

  $stmt = $Smotr->prepare('SELECT t, tra, csmid, ctime, msmid, mtime FROM trtra WHERE id=:id');
  $stmt->execute([':id'=>$id]);
  $rows = $stmt->fetchall(PDO::FETCH_ASSOC);

  $stmt = $Smotr->prepare('INSERT INTO trtra (id, t, tra, csmid, ctime, msmid, mtime) VALUES (:id, :t, :tra, :csmid, :ctime, :msmid, :mtime)');
  foreach ($rows as $row) {
      extract($row);
      $stmt->execute([':id'=>$id2,':t'=>$t,':tra'=>$tra,':csmid'=>$smid,':ctime'=>$ctime,':msmid'=>$msmid,':mtime'=>$mtime]);
  }
  echo 'OK';

?>
