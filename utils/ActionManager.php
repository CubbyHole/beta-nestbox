<?php
/**
 * Created by PhpStorm.
 * User: Crocell
 * Date: 31/05/14
 * Time: 22:33
 */

$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';
require_once $projectRoot.'/required.php';

//@todo vérifier dans la collection refAction que l'action est autorisée

/**
 * vérifier si l'utilisateur a les droits nécessaires, c'est-à-dire si l'utilisateur est propriétaire
 *  ou le droit nécessaire est présent en base.
 * @author Alban Truc
 * @param MongoId|string $idElement
 * @param MongoId|string $idUser
 * @param array $refRightCodes
 * @since 31/05/2014
 * @return bool|array contenant un message d'erreur
 */

function actionAllowed($idElement, $idUser, $refRightCodes)
{
    $idElement = new MongoId($idElement);
    $idUser = new MongoId($idUser);

    $elementPdoManager = new ElementPdoManager();
    $element = $elementPdoManager->findById($idElement);

    if(!array_key_exists('error', $element))
    {
        $isOwner = $hasRight = FALSE;

        if($element->getOwner() == $idUser)
            $isOwner = TRUE;
        else
        {
            $rightPdoManager = new RightPdoManager();
            $hasRight = $rightPdoManager->hasRightOnElement($idUser, $idElement, $refRightCodes);
        }

        if($isOwner || $hasRight)
            return TRUE;
        else return FALSE;
    }
    else return array('error' => 'Element does not exist');
}

/**
 * Prend en paramètre un id de refElement et retourne vrai si le refElement désigne un dossier,
 * faux sinon ou un tableau avec l'index error en cas d'erreur.
 * @author Alban Truc
 * @param MongoId|string $refElementId
 * @param bool $returnFalseIfEmpty pour vérifier en plus que le dossier n'est pas vide
 * @since 04/06/2014
 * @return array|bool
 * @todo employer cette fonction dans la fonction disableHandler
 */

function isFolder($refElementId, $returnFalseIfEmpty = FALSE)
{
    $refElementId = new MongoId($refElementId);

    //récupère le code du refElement de notre élément
    $refElementPdoManager = new RefElementPdoManager();
    $refElement = $refElementPdoManager->findById($refElementId, array('code' => TRUE));

    if(!(array_key_exists('error', $refElement)))
    {
        //si le code commence par un 4 (les codes de dossier commencent par un 4)
        if(preg_match('/^4/', $refElement['code']))
        {
            if($returnFalseIfEmpty == TRUE)
            {
                if($refElement['code'] == '4002')
                    return FALSE;
            }
            return TRUE;
        }
        else
            return FALSE;
    }
    else return $refElement; //message d'erreur
}



/**
 * permet de modifier le statut d'un dossier à empty après un disable dans le dit dossier si il ne contient plus d'éléments actifs (=> state = 1)
 * permet également de modifier le statut d'un dossier à notempty après une copie dans le dit dossier
 * @author Harry Bellod
 * @param string $serverPath (de l'élément disable ou du dossier ou l'on copie)
 * @param string|MongoId $idOwner
 * @since 07/06/2014
 * @return bool|true si update ok
 */

function updateFolderStatus($serverPath, $idOwner)
{
    $idOwner = new MongoId($idOwner);

    $elementPdoManager = new ElementPdoManager();
    $refElementPdoManager = new RefElementPdoManager();

    if($serverPath != '/')
    {
        // on vérifie si il reste des éléments actifs dans le dossier ou l'on désactive l'élément
        $criteria = array(
            'state' => 1,
            'serverPath' => $serverPath,
            'idOwner' => $idOwner
        );

        $elements = $elementPdoManager->find($criteria);

        // si il n'y en a plus alors on passe le dossier courant à empty
        if(array_key_exists('error', $elements))
        {
            if($elements['error'] == 'No match found.')
            {
                $refElement = $refElementPdoManager->findOne(array(
                    'code' => '4002',
                    'state' => 1
                ));
            }
            else
                return $elements;
        }
        else
        {
            $refElement = $refElementPdoManager->findOne(array(
                'code' => '4003',
                'state' => 1
            ));
        }

        if(!(array_key_exists('error', $refElement)))
            $idRefElement = $refElement->getId();
        else
            return $refElement;

        // on récupère le nom du dossier ou l'on se trouve
        $explode = explode("/", $serverPath);
        $directoryCurrent = $explode[sizeof($explode)-2];

        // on récupère son serverPath
        $pattern = "#".$directoryCurrent."/#";
        $path = preg_replace($pattern, "", $serverPath, 1);

        // on réalise un update sur le dossier en question pour modifier son refElement (à Directory File Empty)
        $criteria = array(
            'name' => $directoryCurrent,
            'serverPath' => $path,
            'state' => 1,
            'idOwner' => $idOwner
        );
        $update = array(
            '$set' => array('idRefElement' => $idRefElement)
        );
        return $elementPdoManager->update($criteria, $update);
    }
    return true; //rien à faire
}

/**
 * Fait la somme des size. Accepte une liste d'objets Element ou une liste de tableaux.
 * @author Alban Truc
 * @param array $elementList
 * @since 05/06/2014
 * @return int
 */

function sumSize($elementList)
{
    $totalSize = 0;

    foreach($elementList as $element)
    {
        if($element instanceof Element)
            $totalSize += $element->getSize();
        else
            $totalSize += $element['size'];
    }

    return $totalSize;
}

/**
 * Copie les droits appliqués aux éléments d'une liste source pour les éléments d'une autre liste.
 * Attention: les deux listes doivent être de même taille et l'ordre des éléments car une association des id des éléments
 * sources et ceux de la seconde liste sont associés dans leur ordre de rangement.
 * @author Alban Truc
 * @param array $sourceElementList
 * @param array $pastedElementList
 * @since 07/06/05
 */

function copyRights($sourceElementList, $pastedElementList)
{
    $rightPdoManager = new RightPdoManager();

    $rightCriteria = array(
        'state' => (int) 1,
    );

    $sourceRightList = array();

    $associateIds = array();
    $count = 0;

    foreach($sourceElementList as $sourceElement)
    {
        $associateIds[(string)$sourceElement->getId()] = (string)$pastedElementList[$count]->getId();
        $count++;
        $rightCriteria['idElement'] = $sourceElement->getId();
        $rights = $rightPdoManager->find($rightCriteria);

        if(!(array_key_exists('error', $rights)))
            $sourceRightList = array_merge_recursive($sourceRightList, $rights);
    }

    //si on voulait log
    //$rightsToPaste = array();
    //$pastedRights = array();
    //$failedToPaste = array();
    //$count = 0

    foreach($sourceRightList as $right)
    {
        $rightCopy = clone $right;
        $rightCopy->setId(new MongoId());
        $rightCopy->setElement(new MongoId($associateIds[(string)$right->getElement()]));

        //$rightsToPaste[] = $rightCopy;
        $insertResult = $rightPdoManager->create($rightCopy);

        //si on voulait log
        /*
        if(!(is_bool($insertResult))) //erreur
        {
            $failedToPaste[$count]['rightToCopy'] = $right;
            $failedToPaste[$count]['rightCopy'] = $rightCopy;
            $failedToPaste[$count]['error'] = $insertResult['error'];
            $count++;
        }
        else $pastedRight[] = $rightCopy;
        */
    }
}

/**
 * Désactive les droits appliqués à chaque élément d'une liste d'éléments
 * @author Alban Truc
 * @param array $elementList
 * @since 08/06/2014
 */

