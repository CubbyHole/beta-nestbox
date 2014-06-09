<?php
//appel la session
session_start();

//variable de session a false
$loginOK = false;

$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';
//require $projectRoot.'/controller/functions.php';
require $projectRoot.'../required.php';

//On check si loginForm est bien defini
//S'il est defini alors on entre dans la condition
if(isset($_POST['loginForm'] ))
{
	$email = $_POST['email'];
    $password = $_POST['password'];

    //Avant de se logger on verifie bien que les champs mail et password ne sont pas vide
    if(!empty($email) && !empty($password))
    {
		$userPdoManager = new UserPdoManager();
		$user = $userPdoManager->authenticate($email, $password);
        var_dump($user);
        //var_dump($userPdoManager);
        //http://www.php.net/manual/en/function.array-key-exists.php
		if(!(array_key_exists('error', $user)))
		{
			$loginOK = TRUE;
//            $_SESSION['user'] = serialize($user);
            $_SESSION['user'] = serialize($user);
			$_SESSION['userId'] = $user->getId();
			//redirection vers index
			header('Location:../');
		}
        else
        {
            $_SESSION['errorMessageLogin'] = $user['error'];
            header('Location:../');
            die();
        }

	}
	
}

if($loginOK == TRUE)
{
	//Pour les sessions
	$_SESSION['user'] = serialize($user);

}
else 
{
  //echo $user['error'];
}

