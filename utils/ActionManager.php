<?php
/**
 * Created by PhpStorm.
 * User: Crocell
 * Date: 31/05/14
 * Time: 22:33
 */

$projectRoot = $_SERVER['DOCUMENT_ROOT'].'/Nestbox';
require_once $projectRoot.'/required.php';

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
 * - returnPastedElements pour retourner les éléments copiés (ceux présent dans la destination)
 * - keepRights pour également copier les droits des éléments sources
 * Pour différencier les deux return, un tableau avec un indexe spécifique pour chaque (impactedElements et pastedElements)
 * est créé. On peut donc se retrouver avec cette structure:
 *  array('error' => 'message d'erreur', 'impactedElements' => array(), 'pastedElements' => array())
 * @param $idElement
 * @param $idUser
 * @param $path
 * @param array $options
 * @return array
 * @todo optimisation: découpage en plusieurs fonctions de moins de 80 lignes
 */

function copyHandler($idElement, $idUser, $path, $options = array())
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
                    //var_dump($element->getName());
                    //var_dump($element->getServerPath());
                    /*
                     * extraction de l'emplacement du dossier de destination à partir de $path
                     * @see http://www.php.net/manual/en/function.implode.php
                     * @see http://www.php.net/manual/en/function.explode.php
                     */
                    $destinationFolderPath = implode('/', explode('/', $path, -2)).'/';

                    /*
                     * extraction du nom du dossier de destination à partir du $path
                     * @see http://www.php.net/manual/en/function.array-slice.php
                     */
                    $destinationFolderName = implode(array_slice(explode('/', $path), -2, 1));

                    //récupération de l'id de l'élément en base correspondant au dossier de destination
                    $elementCriteria = array(
                        'state' => (int)1,
                        'name' => $destinationFolderName,
                        'serverPath' => $destinationFolderPath
                    );

                    $idDestinationFolder = $elementPdoManager->findOne($elementCriteria, array('_id' => TRUE));

                    if(!(array_key_exists('error', $idDestinationFolder)))
                    {
                        //vérification des droits dans la destination
                        $hasRightOnDestination = actionAllowed($idDestinationFolder['_id'], $idUser, array('11'));

                        if(!(is_array($hasRightOnDestination)))
                        {
                            if($hasRightOnDestination === TRUE)
                            {
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
                                    else return $elementsWithSameName; //autre erreur bloquante
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
                                        else return $duplicate; //autre erreur bloquante
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
                                        {
                                            $elementNameInDestination = $element->getName().' - Copy';
                                        }
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
                                            var_dump($duplicate);

                                            /*
                                             * déterminer quel nom du type elementName - Copy (number) est disponible,
                                             * avec number le plus proche possible de 0
                                             */

                                            //indexe pour le tableau duplicate
                                            $keyNumber = 0;

                                            //Le "number" dont il était question plus haut commence à 2
                                            $copyNumberIndex = 2;

                                            while($duplicate[$keyNumber] == $element->getName().' - Copy ('.$copyNumberIndex.')')
                                            {
                                                $keyNumber++;
                                                $copyNumberIndex++;
                                            }

                                            $elementNameInDestination = $element->getName().' - Copy ('.$copyNumberIndex.')';
                                            //var_dump($elementNameInDestination); exit();
                                        }
                                    }
                                }


                                //si user pas owner, copie de ses droits à lui dans la desti?
                            }
                            else return array('error' => 'Access denied in destination');
                        }
                        else return $hasRightOnDestination;
                    }
                    else return $idDestinationFolder;
                }
                else return array('error' => 'Element inactivated, nothing to do');
            }
            else return $element;
        }
        else return array('error' => 'Access denied');
    }
    else return $hasRight;
}

function renameHandler($idElement, $idUser, $name)
{
    $idElement = new MongoId($idElement);
    $idUser = new MongoId($idUser);

    // 11 correspond au droit de lecture et écriture
    $hasRight = actionAllowed($idElement, $idUser, '11');

    if(!(is_array($hasRight)))
    {
        if($hasRight === TRUE)
        {

        }
        else return array('error' => 'Access denied');
    }
    else return $hasRight;
}

function moveHandler($idElement, $idUser, $path)
{
    $idElement = new MongoId($idElement);
    $idUser = new MongoId($idUser);

    // 11 correspond au droit de lecture et écriture
    $hasRight = actionAllowed($idElement, $idUser, '11');

    if(!(is_array($hasRight)))
    {
        if($hasRight === TRUE)
        {

        }
        else return array('error' => 'Access denied');
    }
    else return $hasRight;
}