function disableRights($elementList)
{
    //si on voulait log
    //$disabledRights = array();
    //$failedToDisable = array();
    //$count = 0
    $rightPdoManager = new RightPdoManager();

    $rightCriteria = array(
        'state' => (int) 1,
    );

    $rightUpdate = array(
        '$set' => array( 'state' => (int) 0)
    );

    $options = array('multiple' => true);

    foreach($elementList as $element)
    {
        $rightCriteria['idElement'] = $element->getId();

        $disableResult = $rightPdoManager->update($rightCriteria, $rightUpdate, $options);

        /*
        //si on voulait log
        if(!(is_bool($disableResult)))
        {
            $failedToDisable[$count]['rightCriteria'] = $rightCriteria;
            $failedToDisable[$count]['error'] = $disableResult['error'];
            $count++;
        }
        else $disabledRights[] = $element->getId(); //liste des id d'éléments dont on a désactivé les droits
        */
    }
}

/**
 * Renomme de la même manière que le ferait un OS Windows pour éviter les collisions de nom.
 * Remarque: la version actuelle de cette fonction ne prend pas en compte l'extension du fichier (si l'élément est
 * effectivement un fichier). On ne peut donc pas avoir dans un même emplacement un fichier test.flac et test.mp3.
 * @author Alban Truc
 * @param string $path
 * @param string $elementName
 * @param string|MongoId $idOwner
 * @since 07/06/2014
 * @return array|Element[]|string
 */

function avoidNameCollision($path, $elementName, $idOwner)
{
    $elementPdoManager = new ElementPdoManager();

    $idOwner = new MongoId($idOwner);

    //un élément avec le même nom n'est-il pas déjà présent?
    $seekForNameDuplicate = array(
        'state' => (int)1,
        'serverPath' => $path,
        'name' => $elementName,
        'idOwner' => $idOwner
    );
//    return var_dump($seekForNameDuplicate);
    $elementsWithSameName = $elementPdoManager->find($seekForNameDuplicate);
    //var_dump($elementsWithSameName);

    if(array_key_exists('error', $elementsWithSameName))
    {
        //cas no match found => pas d'élément avec le même nom à l'emplacement de destination
        if($elementsWithSameName['error'] == 'No match found.')
        {
            $elementNameInDestination = $elementName;
        }
        else return $elementsWithSameName;
    }
    else //nom déjà utilisé
    {
        //existe-t-il déjà des copies?
        $seekForCopies = array(
            'state' => (int)1,
            'serverPath' => $path,
            'name' => new MongoRegex("/^".$elementName." - Copy/i"),
            'idOwner' => $idOwner
        );

        $duplicate = $elementPdoManager->find($seekForCopies, array('name' => TRUE, '_id' => FALSE));
        //var_dump($duplicate);

        if(array_key_exists('error', $duplicate))
        {
            //cas où il n'y a pas de copie
            if($duplicate['error'] == 'No match found.')
            {
                $elementNameInDestination = $elementName.' - Copy';
            }
            else return $duplicate;
        }
        else //une ou plusieurs copies ont été trouvées
        {
            /**
             * actuellement nous avons un tableau de tableaux contenant les noms des duplicats.
             * Exemple: array ( [0] => array ( ['name'] => 'duplicaName' ) )
             * La manipulation suivante sert à enlever un "étage" pour obtenir par exemple
             * array ( [0] => 'duplicataName' ).
             * Nos environnements de développement ne disposant pas de PHP 5.5.0, nous ne pouvons
             * utiliser pour cela la fonction array_column. En remplacement, nous appliquons une
             * fonction via array_map.
             * @see http://www.php.net/manual/en/function.array-column.php
             * @see http://www.php.net/manual/en/function.array-map.php
             */

            $f = function($array){return $array['name'];};
            $duplicate = array_map($f, $duplicate);
            //var_dump($duplicate);
            //@see http://www.php.net/manual/en/function.in-array.php
            if(!(in_array($elementName.' - Copy', $duplicate)))
                $elementNameInDestination = $elementName.' - Copy';
            else
            {
                /**
                 * @see http://www.php.net/manual/en/function.unset.php
                 * @see http://www.php.net/manual/en/function.array-search.php
                 * Supprime dans le tableau la valeur correspondant à
                 * $element->getName().' - Copy' pour simplifier les opérations suivantes
                 */
                unset($duplicate[array_search($elementName.' - Copy', $duplicate)]);

                //@see http://www.php.net/manual/en/function.sort.php cf. exemple #2
                sort($duplicate, SORT_NATURAL | SORT_FLAG_CASE);
                //var_dump($duplicate);

                /*
                 * déterminer quel nom du type elementName - Copy (number) est disponible,
                 * avec number le plus proche possible de 0
                 */

                //indexe pour le tableau duplicate
                //$keyNumber = 0;

                //Le "number" commence à 2
                $copyNumberIndex = 2;
                //var_dump($duplicate); exit();
                if(!(empty($duplicate))) //Plus d'une copie
                {
                    $count = 0;
                    while(isset($duplicate[$count]))
                    {
                        if($duplicate[$count] == $elementName.' - Copy ('.$copyNumberIndex.')')
                        {
                            $copyNumberIndex++;
                        }
                        $count++;
                    }
                }
//                    var_dump($copyNumberIndex);
                $elementNameInDestination = $elementName.' - Copy ('.$copyNumberIndex.')';
//                    var_dump($elementNameInDestination); exit();
            }
        }
    }
    return $elementNameInDestination;
}

/**
 * Prépare le retour de la fonction copyHandler
 * @author Alban Truc
 * @param array $options
 * @param bool $operationSuccess
 * @param string|array $error
 * @param array $elementsImpacted
 * @param array $pastedElements
 * @param array $failedToPaste
 * @since 06/06/2014
 * @return array
 */

function prepareCopyReturn($options, $operationSuccess, $error, $elementsImpacted, $pastedElements, $failedToPaste)
{
    $return = array();

    $return['operationSuccess'] = $operationSuccess;

    if(!(empty($error)))
    {
        if(is_array($error) && array_key_exists('error', $error))
            $return['error'] = $error['error'];
        else
            $return['error'] = $error;
    }

    if(is_array($options))
    {
        if(array_key_exists('returnImpactedElements', $options) && $options['returnImpactedElements'] == TRUE)
        {
            if(empty($elementsImpacted))
                $return['elementsImpacted'] = 'No impacted element or the function had an error before the element(s) got retrieved.';
            else
                $return['elementsImpacted'] = $elementsImpacted;
        }

        if(array_key_exists('returnPastedElements', $options) && $options['returnPastedElements'] == TRUE)
        {
            if(empty($pastedElements))
                $return['pastedElements'] = 'No pasted element or the function had an error before trying to.';
            else
                $return['pastedElements'] = $pastedElements;

            if(empty($failedToPaste))
                $return['failedToPaste'] = 'No fail or the function had an error before trying to.';
            else
                $return['failedToPaste'] = $failedToPaste;
        }
    }
    return $return;
}

/**
 * Rend un élément inaccessible (en BDD, ne comporte pas d'action sur le serveur de fichier).
 * @author Alban Truc
 * @param MongoId|string $idElement
 * @param MongoId|string $idUser
 * @param bool $returnImpactedElements si TRUE, retourne les éléments impactés par l'action
 * @since 31/05/2014
 * @return array|Element
 */

