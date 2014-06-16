<?php
/**
 * Created by PhpStorm.
 * User: Crocell
 * Date: 15/06/14
 * Time: 15:12
 */

/* Téléchargement anonyme */
header('Content-Type: text/html; charset=utf-8');
$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';
require_once $projectRoot.'/required.php';

?>
<title>Nestbox - File explorer</title>
<link rel="shortcut icon" href="../content/img/logo/logoNestBox.png">
<link rel=stylesheet type="text/css" href="../content/css/style.css">
<link rel=stylesheet type="text/css" href="../content/bootstrap/css/bootstrap.css">
<link rel=stylesheet type="text/css" href="../content/bootstrap/css/bootstrap-theme.css">
<link rel=stylesheet type="text/css" href="../content/css/theme.css">
<body class="bodyGrouse">
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container-fluid" style="height: 85px;">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse"
                    data-target=".navbar-ex1-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a style="height: 70px" class="navbar-brand" href="/Cubbyhole" target="_blank"><img id="nestboxLogo" src="/Nestbox/content/img/logo/logoNestbox.png"><strong>NESTBOX</strong></a>
        </div>
    </div><!-- /.container-fluid -->
</nav>

<div class="grouse">
    <div class="container">
        <div class="row wrapGrouse">
            <div class="col-md-12 header">
                <div class="">

                    <?php
                    if(isset($_GET['token'])):

                    $elementManager = new ElementPdoManager();
                    $userManager = new UserPdoManager();
                    $refElementManager = new RefElementPdoManager();

                    $element = $elementManager->findOne(array('downloadLink' => $_GET['token']));
                    $user = $userManager->findById($element->getOwner());
                    $refElement = $refElementManager->findById($element->getRefElement()); ?>

                    <div id="elementInformations" class="col-md-6 elemInfo">
                        <h3 style="margin-top: 0">Element information:</h3>
                        <ul class="ulElem">
                            <li>Element name : <?= $element->getName() ?></li>
                            <li>Extension : <?= $refElement->getExtension() ?></li>
                            <li>Type : <?= $refElement->getDescription() ?></li>

                          <?php   if(!(preg_match('/^4/', $refElement->getCode())))
                          {
                              if($element->getSize() < 1)
                                  echo '<li>Size : < 1 KB</li>';
                              else
                                  echo '<li>Size : '.$element->getSize().' KB</li>';
                          }
                          ?>
                            <li>Owner : <?= $user->getFirstName().' '.$user->getLastName() ?></li>

                    </div>

                    <div class="col-md-5 elemInfoDownload">
                        <h3 style="margin-top: 0">Finalize:</h3>
                        <p>Your download speed is 100 kb/s</p>
                        <p>Want to download faster ? <a href="/Cubbyhole" class="createAccountElem">Create an account</a></p>




                        <!-- formulaire pour déplacer -->
                        <form style="text-align: left" id="downloadLinkElement" method="POST">
                            <input type="hidden" name="owner" id="owner" value="<?= $element->getOwner() ?>" read-only>
                            <input type="hidden" name="idElement" id="idElement" value="<?= $element->getId() ?>" read-only>
                            <input type="submit" class="btn-success btn" value="Download" name="downloadAnonymousElem" id="downloadAnonymousElem">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php endif ?>
