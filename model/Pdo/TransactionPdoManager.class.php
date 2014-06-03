<?php
/**
 * Created by PhpStorm.
 * User: Crocell
 * Date: 15/04/14
 * Time: 13:53
 */

/** @var string $projectRoot chemin du projet dans le système de fichier */
$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';

require_once $projectRoot.'/required.php';

/**
 * Class TransactionPdoManager
 * @author Alban Truc
 */
class TransactionPdoManager extends AbstractPdoManager implements TransactionManagerInterface{

    /** @var MongoCollection $transactionCollection collection transaction */
    protected $transactionCollection;

    /** @var  UserPdoManager $userPdoManager instance de cette classe */
    protected $userPdoManager;

    /** @var RefActionPdoManager $refActionPdoManager instance de cette classe */
    protected $refActionPdoManager;

    /**
     * Constructeur:
     * - Appelle le constructeur de {@see AbstractPdoManager::__construct} (gestion des accès de la BDD).
     * - Initialise la collection transaction.
     * @author Alban Truc
     * @since 01/2014
     */

    public function __construct()
    {
        parent::__construct();
        $this->transactionCollection = $this->getCollection('transaction');

        $this->userPdoManager = new UserPdoManager();
        $this->refActionPdoManager = new RefActionPdoManager();
    }

