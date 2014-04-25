<?php
/**
 * Created by PhpStorm.
 * User: Alban Truc
 * Date: 17/03/14
 * Time: 14:29
 */

/**
 * Class Transaction
 * @author Alban Truc
 */
class Transaction
{
    /** @var  string|MongoId $_id identifiant unique de la transaction */
    private $_id;

    /** @var  string $calledFrom source de la transaction (API ou web).
     * Pourrait être remplacé par l'appareil de l'utilisateur dans une nouvelle version.
     */
    private $calledFrom;

    /** @var  User|string|MongoId $idEmitter celui qui émet la transaction */
    private $idEmitter;

    /** @var  User|string|MongoId|NULL $idReceiver celui qui reçoit la transaction.
     * Sera null dans le cas d'ajout/modification/suppression d'un document
     */
    //private $idReceiver;

    /** @var  RefAction|string|MongoId|NULL $idRefAction type de transaction */
    private $idRefAction;

    /** @var  Object|string|MongoId|NULL $idDocument id du document touché par la transaction */
    private $idDocument;

    /** @var  string $collectionName nom de la collection affectée par la transaction */
    private $collectionName;

    /** @var  MongoDate $date moment d'exécution de la transaction */
    private $date;

    /** @var  array $serverResponse réponse du serveur */
    private $serverResponse;

    /*
	 * - Récupère le nombre d'arguments de la fonction {@link https://php.net/manual/en/function.func-num-args.php}
     * - Associe chaque propriété de la classe avec le bon argument {@link https://php.net/manual/en/function.func-get-arg.php}
     * @author Alban Truc
     * @since 17/03/2014
	 */
    function __construct()
    {
        $numberOfArgs = func_num_args();

        switch($numberOfArgs)
        {
            case 1: //construit l'objet à partir d'un tableau, issu par exemple d'une requête en base
                $array = func_get_arg(0);
                $this->_id = (array_key_exists('_id', $array)) ? $array['_id'] : NULL;
                $this->calledFrom = (array_key_exists('calledFrom', $array)) ? (string)$array['calledFrom'] : NULL;
                $this->idEmitter = (array_key_exists('idEmitter', $array)) ? $array['idEmitter'] : NULL;
                //$this->idReceiver = (array_key_exists('idReceiver', $array)) ? $array['idReceiver'] : NULL;
                $this->idRefAction = (array_key_exists('idRefAction', $array)) ? $array['idRefAction'] : NULL;
                $this->idDocument = (array_key_exists('idDocument', $array)) ? $array['idDocument'] : NULL;
                $this->collectionName = (array_key_exists('collectionName', $array)) ? (string)$array['collectionName'] : NULL;
                $this->date = (array_key_exists('date', $array)) ? $array['date'] : NULL;
                $this->serverResponse = (array_key_exists('serverResponse', $array)) ? $array['serverResponse'] : NULL;
                break;
            case 7: //toutes les propriétés sont passées dans la fonction, non sous la forme d'un tableau
                $this->calledFrom = (string)func_get_arg(0);
                $this->idEmitter = func_get_arg(1);
                //$this->idReceiver = func_get_arg(2);
                $this->idRefAction = func_get_arg(2);
                $this->idDocument = func_get_arg(3);
                $this->collectionName = func_get_arg(4);
                $this->date = func_get_arg(5);
                $this->serverResponse = func_get_arg(6);
                break;
        }
    }

    /**
     * @param string|MongoId $id
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     * @return string|MongoId
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param string $calledFrom
     */
    public function setCalledFrom($calledFrom)
    {
        $this->calledFrom = (string)$calledFrom;
    }

    /**
     * @return string
     */
    public function getCalledFrom()
    {
        return (string)$this->calledFrom;
    }

    /**
     * @param User|string|MongoId $emitter
     */
    public function setEmitter($emitter)
    {
        $this->idEmitter = $emitter;
    }

    /**
     * @return User|string|MongoId
     */
    public function getEmitter()
    {
        return $this->idEmitter;
    }

    /**
     * @param User|string|MongoId|NULL $receiver
     */
    /*
    public function setReceiver($receiver)
    {
        $this->idReceiver = $receiver;
    }*/

    /**
     * @return User|string|MongoId|NULL
     */
    /*
    public function getReceiver()
    {
        return $this->idReceiver;
    }*/

    /**
     * @param RefAction|string|MongoId|NULL $refAction
     */
    public function setRefAction($refAction)
    {
        $this->idRefAction = $refAction;
    }

    /**
     * @return RefAction|string|MongoId|NULL
     */
    public function getRefAction()
    {
        return $this->idRefAction;
    }

    /**
     * @param Object|string|MongoId|NULL $document
     */
    public function setDocument($document)
    {
        $this->idDocument = $document;
    }

    /**
     * @return Object|string|MongoId|NULL
     */
    public function getDocument()
    {
        return $this->idDocument;
    }

    /**
     * @param string $collectionName
     */
    public function setCollectionName($collectionName)
    {
        $this->collectionName = (string)$collectionName;
    }

    /**
     * @return string
     */
    public function getCollectionName()
    {
        return (string)$this->collectionName;
    }

    /**
     * @param MongoDate $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return MongoDate
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param array $serverResponse
     */
    public function setServerResponse($serverResponse)
    {
        $this->serverResponse = $serverResponse;
    }

    /**
     * @return array
     */
    public function getServerResponse()
    {
        return $this->serverResponse;
    }
} 