function disableHandler($idElement, $idUser, $returnImpactedElements = FALSE)
{
    $idElement = new MongoId($idElement);
    $idUser = new MongoId($idUser);

    // 11 correspond au droit de lecture et écriture
    $hasRight = actionAllowed($idElement, $idUser, array('11'));

    if(!(is_array($hasRight)))
    {
        if($hasRight === TRUE)
        {
            //récupère l'élément
            $elementPdoManager = new ElementPdoManager();
            $element = $elementPdoManager->findById($idElement);

            if($element instanceof Element)
            {
                if($element->getState() == 1)
                {
                    //récupère le code du refElement de notre élément
                    $refElementPdoManager = new RefElementPdoManager();

                    $fieldsToReturn = array('code' => TRUE, 'extension' => TRUE);
                    $refElement = $refElementPdoManager->findById($element->getRefElement(), $fieldsToReturn);

                    if(!(array_key_exists('error', $refElement)))
                    {
                        //File Server -- 13/06/2014
                        if(preg_match('/^4/', $refElement['code']) || preg_match('/^9/', $refElement['code'])) // dossier ou non reconnu, pas d'extension à rajouter
                            $elementName = $element->getName();
                        else
                            $elementName = $element->getName().$refElement['extension'];

                        $FSdisableResult = moveToTrash($idUser, $element->getServerPath(), $elementName);

                        if(!(is_bool($FSdisableResult)) || $FSdisableResult != TRUE)
                            return $FSdisableResult;
                        //-- Fin File Server

                        //si le code commence par un 4 (les codes de dossier commencent par un 4) et n'est pas 4002 (dossier vide)
                        if(preg_match('/^4/', $refElement['code']) && $refElement['code'] != '4002')
                        {
                            $serverPath = $element->getServerPath().$element->getName().'/';

                            //notre criteria inclut tous les éléments se trouvant dans le dossier et ses dossiers enfants
                            $elementCriteria = array(
                                'state' => (int) 1,
                                'idOwner' => $idUser,
                                '$or' => array(
                                    array('_id' => $idElement),
                                    array('serverPath' => new MongoRegex("/^$serverPath/i"))
                                )
                            );
                        }
                        else //un fichier
                        {
                            $elementCriteria = array(
                                '_id' => $idElement,
                                'state' => (int)1,
                            );
                        }

                        //pour mettre à jour tous les documents et pas uniquement le premier répondant au critère
                        $options = array(
                            'multiple' => TRUE
                        );

                        //désactivation de l'élément et suppression du lien de téléchargement
                        $elementUpdate = array(
                            '$set' => array(
                                'state' => (int)0,
                                'downloadLink' => ''
                            )
                        );

                        /*
                         * obligatoirement à récupérer avant la mise à jour, sinon aucun document ne devrait être trouvé
                         * en cas de réussite de cette mise à jour. On ne peut pas non plus faire le même critère avec à la
                         * place un état de 0 parce qu'il se peut qu'il y ait déjà des éléments désactivés en base.
                         * L'id est récupérer pour le critère de mise à jour des droits et le size pour la déduction du
                         * stockage occupé par les éléments (mise à jour du storage du compte utilisateur).
                         */
                        $impactedElements = $elementPdoManager->find($elementCriteria, array('_id' => TRUE, 'size' => TRUE));
                        //var_dump($impactedElements); exit();
                        $elementUpdateResult = $elementPdoManager->update($elementCriteria, $elementUpdate, $options);

                        if(!(is_array($elementUpdateResult)))
                        {
                            if($elementUpdateResult === TRUE)
                            {
                                $updateFolderStatus = updateFolderStatus($element->getServerPath(), $idUser);
                                if(is_bool($updateFolderStatus) && $updateFolderStatus === TRUE)
                                {
                                    //séparation en deux tableaux
                                    $idImpactedElements = array();
                                    $sizeImpactedElements = array();

                                    foreach($impactedElements as $impactedElement)
                                    {
                                        //création d'un tableau contenant uniquement les id des éléments impactés
                                        if(isset($impactedElement['_id']))
                                            $idImpactedElements[] = $impactedElement['_id'];

                                        //création d'un tableau contenant uniquement la taille de chaque élément impacté
                                        if(isset($impactedElement['size']))
                                            $sizeImpactedElements[] = $impactedElement['size'];
                                    }
                                    //var_dump($sizeImpactedElements);
                                    //désactivation des droits sur ces éléments
                                    $rightUpdate = array(
                                        '$set' => array(
                                            'state' => (int)0
                                        )
                                    );

                                    $rightPdoManager = new RightPdoManager();

                                    foreach($idImpactedElements as $id)
                                    {
                                        $rightCriteria = array(
                                            'state' => (int)1,
                                            'idElement' => $id
                                        );

                                        //l'opération n'étant pas bloquante, on ne se soucie pour l'instant pas d'un potentiel échec
                                        $rightPdoManager->update($rightCriteria, $rightUpdate, $options);
                                    }

                                    //déduction du stockage occupé par ces éléments dans la collection account
                                    $totalSize = array_sum($sizeImpactedElements); //http://www.php.net/manual/function.array-sum.php
                                    //var_dump($totalSize); exit();
                                    $accountPdoManager = new AccountPdoManager();

                                    $accountCriteria = array(
                                        'state' => (int)1,
                                        'idUser' => $idUser
                                    );

                                    $accountUpdate = array(
                                        '$inc' => array('storage' => -$totalSize)
                                    );

                                    $accountUpdateResult = $accountPdoManager->update($accountCriteria, $accountUpdate);

                                    if(!(is_array($accountUpdateResult)))
                                    {
                                        if($accountUpdateResult === TRUE)
                                        {
                                            if($returnImpactedElements === TRUE)
                                                return $idImpactedElements;
                                            else return TRUE;
                                        }
                                        else return array('error' => 'Did not manage to update the storage value.');
                                    }
                                    else return $accountUpdateResult;
                                }
                                else return $updateFolderStatus;
                            }
                            else return array(
                                'error' => 'Did not manage to update all elements.
                                The elements that we couldn\'t updates are in this array at the index \'notUpdated\'',
                                /*
                                 * logiquement, les éléments non mis à jour sont ceux pour lequel l'état est toujours à 1,
                                 * d'où la réutilisation du critère précédent
                                 */
                                'notUpdated' => $elementPdoManager->find($elementCriteria, array('_id' => TRUE))
                            );
                        }
                        else return $elementUpdateResult; //message d'erreur
                    }
                    else return $refElement; //message d'erreur
                }
                else return array('error' => 'Element already inactivated');
            }
            else return $element; //message d'erreur
        }
        else return array('error' => 'Access denied');
    }
    else return $hasRight;
}

/**
 * Copie l'élément (et ce qu'il contient dans le cas d'un dossier) dans la destination indiquée.
 * $options est un tableau de booléens avec comme indexes possibles:
 * - returnImpactedElements pour retourner les éléments à copier
 * - returnPastedElements pour retourner les éléments copiés (ceux présent dans la destination), échec et succès
 * - keepRights pour également copier les droits des éléments sources
 * On peut se retrouver avec la structure de retour suivante:
 *  array(
 *          'operationSuccess' => true ou false
 *          'error' => 'message d'erreur',
 *          'impactedElements' => array(),
 *          'pastedElements' => array(),
 *          'failedToPaste' => array()
 *       )
 * @author Alban Truc & Harry Bellod
 * @param string|MongoId $idElement
 * @param string|MongoId $idUser
 * @param string $path
 * @param array $options
 * @since 07/06/2014
 * @return array
 * @todo optimisation: découpage en plusieurs fonctions de moins de 80 lignes
 * @todo meilleure prise en charge des conflits de noms: actuellement l'extension n'est pas prise en compte
 */

