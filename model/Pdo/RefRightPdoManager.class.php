<?php
/**
 * Created by PhpStorm.
 * User: Crocell
 * Date: 25/04/14
 * Time: 15:18
 */

/** @var string $projectRoot chemin du projet dans le système de fichier */
$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Cubbyhole';

require_once $projectRoot.'/required.php';

/**
 * Class RefRightPdoManager
 * @author Alban Truc
 */
class RefRightPdoManager extends AbstractPdoManager implements RefRightManagerInterface{

    /** @var MongoCollection $refRightCollection collection refRight */
    protected $refRightCollection;

    /**
     * Constructeur:
     * - Appelle le constructeur de {@see AbstractPdoManager::__construct} (gestion des accès de la BDD).
     * - Initialise la collection refRight.
     * @author Alban Truc
     * @since 01/2014
     */

    public function __construct()
    {
        parent::__construct();
        $this->refRightCollection = $this->getCollection('refright');
    }

    /**
     * Retrouver un refRight selon des critères donnés
     * @author Alban Truc
     * @param array|RefRight $criteria critères de recherche
     * @param array $fieldsToReturn champs à récupérer
     * @since 29/03/2014
     * @return array|RefRight[]
     */
    function find($criteria, $fieldsToReturn = array())
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof RefRight)
            $criteria = $this->dismount($criteria);

        $cursor = parent::__find('refright', $criteria, $fieldsToReturn);

        if(!(is_array($cursor)) && !(array_key_exists('error', $cursor)))
        {
            $refRights = array();

            foreach($cursor as $refRight)
            {
                if(empty($fieldsToReturn))
                    $refRight = new RefRight($refRight);

                $refRights[] = $refRight;
            }

            if(empty($refRights))
                return array('error' => 'No match found.');
            else
                return $refRights;
        }
        else return $cursor; //message d'erreur
    }

    /**
     * Retourne le premier refRight correspondant au(x) critère(s) donné(s)
     * @author Alban Truc
     * @param array|RefRight $criteria critère(s) de recherche
     * @param array $fieldsToReturn champs à retourner
     * @since 29/03/2014
     * @return array|RefRight
     */
    function findOne($criteria, $fieldsToReturn = array())
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof RefRight)
            $criteria = $this->dismount($criteria);

        $result = parent::__findOne('refright', $criteria, $fieldsToReturn);

        if(!(is_array($result)) && !(array_key_exists('error', $result)))
        {
            if(empty($fieldsToReturn))
                $result = new RefRight($result);
        }

        return $result;
    }

    /**
     * - Retrouver un refRight par son ID.
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param string|MongoId $id Identifiant unique de l'refRight à trouver
     * @param array $fieldsToReturn champs à retourner
     * @since 02/2014
     * @return RefRight|array contenant le message d'erreur
     */
    function findById($id, $fieldsToReturn = array())
    {
        $result = parent::__findOne('refright', array('_id' => new MongoId($id)));

        //Si un compte est trouvé
        if (!(array_key_exists('error', $result)))
        {
            if(empty($fieldsToReturn))
                $result = new RefRight($result);
        }

        return $result;
    }

    /**
     * - Retrouver l'ensemble des refRights
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array $fieldsToReturn champs à retourner
     * @since 11/03/2014
     * @return array|RefRight[] tableau d'objets RefRight
     */
    function findAll($fieldsToReturn = array())
    {
        $cursor = parent::__find('refright', array());

        if(!(is_array($cursor)) && !(array_key_exists('error', $cursor)))
        {
            $refRights = array();

            foreach($cursor as $refRight)
            {
                if(empty($fieldsToReturn))
                    $refRight = new RefRight($refRight);

                $refRights[] = $refRight;
            }
        }

        if(empty($refRights))
            return array('error' => 'No refRight found.');
        else
            return $refRights;
    }

    /**
     * - Retrouver un refRight selon certains critères et le modifier/supprimer
     * - Récupérer cet refRight ou sa version modifiée
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|RefRight $searchQuery critères de recherche
     * @param array|RefRight $updateCriteria les modifications à réaliser
     * @param array|NULL $fieldsToReturn pour ne récupérer que certains champs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return array|RefRight
     */
    function findAndModify($searchQuery, $updateCriteria, $fieldsToReturn = NULL, $options = NULL)
    {
        //Transforme $criteria en array s'il contient un objet
        if($searchQuery instanceof RefRight)
            $searchQuery = $this->dismount($searchQuery);

        //Transforme $criteria en array s'il contient un objet
        if($updateCriteria instanceof RefRight)
            $updateCriteria = $this->dismount($updateCriteria);

        $result = parent::__findAndModify('refright', $searchQuery, $updateCriteria, $fieldsToReturn, $options);

        if($fieldsToReturn === NULL)
            $result = new RefRight($result);

        return $result;
    }

    /**
     * - Ajoute un refRight en base de données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|RefRight $document
     * @param array $options
     * @since 12/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */
    function create($document, $options = array('w' => 1))
    {
        //Transforme $criteria en array s'il contient un objet
        if($document instanceof RefRight)
            $document = $this->dismount($document);

        $result = parent::__create('refright', $document, $options);

        return $result;
    }

    /**
     * Fonction d'update utilisant celle de {@see AbstractPdoManager}
     * @author Alban Truc
     * @param array|RefRight $criteria description des entrées à modifier
     * @param array|RefRight $update nouvelles valeurs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */
    function update($criteria, $update, $options = array('w' => 1))
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof RefRight)
            $criteria = $this->dismount($criteria);

        $result = parent::__update('refright', $criteria, $update, $options);

        return $result;
    }

    /**
     * - Supprime un/des refRight(s) correspondant à des critères données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|RefRight $criteria ce qu'il faut supprimer
     * @param array $options
     * @since 11/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */
    function remove($criteria, $options = array('w' => 1))
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof RefRight)
            $criteria = $this->dismount($criteria);

        $result = parent::__remove('refright', $criteria, $options);

        return $result;
    }
}