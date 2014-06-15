<?php
/**
 * Created by PhpStorm.
 * User: Ken
 * Date: 20/03/14
 * Time: 12:27
 */
$myUserInfo = unserialize($_SESSION['user']);
?>
<body>
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse"
                    data-target=".navbar-ex1-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a style="height: 70px" class="navbar-brand" href="/Nestbox"><img id="nestboxLogo" src="/Nestbox/content/img/logo/logoNestbox.png"><strong>NESTBOX</strong></a>
        </div>


        <div class="collapse navbar-collapse navbar-ex1-collapse" role="navigation">

            <ul id="menuOne"class="nav navbar-nav navbar-left">
                <?php if ($myUserInfo): //recupère la session du login?>
                    <li><a href="/Cubbyhole/view/account.php">MY ACCOUNT</a></li>
                <?php endif ?>


            </ul>

            <ul id="menuTwo" class="nav navbar-nav navbar-right ">
                <?php if ($myUserInfo): //Mise en place d'un module Gravatar pour la photo de profil ?>
                    <li>

                        <img class="img-circle" title="<?php echo $myUserInfo->getLastName().' '.$myUserInfo->getFirstname(); ?>" src=<?php echo getGravatar($myUserInfo->getEmail()); ?>>

                    </li>
                    <li>
                        <a id="cross" title="Logout" href="/Nestbox/view/logout.php"><span style="color:red;" class="glyphicon glyphicon-remove"></span></a></span>
                    </li>
                    <li>
                        <a id="menuTwo-Name">
                            <?php echo $myUserInfo->getLastName().' '.$myUserInfo->getFirstname(); ?>
                        </a>
                    </li>
                <?php endif ?>
            </ul>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>