function copyHandler($idElement, $idUser, $path, $options = array())
{
    $idElement = new MongoId($idElement);
    $idUser = new MongoId($idUser);

    $impactedElements = array();
    $pastedElements = array();
    $failedToPaste = array();

    $operationSuccess = FALSE;

    /*
     * 11 correspond au droit de lecture et écriture.
     * Si on souhaite accepter la copie avec des droits de plus bas niveau, il suffit d'ajouter les codes correspondant
     * au tableau en 3e paramètre ci-dessous.
     */

    $hasRight = actionAllowed($idElement, $idUser, array('11'));

    if(!(is_array($hasRight)))
    {
        if($hasRight === TRUE)
        {
            //récupère l'élément
            $elementPdoManager = new ElementPdoManager();
            $element = $elementPdoManager->findById($idElement);

            if($element instanceof Element)
            {
                if($element->getState() == 1)
                {
                    if($path != $element->getServerPath())
                    {
                        $elementCriteria = array(
                            'state' => (int)1,
                            'idOwner' => $idUser
                        );

                        /*
                         * extraction de l'emplacement du dossier de destination à partir de $path
                         * @see http://www.php.net/manual/en/function.implode.php
                         * @see http://www.php.net/manual/en/function.explode.php
                         */
                        $destinationFolderPath = implode('/', explode('/', $path, -2)).'/';
                        $elementCriteria['serverPath'] = $destinationFolderPath;

                        /**
                         * la racine n'ayant pas d'enregistrement pour elle même, on a un serverPath "/" mais de nom.
                         * il faut donc distinguer les cas de copies d'un élément dans la racine des autres cas.
                         */
                        if($path != "/")
                        {
                            /*
                         * extraction du nom du dossier de destination à partir du $path
                         * @see http://www.php.net/manual/en/function.array-slice.php
                         */
                            $destinationFolderName = implode(array_slice(explode('/', $path), -2, 1));
                            $elementCriteria['name'] = $destinationFolderName;

                            //récupération de l'id de l'élément en base correspondant au dossier de destination
                            $idDestinationFolder = $elementPdoManager->findOne($elementCriteria, array('_id' => TRUE));

                            if((array_key_exists('error', $idDestinationFolder)))
                                return prepareCopyReturn($options, $operationSuccess, $idDestinationFolder, $impactedElements, $pastedElements, $failedToPaste);
                            else
                            {
                                //vérification des droits dans la destination
                                $hasRightOnDestination = actionAllowed($idDestinationFolder['_id'], $idUser, array('11'));

                                if(is_array($hasRightOnDestination) && array_key_exists('error', $hasRightOnDestination))
                                    return prepareCopyReturn($options, $operationSuccess, $hasRightOnDestination, $impactedElements, $pastedElements, $failedToPaste);
                                elseif($hasRightOnDestination == FALSE)
                                    return prepareCopyReturn($options, $operationSuccess, array('error' => 'Access denied in destination'), $impactedElements, $pastedElements, $failedToPaste);

                            }
                        }
                    }

                    $elementNameInDestination = avoidNameCollision($path, $element->getName(), $idUser);

                    if(is_string($elementNameInDestination))
                    {
                        //File Server -- 13/06/2014
                        $refElementPdoManager = new RefElementPdoManager();
                        $refElementFieldsToReturn = array('code' => TRUE, 'extension' => TRUE);
                        $refElement = $refElementPdoManager->findById($element->getRefElement(), $refElementFieldsToReturn);

                        if(array_key_exists('error', $refElement))
                            return $refElement;

                        //dossier ou non reconnu, pas d'extension à rajouter
                        if(preg_match('/^4/', $refElement['code']) || preg_match('/^9/', $refElement['code']))
                        {
                            $completeSourceName = $element->getName();
                            $completeDestinationName = $elementNameInDestination;
                        }
                        else
                        {
                            $completeSourceName = $element->getName().$refElement['extension'];
                            $completeDestinationName = $elementNameInDestination.$refElement['extension'];
                        }

                        $FSCopyResult = copyFSElement($idUser, $element->getServerPath(), $completeSourceName, $path, $completeDestinationName );

                        if(!(is_bool($FSCopyResult)) || $FSCopyResult != TRUE)
                            return $FSCopyResult;
                        //Fin File Server

                        //récupérer la valeur de storage de l'utilisateur
                        $accountPdoManager = new AccountPdoManager();

                        $accountCriteria = array(
                            'state' => (int)1,
                            'idUser' => $idUser
                        );

                        $fieldsToReturn = array(
                            'storage' => TRUE,
                            'idRefPlan' => TRUE
                        );

                        $account = $accountPdoManager->findOne($accountCriteria, $fieldsToReturn);

                        if(!(array_key_exists('error', $account)))
                        {
                            $currentUserStorage = $account['storage'];

                            //récupérer le stockage maximum autorisé par le plan de l'utilisateur
                            $refPlanPdoManager = new RefPlanPdoManager();

                            $refPlan = $refPlanPdoManager->findById($account['idRefPlan'], array('maxStorage' => TRUE));

                            if(!(array_key_exists('error', $refPlan)))
                                $maxStorageAllowed = $refPlan['maxStorage'];
                            else
                                return prepareCopyReturn($options, $operationSuccess, $refPlan, $impactedElements, $pastedElements, $failedToPaste);
                        }
                        else return prepareCopyReturn($options, $operationSuccess, $account, $impactedElements, $pastedElements, $failedToPaste);

                        if($refElement['code'] != '4002' && preg_match('/^4/', $refElement['code'])) //l'élément est un dossier non vide
                        {
                            $serverPath = $element->getServerPath().$element->getName().'/';

                            //récupération des éléments contenus dans le dossier
                            $seekElementsInFolder = array(
                                'state' => (int)1,
                                'serverPath' => new MongoRegex("/^$serverPath/i"),
                                'idOwner' => $idUser
                            );

                            $elementsInFolder = $elementPdoManager->find($seekElementsInFolder);
                        }

                        if(isset($elementsInFolder) && !(array_key_exists('error', $elementsInFolder)))
                            $impactedElements = $elementsInFolder;

                        $impactedElements[] = $element;

                        $totalSize = sumSize($impactedElements); //calcul de la taille du contenu

                        if($currentUserStorage + $totalSize <= $maxStorageAllowed) //copie autorisée
                        {
                            $count = 0;

                            foreach($impactedElements as $key => $impactedElement)
                            {
                                //préparation de la copie
                                $elementCopy = clone $impactedElement;
                                $elementCopy->setId(new MongoId());

                                if(count($impactedElements) != $key+1)
                                {$explode = explode($serverPath, $elementCopy->getServerPath());
                                    if(isset($explode[1]) && $explode[1] != '')
                                    {
                                        $elementPath = $path.$elementNameInDestination.'/'.$explode[1];
                                        $elementCopy->setServerPath($elementPath);
                                    }
                                    else
                                        $elementCopy->setServerPath($path.$elementNameInDestination.'/');
                                }
                                else
                                {
                                    $elementCopy->setName($elementNameInDestination);
                                    $elementCopy->setServerPath($path);
                                }

                                $elementCopy->setDownloadLink('');

                                //insertion de la copie
                                $copyResult = $elementPdoManager->create($elementCopy);

                                //gestion des erreurs

                                if(!(is_bool($copyResult))) //erreur
                                {
                                    $failedToPaste[$count]['elementToCopy'] = $impactedElement;
                                    $failedToPaste[$count]['elementCopy'] = $elementCopy;
                                    $failedToPaste[$count]['error'] = $copyResult['error'];
                                    $count++;
                                }
                                elseif($copyResult == TRUE)
                                    $pastedElements[] = $elementCopy;
                            }

                            if($totalSize > 0)
                            {
                                $updateCriteria = array(
                                    '_id' => $account['_id'],
                                    'state' => (int)1
                                );
                                $storageUpdate = array('$inc' => array('storage' => $totalSize));
                                $accountUpdate = $accountPdoManager->update($updateCriteria, $storageUpdate);

                                if(is_array($accountUpdate) && array_key_exists('error', $accountUpdate))
                                {
                                    $errorMessage = 'Error when trying to add '.$totalSize.' to user account';
                                    return prepareCopyReturn($options, $operationSuccess, $errorMessage, $impactedElements, $pastedElements, $failedToPaste);
                                }
                            }

                            // Lors de copie dans un dossier, on vérifie si le dossier était empty. Au quel cas on le passe à NotEmpty
                            updateFolderStatus($path, $idUser);

                            if(array_key_exists('keepRights', $options) && $options['keepRights'] == TRUE)
                                copyRights($impactedElements, $pastedElements);

                            $operationSuccess = TRUE;

                            return prepareCopyReturn($options, $operationSuccess, array(), $impactedElements, $pastedElements, $failedToPaste);

                        } //pas assez d'espace
                        else
                        {
                            $errorMessage = 'Not enough space available for your account to proceed action';
                            return prepareCopyReturn($options, $operationSuccess, $errorMessage, $impactedElements, $pastedElements, $failedToPaste);
                        }
                    }
                    else return prepareCopyReturn($options, $operationSuccess, $elementNameInDestination, $impactedElements, $pastedElements, $failedToPaste);
                }
                else return prepareCopyReturn($options, $operationSuccess, array('error' => 'Element inactivated, nothing to do'), $impactedElements, $pastedElements, $failedToPaste);
            }
            else return prepareCopyReturn($options, $operationSuccess, $element, $impactedElements, $pastedElements, $failedToPaste);
        }
        else return prepareCopyReturn($options, $operationSuccess, array('error' => 'Access denied'), $impactedElements, $pastedElements, $failedToPaste);
    }
    else return prepareCopyReturn($options, $operationSuccess, $hasRight, $impactedElements, $pastedElements, $failedToPaste);
}

