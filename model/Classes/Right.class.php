<?php
/**
 * Created by PhpStorm.
 * User: Crocell
 * Date: 25/04/14
 * Time: 11:00
 */

/**
 * Class Right
 * @author Alban Truc
 */
class Right
{
    /** @var  string|MongoId $_id identifiant unique du droit */
    private $_id;

    /** @var int $state
     * 0 = droit non appliqué
     * 1 = droit appliqué
     * Plus amples informations: cf. document Correspondance des status et codes
     */
    private $state;

    /** @var User|MongoId|string $idUser utilisateur possédant le droit */
    private $idUser;

    /** @var Element|MongoId|string $idElement élément sur lequel le droit est appliqué */
    private $idElement;

    /** @var RefRight|MongoId|string $idRefRight identifiant du refRight */
    private $idRefRight;

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
                $this->idUser = (array_key_exists('idUser', $array)) ? $array['idUser'] : NULL;
                $this->idElement = (array_key_exists('idElement', $array)) ? $array['idElement'] : NULL;
                $this->idRefRight = (array_key_exists('idRefRight', $array)) ? $array['idRefRight'] : NULL;
                break;
            case 4: //toutes les propriétés sont passées dans la fonction, non sous la forme d'un tableau
                $this->state = (int)func_get_arg(0);
                $this->idUser = func_get_arg(1);
                $this->idElement = func_get_arg(2);
                $this->idRefRight = func_get_arg(3);
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
     * @param MongoId|string|User $user
     */
    public function setUser($user)
    {
        $this->idUser = $user;
    }

    /**
     * @return MongoId|string|User
     */
    public function getUser()
    {
        return $this->idUser;
    }

    /**
     * @param Element|MongoId|string $element
     */
    public function setElement($element)
    {
        $this->idElement = $element;
    }

    /**
     * @return Element|MongoId|string
     */
    public function getElement()
    {
        return $this->idElement;
    }

    /**
     * @param MongoId|RefRight|string $refRight
     */
    public function setRefRight($refRight)
    {
        $this->idRefRight = $refRight;
    }

    /**
     * @return MongoId|RefRight|string
     */
    public function getRefRight()
    {
        return $this->idRefRight;
    }
}