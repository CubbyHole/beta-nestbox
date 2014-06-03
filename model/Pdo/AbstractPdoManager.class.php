<?php
/**
 * Created by Notepad++.
 * User: Alban Truc
 * Date: 30/01/14
 * Time: 14:51
 */

/**
 * Class AbstractPdoManager
 * @abstract
 * @author Alban Truc
 */
abstract class AbstractPdoManager 
{
    const DBHOST = 'localhost';
    //const DBUSER = '';
    //const DBPWD = '';
    const DBPORT = 27017;
    const DBNAME = 'nestbox';

    /** @var Mongo $connection connexion à la base */
    protected $connection;

    /** @var MongoDB $database la base de données */
    protected $database;

    /**
     * Constructeur: génère la connexion à la base de données Mongo.
     * @param string $databaseName
     * @param int $databasePort
     * @param string $databaseHost
     * @author Alban Truc
     * @since 30/01/14
     */

    public function __construct($databaseName = self::DBNAME, $databasePort = self::DBPORT, $databaseHost = self::DBHOST)
    {
        self::selectDatabase($databaseName, $databasePort, $databaseHost);
    }

    /**
     * Renvoie la collection voulue
     * @author Alban Truc
     * @param $name
     * @since 30/01/14
     * @return MongoCollection
     */

    public function getCollection($name)
    {
        return $this->database->selectCollection($name);
    }

    /**
     * Permet de sélectionner la base de données (et d'en changer)
     * @author Alban Truc
     * @param string $databaseName
     * @param int $databasePort
     * @param string $databaseHost
     * @since 03/06/2014
     */

     public function selectDatabase($databaseName, $databasePort = 27017, $databaseHost = 'localhost')
     {
         /** @var string $connectionString chaine de connexion
          * @link http://www.php.net/manual/fr/function.sprintf.php
          */
         $connectionString = sprintf('mongodb://%s:%d/%s', $databaseHost, $databasePort, $databaseName);

         try
         {
             $this->connection = new MongoClient($connectionString);
             $this->database = $this->connection->selectDB($databaseName);
         }
         catch (Exception $e)
         {
             if($e instanceof MongoConnectionException)
             {
                 $error = '<div class="alert alert-danger"><p>Could not reach a database.</p>
                Please contact our technical support (technical.support@cubbyhole.com)
                if this error remains for  more than 30 minutes.</div>';

                 echo $error;
             }
             else
             {
                 $error = '<div class="alert alert-danger"><p>The following error occured when trying to reach a database:</p>
                <p>'.utf8_encode($e->getMessage().' In '.$e->getFile().' at line '.$e->getLine()).'</p>
                <p>Please contact our technical support at technical.support@cubbyhole.com if this error remains.</p></div>';

                 echo $error;
             }
             exit();
         }
     }

    /**
     * Chiffre une chaîne de caractères
     * @author Alban Truc
     * @param string $string
     * @since 02/2014
     * @return string
     */

    public function encrypt($string)
    {
        return sha1(md5($string));
    }

    /**
     * - Génère un GUID
     * - Supprime les tirets et accolades
     * @author Alban Truc
     * @since 23/02/2014
     * @return string
     */

    public function generateGUID()
    {
        //@link http://www.php.net/manual/fr/function.com-create-guid.php
        $guid = com_create_guid();

        //caractères à enlever: -, { et }
        $patterns = array('/{/', '/-/', '/}/');
        $guid = preg_replace($patterns, '', $guid);

        return $guid;
    }

    /**
     * Convertir un objet avec des propriétés protected ou private en tableau associatif
     * @author Alban Truc
     * @param mixed $object
     * @since 12/03/2014
     * @return array
     * Discussions sur le sujet:
     * @link http://stackoverflow.com/questions/4345554/convert-php-object-to-associative-array
     */

    public function dismount($object)
    {
        $reflectionClass = new ReflectionClass(get_class($object));

        $array = array();
        foreach ($reflectionClass->getProperties() as $property)
        {
            $property->setAccessible(true);
            $array[$property->getName()] = $property->getValue($object);
            $property->setAccessible(false);
        }

        return $array;
    }

    /**
     * A partir d'une MongoDate, crée un tableau contenant:
     *  - le timestamp;
     *  - la date formattée en Y-M-d (exemple: 2014-Feb-23)
     *  - le temps formatté en H:i:s (exemple: 18:11:00)
     * @author Alban Truc
     * @param MongoDate $mongoDate
     * @since 17/03/2014
     * @return array
     */

