<?php
/**
 * Created by Notepad++.
 * User: Alban Truc
 * Date: 31/01/14
 * Time: 12:53
 */

/** @var string $projectRoot chemin du projet dans le système de fichier */
$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Cubbyhole';

require_once $projectRoot.'/required.php';
/**
 * Class AccountPdoManager
 * @author Alban Truc
 */
class AccountPdoManager extends AbstractPdoManager implements AccountManagerInterface
{
    /** @var MongoCollection $accountCollection collection account */
    protected $accountCollection;

    /** @var  UserPdoManager $userPdoManager instance de cette classe */
    protected $userPdoManager;

    /** @var RefPlanPdoManager $refPlanPdoManager instance de cette classe */
    protected $refPlanPdoManager;

    /**
     * Constructeur:
     * - Appelle le constructeur de {@see AbstractPdoManager::__construct} (gestion des accès de la BDD).
     * - Initialise la collection account.
     * - Crée un objet RefPlanPdoManager (l'account a une clé étrangère de refPlan).
     * - Crée un objet UserPdoManager (l'account a une clé étrangère de user).
     * @author Alban Truc
     * @since 01/2014
     */

    public function __construct()
    {
        parent::__construct();
        $this->accountCollection = $this->getCollection('account');
        /**
         * Le UserPdoManager nécessite l'AccountPdoManager qui nécessite le UserPdoManager...
         * Pour éviter un appel infini entre ces deux constructeurs:
         * - je passe ici l'instance actuelle d'AccountPdoManager au constructeur de UserPdoManager
         * - le constructeur UserPdoManager se chargera ensuite de distinguer s'il doit créer une nouvelle instance
         * d'AccountPdoManager ou utiliser une référence. {@see UserPdoManager::__construct}
         */
        $this->userPdoManager = new UserPdoManager($this);
        $this->refPlanPdoManager = new RefPlanPdoManager();
    }

    /**
     * Retrouver un Account selon des critères donnés
     * @author Alban Truc
     * @param array|Account $criteria critères de recherche
     * @param array $fieldsToReturn champs à récupérer
     * @since 29/03/2014
     * @return array|Account[]
     */

