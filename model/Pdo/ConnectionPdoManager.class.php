<?php
/**
 * Created by PhpStorm.
 * User: Crocell
 * Date: 25/04/14
 * Time: 14:16
 */

/** @var string $projectRoot chemin du projet dans le système de fichier */
$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';

require_once $projectRoot.'/required.php';

/**
 * Class ConnectionPdoManager
 * @author Alban Truc
 */
class ConnectionPdoManager extends AbstractPdoManager implements ConnectionManagerInterface{

    /** @var MongoCollection $connectionCollection collection connection */
    protected $connectionCollection;

    /** @var  UserPdoManager $userPdoManager instance de cette classe */
    protected $userPdoManager;

    /**
     * Constructeur:
     * - Appelle le constructeur de {@see AbstractPdoManager::__construct} (gestion des accès de la BDD).
     * - Initialise la collection connexion.
     * @author Alban Truc
     * @since 01/2014
     */

    public function __construct()
    {
        parent::__construct();
        $this->connectionCollection = $this->getCollection('connection');

        $this->userPdoManager = new UserPdoManager();
    }

    /**
     * Retrouver une connexion selon des critères donnés
     * @author Alban Truc
     * @param array|Connection $criteria critères de recherche
     * @param array $fieldsToReturn champs à récupérer
     * @since 29/03/2014
     * @return array|Connection[]
     */
    function find($criteria, $fieldsToReturn = array())
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof Connection)
            $criteria = $this->dismount($criteria);

        if(isset($criteria['idUser']))
        {
            if($criteria['idUser'] instanceof User)
                $criteria['idUser'] = new MongoId($criteria['idUser']->getId());
            else if(is_array($criteria['idUser']) && isset($criteria['idUser']['_id']))
                $criteria['idUser'] = $criteria['idUser']['_id'];
        }

        $cursor = parent::__find('connection', $criteria, $fieldsToReturn);

        if(!(is_array($cursor)) && !(array_key_exists('error', $cursor)))
        {
            $connections = array();

            foreach($cursor as $connection)
            {
                if(empty($fieldsToReturn))
                    $connection = new Connection($connection);

                $connections[] = $connection;
            }

            if(empty($connections))
                return array('error' => 'No match found.');
            else
                return $connections;
        }
        else return $cursor; //message d'erreur
    }

    /**
     * Retourne la première connexion correspondant au(x) critère(s) donné(s)
     * @author Alban Truc
     * @param array|Connection $criteria critère(s) de recherche
     * @param array $fieldsToReturn champs à retourner
     * @since 29/03/2014
     * @return array|Connection
     */
    function findOne($criteria, $fieldsToReturn = array())
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof Connection)
            $criteria = $this->dismount($criteria);

        if(isset($criteria['idUser']))
        {
            if($criteria['idUser'] instanceof User)
                $criteria['idUser'] = new MongoId($criteria['idUser']->getId());
            else if(is_array($criteria['idUser']) && isset($criteria['idUser']['_id']))
                $criteria['idUser'] = $criteria['idUser']['_id'];
        }

        $result = parent::__findOne('connection', $criteria, $fieldsToReturn);

        if((is_array($result)) && !(array_key_exists('error', $result)))
        {
            if(empty($fieldsToReturn))
                $result = new Connection($result);
        }

        return $result;
    }

    /**
     * - Retrouver une connexion par son ID.
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param string|MongoId $id Identifiant unique de la connexion à trouver
     * @param array $fieldsToReturn champs à retourner
     * @since 02/2014
     * @return Connection|array contenant le message d'erreur
     */
    function findById($id, $fieldsToReturn = array())
    {
        $result = parent::__findOne('connection', array('_id' => new MongoId($id)), $fieldsToReturn);

        //Si un compte est trouvé
        if (!(array_key_exists('error', $result)))
        {
            if(empty($fieldsToReturn))
                $result = new Connection($result);
        }

        return $result;
    }

    /**
     * - Retrouver l'ensemble des connexions
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array $fieldsToReturn champs à retourner
     * @since 11/03/2014
     * @return array|Connection[] tableau d'objets Connection
     */
    function findAll($fieldsToReturn = array())
    {
        $cursor = parent::__find('connection', $fieldsToReturn);

        if(!(is_array($cursor)) && !(array_key_exists('error', $cursor)))
        {
            $connections = array();

            foreach($cursor as $connection)
            {
                if(empty($fieldsToReturn))
                    $connection = new Connection($connection);

                $connections[] = $connection;
            }
        }

        if(empty($connections))
            return array('error' => 'No connection found.');
        else
            return $connections;
    }

    /**
     * - Retrouver une connexion selon certains critères et le modifier/supprimer
     * - Récupérer cette connexion ou sa version modifiée
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|Connection $searchQuery critères de recherche
     * @param array|Connection $updateCriteria les modifications à réaliser
     * @param array|NULL $fieldsToReturn pour ne récupérer que certains champs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return array|Connection
     */
    function findAndModify($searchQuery, $updateCriteria, $fieldsToReturn = NULL, $options = NULL)
    {
        //Transforme $criteria en array s'il contient un objet
        if($searchQuery instanceof Connection)
            $searchQuery = $this->dismount($searchQuery);

        //Transforme $criteria en array s'il contient un objet
        if($updateCriteria instanceof Connection)
            $updateCriteria = $this->dismount($updateCriteria);

        if(isset($searchQuery['idUser']))
        {
            if($searchQuery['idUser'] instanceof User)
                $searchQuery['idUser'] = new MongoId($searchQuery['idUser']->getId());
            else if(is_array($searchQuery['idUser']) && isset($searchQuery['idUser']['_id']))
                $searchQuery['idUser'] = $searchQuery['idUser']['_id'];
        }

        if(isset($updateCriteria['idUser']))
        {
            if($updateCriteria['idUser'] instanceof User)
                $updateCriteria['idUser'] = new MongoId($updateCriteria['idUser']->getId());
            else if(is_array($updateCriteria['idUser']) && isset($updateCriteria['idUser']['_id']))
                $updateCriteria['idUser'] = $updateCriteria['idUser']['_id'];
        }

        $result = parent::__findAndModify('connection', $searchQuery, $updateCriteria, $fieldsToReturn, $options);

        if($fieldsToReturn === NULL)
            $result = new Connection($result);

        return $result;
    }

    /**
     * - Ajoute une connexion en base de données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|Connection $document
     * @param array $options
     * @since 12/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */
    function create($document, $options = array('w' => 1))
    {
        //Transforme $criteria en array s'il contient un objet
        if($document instanceof Connection)
            $document = $this->dismount($document);

        if(isset($document['idUser']))
        {
            if($document['idUser'] instanceof User)
                $document['idUser'] = new MongoId($document['idUser']->getId());
            else if(is_array($document['idUser']) && isset($document['idUser']['_id']))
                $document['idUser'] = $document['idUser']['_id'];
        }

        $result = parent::__create('connection', $document, $options);

        return $result;
    }

    /**
     * Fonction d'update utilisant celle de {@see AbstractPdoManager}
     * @author Alban Truc
     * @param array|Connection $criteria description des entrées à modifier
     * @param array|Connection $update nouvelles valeurs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */
    function update($criteria, $update, $options = array('w' => 1))
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof Connection)
            $criteria = $this->dismount($criteria);

        if(isset($criteria['idUser']))
        {
            if($criteria['idUser'] instanceof User)
                $criteria['idUser'] = new MongoId($criteria['idUser']->getId());
            else if(is_array($criteria['idUser']) && isset($criteria['idUser']['_id']))
                $criteria['idUser'] = $criteria['idUser']['_id'];
        }

        if(isset($update['idUser']))
        {
            if($update['idUser'] instanceof User)
                $update['idUser'] = new MongoId($update['idUser']->getId());
            else if(is_array($update['idUser']) && isset($update['idUser']['_id']))
                $update['idUser'] = $update['idUser']['_id'];
        }

        $result = parent::__update('connection', $criteria, $update, $options);

        return $result;
    }

    /**
     * - Supprime une/des connexion(s) correspondant à des critères données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|Connection $criteria ce qu'il faut supprimer
     * @param array $options
     * @since 11/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */
    function remove($criteria, $options = array('w' => 1))
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof Connection)
            $criteria = $this->dismount($criteria);

        if(isset($criteria['idUser']))
        {
            if($criteria['idUser'] instanceof User)
                $criteria['idUser'] = new MongoId($criteria['idUser']->getId());
            else if(is_array($criteria['idUser']) && isset($criteria['idUser']['_id']))
                $criteria['idUser'] = $criteria['idUser']['_id'];
        }

        $result = parent::__remove('connection', $criteria, $options);

        return $result;
    }
}