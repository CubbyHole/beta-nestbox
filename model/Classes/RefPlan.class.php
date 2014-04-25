<?php
/**
 * Created by Notepad++.
 * User: Alban Truc
 * Date: 31/01/14
 * Time: 12:52
 */

/**
 * Class RefPlan
 * @author Alban Truc
 */
class RefPlan
{
	/** @var string|MongoId $_id identifiant unique du plan */
	private $_id;

    /** @var int $state 0 = Ce plan n’est pas disponible, on ne peut donc y souscrire (non présent sur le site),
     * 1 = La souscription à ce plan est possible
     */
	private $state;

    /** @var string $name nom du plan */
	private $name;

    /** @var float $price prix du plan */
	private $price;

    /** @var int $maxStorage capacité de stockage maximale du plan */
	private $maxStorage;

    /** @var int $downloadSpeed vitesse de téléchargement en octets/seconde */
	private $downloadSpeed;

    /** @var  int $uploadSpeed vitesse de téléversement (upload) en octets/seconde */
    private $uploadSpeed;

    /** @var int $maxRatio quantité maximale d'octets téléchargeables par jour  */
    private $maxRatio;

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
				$this->name = (array_key_exists('name', $array)) ? (string)$array['name'] : NULL;
				$this->price = (array_key_exists('price', $array)) ? (float)$array['price'] : NULL;
				$this->maxStorage = (array_key_exists('maxStorage', $array)) ? (int)$array['maxStorage'] : NULL;
				$this->downloadSpeed = (array_key_exists('downloadSpeed', $array)) ? (int)$array['downloadSpeed'] : NULL;
                $this->uploadSpeed = (array_key_exists('uploadSpeed', $array)) ? (int)$array['uploadSpeed'] : NULL;
				$this->maxRatio = (array_key_exists('maxRatio', $array)) ? (int)$array['maxRatio'] : NULL;
				break;
			case 7: //toutes les propriétés sont passées dans la fonction, non sous la forme d'un tableau
				$this->state = (int)func_get_arg(0);
				$this->name = (string)func_get_arg(1);
				$this->price = (float)func_get_arg(2);
				$this->maxStorage = (int)func_get_arg(3);
				$this->downloadSpeed = (int)func_get_arg(4);
                $this->uploadSpeed = (int)func_get_arg(5);
				$this->maxRatio = (int)func_get_arg(6);
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
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = (string)$name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return (string)$this->name;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = (float)$price;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return (float)$this->price;
    }

    /**
     * @param int $maxStorage
     */
    public function setMaxStorage($maxStorage)
    {
        $this->maxStorage = (int)$maxStorage;
    }

    /**
     * @return int
     */
    public function getMaxStorage()
    {
        return (int)$this->maxStorage;
    }

    /**
     * @param int $downloadSpeed
     */
    public function setDownloadSpeed($downloadSpeed)
    {
        $this->downloadSpeed = (int)$downloadSpeed;
    }

    /**
     * @return int
     */
    public function getDownloadSpeed()
    {
        return (int)$this->downloadSpeed;
    }

    /**
     * @param int $uploadSpeed
     */
    public function setUploadSpeed($uploadSpeed)
    {
        $this->uploadSpeed = (int)$uploadSpeed;
    }

    /**
     * @return int
     */
    public function getUploadSpeed()
    {
        return (int)$this->uploadSpeed;
    }

    /**
     * @param int $maxRatio
     */
    public function setMaxRatio($maxRatio)
    {
        $this->maxRatio = (int)$maxRatio;
    }

    /**
     * @return int
     */
    public function getMaxRatio()
    {
        return (int)$this->maxRatio;
    }
}

?>