/**
 * Prépare le retour de la fonction renameHandler.
 * On pourrait se servir de prepareMoveReturn étant donné que la structure est similaire, mais en faisant une autre
 * fonction, on peut différencier les indexes, les messages, etc et être mieux préparé à de possibles modifications.
 * @author Alban Truc
 * @param array $options
 * @param bool $operationSuccess
 * @param string|array $error
 * @param array $elementsImpacted
 * @param array $updatedElements
 * @param array $failedToUpdate
 * @return array
 */

function prepareRenameReturn($options, $operationSuccess, $error, $elementsImpacted, $updatedElements, $failedToUpdate)
{
    $return = array();

    $return['operationSuccess'] = $operationSuccess;

    if(!(empty($error)))
    {
        if(is_array($error) && array_key_exists('error', $error))
            $return['error'] = $error['error'];
        else
            $return['error'] = $error;
    }

    if(is_array($options))
    {
        if(array_key_exists('returnImpactedElements', $options) && $options['returnImpactedElements'] == TRUE)
        {
            if(empty($elementsImpacted))
                $return['elementsImpacted'] = 'No impacted element or the function had an error before the element(s) got retrieved.';
            else
                $return['elementsImpacted'] = $elementsImpacted;
        }

        if(array_key_exists('returnUpdatedElements', $options) && $options['returnUpdatedElements'] == TRUE)
        {
            if(empty($updatedElements))
                $return['updatedElements'] = 'No updated element or the function had an error before trying to.';
            else
                $return['updatedElements'] = $updatedElements;

            if(empty($failedToUpdate))
                $return['failedToUpdate'] = 'No fail or the function had an error before trying to.';
            else
                $return['failedToUpdate'] = $failedToUpdate;
        }
    }
    return $return;
}

/**
 * Renomme un élément et met à jour le serverPath de ses enfants dans le cas d'un dossier
 * $options est un tableau de booléens avec comme indexes possibles:
 * - returnImpactedElements à true pour retourner les éléments à modifier
 * - returnUpdatedElements à true pour retourner les éléments modifiés
 * On peut se retrouver avec la structure de retour suivante:
 *  array(
 *          'operationSuccess' => true ou false,
 *          'error' => 'message d'erreur',
 *          'impactedElements' => array(),
 *          'updatedElements' => array(),
 *          'failedToUpdate' => array()
 *  )
 * @author Alban Truc
 * @param string|MongoId $idElement
 * @param string|MongoId $idUser
 * @param string $newName
 * @param array $options
 * @return array
 */

function renameHandler($idElement, $idUser, $newName, $options = array())
{
    $idElement = new MongoId($idElement);
    $idUser = new MongoId($idUser);

    $impactedElements = array();
    $updatedElements = array();
    $failedToUpdate = array();

    $operationSuccess = FALSE;

    /*
     * 11 correspond au droit de lecture et écriture.
     * Si on souhaite accepter la copie avec des droits de plus bas niveau, il suffit d'ajouter les codes correspondant
     * au tableau en 3e paramètre ci-dessous.
     */

    $hasRight = actionAllowed($idElement, $idUser, array('11'));

    if(!(is_array($hasRight)))
    {
        if($hasRight === TRUE)
        {
            //récupère l'élément
            $elementPdoManager = new ElementPdoManager();
            $element = $elementPdoManager->findById($idElement);

            if($element instanceof Element)
            {
                if($element->getState() == 1)
                {
                    $criteria = array(
                        'state' => (int)1,
                        'name' => $newName,
                        'serverPath' => $element->getServerPath(),
                        'idOwner' => $idUser
                    );

                    $elementsWithSameName = $elementPdoManager->find($criteria);

                    if(array_key_exists('error', $elementsWithSameName))
                    {
                        if($elementsWithSameName['error'] != 'No match found.')
                            return $elementsWithSameName;
                    }
                    else
                        return array('error' => 'There is already an element with this name.');


                    //File Server -- 13/06/2014
                    $refElementPdoManager = new RefElementPdoManager();
                    $refElementFieldsToReturn = array('code' => TRUE, 'extension' => TRUE);
                    $refElement = $refElementPdoManager->findById($element->getRefElement(), $refElementFieldsToReturn);

                    if(array_key_exists('error', $refElement))
                        return $refElement;

                    if(preg_match('/^4/', $refElement['code']) || preg_match('/^9/', $refElement['code'])) // dossier ou non reconnu, pas d'extension à rajouter
                    {
                        $oldCompleteName = $element->getName();
                        $newCompleteName = $newName;
                    }
                    else
                    {
                        $oldCompleteName = $element->getName().$refElement['extension'];
                        $newCompleteName = $newName.$refElement['extension'];
                    }

                    $FSRenameResult = renameFSElement($idUser, $element->getServerPath(), $oldCompleteName, $newCompleteName);

                    if(!(is_bool($FSRenameResult)) || $FSRenameResult != TRUE)
                        return $FSRenameResult;
                    //Fin File Server

                    if($refElement['code'] != '4002' && (preg_match('/^4/', $refElement['code']))) //pas un dossier vide
                    {
                        $serverPath = $element->getServerPath().$element->getName().'/';

                        //récupération des éléments contenus dans le dossier
                        $seekElementsInFolder = array(
                            'state' => (int)1,
                            'serverPath' => new MongoRegex("/^$serverPath/i"),
                            'idOwner' => $idUser
                        );

                        $elementsInFolder = $elementPdoManager->find($seekElementsInFolder);
                    }

                    if(isset($elementsInFolder) && !(array_key_exists('error', $elementsInFolder)))
                        $impactedElements = $elementsInFolder;

                    $impactedElements[] = $element;

                    $count = 0;

                    foreach($impactedElements as $key => $impactedElement)
                    {
                        $updateCriteria = array(
                            '_id' => $impactedElement->getId(),
                            'state' => (int)1
                        );
                        //préparation de la copie
                        $elementCopy = clone $impactedElement;

                        if(count($impactedElements) != $key+1)
                        {
                            $impactedElementPath = $impactedElement->getServerPath();
                            $newPath = preg_replace('/'.$element->getName().'/i', $newName, $impactedElementPath);
                            $elementCopy->setServerPath($newPath);
                        }
                        else
                            $elementCopy->setName($newName);

                        //mise à jour
                        $updateResult = $elementPdoManager->update($updateCriteria, $elementCopy);

                        //gestion des erreurs

                        if(!(is_bool($updateResult))) //erreur
                        {
                            $failedToUpdate[$count]['elementToUpdate'] = $impactedElement;
                            $failedToUpdate[$count]['elementUpdated'] = $elementCopy;
                            $failedToUpdate[$count]['error'] = $updateResult['error'];
                            $count++;
                        }
                        elseif($updateResult == TRUE)
                            $updatedElements[] = $elementCopy;
                    }

                    $operationSuccess = TRUE;

                    return prepareRenameReturn($options, $operationSuccess, array(), $impactedElements, $updatedElements, $failedToUpdate);
                }
                else return prepareRenameReturn($options, $operationSuccess, array('error' => 'Element inactivated, nothing to do'), $impactedElements, $updatedElements, $failedToUpdate);
            }
            else return prepareRenameReturn($options, $operationSuccess, $element, $impactedElements, $updatedElements, $failedToUpdate);
        }
        else return prepareRenameReturn($options, $operationSuccess, array('error' => 'Access denied'), $impactedElements, $updatedElements, $failedToUpdate);
    }
    else return prepareRenameReturn($options, $operationSuccess, $hasRight, $impactedElements, $updatedElements, $failedToUpdate);
}

