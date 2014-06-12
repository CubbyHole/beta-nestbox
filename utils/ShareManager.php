<?php
/**
 * Created by PhpStorm.
 * User: Crocell
 * Date: 10/06/14
 * Time: 17:26
 */

/*
 * NE PAS OUBLIER STATE => 1
 */

/*
 * USER
 */

/*
 * créer/modifier un partage avec un autre utilisateur
 */


function shareWithUser($idElement, $idOwner, $recipientEmail, $refRightCode, $sendMail = FALSE)
{
    $idElement = new MongoId($idElement);
    $idOwner = new MongoId($idOwner);

    $elementPdoManager = new ElementPdoManager();

    $elementCriteria = array(
        'state' => (int)1,
        '_id' => $idElement
    );

    $element = $elementPdoManager->findOne($elementCriteria);

    if($element instanceof ELement)
    {
        /*
         * vérification que l'idOwner en param de la fonction est le même que celui de l'element, la gestion des partages
         * n'étant dans cette version qu'accessible au propriétaire de l'élément
         */

        if($idOwner == $element->getOwner())
        {
            //vérification que l'email indiquée appartient bien à un utilisateur inscrit
            $userCriteria = array(
                'state' => (int)1,
                'email' => $recipientEmail
            );

            $userPdoManager = new UserPdoManager();
            $recipientUser = $userPdoManager->findOne($userCriteria);

            if($recipientUser instanceof User)
            {
                if($recipientUser->getId() != $idOwner)
                {
                    //récupérer l'id du refRight
                    $refRightCriteria = array(
                        'state' => (int)1,
                        'code' => $refRightCode
                    );

                    $refRightPdoManager = new RefRightPdoManager();
                    $refRight = $refRightPdoManager->findOne($refRightCriteria, array('_id' => TRUE));

                    if(is_array($refRight) && !(array_key_exists('error', $refRight)))
                    {
                        $rightList = array();

                        $refRightId = $refRight['_id'];

                        $newRight = array(
                            'state' => (int)1,
                            'idUser' => $recipientUser->getId(),
                            'idElement' => $idElement,
                            'idRefRight' => $refRightId
                        );

                        $rightList[] = $newRight;

                        /*
                         * vérification qu'il ne s'agit pas d'un dossier vide (inutile de chercher à copier le droit
                         * pour d'éventuels contenus sinon)
                         */
                        $isNonEmptyFolder = isFolder($element->getRefElement(), TRUE);

                        if(is_bool($isNonEmptyFolder))
                        {
                            if($isNonEmptyFolder == TRUE)
                            {
                                //récupération des éléments contenus dans le dossier
                                $folderPath = $element->getServerPath().$element->getName().'/';

                                $elementsInFolderCriteria = array(
                                    'state' => 1,
                                    'idOwner' => $idOwner,
                                    'serverPath' => new MongoRegex("/^$folderPath/i")
                                );

                                $elementsInFolder = $elementPdoManager->find($elementsInFolderCriteria);

                                if(is_array($elementsInFolder) && !(array_key_exists('error', $elementsInFolder)))
                                {
                                    foreach($elementsInFolder as $elementInFolder)
                                    {
                                        $rightCopy = $newRight;
                                        $rightCopy['idElement'] = $elementInFolder->getId();
                                        $rightList[] = $rightCopy;
                                    }
                                }
                                else return $elementsInFolder;
                            }
                        }
                        else return $isNonEmptyFolder;

                        /*
                         * Insertion ou mise à jour du droit en base. De fait cette fonction est utilisé pour la création
                         * et la mise à jour de droit.
                         */
                        $rightPdoManager = new RightPdoManager();

                        $rightCriteria = array(
                            'state' => (int)1,
                            'idUser' => $recipientUser->getId()
                        );

                        $options = array(
                            'upsert' => TRUE
                        );

                        foreach($rightList as $right)
                        {
                            $rightCriteria['idElement'] = $right['idElement'];
                            $rightPdoManager->update($rightCriteria, array('$set' => $right), $options);
                        }

                        //@todo envoyer un mail
                    }
                    else return $refRight;
                }
                else return array('error' => 'You cannot share an element with yourself');
            }
            else return $recipientUser;
        }
        else return array('error' => 'You are not the owner of this element, you cannot share it.');
    }
    else return $element;
}

/*
 * supprimer un partage avec un autre utilisateur
 * paramètres: idElement idUser
 * action: maj du state => 0 récursivement
 */

/*
 * envoyer un mail
 * ?
 */

/*
 * ANONYME
 */

/*
 * créer un lien de partage
 * paramètres: idElement, email, sendMail
 * action: màj de l'element pour lui ajouter un lien de dl généré
 */
function shareWithAnonymous($idElement, $idOwner, $recipientEmail)
{
    $idElement = new MongoId($idElement);
    $idOwner = new MongoId($idOwner);

    $elementPdoManager = new ElementPdoManager();

    $elementCriteria = array(
        'state' => (int)1,
        '_id' => $idElement
    );

    $element = $elementPdoManager->findOne($elementCriteria);

    if($element->getDownloadLink() == '')
    {
        /*
         * vérification que l'idOwner en param de la fonction est le même que celui de l'element, la gestion des partages
         * n'étant dans cette version qu'accessible au propriétaire de l'élément
         */

        if($idOwner == $element->getOwner())
        {
            //vérification que l'email indiquée appartient bien à un utilisateur inscrit
            $userCriteria = array(
                'state' => (int)1,
                'email' => $recipientEmail
            );

            $userPdoManager = new UserPdoManager();
            $recipientUser = $userPdoManager->findOne($userCriteria);

            /*
             * Tentative de génération de lien de téléchargement anonyme pour un utilsateur existant.
             * L'interdire ici ne résoudra cependant que partiellement cet éventuel problème,
             * mais au moins on limite la permissivité.
             */
            if($recipientUser instanceof User)
                return array('error' => 'The email you entered belongs to one of our users, please use the \'share with a user\' functionality.');

            $downloadLink = $elementPdoManager->generateGUID();

            $updateDownloadLink = array(
                '$set' => array('downloadLink' => $downloadLink)
            );

            $updateStatus = $elementPdoManager->update($elementCriteria, $updateDownloadLink);

            if(is_bool($updateStatus) && $updateStatus == TRUE)
                return array('downloadLink' => $downloadLink);
            else
                return $updateStatus;
        }
        else return array('error' => 'You are not the owner of this element, you cannot share it.');
    }
    else return array('error', 'There is already a download link for this element.');
}

/*
 * envoyer mail
 * paramètres: downloadLink, email
 * envoyer le lien de dl par mail à la personne
 */

/*
 * supprimer un lien de partage
 * paramètres: idElement
 */