    public function find($criteria, $fieldsToReturn = array())
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof Account)
            $criteria = $this->dismount($criteria);

        /**
         * Les key idUser et idRefPlan peuvent contenir des array au lieu des id.
         * Les manipulations suivantes sont donc nécessaires pour éviter tout problème de recherche.
         * Dans le cas où il serait nécessaire de faire un find qui match également le user et l'account,
         * je pense qu'il serait préférable de faire une fonction "deepFind" (par exemple);
         * similaire à celle-ci mais qui appelle en plus les fonctions find de UserPdoManager et RefPlanPdoManager.
         * Bien entendu, pour éviter la redondance de code, il faudrait sans doute faire une 3ème fonction,
         * Il en va de même pour toutes les fonctions de type CRUD.
         *
         * Notons qu'encore une autre fonction serait nécessaire pour répéter cette tâche de "transformation"
         * d'un array imbriqué en simple clé id.
         */
        if(isset($criteria['idUser']))
        {
            if($criteria['idUser'] instanceof User)
                $criteria['idUser'] = new MongoId($criteria['idUser']->getId());
            else if(is_array($criteria['idUser']) && isset($criteria['idUser']['_id']))
                $criteria['idUser'] = $criteria['idUser']['_id'];
        }

        if(isset($criteria['idRefPlan']))
        {
            if($criteria['idRefPlan'] instanceof RefPlan)
                $criteria['idRefPlan'] = new MongoId($criteria['idRefPlan']->getId());
            else if(is_array($criteria['idRefPlan']) && isset($criteria['idRefPlan']['_id']))
                $criteria['idRefPlan'] = $criteria['idRefPlan']['_id'];
        }

        $cursor = parent::__find('account', $criteria, $fieldsToReturn);

        if(!(is_array($cursor)) && !(array_key_exists('error', $cursor)))
        {
            $accounts = array();
//            $user = NULL;
//            $refPlan = NULL;

            foreach($cursor as $account)
            {
//                if(array_key_exists('idUser', $account)) //récupère l'user
//                    $user = $this->userPdoManager->findById($account['idUser']);
//
//                if(array_key_exists('idRefPlan', $account)) //récupère le refPlan
//                    $refPlan = $this->refPlanPdoManager->findById($account['idRefPlan']);

//                if(!(empty($fieldsToReturn)))
//                {
//                    if ($user !== NULL && !(array_key_exists('error', $user))) {
//                        $user = $this->dismount($user);
//                        $account['idUser'] = $user;
//                    }
//
//                    if ($refPlan !== NULL && !(array_key_exists('error', $refPlan))) {
//                        $refPlan = $this->dismount($refPlan);
//                        $account['idRefPlan'] = $refPlan;
//                    }

                    /**
                     * D'après les commentaires sur la page {@link https://php.net/manual/en/function.array-push.php}
                     * la méthode utilisée ici est plus rapide d'exécution qu'utiliser un array_push.
                     * Par ailleurs nous n'avons pas besoin de la valeur que retourne array_push
                     * (à savoir le nombre d'éléments dans le tableau)
                     */
//                    $accounts[] = $account;
//                }
//                else
//                {
//                    $account = new Account($account);
//                    $account->setUser($user);
//                    $account->setRefPlan($refPlan);
//
//                    $accounts[] = $account;
//                }
                if(empty($fieldsToReturn))
                    $account = new Account($account);

                $accounts[] = $account;
            }

            if(empty($accounts))
                return array('error' => 'No match found.');
            else
                return $accounts;
        }
        else return $cursor; //message d'erreur
    }

    /**
     * Retourne le premier Account correspondant au(x) critère(s) donné(s)
     * @author Alban Truc
     * @param array|Account $criteria critère(s) de recherche
     * @param array $fieldsToReturn champs à retourner
     * @since 29/03/2014
     * @return array|Account
     */

    public function findOne($criteria, $fieldsToReturn = array())
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof Account)
            $criteria = $this->dismount($criteria);

        //cf fonction find
        if(isset($criteria['idUser']))
        {
            if($criteria['idUser'] instanceof User)
                $criteria['idUser'] = new MongoId($criteria['idUser']->getId());
            else if(is_array($criteria['idUser']) && isset($criteria['idUser']['_id']))
                $criteria['idUser'] = $criteria['idUser']['_id'];
        }

        if(isset($criteria['idRefPlan']))
        {
            if($criteria['idRefPlan'] instanceof RefPlan)
                $criteria['idRefPlan'] = new MongoId($criteria['idRefPlan']->getId());
            else if(is_array($criteria['idRefPlan']) && isset($criteria['idRefPlan']['_id']))
                $criteria['idRefPlan'] = $criteria['idRefPlan']['_id'];
        }

        $result = parent::__findOne('account', $criteria, $fieldsToReturn);

        if(!(is_array($result)) && !(array_key_exists('error', $result)))
        {
            if(empty($fieldsToReturn))
                $result = new Account($result);
        }

        return $result;
    }

    /**
     * - Retrouver l'ensemble des Account
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array $fieldsToReturn champs à retourner
     * @since 11/03/2014
     * @return array|Account[] tableau d'objets Account
     */

    public function findAll($fieldsToReturn = array())
    {
        $cursor = parent::__find('account', array());

        if(!(is_array($cursor)) && !(array_key_exists('error', $cursor)))
        {
            $accounts = array();

            foreach($cursor as $account)
            {
                if(empty($fieldsToReturn))
                    $account = new Account($account);

                $accounts[] = $account;
            }
        }

        if(empty($accounts))
            return array('error' => 'No account found.');
        else
            return $accounts;
    }

    /**
     * - Retrouver un account par son ID.
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param string|MongoId $id Identifiant unique de l'account à trouver
     * @param array $fieldsToReturn champs à retourner
     * @since 02/2014
     * @return Account|array contenant le message d'erreur
     */

    public function findById($id, $fieldsToReturn = array())
    {
        $result = parent::__findOne('account', array('_id' => new MongoId($id)));

        //Si un compte est trouvé
        if (!(array_key_exists('error', $result)))
        {
            if(empty($fieldsToReturn))
                $result = new Account($result);
        }

        return $result;
    }

    /**
     * - Retrouver un Account selon certains critères et le modifier/supprimer
     * - Récupérer cet Account ou sa version modifiée
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|Account $searchQuery critères de recherche
     * @param array|Account $updateCriteria les modifications à réaliser
     * @param array|NULL $fieldsToReturn pour ne récupérer que certains champs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return array|Account
     */

    public function findAndModify($searchQuery, $updateCriteria, $fieldsToReturn = NULL, $options = NULL)
    {
        //Transforme $searchQuery en array s'il contient un objet
        if($searchQuery instanceof Account)
            $searchQuery = $this->dismount($searchQuery);

        //Transforme $updateCriteria en array s'il contient un objet
        if($updateCriteria instanceof Account)
            $updateCriteria = $this->dismount($updateCriteria);

        //cf fonction find
        if(isset($searchQuery['idUser']))
        {
            if($searchQuery['idUser'] instanceof User)
                $searchQuery['idUser'] = new MongoId($searchQuery['idUser']->getId());
            else if(is_array($searchQuery['idUser']) && isset($searchQuery['idUser']['_id']))
                $searchQuery['idUser'] = $searchQuery['idUser']['_id'];
        }

        if(isset($searchQuery['idRefPlan']))
        {
            if($searchQuery['idRefPlan'] instanceof RefPlan)
                $searchQuery['idRefPlan'] = new MongoId($searchQuery['idRefPlan']->getId());
            else if(is_array($searchQuery['idRefPlan']) && isset($searchQuery['idRefPlan']['_id']))
                $searchQuery['idRefPlan'] = $searchQuery['idRefPlan']['_id'];
        }

        if(isset($updateCriteria['idUser']))
        {
            if($updateCriteria['idUser'] instanceof User)
                $updateCriteria['idUser'] = new MongoId($updateCriteria['idUser']->getId());
            else if(is_array($updateCriteria['idUser']) && isset($updateCriteria['idUser']['_id']))
                $updateCriteria['idUser'] = $updateCriteria['idUser']['_id'];
        }

        if(isset($updateCriteria['idRefPlan']))
        {
            if($updateCriteria['idRefPlan'] instanceof RefPlan)
                $updateCriteria['idRefPlan'] = new MongoId($updateCriteria['idRefPlan']->getId());
            else if(is_array($updateCriteria['idRefPlan']) && isset($updateCriteria['idRefPlan']['_id']))
                $updateCriteria['idRefPlan'] = $updateCriteria['idRefPlan']['_id'];
        }

        $result = parent::__findAndModify('account', $searchQuery, $updateCriteria, $fieldsToReturn, $options);

        if($fieldsToReturn === NULL)
            $result = new Account($result);

        return $result;
    }

    /**
     * - Insère un nouveau compte en base.
     * - Gestion des exceptions et des erreurs
     * - On n'insert pas de nouveau refPlan, ceux-ci sont déjà définis en base.
     * @author Alban Truc
     * @param array|Account $account
     * @param array $options
     * @since 31/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    public function create($account, $options = array('w' => 1))
    {
        //Transforme $account en array s'il contient un objet
        if($account instanceof Account)
            $account = $this->dismount($account);

        //cf fonction find
        if(isset($account['idUser']))
        {
            if($account['idUser'] instanceof User)
                $account['idUser'] = new MongoId($account['idUser']->getId());
            else if(is_array($account['idUser']) && isset($account['idUser']['_id']))
                $account['idUser'] = $account['idUser']['_id'];
        }

        if(isset($account['idRefPlan']))
        {
            if($account['idRefPlan'] instanceof RefPlan)
                $account['idRefPlan'] = new MongoId($account['idRefPlan']->getId());
            else if(is_array($account['idRefPlan']) && isset($account['idRefPlan']['_id']))
                $account['idRefPlan'] = $account['idRefPlan']['_id'];
        }

        $result = parent::__create('account', $account, $options);

        return $result;
    }

    /**
     * Fonction d'update utilisant celle de {@see AbstractPdoManager}
     * @author Alban Truc
     * @param array|Account $criteria description des entrées à modifier
     * @param array|Account $update nouvelles valeurs
     * @param array|NULL $options
     * @since 31/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    public function update($criteria, $update, $options = array('w' => 1))
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof Account)
            $criteria = $this->dismount($criteria);

        //Transforme $update en array s'il contient un objet
        if($update instanceof Account)
            $update = $this->dismount($update);

        //cf fonction find
        if(isset($criteria['idUser']))
        {
            if($criteria['idUser'] instanceof User)
                $criteria['idUser'] = new MongoId($criteria['idUser']->getId());
            else if(is_array($criteria['idUser']) && isset($criteria['idUser']['_id']))
                $criteria['idUser'] = $criteria['idUser']['_id'];
        }

        if(isset($criteria['idRefPlan']))
        {
            if($criteria['idRefPlan'] instanceof RefPlan)
                $criteria['idRefPlan'] = new MongoId($criteria['idRefPlan']->getId());
            else if(is_array($criteria['idRefPlan']) && isset($criteria['idRefPlan']['_id']))
                $criteria['idRefPlan'] = $criteria['idRefPlan']['_id'];
        }

        if(isset($update['idUser']))
        {
            if($update['idUser'] instanceof User)
                $update['idUser'] = new MongoId($update['idUser']->getId());
            else if(is_array($update['idUser']) && isset($update['idUser']['_id']))
                $update['idUser'] = $update['idUser']['_id'];
        }

        if(isset($update['idRefPlan']))
        {
            if($update['idRefPlan'] instanceof RefPlan)
                $update['idRefPlan'] = new MongoId($update['idRefPlan']->getId());
            else if(is_array($update['idRefPlan']) && isset($update['idRefPlan']['_id']))
                $update['idRefPlan'] = $update['idRefPlan']['_id'];
        }

        $result = parent::__update('account', $criteria, $update, $options);

        return $result;
    }

    /**
     * - Supprime un/des compte(s) correspondant à des critères données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|Account $criteria ce qu'il faut supprimer
     * @param array $options
     * @since 31/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    public function remove($criteria, $options = array('w' => 1))
    {
        //Transforme $criteria en array s'il contient un objet
        if(!(is_array($criteria)))
            $criteria = $this->dismount($criteria);

        //cf fonction find
        if(isset($criteria['idUser']))
        {
            if($criteria['idUser'] instanceof User)
                $criteria['idUser'] = new MongoId($criteria['idUser']->getId());
            else if(is_array($criteria['idUser']) && isset($criteria['idUser']['_id']))
                $criteria['idUser'] = $criteria['idUser']['_id'];
        }

        if(isset($criteria['idRefPlan']))
        {
            if($criteria['idRefPlan'] instanceof RefPlan)
                $criteria['idRefPlan'] = new MongoId($criteria['idRefPlan']->getId());
            else if(is_array($criteria['idRefPlan']) && isset($criteria['idRefPlan']['_id']))
                $criteria['idRefPlan'] = $criteria['idRefPlan']['_id'];
        }

        $result = parent::__remove('account', $criteria, $options);

        return $result;
    }
}

?>