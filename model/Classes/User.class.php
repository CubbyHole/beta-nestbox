<?php
/**
 * Created by Notepad++.
 * User: Alban Truc
 * Date: 31/01/14
 * Time: 12:52
 */

/**
 * Class User
 * @author Alban Truc
 */
class User
{
	/** @var string|MongoId $_id identifiant unique de l'utilisateur */
	private $_id;

    /** @var int $state 0 = Inscription non validée OU utilisateur non activé,
     * 1 = Inscription validée ET utilisateur activé
     */
	private $state;

    /** @var bool $isAdmin FALSE = utilisateur classique TRUE = administrateur */
	private $isAdmin;

    /** @var Account|string|MongoId $idCurrentAccount compte actuel de l'utilisateur */
	private $idCurrentAccount;

    /** @var string $lastName nom de l'utilisateur */
	private $lastName;

    /** @var string $firstName prénom de l'utilisateur */
	private $firstName;

    /** @var string $password mot de passe chiffré (sha1 d'un md5) de l'utilisateur */
	private $password;

    /** @var string $email adresse e-mail de l'utilisateur */
	private $email;

    /** @var string $geolocation localisation de l'utilisateur à son inscription */
	private $geolocation;

    /** @var string $apiKey string de connexion de l'utilisateur pour l'API */
	private $apiKey;

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
				$this->state =  (array_key_exists('state', $array)) ? (int)$array['state'] : NULL;
				$this->isAdmin = (array_key_exists('isAdmin', $array)) ? (bool)$array['isAdmin'] : NULL;
				$this->idCurrentAccount = (array_key_exists('idCurrentAccount', $array)) ? $array['idCurrentAccount'] : NULL;
                $this->lastName = (array_key_exists('lastName', $array)) ? (string)$array['lastName'] : NULL;
				$this->firstName = (array_key_exists('firstName', $array)) ? (string)$array['firstName'] : NULL;
				$this->password = (array_key_exists('password', $array)) ? (string)$array['password'] : NULL;
				$this->email = (array_key_exists('email', $array)) ? (string)$array['email'] : NULL;
				$this->geolocation = (array_key_exists('geolocation', $array)) ? (string)$array['geolocation'] : NULL;
				$this->apiKey = (array_key_exists('apiKey', $array)) ? (string)$array['apiKey'] : NULL;
				break;
			case 9: //toutes les propriétés sont passées dans la fonction, non sous la forme d'un tableau
				$this->state = (int)func_get_arg(0);
				$this->isAdmin = (bool)func_get_arg(1);
				$this->idCurrentAccount = func_get_arg(2);
				$this->lastName = (string)func_get_arg(3);
				$this->firstName = (string)func_get_arg(4);
				$this->password = (string)func_get_arg(5);
				$this->email = (string)func_get_arg(6);
				$this->geolocation = (string)func_get_arg(7);
				$this->apiKey = (string)func_get_arg(8);
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
     * @param boolean $isAdmin
     */
    public function setIsAdmin($isAdmin)
    {
        $this->isAdmin = (bool)$isAdmin;
    }

    /**
     * @return boolean
     */
    public function getIsAdmin()
    {
        return (boolean)$this->isAdmin;
    }

    /**
     * @param Account|MongoId|string $account
     */
    public function setCurrentAccount($account)
    {
        $this->idCurrentAccount = $account;
    }

    /**
     * @return Account|MongoId|string
     */
    public function getCurrentAccount()
    {
        return $this->idCurrentAccount;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = (string)$lastName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return (string)$this->lastName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = (string)$firstName;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return (string)$this->firstName;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = (string)$password;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return (string)$this->password;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = (string)$email;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return (string)$this->email;
    }

    /**
     * @param string $geolocation
     */
    public function setGeolocation($geolocation)
    {
        $this->geolocation = (string)$geolocation;
    }

    /**
     * @return string
     */
    public function getGeolocation()
    {
        return (string)$this->geolocation;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = (string)$apiKey;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return (string)$this->apiKey;
    }
}

?>