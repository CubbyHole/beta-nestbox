<?php
/**
 * Created by PhpStorm.
 * User: Crocell
 * Date: 25/04/14
 * Time: 10:59
 */

/**
 * Class Element
 * @author Alban Truc
 */
class Element
{
    /** @var  string|MongoId $_id identifiant unique de l'élément */
    private $_id;

    /** @var int $state
     * 0 = élément inaccessible
     * 1 = élément accessible
     * 2 = élément à supprimer
     * 3 = élément supprimé
     * Plus amples informations: cf. document Correspondance des status et codes
     */
    private $state;

    private $name;

    /** @var  User|string|MongoId $idOwner identifiant de l'utilisateur propriétaire de l'élément */
    private $idOwner;

    /** @var RefElement|string|MongoId $idRefElement identifiant du refElement */
    private $idRefElement;

    /** @var int $size taille du fichier en Kb */
    private $size;

    /** @var string $serverPath chemin de l'élément sur les serveurs de stockage */
    private $serverPath;

    /** @var string $hash pour pouvoir vérifier l'intégrité de l'élément */
    private $hash;

    /** @var string $downloadLink lien de téléchargement anonyme */
    private $downloadLink;

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
                $this->state = (array_key_exists('state', $array)) ? (int)$array['state'] : NULL;
                $this->name = (array_key_exists('name', $array)) ? (string)$array['name'] : NULL;
                $this->idOwner = (array_key_exists('idOwner', $array)) ? $array['idOwner'] : NULL;
                $this->idRefElement = (array_key_exists('idRefElement', $array)) ? $array['idRefElement'] : NULL;
                $this->size = (array_key_exists('size', $array)) ? (int)$array['size'] : NULL;
                $this->serverPath = (array_key_exists('serverPath', $array)) ? (string)$array['serverPath'] : NULL;
                $this->hash = (array_key_exists('hash', $array)) ? (string)$array['hash'] : NULL;
                $this->downloadLink = (array_key_exists('downloadLink', $array)) ? (string)$array['downloadLink'] : NULL;
                break;
            case 8: //toutes les propriétés sont passées dans la fonction, non sous la forme d'un tableau
                $this->state = (int)func_get_arg(0);
                $this->name = (string)func_get_arg(1);
                $this->idOwner = func_get_arg(2);
                $this->idRefElement = func_get_arg(3);
                $this->size = (int)func_get_arg(4);
                $this->serverPath = (string)func_get_arg(5);
                $this->hash = (string)func_get_arg(6);
                $this->downloadLink = (string)func_get_arg(7);
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
        $this->$name = (string)$name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return (string)$this->name;
    }

    /**
     * @param MongoId|string|User $owner
     */
    public function setOwner($owner)
    {
        $this->idOwner = $owner;
    }

    /**
     * @return MongoId|string|User
     */
    public function getOwner()
    {
        return $this->idOwner;
    }

    /**
     * @param MongoId|RefElement|string $refElement
     */
    public function setRefElement($refElement)
    {
        $this->idRefElement = $refElement;
    }

    /**
     * @return MongoId|RefElement|string
     */
    public function getRefElement()
    {
        return $this->idRefElement;
    }

    /**
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = (int)$size;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return (int)$this->size;
    }

    /**
     * @param string $serverPath
     */
    public function setServerPath($serverPath)
    {
        $this->serverPath = (string)$serverPath;
    }

    /**
     * @return string
     */
    public function getServerPath()
    {
        return (string)$this->serverPath;
    }

    /**
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->hash = (string)$hash;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return (string)$this->hash;
    }

    /**
     * @param string $downloadLink
     */
    public function setDownloadLink($downloadLink)
    {
        $this->downloadLink = (string)$downloadLink;
    }

    /**
     * @return string
     */
    public function getDownloadLink()
    {
        return (string)$this->downloadLink;
    }
}