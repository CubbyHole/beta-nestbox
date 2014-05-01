<?php
/**
 * Created by PhpStorm.
 * User: Crocell
 * Date: 25/04/14
 * Time: 14:39
 */

/** @var string $projectRoot chemin du projet dans le système de fichier */
$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';

require_once $projectRoot.'/required.php';

/**
 * Class ElementPdoManager
 * @author Alban Truc
 */
class ElementPdoManager extends AbstractPdoManager implements ElementManagerInterface{

    /** @var MongoCollection $elementCollection collection element */
    protected $elementCollection;

    /** @var  UserPdoManager $userPdoManager instance de cette classe */
    protected $userPdoManager;

    /** @var  RefElementPdoManager $refElementPdoManager instance de cette classe */
    protected $refElementPdoManager;

    /**
     * Constructeur:
     * - Appelle le constructeur de {@see AbstractPdoManager::__construct} (gestion des accès de la BDD).
     * - Initialise la collection element.
     * @author Alban Truc
     * @since 01/2014
     */

    public function __construct()
    {
        parent::__construct();
        $this->elementCollection = $this->getCollection('element');

        $this->userPdoManager = new UserPdoManager();
        $this->refElementPdoManager = new RefElementPdoManager();
    }

    /**
     * Retrouver un élément selon des critères donnés
     * @author Alban Truc
     * @param array|Element $criteria critères de recherche
     * @param array $fieldsToReturn champs à récupérer
     * @since 29/03/2014
     * @return array|Element[]
     */
    function find($criteria, $fieldsToReturn = array())
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof Element)
            $criteria = $this->dismount($criteria);

        if(isset($criteria['idOwner']))
        {
            if($criteria['idOwner'] instanceof User)
                $criteria['idOwner'] = new MongoId($criteria['idOwner']->getId());
            else if(is_array($criteria['idOwner']) && isset($criteria['idOwner']['_id']))
                $criteria['idOwner'] = $criteria['idOwner']['_id'];
        }

        if(isset($criteria['idRefElement']))
        {
            if($criteria['idRefElement'] instanceof RefElement)
                $criteria['idRefElement'] = new MongoId($criteria['idRefElement']->getId());
            else if(is_array($criteria['idRefElement']) && isset($criteria['idRefElement']['_id']))
                $criteria['idRefElement'] = $criteria['idRefElement']['_id'];
        }

        $cursor = parent::__find('element', $criteria, $fieldsToReturn);

        if(!(is_array($cursor)) && !(array_key_exists('error', $cursor)))
        {
            $elements = array();

            foreach($cursor as $element)
            {
                if(empty($fieldsToReturn))
                    $element = new Element($element);

                $elements[] = $element;
            }

            if(empty($elements))
                return array('error' => 'No match found.');
            else
                return $elements;
        }
        else return $cursor; //message d'erreur
    }

    /**
     * Retourne le premier élément correspondant au(x) critère(s) donné(s)
     * @author Alban Truc
     * @param array|Element $criteria critère(s) de recherche
     * @param array $fieldsToReturn champs à retourner
     * @since 29/03/2014
     * @return array|Element
     */
    function findOne($criteria, $fieldsToReturn = array())
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof Element)
            $criteria = $this->dismount($criteria);

        if(isset($criteria['idOwner']))
        {
            if($criteria['idOwner'] instanceof User)
                $criteria['idOwner'] = new MongoId($criteria['idOwner']->getId());
            else if(is_array($criteria['idOwner']) && isset($criteria['idOwner']['_id']))
                $criteria['idOwner'] = $criteria['idOwner']['_id'];
        }

        if(isset($criteria['idRefElement']))
        {
            if($criteria['idRefElement'] instanceof RefElement)
                $criteria['idRefElement'] = new MongoId($criteria['idRefElement']->getId());
            else if(is_array($criteria['idRefElement']) && isset($criteria['idRefElement']['_id']))
                $criteria['idRefElement'] = $criteria['idRefElement']['_id'];
        }

        $result = parent::__findOne('element', $criteria, $fieldsToReturn);

        if(is_array($result) && !(array_key_exists('error', $result)))
        {
            if(empty($fieldsToReturn))
                $result = new Element($result);
        }

        return $result;
    }

    /**
     * - Retrouver un élément par son ID.
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param string|MongoId $id Identifiant unique de l'élément à trouver
     * @param array $fieldsToReturn champs à retourner
     * @since 02/2014
     * @return Element|array contenant le message d'erreur
     */
    function findById($id, $fieldsToReturn = array())
    {
        $result = parent::__findOne('element', array('_id' => new MongoId($id)));

        //Si un compte est trouvé
        if (!(array_key_exists('error', $result)))
        {
            if(empty($fieldsToReturn))
                $result = new Element($result);
        }

        return $result;
    }

    /**
     * - Retrouver l'ensemble des éléments
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array $fieldsToReturn champs à retourner
     * @since 11/03/2014
     * @return array|Element[] tableau d'objets Element
     */
    function findAll($fieldsToReturn = array())
    {
        $cursor = parent::__find('element', array());

        if(!(is_array($cursor)) && !(array_key_exists('error', $cursor)))
        {
            $elements = array();

            foreach($cursor as $element)
            {
                if(empty($fieldsToReturn))
                    $element = new Element($element);

                $elements[] = $element;
            }
        }

        if(empty($elements))
            return array('error' => 'No element found.');
        else
            return $elements;
    }

    /**
     * - Retrouver un élément selon certains critères et le modifier/supprimer
     * - Récupérer cet élément ou sa version modifiée
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|Element $searchQuery critères de recherche
     * @param array|Element $updateCriteria les modifications à réaliser
     * @param array|NULL $fieldsToReturn pour ne récupérer que certains champs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return array|Element
     */
    function findAndModify($searchQuery, $updateCriteria, $fieldsToReturn = NULL, $options = NULL)
    {
        //Transforme $criteria en array s'il contient un objet
        if($searchQuery instanceof Element)
            $searchQuery = $this->dismount($searchQuery);

        //Transforme $criteria en array s'il contient un objet
        if($updateCriteria instanceof Element)
            $updateCriteria = $this->dismount($updateCriteria);

        if(isset($searchQuery['idOwner']))
        {
            if($searchQuery['idOwner'] instanceof User)
                $searchQuery['idOwner'] = new MongoId($searchQuery['idOwner']->getId());
            else if(is_array($searchQuery['idOwner']) && isset($searchQuery['idOwner']['_id']))
                $searchQuery['idOwner'] = $searchQuery['idOwner']['_id'];
        }

        if(isset($searchQuery['idRefElement']))
        {
            if($searchQuery['idRefElement'] instanceof RefElement)
                $searchQuery['idRefElement'] = new MongoId($searchQuery['idRefElement']->getId());
            else if(is_array($searchQuery['idRefElement']) && isset($searchQuery['idRefElement']['_id']))
                $searchQuery['idRefElement'] = $searchQuery['idRefElement']['_id'];
        }

        if(isset($updateCriteria['idOwner']))
        {
            if($updateCriteria['idOwner'] instanceof User)
                $updateCriteria['idOwner'] = new MongoId($updateCriteria['idOwner']->getId());
            else if(is_array($updateCriteria['idOwner']) && isset($updateCriteria['idOwner']['_id']))
                $updateCriteria['idOwner'] = $updateCriteria['idOwner']['_id'];
        }

        if(isset($updateCriteria['idRefElement']))
        {
            if($updateCriteria['idRefElement'] instanceof RefElement)
                $updateCriteria['idRefElement'] = new MongoId($updateCriteria['idRefElement']->getId());
            else if(is_array($updateCriteria['idRefElement']) && isset($updateCriteria['idRefElement']['_id']))
                $updateCriteria['idRefElement'] = $updateCriteria['idRefElement']['_id'];
        }

        $result = parent::__findAndModify('element', $searchQuery, $updateCriteria, $fieldsToReturn, $options);

        if($fieldsToReturn === NULL)
            $result = new Element($result);

        return $result;
    }

    /**
     * - Ajoute un élément en base de données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|Element $document
     * @param array $options
     * @since 12/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */
    function create($document, $options = array('w' => 1))
    {
        //Transforme $criteria en array s'il contient un objet
        if($document instanceof Element)
            $document = $this->dismount($document);

        if(isset($document['idOwner']))
        {
            if($document['idOwner'] instanceof User)
                $document['idOwner'] = new MongoId($document['idOwner']->getId());
            else if(is_array($document['idOwner']) && isset($document['idOwner']['_id']))
                $document['idOwner'] = $document['idOwner']['_id'];
        }

        if(isset($document['idRefElement']))
        {
            if($document['idRefElement'] instanceof RefElement)
                $document['idRefElement'] = new MongoId($document['idRefElement']->getId());
            else if(is_array($document['idRefElement']) && isset($document['idRefElement']['_id']))
                $document['idRefElement'] = $document['idRefElement']['_id'];
        }

        $result = parent::__create('element', $document, $options);

        return $result;
    }

    /**
     * Fonction d'update utilisant celle de {@see AbstractPdoManager}
     * @author Alban Truc
     * @param array|Element $criteria description des entrées à modifier
     * @param array|Element $update nouvelles valeurs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */
    function update($criteria, $update, $options = array('w' => 1))
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof Element)
            $criteria = $this->dismount($criteria);

        if(isset($criteria['idOwner']))
        {
            if($criteria['idOwner'] instanceof User)
                $criteria['idOwner'] = new MongoId($criteria['idOwner']->getId());
            else if(is_array($criteria['idOwner']) && isset($criteria['idOwner']['_id']))
                $criteria['idOwner'] = $criteria['idOwner']['_id'];
        }

        if(isset($criteria['idRefElement']))
        {
            if($criteria['idRefElement'] instanceof RefElement)
                $criteria['idRefElement'] = new MongoId($criteria['idRefElement']->getId());
            else if(is_array($criteria['idRefElement']) && isset($criteria['idRefElement']['_id']))
                $criteria['idRefElement'] = $criteria['idRefElement']['_id'];
        }

        if(isset($update['idOwner']))
        {
            if($update['idOwner'] instanceof User)
                $update['idOwner'] = new MongoId($update['idOwner']->getId());
            else if(is_array($update['idOwner']) && isset($update['idOwner']['_id']))
                $update['idOwner'] = $update['idOwner']['_id'];
        }

        if(isset($update['idRefElement']))
        {
            if($update['idRefElement'] instanceof RefElement)
                $update['idRefElement'] = new MongoId($update['idRefElement']->getId());
            else if(is_array($update['idRefElement']) && isset($update['idRefElement']['_id']))
                $update['idRefElement'] = $update['idRefElement']['_id'];
        }

        $result = parent::__update('element', $criteria, $update, $options);

        return $result;
    }

    /**
     * - Supprime un/des élément(s) correspondant à des critères données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|Element $criteria ce qu'il faut supprimer
     * @param array $options
     * @since 11/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */
    function remove($criteria, $options = array('w' => 1))
    {
        //Transforme $criteria en array s'il contient un objet
        if($criteria instanceof Element)
            $criteria = $this->dismount($criteria);

        if(isset($criteria['idOwner']))
        {
            if($criteria['idOwner'] instanceof User)
                $criteria['idOwner'] = new MongoId($criteria['idOwner']->getId());
            else if(is_array($criteria['idOwner']) && isset($criteria['idOwner']['_id']))
                $criteria['idOwner'] = $criteria['idOwner']['_id'];
        }

        if(isset($criteria['idRefElement']))
        {
            if($criteria['idRefElement'] instanceof RefElement)
                $criteria['idRefElement'] = new MongoId($criteria['idRefElement']->getId());
            else if(is_array($criteria['idRefElement']) && isset($criteria['idRefElement']['_id']))
                $criteria['idRefElement'] = $criteria['idRefElement']['_id'];
        }

        $result = parent::__remove('element', $criteria, $options);

        return $result;
    }

    /**
     * - Distingue deux cas: récupération des éléments d'un utilisateur et récupération des éléments partagés avec un utilisateur
     * - Dans le 1er cas (isOwner = 1), on retourne les infos de l'élément et du refElement
     * - Dans le second cas (isOwner = 0), on retourne le droit, le refRight, l'élément, le refElement et le propriétaire
     * - Gestion des erreurs
     * @author Alban Truc
     * @param string|MongoId $idUser
     * @param string $isOwner
     * @since 01/05/2014
     * @return Element[]
     */

    public function returnElementsDetails($idUser, $isOwner)
    {
        if($isOwner == '1')
        {
            $criteria = array(
                'state' => (int)1,
                'idOwner' => new MongoId($idUser)
            );

            $elements = self::find($criteria);

            if(is_array($elements) && !(array_key_exists('error', $elements)))
            {
                //récupération des refElement pour chaque élément
                foreach($elements as $key => $element)
                {
                    $refElement = $this->refElementPdoManager->findById($element->getRefElement());

                    if($refElement instanceof RefElement)
                    {
                        $element->setRefElement($refElement);
                        $elements[$key] = $element;
                    }
                    else unset($elements[$key]);
                }

                if(empty($elements))
                    return array('error' => 'No match found.');
            }

            return $elements;
        }
        else if($isOwner == '0')
        {
            return self::returnSharedElementsDetails($idUser);
        }
        else return array('error' => 'Parameter isOwner must be 0 or 1');
    }

    /**
     * Retourne le droit, le refRight, l'élément et le refElement
     * @author Alban Truc
     * @param string|MongoId $idUser
     * @since 01/05/2014
     * @return Right[]
     */

    public function returnSharedElementsDetails($idUser)
    {
        $criteria = array(
            'state' => (int)1,
            'idUser' => new MongoId($idUser)
        );

        //récupération des droits sur les éléments
        $rightPdoManager = new RightPdoManager();
        $rights = $rightPdoManager->find($criteria);

        $refRightPdoManager = new RefRightPdoManager();

        //pour chaque droit
        if(is_array($rights) && !(array_key_exists('error', $rights)))
        {
            foreach($rights as $key => $right)
            {
                $owner = NULL;
                $refRight = NULL;

                //Récupération de l'élément. On enlève le droit de la liste si l'élément n'est pas disponible
                $elementCriteria = array(
                    '_id' => new MongoId($right->getElement()),
                    'state' => (int)1
                );

                $element = self::findOne($elementCriteria);

                if($element instanceof Element)
                {
                    //récupération du refElement. On enlève le droit de la liste si le refElement n'est pas trouvé
                    $refElement = $this->refElementPdoManager->findById($element->getRefElement());

                    if($refElement instanceof RefElement)
                    {
                        $element->setRefElement($refElement);
                        $right->setElement($element);
                    }
                    else
                    {
                        unset($rights[$key]);
                        continue;
                    }
                }
                else
                {
                    unset($rights[$key]);
                    continue;
                }

                //Récupération du refRight. S'il n'existe pas on enlève ce droit de la liste.
                $refRight = $refRightPdoManager->findById($right->getRefRight());

                if($refRight instanceof RefRight)
                {
                    $right->setRefRight($refRight);
                }
                else
                {
                    unset($rights[$key]);
                    continue;
                }

                $rights[$key] = $right;
            }

            if(empty($rights))
                return array('error' => 'No match found.');
        }

        return $rights;
    }
}