/**
 * Prépare le retour dela fonction moveHandler
 * @author Alban Truc
 * @param array $options
 * @param bool $operationSuccess
 * @param string|array $error
 * @param array $elementsImpacted
 * @param array $movedElements
 * @param array $failedToMove
 * @return array
 */

function prepareMoveReturn($options, $operationSuccess, $error, $elementsImpacted, $movedElements, $failedToMove)
{
    $return = array();

    $return['operationSuccess'] = $operationSuccess;

    if(!(empty($error)))
    {
        if(is_array($error) && array_key_exists('error', $error))
            $return['error'] = $error['error'];
        else
            $return['error'] = $error;
    }

    if(is_array($options))
    {
        if(array_key_exists('returnImpactedElements', $options) && $options['returnImpactedElements'] == TRUE)
        {
            if(empty($elementsImpacted))
                $return['elementsImpacted'] = 'No impacted element or the function had an error before the element(s) got retrieved.';
            else
                $return['elementsImpacted'] = $elementsImpacted;
        }

        if(array_key_exists('returnMovedElements', $options) && $options['returnMovedElements'] == TRUE)
        {
            if(empty($movedElements))
                $return['movedElements'] = 'No moved element or the function had an error before trying to.';
            else
                $return['movedElements'] = $movedElements;

            if(empty($failedToMove))
                $return['failedToMove'] = 'No fail or the function had an error before trying to.';
            else
                $return['failedToMove'] = $failedToMove;
        }
    }
    return $return;
}

/**
 * Déplace l'élément (et ce qu'il contient dans le cas d'un dossier) dans la destination indiquée.
 * $options est un tableau de booléens avec comme indexes possibles:
 * - returnImpactedElements à true pour retourner les éléments à déplacer
 * - returnMovedElements à true pour retourner les éléments déplacés
 * - keepRights à false pour ne pas conserver les droits sur les éléments
 * - keepDownloadLinks à false pour ne pas conserver les liens de téléchargement
 * On peut se retrouver avec la structure de retour suivante:
 *  array(
 *          'operationSuccess' => true ou false,
 *          'error' => 'message d'erreur',
 *          'impactedElements' => array(),
 *          'movedElements' => array(),
 *          'failedToMove' => array()
 *  )
 * @author Alban Truc
 * @param string|MongoId $idElement
 * @param string|MongoId $idUser
 * @param string $path
 * @param array $options
 * @since 08/06/2014
 * @return array
 * @todo mêmes améliorations que pour la fonction copyHandler
 */

function moveHandler($idElement, $idUser, $path, $options = array())
{
    $idElement = new MongoId($idElement);
    $idUser = new MongoId($idUser);

    $impactedElements = array();
    $movedElements = array();
    $failedToMove = array();

    $operationSuccess = FALSE;

    /*
     * 11 correspond au droit de lecture et écriture.
     * Si on souhaite accepter la copie avec des droits de plus bas niveau, il suffit d'ajouter les codes correspondant
     * au tableau en 3e paramètre ci-dessous.
     */

    $hasRight = actionAllowed($idElement, $idUser, array('11'));

    if(!(is_array($hasRight)))
    {
        if($hasRight === TRUE)
        {
            //récupère l'élément
            $elementPdoManager = new ElementPdoManager();
            $element = $elementPdoManager->findById($idElement);

            if($element instanceof Element)
            {
                if($element->getState() == 1)
                {
                    if($path != $element->getServerPath())
                    {
                        $elementCriteria = array(
                            'state' => (int)1,
                            'idOwner' => $idUser
                        );

                        /*
                         * extraction de l'emplacement du dossier de destination à partir de $path
                         * @see http://www.php.net/manual/en/function.implode.php
                         * @see http://www.php.net/manual/en/function.explode.php
                         */
                        $destinationFolderPath = implode('/', explode('/', $path, -2)).'/';
                        $elementCriteria['serverPath'] = $destinationFolderPath;

                        /**
                         * la racine n'ayant pas d'enregistrement pour elle même, on a un serverPath "/" mais de nom.
                         * il faut donc distinguer les cas de copies d'un élément dans la racine des autres cas.
                         */
                        if($path != "/")
                        {
                            /*
                         * extraction du nom du dossier de destination à partir du $path
                         * @see http://www.php.net/manual/en/function.array-slice.php
                         */
                            $destinationFolderName = implode(array_slice(explode('/', $path), -2, 1));
                            $elementCriteria['name'] = $destinationFolderName;

                            //récupération de l'id de l'élément en base correspondant au dossier de destination
                            $idDestinationFolder = $elementPdoManager->findOne($elementCriteria, array('_id' => TRUE));

                            if((array_key_exists('error', $idDestinationFolder)))
                                return prepareMoveReturn($options, $operationSuccess, $idDestinationFolder, $impactedElements, $movedElements, $failedToMove);
                            else
                            {
                                //vérification des droits dans la destination
                                $hasRightOnDestination = actionAllowed($idDestinationFolder['_id'], $idUser, array('11'));

                                if(is_array($hasRightOnDestination) && array_key_exists('error', $hasRightOnDestination))
                                    return prepareMoveReturn($options, $operationSuccess, $hasRightOnDestination, $impactedElements, $movedElements, $failedToMove);
                                elseif($hasRightOnDestination == FALSE)
                                    return prepareMoveReturn($options, $operationSuccess, array('error' => 'Access denied in destination'), $impactedElements, $movedElements, $failedToMove);
                            }
                        }
                    }

                    $elementNameInDestination = avoidNameCollision($path, $element->getName(), $idUser);

                    if(is_string($elementNameInDestination))
                    {
                        //File Server 14/06/2014
                        $refElementPdoManager = new RefElementPdoManager();
                        $refElementFieldsToReturn = array('code' => TRUE, 'extension' => TRUE);
                        $refElement = $refElementPdoManager->findById($element->getRefElement(), $refElementFieldsToReturn);

                        if(array_key_exists('error', $refElement))
                            return $refElement;

                        //dossier ou non reconnu, pas d'extension à rajouter
                        if(preg_match('/^4/', $refElement['code']) || preg_match('/^9/', $refElement['code']))
                        {
                            $completeSourceName = $element->getName();
                            $completeDestinationName = $elementNameInDestination;
                        }
                        else
                        {
                            $completeSourceName = $element->getName().$refElement['extension'];
                            $completeDestinationName = $elementNameInDestination.$refElement['extension'];
                        }

                        $FSMoveResult = moveFSElement($idUser, $element->getServerPath(), $completeSourceName, $path, $completeDestinationName);

                        if(!(is_bool($FSMoveResult)) || $FSMoveResult != TRUE)
                            return $FSMoveResult;
                        //Fin File Server

                        if($refElement['code'] != '4002' && preg_match('/^4/', $refElement['code'])) //l'élément est un dossier non vide
                        {
                            $serverPath = $element->getServerPath().$element->getName().'/';

                            //récupération des éléments contenus dans le dossier
                            $seekElementsInFolder = array(
                                'state' => (int)1,
                                'serverPath' => new MongoRegex("/^$serverPath/i"),
                                'idOwner' => $idUser
                            );

                            $elementsInFolder = $elementPdoManager->find($seekElementsInFolder);
                        }

                        if(isset($elementsInFolder) && !(array_key_exists('error', $elementsInFolder)))
                            $impactedElements = $elementsInFolder;

                        $impactedElements[] = $element;

                        $count = 0;

                        foreach($impactedElements as $key => $impactedElement)
                        {
                            $updateCriteria = array(
                                '_id' => $impactedElement->getId(),
                                'state' => (int)1
                            );
                            //préparation de la copie
                            $elementCopy = clone $impactedElement;

                            if(count($impactedElements) != $key+1)
                            {
                                $explode = explode($serverPath, $elementCopy->getServerPath());
                                if(isset($explode[1]) && $explode[1] != '')
                                {
                                    $elementPath = $path.$elementNameInDestination.'/'.$explode[1];
                                    $elementCopy->setServerPath($elementPath);
                                }
                                else
                                    $elementCopy->setServerPath($path.$elementNameInDestination.'/');
                            }
                            else
                            {
                                $elementCopy->setName($elementNameInDestination);
                                $elementCopy->setServerPath($path);
                            }

                            if(array_key_exists('keepDownloadLinks', $options) && $options['keepDownloadLinks'] == FALSE)
                                $elementCopy->setDownloadLink('');

                            //mise à jour
                            $updateResult = $elementPdoManager->update($updateCriteria, $elementCopy);

                            //gestion des erreurs

                            if(!(is_bool($updateResult))) //erreur
                            {
                                $failedToPaste[$count]['elementToMove'] = $impactedElement;
                                $failedToPaste[$count]['elementMoved'] = $elementCopy;
                                $failedToPaste[$count]['error'] = $updateResult['error'];
                                $count++;
                            }
                            elseif($updateResult == TRUE)
                                $movedElements[] = $elementCopy;
                        }

                        /*
                         * Si le déplacement vide un dossier ou rempli un dossier qui était vide,
                         * on met à jour son refElement
                         */
                        updateFolderStatus($path, $idUser);
                        updateFolderStatus($element->getServerPath(), $idUser);

                        if(array_key_exists('keepRights', $options) && $options['keepRights'] == FALSE)
                            disableRights($impactedElements);

                        $operationSuccess = TRUE;

                        return prepareMoveReturn($options, $operationSuccess, array(), $impactedElements, $movedElements, $failedToMove);
                    }
                    else return prepareMoveReturn($options, $operationSuccess, $elementNameInDestination, $impactedElements, $movedElements, $failedToMove);
                }
                else return prepareMoveReturn($options, $operationSuccess, array('error' => 'Element inactivated, nothing to do'), $impactedElements, $movedElements, $failedToMove);
            }
            else return prepareMoveReturn($options, $operationSuccess, $element, $impactedElements, $movedElements, $failedToMove);
        }
        else return prepareMoveReturn($options, $operationSuccess, array('error' => 'Access denied'), $impactedElements, $movedElements, $failedToMove);
    }
    else return prepareMoveReturn($options, $operationSuccess, $hasRight, $impactedElements, $movedElements, $failedToMove);
}