    public function formatMongoDate($mongoDate)
    {
        $timestamp = $mongoDate->sec;
        $date = date('Y-M-d', $timestamp);
        $time = date('H:i:s', $timestamp);

        return array(
            'timestamp' => $timestamp,
            'date' => $date,
            'time' => $time
        );
    }

    /**
     * - Retrouver un document unique correspondant à des critères donnés dans une collection donnée
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param string $collectionName Nom de la collection
     * @param array $criteria Critère de recherche
     * @param array $fieldsToReturn Champs à retourner
     * @since 29/03/2014
     * @return array contenant le résultat ou un message d'erreur
     */

    public function __findOne($collectionName, $criteria, $fieldsToReturn = array())
    {
        if(!($collectionName instanceof MongoCollection))
            $collection = self::getCollection($collectionName);
        /**
         * Doc du findOne: {@link http://www.php.net/manual/en/mongo.tutorial.findone.php}
         * Utilisé lorsqu'on attend un résultat unique (notre cas) ou si l'on ne veut que le 1er résultat.
         * Les ID dans Mongo sont des objets MongoId: {@link http://www.php.net/manual/en/class.mongoid.php}
         */
        try
        {
            $result = $collection->findOne($criteria, $fieldsToReturn);
        }
        catch(Exception $e)
        {
            if($e instanceof MongoException)
                if($e instanceof MongoCursorException)
                    return array('error' => $e->getMessage());
        }

        //Si un refPlan est trouvé
        if($result !== NULL)
            return $result;

        else return array('error' => 'No match found.');
    }

    /**
     * - Retrouver les documents correspondants à des critères donnés dans une collection donnée
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param string $collectionName
     * @param array $criteria Critère de recherche
     * @param array $fieldsToReturn Champs à retourner
     * @since 29/03/2014
     * @return array|MongoCursor
     */

    public function __find($collectionName, $criteria, $fieldsToReturn = array())
    {
        if(!($collectionName instanceof MongoCollection))
            $collection = self::getCollection($collectionName);

        //Doc du find: {@link http://www.php.net/manual/en/mongocollection.find.php}
        try
        {
            $cursor = $collection->find($criteria, $fieldsToReturn);

            return $cursor;
        }
        catch(Exception $e)
        {
            if($e instanceof MongoException)
                if($e instanceof MongoCursorException)
                    return array('error' => $e->getMessage());
        }
    }

    /**
     * Inspiré de la méthode findAndModify: {@link http://www.php.net/manual/en/mongocollection.findandmodify.php}
     * - Retrouver un document selon certains critères et le modifier/supprimer
     * - Récupérer ce document ou sa version modifiée
     * - Gestion des exceptions et des erreurs
     * Remarque: seul le 1er document trouvé est affecté
     * @author Alban Truc
     * @param string $collectionName
     * @param array $searchQuery critères de recherche
     * @param array $updateCriteria les modifications à réaliser
     * @param array|NULL $fieldsToReturn pour ne récupérer que certains champs
     * @param array|NULL $options voir le lien php.net pour la liste des options
     * @since 29/03/2014
     * @return array
     */

    public function __findAndModify($collectionName, $searchQuery, $updateCriteria, $fieldsToReturn = NULL, $options = NULL)
    {
        $result = NULL;

        if(!($collectionName instanceof MongoCollection))
            $collection = self::getCollection($collectionName);

        try
        {
            $result = $collection->findAndModify(
                $searchQuery,
                $updateCriteria,
                $fieldsToReturn,
                $options
            );
        }
        catch(Exception $e)
        {
            if($e instanceof MongoException)
                if($e instanceof MongoResultException)
                    return array('error' => $e->getMessage());
        }

        if($result !== NULL)
            return $result;
        else
            return array(
                'error' => 'No match found.'
            );
    }

