<?php
/**
 * Created by PhpStorm.
 * User: Crocell
 * Date: 25/04/14
 * Time: 13:44
 */

/**
 * Interface ElementManagerInterface
 * @interface
 * @author Alban Truc
 */
interface ElementManagerInterface
{
    /**
     * Retrouver un Element selon des critères donnés
     * @author Alban Truc
     * @param array|Element $criteria critères de recherche
     * @param array $fieldsToReturn champs à récupérer
     * @since 29/03/2014
     * @return array|Element[]
     */

    function find($criteria, $fieldsToReturn = array());

    /**
     * Retourne le premier Element correspondant au(x) critère(s) donné(s)
     * @author Alban Truc
     * @param array|Element $criteria critère(s) de recherche
     * @param array $fieldsToReturn champs à retourner
     * @since 29/03/2014
     * @return array|Element
     */

    function findOne($criteria, $fieldsToReturn = array());

    /**
     * - Retrouver l'ensemble des Element
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array $fieldsToReturn champs à retourner
     * @since 11/03/2014
     * @return array|Element[]
     */

    function findAll($fieldsToReturn = array());

    /**
     * - Retrouver un Element par son ID.
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param string|MongoId $id Identifiant unique de l'Element à trouver
     * @param array $fieldsToReturn champs à retourner
     * @since 02/2014
     * @return Element|array contenant le message d'erreur
     */

    function findById($id, $fieldsToReturn = array());

    /**
     * - Retrouver un Element selon certains critères et le modifier/supprimer
     * - Récupérer cet Element ou sa version modifiée
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|Element $searchQuery critères de recherche
     * @param array|Element $updateCriteria les modifications à réaliser
     * @param array|NULL $fieldsToReturn pour ne récupérer que certains champs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return array|Element
     */

    function findAndModify($searchQuery, $updateCriteria, $fieldsToReturn = NULL, $options = NULL);

    /**
     * - Insère un nouvel élément en base.
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|Element $element
     * @param array $options
     * @since 31/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    function create($element, $options = array('w' => 1));

    /**
     * Fonction d'update utilisant celle de {@see AbstractPdoManager}
     * @author Alban Truc
     * @param array|Element $criteria description des entrées à modifier
     * @param array|Element $update nouvelles valeurs
     * @param array|NULL $options
     * @since 31/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    function update($criteria, $update, $options = array('w' => 1));

    /**
     * - Supprime un/des élément(s) correspondant à des critères données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|Element $criteria ce qu'il faut supprimer
     * @param array $options
     * @since 31/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    function remove($criteria, $options = array('w' => 1));

    /**
     * - Distingue deux cas: récupération des éléments d'un utilisateur et récupération des éléments partagés avec un utilisateur
     * - Dans le 1er cas (isOwner = true), on retourne les infos de l'élément et du refElement
     * - Dans le second cas (isOwner = false), on retourne le droit, le refRight, l'élément, le refElement et le propriétaire
     * - Gestion des erreurs
     * @author Alban Truc
     * @param string|MongoId $idUser
     * @param string $isOwner
     * @param string $path emplacement sur le serveur des éléments
     * @since 01/05/2014
     * @return Element[]
     */

    function returnElementsDetails($idUser, $isOwner, $path = 'all');

    /**
     * Retourne le droit, le refRight, l'élément et le refElement
     * @author Alban Truc
     * @param string|MongoId $idUser
     * @since 01/05/2014
     * @return Right[]
     */

    function returnSharedElementsDetails($idUser);
}