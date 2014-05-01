<?php
/**
 * Created by Notepad++.
 * User: Alban Truc
 * Date: 30/01/14
 * Time: 14:51
 */

/** @var string $projectRoot chemin du projet dans le système de fichier */
$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';

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
}
?>