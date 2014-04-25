<?php
/**
 * Created by PhpStorm.
 * User: Crocell
 * Date: 25/04/14
 * Time: 13:57
 */

/**
 * Interface RefElementManagerInterface
 * @interface
 * @author Alban Truc
 */
interface RefElementManagerInterface
{
    /**
     * Retrouver un RefElement selon des critères donnés
     * @author Alban Truc
     * @param array|RefElement $criteria critères de recherche
     * @param array $fieldsToReturn champs à récupérer
     * @since 29/03/2014
     * @return array|RefElement[]
     */

    function find($criteria, $fieldsToReturn = array());

    /**
     * Retourne le premier RefElement correspondant au(x) critère(s) donné(s)
     * @author Alban Truc
     * @param array|RefElement $criteria critère(s) de recherche
     * @param array $fieldsToReturn champs à retourner
     * @since 29/03/2014
     * @return array|RefElement
     */

    function findOne($criteria, $fieldsToReturn = array());

    /**
     * - Retrouver l'ensemble des RefElement
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array $fieldsToReturn champs à retourner
     * @since 11/03/2014
     * @return array|RefElement[]
     */

    function findAll($fieldsToReturn = array());

    /**
     * - Retrouver un RefElement par son ID.
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param string|MongoId $id Identifiant unique du RefElement à trouver
     * @param array $fieldsToReturn champs à retourner
     * @since 02/2014
     * @return RefElement|array contenant le message d'erreur
     */

    function findById($id, $fieldsToReturn = array());

    /**
     * - Retrouver un RefElement selon certains critères et le modifier/supprimer
     * - Récupérer ce RefElement ou sa version modifiée
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|RefElement $searchQuery critères de recherche
     * @param array|RefElement $updateCriteria les modifications à réaliser
     * @param array|NULL $fieldsToReturn pour ne récupérer que certains champs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return array|RefElement
     */

    function findAndModify($searchQuery, $updateCriteria, $fieldsToReturn = NULL, $options = NULL);

    /**
     * - Insère un nouveau refElement en base.
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|RefElement $refElement
     * @param array $options
     * @since 31/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    function create($refElement, $options = array('w' => 1));

    /**
     * Fonction d'update utilisant celle de {@see AbstractPdoManager}
     * @author Alban Truc
     * @param array|RefElement $criteria description des entrées à modifier
     * @param array|RefElement $update nouvelles valeurs
     * @param array|NULL $options
     * @since 31/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    function update($criteria, $update, $options = array('w' => 1));

    /**
     * - Supprime un/des RefElement correspondant à des critères données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|RefElement $criteria ce qu'il faut supprimer
     * @param array $options
     * @since 31/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    function remove($criteria, $options = array('w' => 1));
}