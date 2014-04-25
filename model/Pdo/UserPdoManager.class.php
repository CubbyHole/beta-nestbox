<?php
/**
 * Created by Notepad++.
 * User: Alban Truc
 * Date: 30/01/14
 * Time: 14:51
 */

/** @var string $projectRoot chemin du projet dans le système de fichier */
$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Cubbyhole';

require_once 'AbstractPdoManager.class.php';
require_once 'AccountPdoManager.class.php';
require_once $projectRoot.'/model/Classes/User.class.php';
require_once $projectRoot.'/model/Interfaces/UserManager.interface.php';

/**
 * Class UserPdoManager
 * @author Alban Truc
 * @extends AbstractPdoManager
 */
class UserPdoManager extends AbstractPdoManager implements UserManagerInterface{

    /** @var MongoCollection $userCollection collection user */
	protected $userCollection;

    /** @var AccountPdoManager $accountPdoManager instance de cette classe */
	protected $accountPdoManager;

    /** @var RefPlanPdoManager $refPlanPdoManager instance de cette classe */
    protected $refPlanPdoManager;

    /**
     * Constructeur:
     * - Apelle le constructeur de {@see AbstractPdoManager::__construct} (gestion des accès de la BDD).
     * - Initialise la collection user.
     * - Crée un objet AccountPdoManager ou utilise une référence d'une instance de cet objet
     * @author Alban Truc
     * @since 01/2014
     */

    public function __construct()
    {
        parent::__construct();
        $this->userCollection = $this->getCollection('user');

        $numberOfArgs = func_num_args();

        switch($numberOfArgs)
        {
            case 1:
                $accountPdoManager = func_get_arg(0);
                $this->accountPdoManager = &$accountPdoManager;
                break;
            default:
                $this->accountPdoManager = new AccountPdoManager();
                break;
        }

        $this->refPlanPdoManager = new RefPlanPdoManager();
    }

    /**
     * Retrouver un User selon des critères donnés
     * @author Alban Truc
     * @param array|User $criteria critères de recherche
     * @param array $fieldsToReturn champs à récupérer
     * @since 31/03/2014
     * @return array|User[]
     */

