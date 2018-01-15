<?php
    require_once 'SomeoneIdClient.php';
    
    $SOMEONE_CLIENT_ID = "1abda4204-a25b-41a2-3b31-1a65df4ca243.logmebot.com";
    $SOMEONE_CLIENT_SECRET = "-oh6Sh6cuATLASS3i7K1i_U-HMfAGSIQ2oLfER4q-MI=";
    $SOMEONE_CALLBACK_URI = "http://127.0.0.1/php-sample/index.php";
	

    $someone = new SomeoneClient($SOMEONE_CLIENT_ID, $SOMEONE_CLIENT_SECRET, $SOMEONE_CALLBACK_URI);
    
    $exampleResult = "Unauthorized";

    if (isset($_REQUEST["LogOn"])) { // LogOn button clicked
        $someone->logOn();
    } else if (isset($_REQUEST["code"])) {

        if ($someone->get_oauth_token($_REQUEST["code"], $_REQUEST["state"])){
            $oauth_identity = $someone->get_oauth_identity($_SESSION['SOMEONE_CLIENT']['ACCESS_TOKEN']);
            $exampleResult = "Authorized user id: " . $oauth_identity['id'] . "<br>";
            $exampleResult .= "email: " . $oauth_identity['email'] . "<br>";
            $exampleResult .= "nickname " . $oauth_identity['nickname'] . "<br>";
        }
        else{
            $exampleResult = "Problems with authentication";
        }

    }
    
    
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>someone.id Demo Client</title>
</head>
<body>
<h2>
someone.id Demo Client
</h2>

<form action="index.php" method="POST">
    <p>
    <input type="submit" name="LogOn" value="Login with someone.id"/>
    </p>

    <p id="pAuthorization">
    <?php echo $exampleResult ?>
</form>

</body>
</html>
