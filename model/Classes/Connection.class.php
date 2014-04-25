<?php
/**
 * Created by PhpStorm.
 * User: Crocell
 * Date: 25/04/14
 * Time: 10:24
 */

/**
 * Class Connection
 * @author Alban Truc
 */
class Connection
{
    /** @var  string|MongoId $_id identifiant unique de la connexion */
    private $_id;

    /** @var  User|string|MongoId|NULL $idUser utilisateur s'étant connecté. NULL si anonyme */
    private $idUser;

    /** @var  string $IPaddress adresse IP publique de la personne connectée */
    private $IPaddress;

    /** @var  bool $isDownloading TRUE = l'utilisateur est en train de télécharger.
     * Cette donnée nous permet de gérer les téléchargements simultannés.
     */
    private $isDownloading;

    /** @var MongoDate $startDate début de la connexion */
    private $startDate;

    /** @var MongoDate $endDate fin de la connexion */
    private $endDate;

    /*
	 * - Récupère le nombre d'arguments de la fonction {@link https://php.net/manual/en/function.func-num-args.php}
     * - Associe chaque propriété de la classe avec le bon argument {@link https://php.net/manual/en/function.func-get-arg.php}
     * @author Alban Truc
     * @since 25/04/2014
	 */
    public function __construct()
    {
        $numberOfArgs = func_num_args();

        switch($numberOfArgs)
        {
            case 1: //construit l'objet à partir d'un tableau, issu par exemple d'une requête en base
                $array = func_get_arg(0);
                $this->_id = (array_key_exists('_id', $array)) ? $array['_id'] : NULL;
                $this->idUser = (array_key_exists('idUser', $array)) ? $array['idUser'] : NULL;
                $this->IPaddress = (array_key_exists('IPaddress', $array)) ? (string)$array['IPaddress'] : NULL;
                $this->isDownloading = (array_key_exists('isDownloading', $array)) ? (bool)$array['isDownloading'] : NULL;
                $this->startDate = (array_key_exists('startDate', $array)) ? $array['startDate'] : NULL;
                $this->endDate = (array_key_exists('endDate', $array)) ? $array['endDate'] : NULL;
                break;
            case 5: //toutes les propriétés sont passées dans la fonction, non sous la forme d'un tableau
                $this->idUser = (int)func_get_arg(0);
                $this->IPaddress = func_get_arg(1);
                $this->isDownloading = func_get_arg(2);
                $this->startDate = (int)func_get_arg(3);
                $this->endDate = (int)func_get_arg(4);
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
     * @param MongoId|User|string|NULL $user
     */
    public function setUser($user)
    {
        $this->idUser = $user;
    }

    /**
     * @return MongoId|User|string|NULL
     */
    public function getUser()
    {
        return $this->idUser;
    }

    /**
     * @param string $IPaddress
     */
    public function setIPaddress($IPaddress)
    {
        $this->IPaddress = (string)$IPaddress;
    }

    /**
     * @return string
     */
    public function getIPaddress()
    {
        return (string)$this->IPaddress;
    }

    /**
     * @param bool $isDownloading
     */
    public function setIsDownloading($isDownloading)
    {
        $this->$isDownloading = (bool)$isDownloading;
    }

    /**
     * @return bool
     */
    public function getIsDownloading()
    {
        return (bool)$this->$isDownloading;
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