    public function find($criteria, $fieldsToReturn = array())
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof User)
            $criteria = $this->dismount($criteria);

        if(isset($criteria['idCurrentAccount']))
        {
            if($criteria['idCurrentAccount'] instanceof Account)
                $criteria['idCurrentAccount'] = new MongoId($criteria['idCurrentAccount']->getId());
            else if(is_array($criteria['idCurrentAccount']) && isset($criteria['idCurrentAccount']['_id']))
                $criteria['idCurrentAccount'] = $criteria['idCurrentAccount']['_id'];
        }

        $cursor = parent::__find('user', $criteria, $fieldsToReturn);

        if(!(is_array($cursor)) && !(array_key_exists('error', $cursor)))
        {
            $users = array();
            //$account = NULL;

            foreach($cursor as $user)
            {
//                if(array_key_exists('idCurrentAccount', $user)) //récupère le compte actuel
//                    $account = $this->userPdoManager->findById($user['idCurrentAccount']);
//
//                if(!(empty($fieldsToReturn)))
//                {
//                    if ($account !== NULL && !(array_key_exists('error', $account))) {
//                        if(!(is_array($account)))
//                            $account = $this->dismount($account);
//                        $user['idCurrentAccount'] = $account;
//                    }
//
//                    /**
//                     * D'après les commentaires sur la page {@link https://php.net/manual/en/function.array-push.php}
//                     * la méthode utilisée ici est plus rapide d'exécution qu'utiliser un array_push.
//                     * Par ailleurs nous n'avons pas besoin de la valeur que retourne array_push
//                     * (à savoir le nombre d'éléments dans le tableau)
//                     */
//                    $users[] = $user;
//                }
//                else
//                {
//                    $user = new User($user);
//                    $user->setCurrentAccount($account);
//
//                    $users[] = $user;
//                }
                if(empty($fieldsToReturn))
                    $user = new User($user);

                $users[] = $user;
            }

            if(empty($users))
                return array('error' => 'No match found.');
            else
                return $users;
        }
        else return $cursor; //message d'erreur
    }

    /**
     * Retourne le premier User correspondant au(x) critère(s) donné(s)
     * @author Alban Truc
     * @param array|User $criteria critère(s) de recherche
     * @param array $fieldsToReturn champs à retourner
     * @since 31/03/2014
     * @return array|User
     */

    public function findOne($criteria, $fieldsToReturn = array())
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof User)
            $criteria = $this->dismount($criteria);

        if(isset($criteria['idCurrentAccount']))
        {
            if($criteria['idCurrentAccount'] instanceof Account)
                $criteria['idCurrentAccount'] = new MongoId($criteria['idCurrentAccount']->getId());
            else if(is_array($criteria['idCurrentAccount']) && isset($criteria['idCurrentAccount']['_id']))
                $criteria['idCurrentAccount'] = $criteria['idCurrentAccount']['_id'];
        }

        $result = parent::__findOne('user', $criteria, $fieldsToReturn);

        if(!(array_key_exists('error', $result)))
        {
            if(empty($fieldsToReturn))
                $result = new User($result);
        }

        return $result;
    }

    /**
     * - Retrouver l'ensemble des User
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array $fieldsToReturn champs à retourner
     * @since 11/03/2014
     * @return array|User[] tableau d'objets User
     */

    public function findAll($fieldsToReturn = array())
    {
        $cursor = parent::__find('user', array());

        if(!(is_array($cursor)) && !(array_key_exists('error', $cursor)))
        {
            $users = array();

            foreach($cursor as $user)
            {
                if(empty($fieldsToReturn))
                    $user = new User($user);

                $users[] = $user;
            }
        }

        if(empty($users))
            return array('error' => 'No user found.');
        else
            return $users;
    }

    /**
     * - Retrouver un user par son ID.
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array $fieldsToReturn champs à retourner
     * @param string|MongoId $id Identifiant unique de l'user à trouver
     * @since 02/2014
     * @return User|array contenant le message d'erreur
     */

    public function findById($id, $fieldsToReturn = array())
    {
        $result = parent::__findOne('user', array('_id' => new MongoId($id)));

        //Si un user est trouvé
        if (!(array_key_exists('error', $result)))
        {
//            //On récupère le compte actuel de l'utilisateur
//            $search = array('_id' => $result['idCurrentAccount']);
//            $fieldsToReturn = array('idUser' => 0); //tous sauf idUser, on est déjà en train de le récupérer...
//
//            $account = $this->userPdoManager->findOne($search, $fieldsToReturn);
//
//            //Si un compte est trouvé
//            if (!(array_key_exists("error", $account)))
//            {
//                //On retourne toutes les infos du compte plutôt que (seulement) son ID
//                $user = new User($result);
//                $user->setCurrentAccount($account);
//
//                return $user;
//            }
//            else return $account; //Message d'erreur approprié
            if(empty($fieldsToReturn))
                $result = new User($result);
        }

        return $result;
    }

    /**
     * - Retrouver un User selon certains critères et le modifier/supprimer
     * - Récupérer cet User ou sa version modifiée
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|User $searchQuery critères de recherche
     * @param array|User $updateCriteria les modifications à réaliser
     * @param array|NULL $fieldsToReturn pour ne récupérer que certains champs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return array|User
     */

    public function findAndModify($searchQuery, $updateCriteria, $fieldsToReturn = NULL, $options = NULL)
    {
        //Transforme $searchQuery en array s'il contient un objet
        if($searchQuery instanceof User)
            $searchQuery = $this->dismount($searchQuery);

        //Transforme $updateCriteria en array s'il contient un objet
        if($updateCriteria instanceof User)
            $updateCriteria = $this->dismount($updateCriteria);

        if(isset($searchQuery['idCurrentAccount']))
        {
            if($searchQuery['idCurrentAccount'] instanceof Account)
                $searchQuery['idCurrentAccount'] = new MongoId($searchQuery['idCurrentAccount']->getId());
            else if(is_array($searchQuery['idCurrentAccount']) && isset($searchQuery['idCurrentAccount']['_id']))
                $searchQuery['idCurrentAccount'] = $searchQuery['idCurrentAccount']['_id'];
        }

        if(isset($updateCriteria['idCurrentAccount']))
        {
            if($updateCriteria['idCurrentAccount'] instanceof Account)
                $updateCriteria['idCurrentAccount'] = new MongoId($updateCriteria['idCurrentAccount']->getId());
            else if(is_array($updateCriteria['idCurrentAccount']) && isset($updateCriteria['idCurrentAccount']['_id']))
                $updateCriteria['idCurrentAccount'] = $updateCriteria['idCurrentAccount']['_id'];
        }

        $result = parent::__findAndModify('user', $searchQuery, $updateCriteria, $fieldsToReturn, $options);

        if($fieldsToReturn === NULL)
            $result = new User($result);

        return $result;
    }

    /**
     * - Insère un nouvel utilisateur en base.
     * - Gestion des exceptions et des erreurs.
     * @author Alban Truc
     * @param array|User $user
     * @param array $options
     * @since 31/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    public function create($user, $options = array('w' => 1))
    {
        //Transforme $user en array s'il contient un objet
        if($user instanceof User)
            $user = $this->dismount($user);

        if(isset($user['idCurrentAccount']))
        {
            if($user['idCurrentAccount'] instanceof Account)
                $user['idCurrentAccount'] = new MongoId($user['idCurrentAccount']->getId());
            else if(is_array($user['idCurrentAccount']) && isset($user['idCurrentAccount']['_id']))
                $user['idCurrentAccount'] = $user['idCurrentAccount']['_id'];
        }

        $result = parent::__create('user', $user, $options);

        return $result;
    }

    /**
     * Fonction d'update utilisant celle de {@see AbstractPdoManager}
     * @author Alban Truc
     * @param array|User $criteria description des entrées à modifier
     * @param array|User $update nouvelles valeurs
     * @param array|NULL $options
     * @since 31/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    public function update($criteria, $update, $options = array('w' => 1))
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof User)
            $criteria = $this->dismount($criteria);

        //Transforme $update en array s'il contient un objet
        if($update instanceof User)
            $update = $this->dismount($update);

        if(isset($criteria['idCurrentAccount']))
        {
            if($criteria['idCurrentAccount'] instanceof Account)
                $criteria['idCurrentAccount'] = new MongoId($criteria['idCurrentAccount']->getId());
            else if(is_array($criteria['idCurrentAccount']) && isset($criteria['idCurrentAccount']['_id']))
                $criteria['idCurrentAccount'] = $criteria['idCurrentAccount']['_id'];
        }

        if(isset($update['idCurrentAccount']))
        {
            if($update['idCurrentAccount'] instanceof Account)
                $update['idCurrentAccount'] = new MongoId($update['idCurrentAccount']->getId());
            else if(is_array($update['idCurrentAccount']) && isset($update['idCurrentAccount']['_id']))
                $update['idCurrentAccount'] = $update['idCurrentAccount']['_id'];
        }

        $result = parent::__update('user', $criteria, $update, $options);

        return $result;
    }

    /**
     * - Supprime un/des utilisateur(s) correspondant à des critères données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|User $criteria ce qu'il faut supprimer
     * @param array $options
     * @since 31/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    public function remove($criteria, $options = array('w' => 1))
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof User)
            $criteria = $this->dismount($criteria);

        if(isset($criteria['idCurrentAccount']))
        {
            if($criteria['idCurrentAccount'] instanceof Account)
                $criteria['idCurrentAccount'] = new MongoId($criteria['idCurrentAccount']->getId());
            else if(is_array($criteria['idCurrentAccount']) && isset($criteria['idCurrentAccount']['_id']))
                $criteria['idCurrentAccount'] = $criteria['idCurrentAccount']['_id'];
        }

        $result = parent::__remove('user', $criteria, $options);

        return $result;
    }

    /**
     * - Insère un compte gratuit.
     * - Insère l'utilisateur qui va posséder ce compte.
     * - Gestion des exceptions MongoCursor: {@link http://www.php.net/manual/en/class.mongocursorexception.php}
     * - Gestion des erreurs, avec notamment:
     *       Annulation de l'insertion du compte gratuit si l'insertion de l'utilisateur a échoué
     * @author Alban Truc
     * @param string $lastName
     * @param string $firstName
     * @param string $email
     * @param string $password
     * @param string $geolocation
     * @since 02/2014
     * @return bool TRUE si l'insertion a réussi, FALSE sinon
     */

    public function addFreeUser($lastName, $firstName, $email, $password, $geolocation)
    {

        $accountId = new MongoId();
        $userId = new MongoId();
        //@link http://www.php.net/manual/en/class.mongodate.php
        $time = time();
        $end = $time + (30 * 24 * 60 * 60); // + 30 jours

        //Caractéristiques du compte gratuit
        $account = array
        (
            '_id' => $accountId,
            'state' => (int)1,
            'idUser' => $userId,
            'idRefPlan' => new MongoId('52eb5e743263d8b6a4395df0'), //id du plan gratuit
            'storage' => (int)0,
            'ratio' => (int)0,
            'startDate' => new MongoDate($time),
            'endDate' => new MongoDate($end)
        );
        $isAccountAdded = $this->accountPdoManager->create($account);

        if($isAccountAdded == TRUE) //inutile d'ajouter un utilisateur si l'ajout d'account a échoué
        {
            //Caractéristiques de l'utilisateur
            $user = array
            (
                '_id' => $userId,
                'state' => (int)0,
                'isAdmin' => false,
                'idCurrentAccount' => $accountId,
                'lastName' => $lastName,
                'firstName' => $firstName,
                'password' => $password,
                'email' => $email,
                'geolocation' => $geolocation,
                'apiKey' => $this->generateGUID()
            );

            $info = self::create($user);

            if($info != TRUE)
            {
                //annuler l'insertion de l'account
                $removeInfo = $this->accountPdoManager->remove($account);

                if($removeInfo == TRUE)
                    $info['error'] .= 'The account created for this user has been removed successfully.';
                else
                    $info['error'] .= 'The account created for this user has not been removed successfully: '
                                   .$removeInfo;   //contient le détail de l'erreur de suppression
            }

            return $info;
        }
        else return $isAccountAdded; //Message d'erreur approprié
    }

    /**
     * Authentifier un utilisateur:
     * - Récupère l'utilisateur inscrit avec l'e-mail indiquée. S'il y en a un:
     *  - Vérifie le mot de passe. S'il correspond:
     *      - Récupère son compte
     * @author Alban Truc
     * @param string $email
     * @param string $password
     * @since 02/2014
     * @return User|array contenant le message d'erreur
     */

    public function authenticate($email, $password)
    {
        //Récupère l'utilisateur inscrit avec l'e-mail indiquée.
        $query = array(
            'state' => (int)1,
            'email' => $email
        );

        $user = self::findOne($query);

        if($user instanceof User) //Si l'utilisateur existe
        {
            if($user->getPassword() == $password)
            {
                //On récupère le compte correspondant à l'utilisateur
                $accountCriteria = array(
                    '_id' => new MongoId($user->getCurrentAccount()),
                    'state' => (int)1
                );
                $account = $this->accountPdoManager->findOne($accountCriteria);

                if($account instanceof Account) //Si le compte existe
                {
                    $refPlan = $this->refPlanPdoManager->findById($account->getRefPlan());

                    if($refPlan instanceof RefPlan)
                    {
                        $account->setRefPlan($refPlan);
                        $user->setCurrentAccount($account);
                        return $user;
                    }
                    else
                    {
                        $errorInfo = 'RefPlan with ID '.$account->getRefPlan().' not found';
                        return array('error' => $errorInfo);
                    }
                }
                else
                {
                    $errorInfo = 'No active account with ID '.$user->getCurrentAccount().' for user '.$user->getId();
                    return array('error' => $errorInfo);
                }
            }
            else
            {
                $errorInfo = 'Password given ('.$password.') does not match with password in database.';
                return array('error' => $errorInfo);
            }
        }
        else
        {
            $errorInfo = 'No ACTIVE user found for the following e-mail: '.$email.' Maybe you didn\'t activate your account?';
            return array('error' => $errorInfo);
        }
    }

    /**
     * Vérifier la disponibilité d'une adresse e-mail
     * @author Alban Truc
     * @param string $email
     * @since 02/2014
     * @return bool FALSE si email déjà prise, TRUE sinon
     */

	public function checkEmailAvailability($email)
	{
        $query = array('email' => $email);

        $result = $this->userCollection->findOne($query);

        //False parce qu'on ne veut pas inscrire deux personnes pour la même e-mail
        if($result) return FALSE;
        else return TRUE;
	}

    /**
     * Inscription:
     * - Vérifie certains critères sur les paramètres fournis
     * - Appelle la fonction de vérification de disponibilité de l'e-mail
     * - Appelle la fonction d'ajout d'un free user
     * - Appelle la fonction d'envoi du mail d'inscription
     * @author Alban Truc
     * @param string $name
     * @param string $firstName
     * @param string $email
     * @param string $password
     * @param string $passwordConfirmation
     * @param string $geolocation
     * @since 02/2014
     * @return User|array contenant le message d'erreur
     */
	
	public function register($name, $firstName, $email, $password, $passwordConfirmation, $geolocation = 'Not specified')
	{
		/*
		**Entre 8 et 26 caractères, mini un chiffre, mini une lettre minuscule, mini une lettre majuscule,
		**minimum un caractère spécial (@*#).
		**Exemples de caractères non acceptés: ‘ , \ &amp; $ &lt; &gt; et l'espace (\s).
		*/
		$regex = '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@*#]).([a-zA-Z0-9@*#]{8,26})$/';

		if( 
			!(empty($name)) &&      //http://www.php.net/manual/en/function.empty.php
			!(empty($firstName)) &&
			!(empty($email)) &&
			!(empty($password)) &&
			!(empty($passwordConfirmation))
		   )
		{
			if(
				$password == $passwordConfirmation &&
				$password != $name &&
				$password != $firstName &&
				$password != $email &&
				preg_match($regex, $password) &&    //@link http://www.php.net/manual/en/function.preg-match.php
				strlen($email) <= 26 &&     //@link http://www.php.net/manual/en/function.strlen.php
				filter_var($email, FILTER_VALIDATE_EMAIL) &&    //@link http://www.php.net/manual/en/function.filter-var.php
				(2 <= strlen($name) && strlen($name) <= 15) &&
				(2 <= strlen($firstName) && strlen($firstName) <= 15)
			   )
			{
				if(self::checkEmailAvailability($email) != FALSE)
				{

                    $isRegisterValid = self::addFreeUser($name, $firstName, $email, $password, $geolocation);

                    if($isRegisterValid == TRUE)
                    {
                        $mailSent = self::sendRegistrationMail($email);
                        if($mailSent == FALSE)
                            return array('error' => 'Failed to send confirmation e-mail.');
                        else return $mailSent;
                    }
                    else return $isRegisterValid; //contient le message d'erreur approprié
				}
                else
                {
                    $errorInfo = 'Email already used';
                    return array('error' => $errorInfo);
                }
            }
            else
            {
                $errorInfo = 'E-mail address not valid or length specifications not respected';
                return array('error' => $errorInfo);
            }
        }
        else
        {
            $errorInfo = 'Some fields are empty';
            return array('error' => $errorInfo);
        }
	}

    /**
     * Ajoute en base un token pour la validation de l'inscription
     * @author Alban Truc
     * @param int $state
     * @param string $email
     * @since 16/04/2014
     * @return array|TRUE
     */

    public function createValidationToken($state, $email)
    {
        $validation = array(
            'state' => (int)$state,
            'email' => (string)$email,
            'token' => $this->generateGUID(),
            'date' => new MongoDate()
        );

        $result = parent::__create('validation', $validation);

        return $result;
    }

    /**
     * Envoie un mail pour demander à l'utilisateur de confirmer son inscription.
     * @author Alban Truc
     * @param string $email
     * @since 16/04/2014
     * @return TRUE|FALSE
     */

    public function sendRegistrationMail($email)
    {
        $result = $this->createValidationToken(0, $email);

        if($result == TRUE)
        {
            $result = parent::__findOne('validation', array('email' => $email));

            if(!(array_key_exists('error', $result)))
            {
                $boundary = "-----=".md5(rand());

                global $projectRoot;

                $htmlFileContent = file_get_contents($projectRoot.'/view/emailTemplates/registration/html.php');
                $textFileContent = file_get_contents($projectRoot.'/view/emailTemplates/registration/plainText.txt');

                $activationLink = 'http://localhost:8081/Cubbyhole/view/member/confirmRegistration.php?email='.$email
                                 .'&token='.$result['token'];
                $htmlFileContent = str_replace('activationLink', $activationLink, $htmlFileContent);
                $textFileContent = str_replace('activationLink', $activationLink, $textFileContent);

                $header = setMailHeader($boundary);
                $message = setMailContent($boundary, $htmlFileContent, $textFileContent);

                $mailSent = sendMail($email, "Cubbyhole Account Confirmation", $message, $header);

                return $mailSent;
            }
        }

    }

    /**
     * Confirme l'inscription de l'utilisateur. Celui-ci pourra à présent se connecter.
     * @author Alban Truc
     * @param $email
     * @param $token
     * @since 16/04/2014
     * @return array|string
     */

    public function validateRegistration($email, $token)
    {
        $criteria = array(
            'email' => $email,
            'token' => $token
        );

        $result = parent::__findOne('validation', $criteria);

        if(!(array_key_exists('error', $result)))
        {
            //Activation non encore effectuée
            if($result['state'] == 0)
            {
                $updateCriteria = array('email' => $email);
                $update = array('$set' => array('state' => (int)1));
                $updateUserState = $this->update($updateCriteria, $update);

                if($updateUserState == TRUE)
                {
                    $updateValidationState = parent::__update('validation', $updateCriteria, $update);

                    if($updateValidationState == TRUE)
                        return 'Registration successfully validated. You can now login!';
                    else
                    {
                        $errorMessage = 'Your account was activated but we had trouble acknowledging this information.'
                                       .'Please contact us.';
                        return array('error' => $errorMessage);
                    }
                }
                else return array('error' => 'Couldn\'t activate your account.');
            }
            else return array('error' => 'You already validated your registration.');
        }
        else return array('error' => 'No registration found');
    }

    /**
     * Pour changer de mot de passe.
     * @author Alban Truc
     * @param string $email
     * @param string $oldPassword
     * @param string $newPassword
     * @param string $newPasswordConfirmation
     * @since 17/04/2014
     * @return array|TRUE
     */

    public function changePassword($email, $oldPassword, $newPassword, $newPasswordConfirmation)
    {
        /*
        **Entre 8 et 26 caractères, mini un chiffre, mini une lettre minuscule, mini une lettre majuscule,
        **minimum un caractère spécial (@*#).
        **Exemples de caractères non acceptés: ‘ , \ &amp; $ &lt; &gt; et l'espace (\s).
        */
        $regex = '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@*#]).([a-zA-Z0-9@*#]{8,26})$/';

        if($newPassword == $newPasswordConfirmation)
        {
            if(preg_match($regex, $newPassword))
            {
                //Récupère l'utilisateur inscrit avec l'e-mail indiquée.
                $query = array(
                    'state' => (int)1,
                    'email' => $email
                );

                $user = self::findOne($query);

                if($user instanceof User) //Si l'utilisateur existe
                {
                    if($user->getPassword() == $oldPassword)
                    {
                        if($oldPassword != $newPassword)
                        {
                            $criteria = array(
                                '_id' => $user->getId()
                            );
                            $result = $this->update($criteria, array('$set' => array('password' => (string)$newPassword)));

                            if($result == TRUE)
                            {
                                session_start();
                                session_destroy();
                                return TRUE;
                            }
                            else return array('error' => 'We didn\'t manage to change your password.');
                        }
                        else
                        {
                            $errorInfo = 'Old password and new password are the same!';
                            return array('error' => $errorInfo);
                        }
                    }
                    else
                    {
                        $errorInfo = 'Password given ('.$oldPassword.') does not match with password in database.';
                        return array('error' => $errorInfo);
                    }
                }
                else
                {
                    $errorInfo = 'No ACTIVE user found for the following e-mail: '.$email;
                    return array('error' => $errorInfo);
                }
            }
            else return array('error' => 'New password doesn\'t match password specifications');
        }
        else return array('error' => 'The new password and its confirmation aren\'t the same');
    }

    /**
     * Envoi un e-mail pour permettre à un utilisateur de reset sont mot de passe.
     * @author Alban Truc
     * @param $email
     * @since 17/04/2014
     * @return array|bool
     */
    public function sendResetPasswordRequest($email)
    {
        //Récupère l'utilisateur inscrit avec l'e-mail indiquée.
        $query = array(
            'state' => (int)1,
            'email' => $email
        );

        $user = self::findOne($query);

        if($user instanceof User) //Si l'utilisateur existe
        {
            //insertion dans validation. 2 correspond à une demande de reset non utilisée
            $validation = $this->createValidationToken(2, $email);

            if($validation == TRUE)
            {
                $result = parent::__find('validation', array('email' => $email));

                if(!(is_array($result)) && !(array_key_exists('error', $result)))
                {
                    $result->sort(array('date' => -1));
                    $result->limit(1);
                    $tmp = iterator_to_array($result);
                    $result = array_shift($tmp);

                    $boundary = "-----=".md5(rand());

                    global $projectRoot;

                    $htmlFileContent = file_get_contents($projectRoot.'/view/emailTemplates/passwordLost/html.php');
                    $textFileContent = file_get_contents($projectRoot.'/view/emailTemplates/passwordLost/plainText.txt');

                    $resetPasswordLink = 'http://localhost:8081/Cubbyhole/view/member/confirmPasswordReset.php?email='.$email
                        .'&token='.$result['token'];
                    $htmlFileContent = str_replace('resetPasswordLink', $resetPasswordLink, $htmlFileContent);
                    $textFileContent = str_replace('resetPasswordLink', $resetPasswordLink, $textFileContent);

                    $header = setMailHeader($boundary);
                    $message = setMailContent($boundary, $htmlFileContent, $textFileContent);

                    $mailSent = sendMail($email, "Cubbyhole Password Reset", $message, $header);

                    return $mailSent;
                }
                else return $result;
            }
        }
        else
        {
            $errorInfo = 'No ACTIVE user found for the following e-mail: '.$email;
            return array('error' => $errorInfo);
        }
    }

    public function validatePasswordReset($email, $token, $newPassword, $newPasswordConfirmation)
    {
        $criteria = array(
            'email' => $email,
            'token' => $token
        );

        $result = parent::__findOne('validation', $criteria);

        if(!(array_key_exists('error', $result)))
        {
            //Reset non encore effectué
            if($result['state'] == 2)
            {
                $user = $this->findOne(array());

                $updateUserState = $this->update(array('email' => $email), array('$set' => array('password' => $newPassword)));

                if($updateUserState == TRUE)
                {
                    $updateValidation = array('$set' => array('state' => (int)3)); //3 = reset effectué
                    $updateValidationState = parent::__update('validation', $criteria, $updateValidation);

                    if($updateValidationState == TRUE)
                        return 'Password changed successfully. You can now login!';
                    else
                    {
                        $errorMessage = 'Your password was changed but we had trouble acknowledging this information.'
                            .'Please contact us.';
                        return array('error' => $errorMessage);
                    }
                }
                else return $updateUserState;
            }
            else
            {
                $errorMessage = 'You already used your token to reset your password.'
                               .'You will have to request for another reset if you really want to do so.';
                return array('error' => $errorMessage);
            }
        }
        else return $result;
    }
}
?>