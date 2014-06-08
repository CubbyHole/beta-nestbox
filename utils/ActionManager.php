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
 * @since 04/06/2014
 * @return array|bool
 * @todo employer cette fonction dans la fonction disableHandler
 */

function isFolder($refElementId)
{
    $refElementId = new MongoId($refElementId);

    //récupère le code du refElement de notre élément
    $refElementPdoManager = new RefElementPdoManager();
    $refElement = $refElementPdoManager->findById($refElementId, array('code' => TRUE));

    if(!(array_key_exists('error', $refElement)))
    {
        //si le code commence par un 4 (les codes de dossier commencent par un 4)
        if(preg_match('/^4/', $refElement['code']))
            return TRUE;
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
 * @since 07/06/2014
 * @return bool|true si update ok
 */

function updateFolderStatus($serverPath)
{
    $elementPdoManager = new ElementPdoManager();
    $refElementPdoManager = new RefElementPdoManager();

    if($serverPath != '/')
    {
        // on vérifie si il reste des éléments actifs dans le dossier ou l'on désactive l'élément
        $findElement = $elementPdoManager->find(array("serverPath" => $serverPath, "state" => 1));

        // si il n'y en a plus alors on passe le dossier courant à empty
        if(array_key_exists('error', $findElement))
        {
            if($findElement['error'] == 'No match found.')
            {
                $refElement = $refElementPdoManager->findOne(array(
                    'code' => '4002',
                    'state' => 1
                ));
            }
            else
                return $findElement;
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
            $path = preg_replace($pattern, "", $serverPath,1);

            // on réalise un update sur le dossier en question pour modifier son refElement (à Directory File Empty)
            $criteria = array('name' => $directoryCurrent, 'serverPath' => $path, 'state' => 1);
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
 * @param Element $element
 * @since 07/06/2014
 * @return array|Element[]|string
 */

function avoidNameCollision($path, $element)
{
    if($element instanceof Element)
    {
        $elementPdoManager = new ElementPdoManager();

        //un élément avec le même nom n'est-il pas déjà présent?
        $seekForNameDuplicate = array(
            'state' => (int)1,
            'serverPath' => $path,
            'name' => $element->getName()
        );

        $elementsWithSameName = $elementPdoManager->find($seekForNameDuplicate);
        //var_dump($elementsWithSameName);

        if(array_key_exists('error', $elementsWithSameName))
        {
            //cas no match found => pas d'élément avec le même nom à l'emplacement de destination
            if($elementsWithSameName['error'] == 'No match found.')
            {
                $elementNameInDestination = $element->getName();
            }
            else return $elementsWithSameName;
        }
        else //nom déjà utilisé
        {
            //existe-t-il déjà des copies?
            $seekForCopies = array(
                'state' => (int)1,
                'serverPath' => $path,
                'name' => new MongoRegex("/^".$element->getName()." - Copy/i")
            );

            $duplicate = $elementPdoManager->find($seekForCopies, array('name' => TRUE, '_id' => FALSE));
            //var_dump($duplicate);

            if(array_key_exists('error', $duplicate))
            {
                //cas où il n'y a pas de copie
                if($duplicate['error'] == 'No match found.')
                {
                    $elementNameInDestination = $element->getName().' - Copy';
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
                if(!(in_array($element->getName().' - Copy', $duplicate)))
                    $elementNameInDestination = $element->getName().' - Copy';
                else
                {
                    /**
                     * @see http://www.php.net/manual/en/function.unset.php
                     * @see http://www.php.net/manual/en/function.array-search.php
                     * Supprime dans le tableau la valeur correspondant à
                     * $element->getName().' - Copy' pour simplifier les opérations suivantes
                     */
                    unset($duplicate[array_search($element->getName().' - Copy', $duplicate)]);

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
                            if($duplicate[$count]==$element->getName().' - Copy ('.$copyNumberIndex.')')
                            {
                                $copyNumberIndex++;
                            }
                            $count++;
                        }
                    }
//                    var_dump($copyNumberIndex);
                    $elementNameInDestination = $element->getName().' - Copy ('.$copyNumberIndex.')';
//                    var_dump($elementNameInDestination); exit();
                }
            }
        }
        return $elementNameInDestination;
    }
    else
        return array('error' => 'Object Element required');
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

    if(is_array($error) && array_key_exists('error', $error))
        $return['error'] = $error['error'];
    else
        $return['error'] = $error;

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
 * @todo appel de la fonction qui fait diverses tâches (cf. documentation) sur le serveur de fichier
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
                    $refElement = $refElementPdoManager->findById($element->getRefElement(), array('code' => TRUE));

                    if(!(array_key_exists('error', $refElement)))
                    {
                        //si le code commence par un 4 (les codes de dossier commencent par un 4)
                        if(preg_match('/^4/', $refElement['code']))
                        {
                            $serverPath = $element->getServerPath().$element->getName().'/';

                            //notre criteria inclut tous les éléments se trouvant dans le dossier et ses dossiers enfants
                            $elementCriteria = array(
                                'state' => (int) 1,
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
                                $updateFolderStatus = updateFolderStatus($element->getServerPath());
                                if(is_bool($updateFolderStatus) && $updateFolderStatus === TRUE)
                                {
                                    //séparation en deux tableaux
                                    $idImpactedElements = array();
                                    $sizeImpactedElements = array();

                                    foreach($impactedElements as $impactedElement)
                                    {
                                        //création d'un tableau contenant uniquement les id des éléments impactés
                                        $idImpactedElements[] = $impactedElement['_id'];

                                        //création d'un tableau contenant uniquement la taille de chaque élément impacté
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
                        }

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

                    $elementNameInDestination = avoidNameCollision($path, $element);

                    if(is_string($elementNameInDestination))
                    {
                        $isElementAFolder = isFolder($element->getRefElement());

                        if(!(is_array($isElementAFolder))) //pas d'erreur
                        {
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

                            if($isElementAFolder == TRUE) //l'élément est un dossier
                            {
                                $serverPath = $element->getServerPath().$element->getName().'/';

                                //récupération des éléments contenus dans le dossier
                                $seekElementsInFolder = array(
                                    'state' => (int)1,
                                    'serverPath' => new MongoRegex("/^$serverPath/i")
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
                                updateFolderStatus($path);

                                if(array_key_exists('keepRights', $options) && $options['keepRights'] == TRUE)
                                    copyRights($impactedElements, $pastedElements);

                                //@todo copie sur le serveur de fichier

                                $operationSuccess = TRUE;

                                return prepareCopyReturn($options, $operationSuccess, array(), $impactedElements, $pastedElements, $failedToPaste);

                            } //pas assez d'espace
                            else
                            {
                                $errorMessage = 'Not enough space available for your account to proceed action';
                                return prepareCopyReturn($options, $operationSuccess, $errorMessage, $impactedElements, $pastedElements, $failedToPaste);
                            }
                        }
                        else return prepareCopyReturn($options, $operationSuccess, $isElementAFolder, $impactedElements, $pastedElements, $failedToPaste);
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


function renameHandler($idElement, $idUser, $name)
{
    $idElement = new MongoId($idElement);
    $idUser = new MongoId($idUser);

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

                }
            }

        }
        else return array('error' => 'Access denied');
    }
    else return $hasRight;
}

/**
 * Prépare le retour de la fonction moveHandler
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

    if(is_array($error) && array_key_exists('error', $error))
        $return['error'] = $error['error'];
    else
        $return['error'] = $error;

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
                        }

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

                    $elementNameInDestination = avoidNameCollision($path, $element);

                    if(is_string($elementNameInDestination))
                    {
                        $isElementAFolder = isFolder($element->getRefElement());

                        if(!(is_array($isElementAFolder))) //pas d'erreur
                        {
                            if($isElementAFolder == TRUE) //l'élément est un dossier
                            {
                                $serverPath = $element->getServerPath().$element->getName().'/';

                                //récupération des éléments contenus dans le dossier
                                $seekElementsInFolder = array(
                                    'state' => (int)1,
                                    'serverPath' => new MongoRegex("/^$serverPath/i")
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
                                updateFolderStatus($path);

                                if(array_key_exists('keepRights', $options) && $options['keepRights'] == FALSE)
                                    disableRights($impactedElements);

                                //@todo déplacement sur le serveur de fichier

                                $operationSuccess = TRUE;

                                return prepareMoveReturn($options, $operationSuccess, array(), $impactedElements, $movedElements, $failedToMove);

                        }
                        else return prepareMoveReturn($options, $operationSuccess, $isElementAFolder, $impactedElements, $movedElements, $failedToMove);
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