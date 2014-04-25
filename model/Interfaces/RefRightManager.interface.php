<?php
/**
 * Created by PhpStorm.
 * User: Crocell
 * Date: 25/04/14
 * Time: 14:10
 */

/**
 * Interface RefRightManagerInterface
 * @interface
 * @author Alban Truc
 */
interface RefRightManagerInterface
{
    /**
     * Retrouver un RefRight selon des critères donnés
     * @author Alban Truc
     * @param array|RefRight $criteria critères de recherche
     * @param array $fieldsToReturn champs à récupérer
     * @since 29/03/2014
     * @return array|RefRight[]
     */

    function find($criteria, $fieldsToReturn = array());

    /**
     * Retourne le premier RefRight correspondant au(x) critère(s) donné(s)
     * @author Alban Truc
     * @param array|RefRight $criteria critère(s) de recherche
     * @param array $fieldsToReturn champs à retourner
     * @since 29/03/2014
     * @return array|RefRight
     */

    function findOne($criteria, $fieldsToReturn = array());

    /**
     * - Retrouver l'ensemble des RefRight
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array $fieldsToReturn champs à retourner
     * @since 11/03/2014
     * @return array|RefRight[]
     */

    function findAll($fieldsToReturn = array());

    /**
     * - Retrouver un RefRight par son ID.
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param string|MongoId $id Identifiant unique du RefRight à trouver
     * @param array $fieldsToReturn champs à retourner
     * @since 02/2014
     * @return RefRight|array contenant le message d'erreur
     */

    function findById($id, $fieldsToReturn = array());

    /**
     * - Retrouver un RefRight selon certains critères et le modifier/supprimer
     * - Récupérer ce RefRight ou sa version modifiée
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|RefRight $searchQuery critères de recherche
     * @param array|RefRight $updateCriteria les modifications à réaliser
     * @param array|NULL $fieldsToReturn pour ne récupérer que certains champs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return array|RefRight
     */

    function findAndModify($searchQuery, $updateCriteria, $fieldsToReturn = NULL, $options = NULL);

    /**
     * - Insère un nouveau droit en base.
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|RefRight $refRight
     * @param array $options
     * @since 31/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    function create($refRight, $options = array('w' => 1));

    /**
     * Fonction d'update utilisant celle de {@see AbstractPdoManager}
     * @author Alban Truc
     * @param array|RefRight $criteria description des entrées à modifier
     * @param array|RefRight $update nouvelles valeurs
     * @param array|NULL $options
     * @since 31/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    function update($criteria, $update, $options = array('w' => 1));

    /**
     * - Supprime un/des droit(s) correspondant à des critères données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|RefRight $criteria ce qu'il faut supprimer
     * @param array $options
     * @since 31/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    function remove($criteria, $options = array('w' => 1));
}