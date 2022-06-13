<?php
  if (!include('autoload.inc.php'))
    header("Location:http://claran.smo.uhi.ac.uk/mearachd/include_a_dhith/?faidhle=autoload.inc.php");

  header("Cache-Control:max-age=0");

  try {
    $myCLIL = SM_myCLIL::singleton();
    $myCLIL::logout();
    $servername = SM_myCLIL::servername();
    $serverhome = SM_myCLIL::serverhome();
    $till_gu = $_GET['till_gu'] ?? "$serverhome/teanga/";

    echo <<<EOD1
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logout from Clilstore</title>
    <meta http-equiv="refresh" content="2; url=$till_gu">
    <link rel="icon" type="image/png" href="/favicons/clilstore.png">
</head>
<body>

<p><img src="/icons-smo/wave.gif" alt=""> You have been logged out from myCLIL on www3.smo.uhi.ac.uk</p>

</body>
</html>
EOD1;

  } catch (Exception $e) { echo $e; }

?>
