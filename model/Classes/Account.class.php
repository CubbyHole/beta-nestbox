<?php
/**
 * Created by Notepad++.
 * User: Alban Truc
 * Date: 31/01/14
 * Time: 12:52
 */

/**
 * Class Account
 * @author Alban Truc
 */
class Account
{
	/** @var  string|MongoId $_id identifiant unique du compte */
    private $_id;

    /** @var int $state 0 = compte désactivé car il ne s'agit pas du compte actuel d'un utilisateur
     * 1 = compte activé, il s'agit du compte actuel d'un utilisateur
     */
    private $state;

    /** @var  User|string|MongoId $idUser utilisateur propriétaire du compte */
    private $idUser;

    /** @var RefPlan|string|MongoId $idRefPlan type de compte */
    private $idRefPlan;

    /** @var int $storage quantité de données stockées (en octets) */
    private $storage;

    /** @var int $ratio quantité d'octets téléchargés ce jour */
    private $ratio;

    /** @var MongoDate $startDate date de création du compte  */
    private $startDate;

    /** @var MongoDate $endDate date de fin du compte */
    private $endDate;

    /*
	 * - Récupère le nombre d'arguments de la fonction {@link https://php.net/manual/en/function.func-num-args.php}
     * - Associe chaque propriété de la classe avec le bon argument {@link https://php.net/manual/en/function.func-get-arg.php}
     * @author Alban Truc
     * @since 02/2014
	 */
    public function __construct()
    {
        $numberOfArgs = func_num_args();

        switch($numberOfArgs)
        {
            case 1: //construit l'objet à partir d'un tableau, issu par exemple d'une requête en base
                $array = func_get_arg(0);
                $this->_id = (array_key_exists('_id', $array)) ? $array['_id'] : NULL;
                $this->state = (array_key_exists('state', $array)) ? (int)$array['state'] : NULL;
                $this->idUser = (array_key_exists('idUser', $array)) ? $array['idUser'] : NULL;
                $this->idRefPlan = (array_key_exists('idRefPlan', $array)) ? $array['idRefPlan'] : NULL;
                $this->storage = (array_key_exists('storage', $array)) ? (int)$array['storage'] : NULL;
                $this->ratio = (array_key_exists('ratio', $array)) ? (int)$array['ratio'] : NULL;
                $this->startDate = (array_key_exists('startDate', $array)) ? $array['startDate'] : NULL;
                $this->endDate = (array_key_exists('endDate', $array)) ? $array['endDate'] : NULL;
                break;
            case 7: //toutes les propriétés sont passées dans la fonction, non sous la forme d'un tableau
                $this->state = (int)func_get_arg(0);
                $this->idUser = func_get_arg(1);
                $this->idRefPlan = func_get_arg(2);
                $this->storage = (int)func_get_arg(3);
                $this->ratio = (int)func_get_arg(4);
                $this->startDate = func_get_arg(5);
                $this->endDate = func_get_arg(6);
                break;
        }
    }

    /**
     * @param MongoId|string $id
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     * @return MongoId|string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param int $state
     */
    public function setState($state)
    {
        $this->state = (int)$state;
    }

    /**
     * @return int
     */
    public function getState()
    {
        return (int)$this->state;
    }

    /**
     * @param MongoId|User|string $user
     */
    public function setUser($user)
    {
        $this->idUser = $user;
    }

    /**
     * @return MongoId|User|string
     */
    public function getUser()
    {
        return $this->idUser;
    }

    /**
     * @param MongoId|RefPlan|string $refPlan
     */
    public function setRefPlan($refPlan)
    {
        $this->idRefPlan = $refPlan;
    }

    /**
     * @return MongoId|RefPlan|string
     */
    public function getRefPlan()
    {
        return $this->idRefPlan;
    }

    /**
     * @param int $storage
     */
    public function setStorage($storage)
    {
        $this->storage = (int)$storage;
    }

    /**
     * @return int
     */
    public function getStorage()
    {
        return (int)$this->storage;
    }

    /**
     * @param int $ratio
     */
    public function setRatio($ratio)
    {
        $this->ratio = (int)$ratio;
    }

    /**
     * @return int
     */
    public function getRatio()
    {
        return (int)$this->ratio;
    }

    /**
     * @param MongoDate $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return MongoDate
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param MongoDate $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return MongoDate
     */
    public function getEndDate()
    {
        return $this->endDate;
    }
}

?>