/**
 * Crée un nouveau dossier vierge à l'emplacement voulu.
 * @author Alban Truc
 * @param string|MongoId $idUser
 * @param string $path
 * @param string $folderName
 * @param bool $inheritRightsFromParent
 * @since 09/06/2014
 * @return array|TRUE
 */

function createNewFolder($idUser, $path, $folderName, $inheritRightsFromParent)
{
    $idUser = new MongoId($idUser);

    $operationSuccess = FALSE;

    $elementPdoManager = new ElementPdoManager();

    if($path != '/')
    {
        //récupération du dossier parent
        $explode = explode('/', $path);
        $parentDirectoryName = $explode[sizeof($explode) - 2];
        $parentDirectoryPath = implode('/', array_slice($explode, 0, sizeof($explode) - 2)).'/';

        $parentElementCriteria = array(
            'state' => (int)1,
            'name' => $parentDirectoryName,
            'serverPath' => $parentDirectoryPath,
            'idOwner' => $idUser
        );

        $parentElement = $elementPdoManager->findOne($parentElementCriteria);

        if($parentElement instanceof Element)
        {
            /*
             * 11 correspond au droit de lecture et écriture.
             * Si on souhaite accepter la copie avec des droits de plus bas niveau, il suffit d'ajouter les codes correspondant
             * au tableau en 3e paramètre ci-dessous.
             */

            $hasRight = actionAllowed($parentElement->getId(), $idUser, array('11'));

            if(is_bool($hasRight) && $hasRight == FALSE)
                return array('error' => 'Creation not allowed.');
            elseif(is_array($hasRight))
                return $hasRight;
        }
        else return $parentElement;
    }

    //vérification qu'il n'existe pas déjà un dossier avec le même nom
    $elementCriteria = array(
        'state' => (int)1,
        'name' => $folderName,
        'serverPath' => $path,
        'idOwner' => $idUser
    );

    $elementsWithSameName = $elementPdoManager->find($elementCriteria);

    if(is_array($elementsWithSameName) && array_key_exists('error', $elementsWithSameName))
    {
        if($elementsWithSameName['error'] != 'No match found.')
            return $elementsWithSameName;
    }
    else
    {
        foreach($elementsWithSameName as $key => $elementWithSameName)
        {
            $isFolder = isFolder($elementWithSameName->getRefElement());
            if(is_bool($isFolder))
            {
                if($isFolder == FALSE)
                {
                    unset($elementsWithSameName[$key]);
                }
            }
            else return $isFolder;
        }

        if(!(empty($elementsWithSameName)))
            return array('error' => 'Folder name not available.');
    }

    //File Server - 13/06/2014
    $mkdirResult = createFSDirectory($idUser, $path, $folderName);

    if(!(is_bool($mkdirResult)) || !($mkdirResult == TRUE))
        return $mkdirResult;
    //Fin File Server

    //Récupération de l'id de RefElement dossier vide
    $refElementPdoManager = new RefElementPdoManager();
    $emptyFolder = $refElementPdoManager->findOne(array('state' => 1, 'code' => '4002'), array('_id' => TRUE));

    $newFolder = array(
        'state' => 1,
        'name' => $folderName,
        'idOwner' => $idUser,
        'idRefElement' => $emptyFolder['_id'],
        'serverPath' => $path
    );

    $insertResult = $elementPdoManager->create($newFolder);

    if(is_bool($insertResult))
    {
        if($insertResult == TRUE)
        {
            //Le dossier parent était vide
            if(isset($parentElement))
            {
                if($parentElement->getRefElement() == $emptyFolder['_id'])
                {
                    $parentElementCriteria = array(
                        '_id' => $parentElement->getId()
                    );
                    //on change l'id du dossier parent pour dossier non vide
                    $notEmptyFolder = $refElementPdoManager->findOne(array('state' => 1, 'code' => '4003'), array('_id' => TRUE));
                    $update = array(
                        '$set' => array(
                            'idRefElement' => $notEmptyFolder['_id']
                        )
                    );

                    //dans le cas où on voudrait récupérer le dossier parent mis à jour, on peut utiliser $updatedFolder
                    $updatedFolder = $elementPdoManager->findAndModify($parentElementCriteria, $update, array('new' => TRUE));
                    if($updatedFolder instanceof Element)
                        $operationSuccess = TRUE;
                }

                if($inheritRightsFromParent == TRUE)
                {
                    //récupération des droits appliqués sur le dossier parent
                    $rightPdoManager = new RightPdoManager();

                    $rightCriteria = array(
                        'state' => 1,
                        'idElement' => $parentElement->getId()
                    );

                    $rights = $rightPdoManager->find($rightCriteria);

                    if(!(array_key_exists('error', $rights)))
                    {
                        //récupération du dossier précédemment inséré
                        $newElement = $elementPdoManager->findOne($newFolder);

                        if($newElement instanceof Element)
                        {
                            $insertRightCopy = array();
                            foreach($rights as $right)
                            {
                                $rightCopy = clone $right;
                                $rightCopy->setId(new MongoId());
                                $rightCopy->setElement($newElement->getId());

                                $insertRightCopy[] = $elementPdoManager->create($rightCopy);
                                //on pourrait se servir de $insertRightCopy pour identifier les erreurs éventuelles
                            }
                            //@todo vérifier que tous les insertRightCopy sont OK et si c'est le cas operationSuccess = TRUE
                            $operationSuccess = TRUE;
                        }
                        else return $newElement;
                    }
                }
            }

            $operationSuccess = TRUE;
            return $operationSuccess;
        }
        else return array('error' => 'Could not create folder in database.');
    }
    else return $insertResult;
}

