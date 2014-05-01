<?php
/**
 * Created by PhpStorm.
 * User: Crocell
 * Date: 25/04/14
 * Time: 15:02
 */

/** @var string $projectRoot chemin du projet dans le système de fichier */
$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Cubbyhole';

require_once $projectRoot.'/required.php';

/**
 * Class RefElementPdoManager
 * @author Alban Truc
 */
class RefElementPdoManager extends AbstractPdoManager implements RefElementManagerInterface{

    /** @var MongoCollection $refElementCollection collection refElement */
    protected $refElementCollection;

    /**
     * Constructeur:
     * - Appelle le constructeur de {@see AbstractPdoManager::__construct} (gestion des accès de la BDD).
     * - Initialise la collection refElement.
     * @author Alban Truc
     * @since 01/2014
     */

    public function __construct()
    {
        parent::__construct();
        $this->refElementCollection = $this->getCollection('refelement');
    }

    /**
     * Retrouver un refElement selon des critères donnés
     * @author Alban Truc
     * @param array|RefElement $criteria critères de recherche
     * @param array $fieldsToReturn champs à récupérer
     * @since 29/03/2014
     * @return array|RefElement[]
     */
    function find($criteria, $fieldsToReturn = array())
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof RefElement)
            $criteria = $this->dismount($criteria);

        $cursor = parent::__find('refelement', $criteria, $fieldsToReturn);

        if(!(is_array($cursor)) && !(array_key_exists('error', $cursor)))
        {
            $refElements = array();

            foreach($cursor as $refElement)
            {
                if(empty($fieldsToReturn))
                    $refElement = new RefElement($refElement);

                $refElements[] = $refElement;
            }

            if(empty($refElements))
                return array('error' => 'No match found.');
            else
                return $refElements;
        }
        else return $cursor; //message d'erreur
    }

    /**
     * Retourne le premier refElement correspondant au(x) critère(s) donné(s)
     * @author Alban Truc
     * @param array|RefElement $criteria critère(s) de recherche
     * @param array $fieldsToReturn champs à retourner
     * @since 29/03/2014
     * @return array|RefElement
     */
    function findOne($criteria, $fieldsToReturn = array())
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof RefElement)
            $criteria = $this->dismount($criteria);

        $result = parent::__findOne('refelement', $criteria, $fieldsToReturn);

        if(!(is_array($result)) && !(array_key_exists('error', $result)))
        {
            if(empty($fieldsToReturn))
                $result = new RefElement($result);
        }

        return $result;
    }

    /**
     * - Retrouver un refElement par son ID.
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param string|MongoId $id Identifiant unique de l'refElement à trouver
     * @param array $fieldsToReturn champs à retourner
     * @since 02/2014
     * @return RefElement|array contenant le message d'erreur
     */
    function findById($id, $fieldsToReturn = array())
    {
        $result = parent::__findOne('refelement', array('_id' => new MongoId($id)));

        //Si un compte est trouvé
        if (!(array_key_exists('error', $result)))
        {
            if(empty($fieldsToReturn))
                $result = new RefElement($result);
        }

        return $result;
    }

    /**
     * - Retrouver l'ensemble des refElements
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array $fieldsToReturn champs à retourner
     * @since 11/03/2014
     * @return array|RefElement[] tableau d'objets RefElement
     */
    function findAll($fieldsToReturn = array())
    {
        $cursor = parent::__find('refelement', array());

        if(!(is_array($cursor)) && !(array_key_exists('error', $cursor)))
        {
            $refElements = array();

            foreach($cursor as $refElement)
            {
                if(empty($fieldsToReturn))
                    $refElement = new RefElement($refElement);

                $refElements[] = $refElement;
            }
        }

        if(empty($refElements))
            return array('error' => 'No refElement found.');
        else
            return $refElements;
    }

    /**
     * - Retrouver un refElement selon certains critères et le modifier/supprimer
     * - Récupérer cet refElement ou sa version modifiée
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|RefElement $searchQuery critères de recherche
     * @param array|RefElement $updateCriteria les modifications à réaliser
     * @param array|NULL $fieldsToReturn pour ne récupérer que certains champs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return array|RefElement
     */
    function findAndModify($searchQuery, $updateCriteria, $fieldsToReturn = NULL, $options = NULL)
    {
        //Transforme $criteria en array s'il contient un objet
        if($searchQuery instanceof RefElement)
            $searchQuery = $this->dismount($searchQuery);

        //Transforme $criteria en array s'il contient un objet
        if($updateCriteria instanceof RefElement)
            $updateCriteria = $this->dismount($updateCriteria);

        $result = parent::__findAndModify('refelement', $searchQuery, $updateCriteria, $fieldsToReturn, $options);

        if($fieldsToReturn === NULL)
            $result = new RefElement($result);

        return $result;
    }

    /**
     * - Ajoute un refElement en base de données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|RefElement $document
     * @param array $options
     * @since 12/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */
    function create($document, $options = array('w' => 1))
    {
        //Transforme $criteria en array s'il contient un objet
        if($document instanceof RefElement)
            $document = $this->dismount($document);

        $result = parent::__create('refelement', $document, $options);

        return $result;
    }

    /**
     * Fonction d'update utilisant celle de {@see AbstractPdoManager}
     * @author Alban Truc
     * @param array|RefElement $criteria description des entrées à modifier
     * @param array|RefElement $update nouvelles valeurs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */
    function update($criteria, $update, $options = array('w' => 1))
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof RefElement)
            $criteria = $this->dismount($criteria);

        $result = parent::__update('refelement', $criteria, $update, $options);

        return $result;
    }

    /**
     * - Supprime un/des refElement(s) correspondant à des critères données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|RefElement $criteria ce qu'il faut supprimer
     * @param array $options
     * @since 11/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */
    function remove($criteria, $options = array('w' => 1))
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof RefElement)
            $criteria = $this->dismount($criteria);

        $result = parent::__remove('refelement', $criteria, $options);

        return $result;
    }
}