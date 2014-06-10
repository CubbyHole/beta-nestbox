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
 * créer un partage avec un autre utilisateur
 * paramètres: idElement, email, codeRefRight, applyRecursively, sendMail
 */

/*
 * supprimer un partage avec un autre utilisateur
 * paramètres: idElement idUser
 * action: maj du state => 0 récursivement
 */

/*
 * modifier un droit de partage avec un autre utilisateur
 * paramètres: idElement, idUser, newCodeRefRight, applyRecursively
 * action: màj de l'idRefRight pour les state à 1 de idElement et idUser
 * si dossier: demander à l'utilisateur s'il veut appliquer le droit récursivement, auquel cas update upsert => TRUE
 * (upsert => TRUE signifie insertion du droit s'il n'est pas trouvé plutôt que màj) et multiple => TRUE
 */

/*
 * ANONYME
 */

/*
 * créer un lien de partage
 * paramètres: idElement, email, sendMail
 * action: màj de l'element pour lui ajouter un lien de dl généré
 */

/*
 * envoyer mail
 * paramètres: downloadLink, email
 * envoyer le lien de dl par mail à la personne
 */

/*
 * supprimer un lien de partage
 * paramètres: idElement
 */