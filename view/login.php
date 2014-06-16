<?php

$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';
//require $projectRoot.'/controller/functions.php';
//include '../header/header.php';
?>

    <link rel="stylesheet" href="../content/bootstrap/css/bootstrap.min.css" />
    <link href='http://fonts.googleapis.com/css?family=Lato:300,400,700,900,300italic,400italic,700italic,900italic' rel='stylesheet' type='text/css' />
<!-- Styles -->
<link rel="stylesheet" href="../content/css/bootstrap-overrides.css" type="text/css" />
<link rel="stylesheet" href="../content/css/theme.css" type="text/css" />

<link rel="stylesheet" href="../content/css/sign-in.css" type="text/css" media="screen" />

<link rel="stylesheet" href="../content/css/style.css" type="text/css" media="screen" />

<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->


    <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    </head>
<?php
 //include '../header/menu.php';
?>

    <!-- Sign In Option 1 -->
    <div id="sign_in1" class="sign_in1">
        <?php if(isset($_SESSION['errorMessageLogin'])): ?>
            <div class="alert alert-danger">
                <p>Message from the server :</p>
                <br />
                <?php echo  $_SESSION['errorMessageLogin']; ?>
                <br />
                <br />
                <p>Please contact the technical support at <a>technical.support@cubbyhole.com</a> or retry</p>
                <?php unset($_SESSION['errorMessageLogin']); ?>
            </div>
        <?php endif ?>

        <?php if(empty($_SESSION['user'])): ?>
        <div class="container">
            <div class="row">
                <div class="col-md-12 header">
                    <h4>Authentification</h4>
                    
                    <div class="col-md-4 social">
                        <a href="#" class="circle facebook">
                            <img src="../content/img/face.png" alt="">
                        </a>
                         <a href="#" class="circle twitter">
                            <img src="../content/img/twt.png" alt="">
                        </a>
                         <a href="#" class="circle gplus">
                            <img src="../content/img/gplus.png" alt="">
                        </a>
                    </div>
                </div>

                <div class="col-sm-3 division">
                    <div class="line l"></div>
                    <span>here</span>
                    <div class="line r"></div>
                </div>

                <div class="col-md-12 footer">
                    <form method="post" action="../controller/login.php" class="form-inline">
                        <input id="email" name="email" type="text"  autofocus placeholder="Email" class="form-control" required>
                        <input id="password" name="password" type="password"  placeholder="Password" class="form-control" required>
                        <input name="loginForm" type="submit" value="login">
                    </form>
                </div>

                <div class="col-md-12 proof">
                    <div class="col-md-6 remember">

                        <a href="reset.php">Forgot password?</a>
                    </div>

                    <div class="col-md-6">
                        <div class="dosnt">
                            <span>Don’t have an account?</span>
                            <a href="register.php">Sign up</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-danger">
            <p>Message from the server :</p>
            <br />
            <strong>You are already identified</strong>
            <br />
            <br />
            <p>Please contact the technical support at <a>technical.support@cubbyhole.com</a> or retry</p>
        </div>
        <?php endif; ?>
    </div>
<script src="../content/js/jquery.js"></script>

