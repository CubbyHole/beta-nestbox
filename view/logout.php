<?php
/**
 * Created by PhpStorm.
 * User: Ken
 * Date: 15/06/14
 * Time: 19:13
 */

// On appelle la session
session_start();

// On écrase le tableau de session
$_SESSION['user'] = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
//// On détruit la session
session_destroy();

//redirection vers le dashboard
header('Location: login.php');