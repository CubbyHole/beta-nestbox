<?php
/**
 * Created by PhpStorm.
 * User: Crocell
 * Date: 25/04/14
 * Time: 15:09
 */

/** @var string $projectRoot chemin du projet dans le système de fichier */
$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';

require_once $projectRoot.'/required.php';

/**
 * Class RightPdoManager
 * @author Alban Truc
 */
class RightPdoManager extends AbstractPdoManager implements RightManagerInterface{

    /** @var MongoCollection $rightCollection collection right */
    protected $rightCollection;

    /** @var  UserPdoManager $userPdoManager instance de cette classe */
    protected $userPdoManager;

    /** @var  RefRightPdoManager $refRightPdoManager instance de cette classe */
    protected $refRightPdoManager;

    /**
     * Constructeur:
     * - Appelle le constructeur de {@see AbstractPdoManager::__construct} (gestion des accès de la BDD).
     * - Initialise la collection right.
     * @author Alban Truc
     * @since 01/2014
     */

    public function __construct()
    {
        parent::__construct();
        $this->rightCollection = $this->getCollection('right');

        $this->userPdoManager = new UserPdoManager();
        $this->refRightPdoManager = new RefRightPdoManager();
    }

    /**
     * Retrouver un droit selon des critères donnés
     * @author Alban Truc
     * @param array|Right $criteria critères de recherche
     * @param array $fieldsToReturn champs à récupérer
     * @since 29/03/2014
     * @return array|Right[]
     */
    function find($criteria, $fieldsToReturn = array())
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof Right)
            $criteria = $this->dismount($criteria);

        if(isset($criteria['idUser']))
        {
            if($criteria['idUser'] instanceof User)
                $criteria['idUser'] = new MongoId($criteria['idUser']->getId());
            else if(is_array($criteria['idUser']) && isset($criteria['idUser']['_id']))
                $criteria['idUser'] = $criteria['idUser']['_id'];
        }

        if(isset($criteria['idElement']))
        {
            if($criteria['idElement'] instanceof Element)
                $criteria['idElement'] = new MongoId($criteria['idElement']->getId());
            else if(is_array($criteria['idElement']) && isset($criteria['idElement']['_id']))
                $criteria['idElement'] = $criteria['idElement']['_id'];
        }

        if(isset($criteria['idRefRight']))
        {
            if($criteria['idRefRight'] instanceof RefRight)
                $criteria['idRefRight'] = new MongoId($criteria['idRefRight']->getId());
            else if(is_array($criteria['idRefRight']) && isset($criteria['idRefRight']['_id']))
                $criteria['idRefRight'] = $criteria['idRefRight']['_id'];
        }

        $cursor = parent::__find('right', $criteria, $fieldsToReturn);

        if(!(is_array($cursor)) && !(array_key_exists('error', $cursor)))
        {
            $rights = array();

            foreach($cursor as $right)
            {
                if(empty($fieldsToReturn))
                    $right = new Right($right);

                $rights[] = $right;
            }

            if(empty($rights))
                return array('error' => 'No match found.');
            else
                return $rights;
        }
        else return $cursor; //message d'erreur
    }

    /**
     * Retourne le premier droit correspondant au(x) critère(s) donné(s)
     * @author Alban Truc
     * @param array|Right $criteria critère(s) de recherche
     * @param array $fieldsToReturn champs à retourner
     * @since 29/03/2014
     * @return array|Right
     */
    function findOne($criteria, $fieldsToReturn = array())
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof Right)
            $criteria = $this->dismount($criteria);

        if(isset($criteria['idUser']))
        {
            if($criteria['idUser'] instanceof User)
                $criteria['idUser'] = new MongoId($criteria['idUser']->getId());
            else if(is_array($criteria['idUser']) && isset($criteria['idUser']['_id']))
                $criteria['idUser'] = $criteria['idUser']['_id'];
        }

        if(isset($criteria['idElement']))
        {
            if($criteria['idElement'] instanceof Element)
                $criteria['idElement'] = new MongoId($criteria['idElement']->getId());
            else if(is_array($criteria['idElement']) && isset($criteria['idElement']['_id']))
                $criteria['idElement'] = $criteria['idElement']['_id'];
        }

        if(isset($criteria['idRefRight']))
        {
            if($criteria['idRefRight'] instanceof RefRight)
                $criteria['idRefRight'] = new MongoId($criteria['idRefRight']->getId());
            else if(is_array($criteria['idRefRight']) && isset($criteria['idRefRight']['_id']))
                $criteria['idRefRight'] = $criteria['idRefRight']['_id'];
        }

        $result = parent::__findOne('right', $criteria, $fieldsToReturn);

        if(!(is_array($result)) && !(array_key_exists('error', $result)))
        {
            if(empty($fieldsToReturn))
                $result = new Right($result);
        }

        return $result;
    }

    /**
     * - Retrouver un droit par son ID.
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param string|MongoId $id Identifiant unique du droit à trouver
     * @param array $fieldsToReturn champs à retourner
     * @since 02/2014
     * @return Right|array contenant le message d'erreur
     */
    function findById($id, $fieldsToReturn = array())
    {
        $result = parent::__findOne('right', array('_id' => new MongoId($id)), $fieldsToReturn);

        //Si un compte est trouvé
        if (!(array_key_exists('error', $result)))
        {
            if(empty($fieldsToReturn))
                $result = new Right($result);
        }

        return $result;
    }

    /**
     * - Retrouver l'ensemble des droits
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array $fieldsToReturn champs à retourner
     * @since 11/03/2014
     * @return array|Right[] tableau d'objets Right
     */
    function findAll($fieldsToReturn = array())
    {
        $cursor = parent::__find('right', $fieldsToReturn);

        if(!(is_array($cursor)) && !(array_key_exists('error', $cursor)))
        {
            $rights = array();

            foreach($cursor as $right)
            {
                if(empty($fieldsToReturn))
                    $right = new Right($right);

                $rights[] = $right;
            }
        }

        if(empty($rights))
            return array('error' => 'No right found.');
        else
            return $rights;
    }

    /**
     * - Retrouver un droit selon certains critères et le modifier/supprimer
     * - Récupérer ce droit ou sa version modifiée
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|Right $searchQuery critères de recherche
     * @param array|Right $updateCriteria les modifications à réaliser
     * @param array|NULL $fieldsToReturn pour ne récupérer que certains champs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return array|Right
     */
    function findAndModify($searchQuery, $updateCriteria, $fieldsToReturn = NULL, $options = NULL)
    {
        //Transforme $criteria en array s'il contient un objet
        if($searchQuery instanceof Right)
            $searchQuery = $this->dismount($searchQuery);

        //Transforme $criteria en array s'il contient un objet
        if($updateCriteria instanceof Right)
            $updateCriteria = $this->dismount($updateCriteria);

        if(isset($searchQuery['idUser']))
        {
            if($searchQuery['idUser'] instanceof User)
                $searchQuery['idUser'] = new MongoId($searchQuery['idUser']->getId());
            else if(is_array($searchQuery['idUser']) && isset($searchQuery['idUser']['_id']))
                $searchQuery['idUser'] = $searchQuery['idUser']['_id'];
        }

        if(isset($searchQuery['idElement']))
        {
            if($searchQuery['idElement'] instanceof Element)
                $searchQuery['idElement'] = new MongoId($searchQuery['idElement']->getId());
            else if(is_array($searchQuery['idElement']) && isset($searchQuery['idElement']['_id']))
                $searchQuery['idElement'] = $searchQuery['idElement']['_id'];
        }

        if(isset($searchQuery['idRefRight']))
        {
            if($searchQuery['idRefRight'] instanceof RefRight)
                $searchQuery['idRefRight'] = new MongoId($searchQuery['idRefRight']->getId());
            else if(is_array($searchQuery['idRefRight']) && isset($searchQuery['idRefRight']['_id']))
                $searchQuery['idRefRight'] = $searchQuery['idRefRight']['_id'];
        }

        if(isset($updateCriteria['idUser']))
        {
            if($updateCriteria['idUser'] instanceof User)
                $updateCriteria['idUser'] = new MongoId($updateCriteria['idUser']->getId());
            else if(is_array($updateCriteria['idUser']) && isset($updateCriteria['idUser']['_id']))
                $updateCriteria['idUser'] = $updateCriteria['idUser']['_id'];
        }

        if(isset($updateCriteria['idElement']))
        {
            if($updateCriteria['idElement'] instanceof Element)
                $updateCriteria['idElement'] = new MongoId($updateCriteria['idElement']->getId());
            else if(is_array($updateCriteria['idElement']) && isset($updateCriteria['idElement']['_id']))
                $updateCriteria['idElement'] = $updateCriteria['idElement']['_id'];
        }

        if(isset($updateCriteria['idRefRight']))
        {
            if($updateCriteria['idRefRight'] instanceof RefRight)
                $updateCriteria['idRefRight'] = new MongoId($updateCriteria['idRefRight']->getId());
            else if(is_array($updateCriteria['idRefRight']) && isset($updateCriteria['idRefRight']['_id']))
                $updateCriteria['idRefRight'] = $updateCriteria['idRefRight']['_id'];
        }

        $result = parent::__findAndModify('right', $searchQuery, $updateCriteria, $fieldsToReturn, $options);

        if($fieldsToReturn === NULL)
            $result = new Right($result);

        return $result;
    }

    /**
     * - Ajoute un droit en base de données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|Right $document
     * @param array $options
     * @since 12/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */
    function create($document, $options = array('w' => 1))
    {
        //Transforme $criteria en array s'il contient un objet
        if($document instanceof Right)
            $document = $this->dismount($document);

        if(isset($document['idUser']))
        {
            if($document['idUser'] instanceof User)
                $document['idUser'] = new MongoId($document['idUser']->getId());
            else if(is_array($document['idUser']) && isset($document['idUser']['_id']))
                $document['idUser'] = $document['idUser']['_id'];
        }

        if(isset($document['idElement']))
        {
            if($document['idElement'] instanceof Element)
                $document['idElement'] = new MongoId($document['idElement']->getId());
            else if(is_array($document['idElement']) && isset($document['idElement']['_id']))
                $document['idElement'] = $document['idElement']['_id'];
        }

        if(isset($document['idRefRight']))
        {
            if($document['idRefRight'] instanceof RefRight)
                $document['idRefRight'] = new MongoId($document['idRefRight']->getId());
            else if(is_array($document['idRefRight']) && isset($document['idRefRight']['_id']))
                $document['idRefRight'] = $document['idRefRight']['_id'];
        }

        $result = parent::__create('right', $document, $options);

        return $result;
    }

    /**
     * Fonction d'update utilisant celle de {@see AbstractPdoManager}
     * @author Alban Truc
     * @param array|Right $criteria description des entrées à modifier
     * @param array|Right $update nouvelles valeurs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */
    function update($criteria, $update, $options = array('w' => 1))
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof Right)
            $criteria = $this->dismount($criteria);

        if(isset($criteria['idUser']))
        {
            if($criteria['idUser'] instanceof User)
                $criteria['idUser'] = new MongoId($criteria['idUser']->getId());
            else if(is_array($criteria['idUser']) && isset($criteria['idUser']['_id']))
                $criteria['idUser'] = $criteria['idUser']['_id'];
        }

        if(isset($criteria['idElement']))
        {
            if($criteria['idElement'] instanceof Element)
                $criteria['idElement'] = new MongoId($criteria['idElement']->getId());
            else if(is_array($criteria['idElement']) && isset($criteria['idElement']['_id']))
                $criteria['idElement'] = $criteria['idElement']['_id'];
        }

        if(isset($criteria['idRefRight']))
        {
            if($criteria['idRefRight'] instanceof RefRight)
                $criteria['idRefRight'] = new MongoId($criteria['idRefRight']->getId());
            else if(is_array($criteria['idRefRight']) && isset($criteria['idRefRight']['_id']))
                $criteria['idRefRight'] = $criteria['idRefRight']['_id'];
        }

        if(isset($update['idUser']))
        {
            if($update['idUser'] instanceof User)
                $update['idUser'] = new MongoId($update['idUser']->getId());
            else if(is_array($update['idUser']) && isset($update['idUser']['_id']))
                $update['idUser'] = $update['idUser']['_id'];
        }

        if(isset($update['idElement']))
        {
            if($update['idElement'] instanceof Element)
                $update['idElement'] = new MongoId($update['idElement']->getId());
            else if(is_array($update['idElement']) && isset($update['idElement']['_id']))
                $update['idElement'] = $update['idElement']['_id'];
        }

        if(isset($update['idRefRight']))
        {
            if($update['idRefRight'] instanceof RefRight)
                $update['idRefRight'] = new MongoId($update['idRefRight']->getId());
            else if(is_array($update['idRefRight']) && isset($update['idRefRight']['_id']))
                $update['idRefRight'] = $update['idRefRight']['_id'];
        }

        $result = parent::__update('right', $criteria, $update, $options);

        return $result;
    }

    /**
     * - Supprime un/des droit(s) correspondant à des critères données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|Right $criteria ce qu'il faut supprimer
     * @param array $options
     * @since 11/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */
    function remove($criteria, $options = array('w' => 1))
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof Right)
            $criteria = $this->dismount($criteria);

        if(isset($criteria['idUser']))
        {
            if($criteria['idUser'] instanceof User)
                $criteria['idUser'] = new MongoId($criteria['idUser']->getId());
            else if(is_array($criteria['idUser']) && isset($criteria['idUser']['_id']))
                $criteria['idUser'] = $criteria['idUser']['_id'];
        }

        if(isset($criteria['idElement']))
        {
            if($criteria['idElement'] instanceof Element)
                $criteria['idElement'] = new MongoId($criteria['idElement']->getId());
            else if(is_array($criteria['idElement']) && isset($criteria['idElement']['_id']))
                $criteria['idElement'] = $criteria['idElement']['_id'];
        }

        if(isset($criteria['idRefRight']))
        {
            if($criteria['idRefRight'] instanceof RefRight)
                $criteria['idRefRight'] = new MongoId($criteria['idRefRight']->getId());
            else if(is_array($criteria['idRefRight']) && isset($criteria['idRefRight']['_id']))
                $criteria['idRefRight'] = $criteria['idRefRight']['_id'];
        }

        $result = parent::__remove('right', $criteria, $options);

        return $result;
    }

    /**
     * Indique si l'utilisateur donné a les droits voulus sur l'élément donné
     * @author Alban Truc
     * @param MongoId|string $idUser
     * @param MongoId|string $idElement
     * @param string $refRightCode
     * @since 15/05/2014
     * @return bool
     */

    public function hasRightOnElement($idUser, $idElement, $refRightCode)
    {
        //récupérer l'id du refRight à partir du code
        $refRightPdoManager = new RefRightPdoManager();

        $refRightCriteria = array(
            'state' => (int)1,
            'code' => (string)$refRightCode
        );

        $refRight = $refRightPdoManager->findOne($refRightCriteria);

        //récupérer le droit
        $rightCriteria = array(
            'state' => (int)1,
            'idUser' => new MongoId($idUser),
            'idElement' => new MongoId($idElement),
            'idRefRigt' => $refRight->getId()
        );

        $right = self::find($rightCriteria);

        if(!(array_key_exists('error', $right)))
            return TRUE;
        else return FALSE;
    }
}