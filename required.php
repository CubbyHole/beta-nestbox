<?php
/**
 * Created by PhpStorm.
 * User: Crocell
 * Date: 25/04/14
 * Time: 15:20
 */

$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Cubbyhole';

require_once $projectRoot.'/model/classes/User.class.php';
require_once $projectRoot.'/model/classes/Connection.class.php';
require_once $projectRoot.'/model/classes/Element.class.php';
require_once $projectRoot.'/model/classes/RefElement.class.php';
require_once $projectRoot.'/model/classes/Right.class.php';
require_once $projectRoot.'/model/classes/RefRight.class.php';
require_once $projectRoot.'/model/classes/RefPlan.class.php';
require_once $projectRoot.'/model/classes/Account.class.php';
require_once $projectRoot.'/model/classes/RefAction.class.php';
require_once $projectRoot.'/model/classes/Transaction.class.php';

require_once $projectRoot.'/model/interfaces/AccountManager.interface.php';
require_once $projectRoot.'/model/interfaces/RefPlanManager.interface.php';
require_once $projectRoot.'/model/interfaces/UserManager.interface.php';
require_once $projectRoot.'/model/interfaces/RefActionManager.interface.php';
require_once $projectRoot.'/model/interfaces/TransactionManager.interface.php';
require_once $projectRoot.'/model/interfaces/ConnectionManager.interface.php';
require_once $projectRoot.'/model/interfaces/ElementManager.interface.php';
require_once $projectRoot.'/model/interfaces/RefElementManager.interface.php';
require_once $projectRoot.'/model/interfaces/RightManager.interface.php';
require_once $projectRoot.'/model/interfaces/RefRightManager.interface.php';

require_once $projectRoot.'/model/pdo/AbstractPdoManager.class.php';
require_once $projectRoot.'/model/pdo/UserPdoManager.class.php';
require_once $projectRoot.'/model/pdo/RefPlanPdoManager.class.php';
require_once $projectRoot.'/model/pdo/AccountPdoManager.class.php';
require_once $projectRoot.'/model/pdo/RefActionPdoManager.class.php';
require_once $projectRoot.'/model/pdo/TransactionPdoManager.class.php';
require_once $projectRoot.'/model/pdo/ConnectionPdoManager.class.php';
require_once $projectRoot.'/model/pdo/ElementPdoManager.class.php';
require_once $projectRoot.'/model/pdo/RefElementPdoManager.class.php';
require_once $projectRoot.'/model/pdo/RightPdoManager.class.php';
require_once $projectRoot.'/model/pdo/RefRightPdoManager.class.php';

require_once $projectRoot.'/controller/functions.php';