/**
 * Détermine à partir d'une extension le content-type pour un téléchargement
 * @author Alban Truc
 * @param string $extension
 * @since 15/06/2014
 * @return string
 */

function getContentType($extension)
{
    switch (strtolower($extension))
    {
        case 'pdf':
            $contentType = 'application/pdf';
            break;
        case 'exe':
            $contentType = 'application/octet-stream';
            break;
        case 'zip':
            $contentType = 'application/zip';
            break;
        case 'doc':
            $contentType = 'application/msword';
            break;
        case 'xls':
            $contentType = 'application/vnd.ms-excel';
            break;
        case 'ppt':
            $contentType = 'application/vnd.ms-powerpoint';
            break;
        case 'gif':
            $contentType = 'image/gif';
            break;
        case 'png':
            $contentType = 'image/png';
            break;
        case 'jpeg':
        case 'jpg':
        $contentType = 'image/jpg';
            break;
        case 'mkv':
            $contentType = 'video/x-matroska';
            break;
        default: $contentType = 'application/force-download';
    }

    return $contentType;
}

/**
 * @todo vérification du ratio (suffisant ou non pour autoriser le téléchargement)
 * @todo support de lourds fichiers
 * @author Alban Truc
 * @param string|MongoId $idUser
 * @param string|MongoId $idElement
 * @since 15/06/2014
 * @return array
 */

function userDownload($idUser, $idElement)
{
    $idUser = new MongoId($idUser);
    $idElement = new MongoId($idElement);

    $elementPdoManager = new ElementPdoManager();

    $elementCriteria = array(
        'state' => (int)1,
        '_id' => $idElement
    );

    $element = $elementPdoManager->findOne($elementCriteria);

    if(!($element instanceof Element))
        return $element;

    //récupération de la vitesse de téléchargement de l'utilisateur
    $accountPdoManager = new AccountPdoManager();
    $accountCriteria = array(
        'state' => 1,
        'idUser' => $idUser
    );

    $account = $accountPdoManager->findOne($accountCriteria);

    if(!($account instanceof Account))
        return $account;

    $refPlanPdoManager = new RefPlanPdoManager();
    $refPlan = $refPlanPdoManager->findById($account->getRefPlan());

    if(!($refPlan instanceof RefPlan))
        return $refPlan;

    $downloadSpeed = $refPlan->getDownloadSpeed();
    //return $downloadSpeed;
    //récupère le code et l'extension de notre élément
    $refElementPdoManager = new RefElementPdoManager();

    $fieldsToReturn = array('code' => TRUE, 'extension' => TRUE);
    $refElement = $refElementPdoManager->findById($element->getRefElement(), $fieldsToReturn);

    if(!(array_key_exists('error', $refElement)))
    {
        if(preg_match('/^4/', $refElement['code']) || preg_match('/^9/', $refElement['code'])) // dossier ou non reconnu, pas d'extension à rajouter
            return array('error' => 'Donwload not available on folder or unrecognized element');
    }
    else return $refElement;

    // 01 correspond au droit de lecture.
    $hasRight = actionAllowed($idElement, $idUser, array('01'));

    if(is_bool($hasRight) && $hasRight == FALSE)
        return array('error' => 'You are not allowed to download this file.');
    elseif(is_array($hasRight))
        return $hasRight;

    $filePath = PATH.$idUser.$element->getServerPath();
    $fileName = $element->getName().$refElement['extension'];
    $fullFilePath = $filePath.$fileName;

    $fileSize = round($element->getSize() * 1024);

    set_time_limit(0);

    if($fd = fopen($fullFilePath, 'r'))
    {
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header("Content-Transfer-Encoding: binary");
        header("Content-length: $fileSize");

        $fileExtension = pathinfo($fullFilePath, PATHINFO_EXTENSION);

        //déterminer le Content-Type
        $ctype = getContentType($fileExtension);

    //nécessite http://pecl.php.net/package/pecl_http
    /*
    http_send_content_disposition($fileName);
    http_send_content_type($ctype);
    http_throttle(0.1, $downloadSpeed * 1024);
    http_send_file($fullFilePath);
    */
        header("Content-Type: $ctype");

        $file = @fopen($fullFilePath, 'rb');
        if($file)
        {
            while(!(feof($file)))
            {
                print(fread($file, 1024 * $downloadSpeed));
                flush();
                usleep(500);
                if(connection_status() != 0)
                {
                    @fclose($file);
                    die();
                }
            }
            @fclose($file);
        }
    }
}

/**
 * @todo vérification du ratio du propriétaire (suffisant ou non pour autoriser le téléchargement)
 * @todo support de lourds fichiers
 * @author Alban Truc
 * @param $token
 * @param int $downloadSpeed par défaut 100 KB/s
 * @since 15/06/2014
 * @return array
 */

function anonymousDownload($token, $downloadSpeed = 102400)
{
    if($token == '')
        return array('error' => 'Invalid link.');

    $elementPdoManager = new ElementPdoManager();

    $elementCriteria = array(
        'state' => (int)1,
        'downloadLink' => $token
    );

    $element = $elementPdoManager->findOne($elementCriteria);

    if(!($element instanceof Element))
        return $element;

    //récupère le code et l'extension de notre élément
    $refElementPdoManager = new RefElementPdoManager();

    $fieldsToReturn = array('code' => TRUE, 'extension' => TRUE);
    $refElement = $refElementPdoManager->findById($element->getRefElement(), $fieldsToReturn);

    if(!(array_key_exists('error', $refElement)))
    {
        if(preg_match('/^4/', $refElement['code']) || preg_match('/^9/', $refElement['code'])) // dossier ou non reconnu, pas d'extension à rajouter
            return array('error' => 'Donwload not available on folder or unrecognized element');
    }
    else return $refElement;

    $filePath = PATH.$element->getOwner().$element->getServerPath();
    $fileName = $element->getName().$refElement['extension'];
    $fullFilePath = $filePath.$fileName;

    $fileSize = round($element->getSize() * 1024);

    set_time_limit(0);

    if($fd = fopen($fullFilePath, 'r'))
    {
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header("Content-Transfer-Encoding: binary");
        header("Content-length: $fileSize");

        $fileExtension = pathinfo($fullFilePath, PATHINFO_EXTENSION);

        //déterminer le Content-Type
        $ctype = getContentType($fileExtension);

        header("Content-Type: $ctype");

        $file = @fopen($fullFilePath, 'rb');
        if($file)
        {
            while(!(feof($file)))
            {
                print(fread($file, 1024 * $downloadSpeed));
                flush();
                usleep(500);
                if(connection_status() != 0)
                {
                    @fclose($file);
                    die();
                }
            }
            @fclose($file);
        }
    }
}