    /**
     * Retrouver une transaction selon des critères donnés
     * @author Alban Truc
     * @param array|Transaction $criteria critères de recherche
     * @param array $fieldsToReturn champs à récupérer
     * @since 29/03/2014
     * @return array|Transaction[]
     */
    function find($criteria, $fieldsToReturn = array())
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof Transaction)
            $criteria = $this->dismount($criteria);

        if(isset($criteria['idEmitter']))
        {
            if($criteria['idEmitter'] instanceof User)
                $criteria['idEmitter'] = new MongoId($criteria['idEmitter']->getId());
            else if(is_array($criteria['idEmitter']) && isset($criteria['idEmitter']['_id']))
                $criteria['idEmitter'] = $criteria['idEmitter']['_id'];
        }

        if(isset($criteria['idReceiver']))
        {
            if($criteria['idReceiver'] instanceof User)
                $criteria['idReceiver'] = new MongoId($criteria['idReceiver']->getId());
            else if(is_array($criteria['idReceiver']) && isset($criteria['idReceiver']['_id']))
                $criteria['idReceiver'] = $criteria['idReceiver']['_id'];
        }

        if(isset($criteria['idRefAction']))
        {
            if($criteria['idRefAction'] instanceof RefAction)
                $criteria['idRefAction'] = new MongoId($criteria['idRefAction']->getId());
            else if(is_array($criteria['idRefAction']) && isset($criteria['idRefAction']['_id']))
                $criteria['idRefAction'] = $criteria['idRefAction']['_id'];
        }

        $cursor = parent::__find('transaction', $criteria, $fieldsToReturn);

        if(!(is_array($cursor)) && !(array_key_exists('error', $cursor)))
        {
            $transactions = array();

            foreach($cursor as $transaction)
            {
                if(empty($fieldsToReturn))
                    $transaction = new Transaction($transaction);

                $transactions[] = $transaction;
            }

            if(empty($transactions))
                return array('error' => 'No match found.');
            else
                return $transactions;
        }
        else return $cursor; //message d'erreur
    }

    /**
     * Retourne la première transaction correspondant au(x) critère(s) donné(s)
     * @author Alban Truc
     * @param array|Transaction $criteria critère(s) de recherche
     * @param array $fieldsToReturn champs à retourner
     * @since 29/03/2014
     * @return array|Transaction
     */
    function findOne($criteria, $fieldsToReturn = array())
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof Transaction)
            $criteria = $this->dismount($criteria);

        if(isset($criteria['idEmitter']))
        {
            if($criteria['idEmitter'] instanceof User)
                $criteria['idEmitter'] = new MongoId($criteria['idEmitter']->getId());
            else if(is_array($criteria['idEmitter']) && isset($criteria['idEmitter']['_id']))
                $criteria['idEmitter'] = $criteria['idEmitter']['_id'];
        }

        if(isset($criteria['idReceiver']))
        {
            if($criteria['idReceiver'] instanceof User)
                $criteria['idReceiver'] = new MongoId($criteria['idReceiver']->getId());
            else if(is_array($criteria['idReceiver']) && isset($criteria['idReceiver']['_id']))
                $criteria['idReceiver'] = $criteria['idReceiver']['_id'];
        }

        if(isset($criteria['idRefAction']))
        {
            if($criteria['idRefAction'] instanceof RefAction)
                $criteria['idRefAction'] = new MongoId($criteria['idRefAction']->getId());
            else if(is_array($criteria['idRefAction']) && isset($criteria['idRefAction']['_id']))
                $criteria['idRefAction'] = $criteria['idRefAction']['_id'];
        }

        $result = parent::__findOne('transaction', $criteria, $fieldsToReturn);

        if((is_array($result)) && !(array_key_exists('error', $result)))
        {
            if(empty($fieldsToReturn))
                $result = new Transaction($result);
        }

        return $result;
    }

    /**
     * - Retrouver une transaction par son ID.
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param string|MongoId $id Identifiant unique de la transaction à trouver
     * @param array $fieldsToReturn champs à retourner
     * @since 02/2014
     * @return Transaction|array contenant le message d'erreur
     */
    function findById($id, $fieldsToReturn = array())
    {
        $result = parent::__findOne('transaction', array('_id' => new MongoId($id)), $fieldsToReturn);

        //Si un compte est trouvé
        if (!(array_key_exists('error', $result)))
        {
            if(empty($fieldsToReturn))
                $result = new Transaction($result);
        }

        return $result;
    }

    /**
     * - Retrouver l'ensemble des transactions
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array $fieldsToReturn champs à retourner
     * @since 11/03/2014
     * @return array|Transaction[] tableau d'objets Transaction
     */
    function findAll($fieldsToReturn = array())
    {
        $cursor = parent::__find('transaction', $fieldsToReturn);

        if(!(is_array($cursor)) && !(array_key_exists('error', $cursor)))
        {
            $transactions = array();

            foreach($cursor as $transaction)
            {
                if(empty($fieldsToReturn))
                    $transaction = new Transaction($transaction);

                $transactions[] = $transaction;
            }
        }

        if(empty($transactions))
            return array('error' => 'No transaction found.');
        else
            return $transactions;
    }

    /**
     * - Retrouver une transaction selon certains critères et le modifier/supprimer
     * - Récupérer cette transaction ou sa version modifiée
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|Transaction $searchQuery critères de recherche
     * @param array|Transaction $updateCriteria les modifications à réaliser
     * @param array|NULL $fieldsToReturn pour ne récupérer que certains champs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return array|Transaction
     */
    function findAndModify($searchQuery, $updateCriteria, $fieldsToReturn = NULL, $options = NULL)
    {
        //Transforme $criteria en array s'il contient un objet
        if($searchQuery instanceof Transaction)
            $searchQuery = $this->dismount($searchQuery);

        //Transforme $criteria en array s'il contient un objet
        if($updateCriteria instanceof Transaction)
            $updateCriteria = $this->dismount($updateCriteria);

        if(isset($searchQuery['idEmitter']))
        {
            if($searchQuery['idEmitter'] instanceof User)
                $searchQuery['idEmitter'] = new MongoId($searchQuery['idEmitter']->getId());
            else if(is_array($searchQuery['idEmitter']) && isset($searchQuery['idEmitter']['_id']))
                $searchQuery['idEmitter'] = $searchQuery['idEmitter']['_id'];
        }

        if(isset($searchQuery['idReceiver']))
        {
            if($searchQuery['idReceiver'] instanceof User)
                $searchQuery['idReceiver'] = new MongoId($searchQuery['idReceiver']->getId());
            else if(is_array($searchQuery['idReceiver']) && isset($searchQuery['idReceiver']['_id']))
                $searchQuery['idReceiver'] = $searchQuery['idReceiver']['_id'];
        }

        if(isset($searchQuery['idRefAction']))
        {
            if($searchQuery['idRefAction'] instanceof RefAction)
                $searchQuery['idRefAction'] = new MongoId($searchQuery['idRefAction']->getId());
            else if(is_array($searchQuery['idRefAction']) && isset($searchQuery['idRefAction']['_id']))
                $searchQuery['idRefAction'] = $searchQuery['idRefAction']['_id'];
        }

        if(isset($updateCriteria['idEmitter']))
        {
            if($updateCriteria['idEmitter'] instanceof User)
                $updateCriteria['idEmitter'] = new MongoId($updateCriteria['idEmitter']->getId());
            else if(is_array($updateCriteria['idEmitter']) && isset($updateCriteria['idEmitter']['_id']))
                $updateCriteria['idEmitter'] = $updateCriteria['idEmitter']['_id'];
        }

        if(isset($updateCriteria['idReceiver']))
        {
            if($updateCriteria['idReceiver'] instanceof User)
                $updateCriteria['idReceiver'] = new MongoId($updateCriteria['idReceiver']->getId());
            else if(is_array($updateCriteria['idReceiver']) && isset($updateCriteria['idReceiver']['_id']))
                $updateCriteria['idReceiver'] = $updateCriteria['idReceiver']['_id'];
        }

        if(isset($updateCriteria['idRefAction']))
        {
            if($updateCriteria['idRefAction'] instanceof RefAction)
                $updateCriteria['idRefAction'] = new MongoId($updateCriteria['idRefAction']->getId());
            else if(is_array($updateCriteria['idRefAction']) && isset($updateCriteria['idRefAction']['_id']))
                $updateCriteria['idRefAction'] = $updateCriteria['idRefAction']['_id'];
        }

        $result = parent::__findAndModify('transaction', $searchQuery, $updateCriteria, $fieldsToReturn, $options);

        if($fieldsToReturn === NULL)
            $result = new Transaction($result);

        return $result;
    }

    /**
     * - Ajoute une transaction en base de données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|Transaction $document
     * @param array $options
     * @since 12/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */
    function create($document, $options = array('w' => 1))
    {
        //Transforme $criteria en array s'il contient un objet
        if($document instanceof Transaction)
            $document = $this->dismount($document);

        if(isset($document['idEmitter']))
        {
            if($document['idEmitter'] instanceof User)
                $document['idEmitter'] = new MongoId($document['idEmitter']->getId());
            else if(is_array($document['idEmitter']) && isset($document['idEmitter']['_id']))
                $document['idEmitter'] = $document['idEmitter']['_id'];
        }

        if(isset($document['idReceiver']))
        {
            if($document['idReceiver'] instanceof User)
                $document['idReceiver'] = new MongoId($document['idReceiver']->getId());
            else if(is_array($document['idReceiver']) && isset($document['idReceiver']['_id']))
                $document['idReceiver'] = $document['idReceiver']['_id'];
        }

        if(isset($document['idRefAction']))
        {
            if($document['idRefAction'] instanceof RefAction)
                $document['idRefAction'] = new MongoId($document['idRefAction']->getId());
            else if(is_array($document['idRefAction']) && isset($document['idRefAction']['_id']))
                $document['idRefAction'] = $document['idRefAction']['_id'];
        }

        $document['calledFrom'] = "commercial website";

        $result = parent::__create('transaction', $document, $options);

        return $result;
    }

    /**
     * Fonction d'update utilisant celle de {@see AbstractPdoManager}
     * @author Alban Truc
     * @param array|Transaction $criteria description des entrées à modifier
     * @param array|Transaction $update nouvelles valeurs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */
    function update($criteria, $update, $options = array('w' => 1))
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof Transaction)
            $criteria = $this->dismount($criteria);

        if(isset($criteria['idEmitter']))
        {
            if($criteria['idEmitter'] instanceof User)
                $criteria['idEmitter'] = new MongoId($criteria['idEmitter']->getId());
            else if(is_array($criteria['idEmitter']) && isset($criteria['idEmitter']['_id']))
                $criteria['idEmitter'] = $criteria['idEmitter']['_id'];
        }

        if(isset($criteria['idReceiver']))
        {
            if($criteria['idReceiver'] instanceof User)
                $criteria['idReceiver'] = new MongoId($criteria['idReceiver']->getId());
            else if(is_array($criteria['idReceiver']) && isset($criteria['idReceiver']['_id']))
                $criteria['idReceiver'] = $criteria['idReceiver']['_id'];
        }

        if(isset($criteria['idRefAction']))
        {
            if($criteria['idRefAction'] instanceof RefAction)
                $criteria['idRefAction'] = new MongoId($criteria['idRefAction']->getId());
            else if(is_array($criteria['idRefAction']) && isset($criteria['idRefAction']['_id']))
                $criteria['idRefAction'] = $criteria['idRefAction']['_id'];
        }

        if(isset($update['idEmitter']))
        {
            if($update['idEmitter'] instanceof User)
                $update['idEmitter'] = new MongoId($update['idEmitter']->getId());
            else if(is_array($update['idEmitter']) && isset($update['idEmitter']['_id']))
                $update['idEmitter'] = $update['idEmitter']['_id'];
        }

        if(isset($update['idReceiver']))
        {
            if($update['idReceiver'] instanceof User)
                $update['idReceiver'] = new MongoId($update['idReceiver']->getId());
            else if(is_array($update['idReceiver']) && isset($update['idReceiver']['_id']))
                $update['idReceiver'] = $update['idReceiver']['_id'];
        }

        if(isset($update['idRefAction']))
        {
            if($update['idRefAction'] instanceof RefAction)
                $update['idRefAction'] = new MongoId($update['idRefAction']->getId());
            else if(is_array($update['idRefAction']) && isset($update['idRefAction']['_id']))
                $update['idRefAction'] = $update['idRefAction']['_id'];
        }

        $result = parent::__update('transaction', $criteria, $update, $options);

        return $result;
    }

    /**
     * - Supprime une/des transaction(s) correspondant à des critères données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|Transaction $criteria ce qu'il faut supprimer
     * @param array $options
     * @since 11/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */
    function remove($criteria, $options = array('w' => 1))
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof Transaction)
            $criteria = $this->dismount($criteria);

        if(isset($criteria['idEmitter']))
        {
            if($criteria['idEmitter'] instanceof User)
                $criteria['idEmitter'] = new MongoId($criteria['idEmitter']->getId());
            else if(is_array($criteria['idEmitter']) && isset($criteria['idEmitter']['_id']))
                $criteria['idEmitter'] = $criteria['idEmitter']['_id'];
        }

        if(isset($criteria['idReceiver']))
        {
            if($criteria['idReceiver'] instanceof User)
                $criteria['idReceiver'] = new MongoId($criteria['idReceiver']->getId());
            else if(is_array($criteria['idReceiver']) && isset($criteria['idReceiver']['_id']))
                $criteria['idReceiver'] = $criteria['idReceiver']['_id'];
        }

        if(isset($criteria['idRefAction']))
        {
            if($criteria['idRefAction'] instanceof RefAction)
                $criteria['idRefAction'] = new MongoId($criteria['idRefAction']->getId());
            else if(is_array($criteria['idRefAction']) && isset($criteria['idRefAction']['_id']))
                $criteria['idRefAction'] = $criteria['idRefAction']['_id'];
        }

        $result = parent::__remove('transaction', $criteria, $options);

        return $result;
    }
}
?>