<?php
/**
 * Created by PhpStorm.
 * User: Crocell
 * Date: 25/04/14
 * Time: 11:00
 */

/**
 * Class RefRight
 * @author Alban Truc
 */
class RefRight
{
    /** @var  string|MongoId $_id identifiant unique du refRight */
    private $_id;

    /** @var int $state
     * 0 = droit non applicable
     * 1 = droit applicable
     * Plus amples informations: cf. document Correspondance des status et codes
     */
    private $state;

    /** @var string code numérique représentant le type de fichier */
    private $code;

    /** @var string type de fichier, compréhensible par l'humain. Exemple: fichier système. */
    private $description;

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
                break;
            case 3: //toutes les propriétés sont passées dans la fonction, non sous la forme d'un tableau
                $this->state = (int)func_get_arg(0);
                $this->code = (string)func_get_arg(1);
                $this->description = (string)func_get_arg(2);
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
}