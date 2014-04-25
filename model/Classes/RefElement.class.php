<?php
/**
 * Created by PhpStorm.
 * User: Crocell
 * Date: 25/04/14
 * Time: 10:59
 */

/**
 * Class RefElement
 * @author Alban Truc
 */
class RefElement
{
    /** @var  string|MongoId $_id identifiant unique du refElement */
    private $_id;

    /** @var int $state
     * 0 = extension désactivée
     * 1 = extension activée
     * Plus amples informations: cf. document Correspondance des status et codes
     */
    private $state;

    /** @var string code numérique représentant le type de fichier */
    private $code;

    /** @var string type de fichier, compréhensible par l'humain. Exemple: fichier système. */
    private $description;

    /** @var string $extension extension de l'élément. Exemple: .jpg */
    private $extension;

    /** @var string $imagePath chemin de l'image */
    private $imagePath;

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
                $this->code = (array_key_exists('code', $array)) ? (string)$array['code'] : NULL;
                $this->description = (array_key_exists('description', $array)) ? (string)$array['description'] : NULL;
                $this->extension = (array_key_exists('extension', $array)) ? (string)$array['extension'] : NULL;
                $this->imagePath = (array_key_exists('imagePath', $array)) ? (string)$array['imagePath'] : NULL;
                break;
            case 5: //toutes les propriétés sont passées dans la fonction, non sous la forme d'un tableau
                $this->state = (int)func_get_arg(0);
                $this->code = (string)func_get_arg(1);
                $this->description = (string)func_get_arg(2);
                $this->extension = (string)func_get_arg(3);
                $this->imagePath = (string)func_get_arg(4);
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
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = (string)$code;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return (string)$this->code;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = (string)$description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return (string)$this->description;
    }

    /**
     * @param string $extension
     */
    public function setExtension($extension)
    {
        $this->extension = (string)$extension;
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return (string)$this->extension;
    }

    /**
     * @param string $imagePath
     */
    public function setImagePath($imagePath)
    {
        $this->imagePath = (string)$imagePath;
    }

    /**
     * @return string
     */
    public function getImagePath()
    {
        return (string)$this->imagePath;
    }
}