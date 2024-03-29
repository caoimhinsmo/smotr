<?php
  if (!include('autoload.inc.php'))
    header("Location:http://claran.smo.uhi.ac.uk/mearachd/include_a_dhith/?faidhle=autoload.inc.php");
  header("Cache-Control:no-cache,must-revalidate");

  try {
    $myCLIL = SM_myCLIL::singleton();
    $csSess = SM_csSess::singleton();
    $DbMultidict = SM_DbMultidictPDO::singleton('rw');
    $servername = SM_myCLIL::servername();
    $serverhome = SM_myCLIL::serverhome();

    $formRequired = TRUE;
    $successMessage = $refreshHeader = $formHtml = '';
    $userAsTyped = $passwordAsTyped = $userAutofocus = $passwordAutofocus = '';

    if (!empty($csSess->getCsSession()->user)) { $userAsTyped = $csSess->getCsSession()->user; }
    if (!empty($_REQUEST['user'])) {
        $userAsTyped     = trim($_REQUEST['user']);
        $passwordAsTyped = $_POST['password'];
        $stmt1 = $DbMultidict->prepare('SELECT user,password,csid FROM users WHERE user=:user OR email=:email');
        $stmt1->bindParam(':user',$userAsTyped);
        $stmt1->bindParam(':email',$userAsTyped);
        $stmt1->bindColumn(1,$user);
        $stmt1->bindColumn(2,$password);
        $stmt1->bindColumn(3,$csid);
        if  ($stmt1->execute()
          && $stmt1->fetch()
          && (crypt($passwordAsTyped,$password)==$password || $password=='')) {
           //Copy filter parameters from the most recent previous Clilstore session (if any) for this user. Remember the new csid.
            if (isset($_COOKIE['csSessionId'])) {
                $newCsid = $_COOKIE['csSessionId'];
                $stmt4 = $DbMultidict->prepare('UPDATE users SET csid=:newCsid WHERE user=:user');
                $stmt4->execute([':newCsid'=>$newCsid,':user'=>$user]);
            }
           //Create cookie
            $cookieDomain = $servername;
            if (preg_match('|www\d*\.(.*)|',$cookieDomain,$matches)) { $cookieDomain = $matches[1]; }   // Remove www., www2., etc. e.g. www2.smo.uhi.ac.uk->smo.uhi.ac.uk
            $myCLIL::cuirCookie('myCLIL_authentication',$user,0,108000); //Cookie expires at session end, or max 30 hours
            $csSess->setUser($user);  //Remember $user, to make the next login easier
            SM_csSess::logWrite($user,'login');
            $till_gu = ( empty($_GET['till_gu']) ? "$serverhome/teanga/smotr/" : $_GET['till_gu'] );
            $successMessage = <<<ENDsuccess
<p style="color:green"><span style="font-size:200%">✔</span> You have successfully logged in.</p>
<p style="margin-left:1em">⇨ Go to <a href="./" style="font-weight:bold">Smotr</a></p>
ENDsuccess;
            $formRequired = FALSE;
            $refreshHeader =  "<meta http-equiv='refresh' content='1; url=$till_gu'>";
        } elseif (!isset($_GET['user'])) {
            $successMessage = <<<ENDfailure
<p style="color:red">Userid or password incorrect</p>
ENDfailure;
        }
    }

    if ($formRequired) {
        $userSC     = htmlspecialchars($userAsTyped);
        $passwordSC = htmlspecialchars($passwordAsTyped);
        if (empty($userSC)) { $userAutofocus = 'autofocus'; } else { $passwordAutofocus = 'autofocus'; }
        $userRegistrationUrl = SM_Smotr::userRegistrationUrl();
        $formHtml = <<<ENDform
<form method="POST">
<table>
<tr><td>Userid:</td><td><input name="user" value="$userSC" required $userAutofocus>
<span class="info">You can also use your e-mail address to login</span></td></tr>
<tr><td>Password:</td><td><input type="password" name="password" value="$passwordSC" required $passwordAutofocus> <a href="forgotPassword.php" style="font-size:80%">Forgotten your password?</a></td></tr>
<tr><td></td><td><input type="submit" value="Login"></td></tr>
</table>
</form>

<p style="margin-top:3em">(Or else <a href="$userRegistrationUrl">register</a> a new userid)</p>
ENDform;
    }
 
   echo <<<EOD
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    $refreshHeader
    <title>Login to myCLIL</title>
    <link rel="stylesheet" href="/css/smo.css">
    <link rel="stylesheet" href="style.css?version=2014-04-15">
    <link rel="icon" type="image/png" href="/favicons/clilstore.png">
    <style>
        span.info { color:green; font-size:70%; font-style:italic; }
    </style>
</head>
<body>

<ul class="smo-navlist">
<li><a href="./">Clilstore</a></li>
</ul>
<div class="smo-body-indent">

<h1>Login to myCLIL</h1>

$successMessage
$formHtml

</div>
<ul class="smo-navlist">
<li><a href="./">Clilstore</a></li>
</ul>

</body>
</html>
EOD;

  } catch (Exception $e) { echo $e; }

?>