    /**
     * - Fonction d'update inspirée de {@link http://www.php.net/manual/en/mongocollection.update.php}
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param string $collectionName
     * @param array $criteria description des entrées à modifier
     * @param array $update nouvelles valeurs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    public function __update($collectionName, $criteria, $update, $options = array('w' => 1))
    {
        if(!($collectionName instanceof MongoCollection))
            $collection = self::getCollection($collectionName);

        try
        {
            /**
             * Informations sur toutes les options au chapitre "Parameters":
             * @link http://www.php.net/manual/en/mongocollection.insert.php
             */
            $info = $collection->update($criteria, $update, $options);
        }
        catch(Exception $e)
        {
            if($e instanceof MongoException)
            {
                if($e instanceof MongoCursorException)
                    return array('error' => $e->getMessage());
                else if($e instanceof MongoCursorTimeoutException)
                {
                    $error = 'It took too long to get a response from the server.';
                    return array('error' => $error);
                }
            }
        }

        /**
         * Gestion de ce qui est retourné grâce à l'option w.
         * Si on essaye de supprimer un document qui n'existe pas, remove() ne renvoie pas d'exception.
         * Dans ce cas, $info['n'] contiendra 0. Nous devons donc vérifer que ce n est différent de 0.
         * Plus d'informations sur les retours, voir chapitre "Return Values":
         * @link http://www.php.net/manual/en/mongocollection.insert.php
         */

        if(!(empty($info)) && $info['ok'] == '1' && $info['err'] === NULL)
        {
            if($info['n'] != '0') return TRUE;

            else return array(
                'error' => 'Unknown error when trying to update.'
            );
        }
        else return array('error' => $info);
    }

    /**
     * - Supprime un ou plusieurs document(s) correspondant à des critères données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param string $collectionName
     * @param array $criteria description des entrées à supprimer
     * @param array $options
     * @since 29/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    public function __remove($collectionName, $criteria, $options = array('w' => 1))
    {
        if(!($collectionName instanceof MongoCollection))
            $collection = self::getCollection($collectionName);

        try
        {
            /**
             * w = 1 est optionnel, il est déjà à 1 par défaut.
             * Cela permet d'avoir un retour du status de la suppression.
             * Documentation du remove: {@link http://www.php.net/manual/en/mongocollection.remove.php}
             */

            $info = $collection->remove($criteria, $options);

        }
        catch(Exception $e)
        {
            if($e instanceof MongoException)
            {
                if($e instanceof MongoCursorException)
                    return array('error' => $e->getMessage());
                else if($e instanceof MongoCursorTimeoutException)
                {
                    $error = 'It took too long to get a response from the server.';
                    return array('error' => $error);
                }
            }
        }

        /**
         * Gestion de ce qui est retourné grâce à l'option w.
         * Si on essaye de supprimer un document qui n'existe pas, remove() ne renvoie pas d'exception.
         * Dans ce cas, $info['n'] contiendra 0. Nous devons donc vérifer que ce n est différent de 0.
         * Plus d'informations sur les retours, voir chapitre "Return Values":
         * @link http://www.php.net/manual/en/mongocollection.insert.php
         */

        if(!(empty($info)) && $info['ok'] == '1' && $info['err'] === NULL)
        {
            if($info['n'] != '0') return TRUE;

            else return array('error' => 'Could not remove the document.');
        }
        else return array('error' => $info['err']);
    }

    /**
     * - Ajoute un document en base de données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param string $collectionName
     * @param array $document ce qu'on veut insérer
     * @param array $options
     * @since 12/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    public function __create($collectionName, $document, $options = array('w' => 1))
    {
        if(!($collectionName instanceof MongoCollection))
            $collection = self::getCollection($collectionName);

        try
        {
            //@link http://www.php.net/manual/en/mongocollection.insert.php
            $info = $collection->insert($document, $options);
        }
        catch(Exception $e)
        {
            if($e instanceof MongoException)
            {
                if($e instanceof MongoCursorException)
                    return array('error' => $e->getMessage());
                else if($e instanceof MongoCursorTimeoutException)
                {
                    $error = 'It took too long to get a response from the server.';
                    return array('error' => $error);
                }
                else
                {
                    $error = 'Document is empty or contains zero-length keys';
                    return array('error' => $error);
                }
            }
        }

        /**
         * Gestion de ce qui est retourné grâce à l'option w.
         * Plus d'informations sur les retours, voir chapitre "Return Values":
         * @link http://www.php.net/manual/en/mongocollection.insert.php
         */
        if(!(empty($info)) && $info['ok'] == '1' && $info['err'] === NULL) return TRUE;

        else return array('error' => $info['err']);
    }
}