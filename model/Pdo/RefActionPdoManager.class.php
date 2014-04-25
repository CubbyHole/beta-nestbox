<?php
/**
 * Created by PhpStorm.
 * User: Crocell
 * Date: 15/04/14
 * Time: 13:14
 */

/** @var string $projectRoot chemin du projet dans le système de fichier */
$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Cubbyhole';

require_once 'AbstractPdoManager.class.php';
require_once $projectRoot.'/model/Classes/RefAction.class.php';
require_once $projectRoot.'/model/Interfaces/RefActionManager.interface.php';

/**
 * Class RefActionPdoManager
 * @author Alban Truc
 */
class RefActionPdoManager extends AbstractPdoManager implements RefActionManagerInterface{

    /** @var MongoCollection $refActionCollection collection refAction */
    protected $refActionCollection;

    /**
     * Constructeur:
     * - Appelle le constructeur de {@see AbstractPdoManager::__construct} (gestion des accès de la BDD).
     * - Initialise la collection refaction.
     * @author Alban Truc
     * @since 01/2014
     */

    public function __construct()
    {
        parent::__construct();
        $this->refActionCollection = $this->getCollection('refaction');
    }

    /**
     * Retrouver un refAction selon des critères donnés
     * @author Alban Truc
     * @param array|RefAction $criteria critères de recherche
     * @param array $fieldsToReturn champs à récupérer
     * @since 29/03/2014
     * @return array|RefAction[]
     */

    public function find($criteria, $fieldsToReturn = array())
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof RefAction)
            $criteria = $this->dismount($criteria);

        $cursor = parent::__find('refaction', $criteria, $fieldsToReturn);

        if(!(is_array($cursor)) && !(array_key_exists('error', $cursor)))
        {
            $refActions = array();

            foreach($cursor as $refAction)
            {
                /**
                 * D'après les commentaires sur la page {@link https://php.net/manual/en/function.array-push.php}
                 * la méthode utilisée ici est plus rapide d'exécution qu'utiliser un array_push.
                 * Par ailleurs nous n'avons pas besoin de la valeur que retourne array_push
                 * (à savoir le nombre d'éléments dans le tableau)
                 */
                if(empty($fieldsToReturn))
                    $refAction = new RefAction($refAction);

                $refActions[] = $refAction;
            }

            if(empty($refActions))
                return array('error' => 'No match found.');
            else
                return $refActions;
        }
        else return $cursor; //message d'erreur
    }

    /**
     * Retourne le premier refAction correspondant au(x) critère(s) donné(s)
     * @author Alban Truc
     * @param array|RefAction $criteria critère(s) de recherche
     * @param array $fieldsToReturn champs à retourner
     * @since 29/03/2014
     * @return array|RefAction
     */

    public function findOne($criteria, $fieldsToReturn = array())
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof RefAction)
            $criteria = $this->dismount($criteria);

        $result = parent::__findOne('refaction', $criteria, $fieldsToReturn);

        if(!(is_array($result)) && !(array_key_exists('error', $result)))
        {
            if(empty($fieldsToReturn))
                $result = new RefAction($result);
        }

        return $result;
    }

    /**
     * - Retrouver un refAction par son ID.
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param string|MongoId $id Identifiant unique du refAction à trouver
     * @param array $fieldsToReturn champs à retourner
     * @since 02/2014
     * @return RefAction|array contenant le message d'erreur
     */

    public function findById($id, $fieldsToReturn = array())
    {
        $result = parent::__findOne('refaction', array('_id' => new MongoId($id)));

        if(!(array_key_exists('error', $result)))
        {
            if(empty($fieldsToReturn))
                $result = new RefAction($result);
        }

        return $result;
    }

    /**
     * - Retrouver l'ensemble des refAction
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array $fieldsToReturn champs à retourner
     * @since 11/03/2014
     * @return array|RefAction[] tableau d'objets RefAction
     */

    public function findAll($fieldsToReturn = array())
    {
        $cursor = parent::__find('refaction', array());

        if(!(is_array($cursor)) && !(array_key_exists('error', $cursor)))
        {
            $refActions = array();

            foreach($cursor as $refAction)
            {
                if(empty($fieldsToReturn))
                    $refAction = new RefAction($refAction);

                $refActions[] = $refAction;
            }
        }

        if(empty($refActions))
            return array('error' => 'No refAction found.');
        else
            return $refActions;
    }

    /**
     * - Retrouver un refAction selon certains critères et le modifier/supprimer
     * - Récupérer ce refAction ou sa version modifiée
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|RefAction $searchQuery critères de recherche
     * @param array|RefAction $updateCriteria les modifications à réaliser
     * @param array|NULL $fieldsToReturn pour ne récupérer que certains champs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return array|RefAction
     */

    public function findAndModify($searchQuery, $updateCriteria, $fieldsToReturn = NULL, $options = NULL)
    {
        //Transforme $searchQuery en array s'il contient un objet
        if($searchQuery instanceof RefAction)
            $searchQuery = $this->dismount($searchQuery);

        //Transforme $updateCriteria en array s'il contient un objet
        if($updateCriteria instanceof RefAction)
            $updateCriteria = $this->dismount($updateCriteria);

        $result = parent::__findAndModify('refaction', $searchQuery, $updateCriteria, $fieldsToReturn, $options);

        if($fieldsToReturn === NULL)
            $result = new RefAction($result);

        return $result;
    }

    /**
     * Fonction d'update utilisant celle de {@see AbstractPdoManager}
     * @author Alban Truc
     * @param array|RefAction $criteria description des entrées à modifier
     * @param array|RefAction $update nouvelles valeurs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    public function update($criteria, $update, $options = array('w' => 1))
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof RefAction)
            $criteria = $this->dismount($criteria);

        //Transforme $update en array s'il contient un objet
        if($update instanceof RefAction)
            $update = $this->dismount($update);

        $result = parent::__update('refaction', $criteria, $update, $options);

        return $result;
    }

    /**
     * - Supprime un/des plan(s) correspondant à des critères données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|RefAction $criteria ce qu'il faut supprimer
     * @param array $options
     * @since 11/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    public function remove($criteria, $options = array('w' => 1))
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof RefAction)
            $criteria = $this->dismount($criteria);

        $result = parent::__remove('refaction', $criteria, $options);

        return $result;
    }

    /**
     * - Ajoute un refAction en base de données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|RefAction $document
     * @param array $options
     * @since 12/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    public function create($document, $options = array('w' => 1))
    {
        //Transforme $document en array s'il contient un objet
        if($document instanceof RefAction)
            $document = $this->dismount($document);

        $result = parent::__create('refaction', $document, $options);

        return $result;
    }
}

?>