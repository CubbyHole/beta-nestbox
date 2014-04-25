<?php
/**
 * Created by Notepad++.
 * User: Alban Truc
 * Date: 31/01/14
 * Time: 12:52
 */

/**
 * Interface RefPlanManagerInterface
 * @interface
 * @author Alban Truc
 */
interface RefPlanManagerInterface
{
    /**
     * Retrouver un refPlan selon des critères donnés
     * @author Alban Truc
     * @param array|RefPlan $criteria critères de recherche
     * @param array $fieldsToReturn champs à récupérer
     * @since 29/03/2014
     * @return array|RefPlan[]
     */

    function find($criteria, $fieldsToReturn = array());

    /**
     * Retourne le premier refPlan correspondant au(x) critère(s) donné(s)
     * @author Alban Truc
     * @param array|RefPlan $criteria critère(s) de recherche
     * @param array $fieldsToReturn champs à retourner
     * @since 29/03/2014
     * @return array|RefPlan
     */

    function findOne($criteria, $fieldsToReturn = array());

    /**
     * - Retrouver un refPlan par son ID.
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param string|MongoId $id Identifiant unique du refPlan à trouver
     * @param array $fieldsToReturn champs à retourner
     * @since 02/2014
     * @return RefPlan|array contenant le message d'erreur
     */

    function findById($id, $fieldsToReturn = array());

    /**
     * - Retrouver le(s) refPlan gratuit(s), soit par son nom (free) soit par son prix de 0
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array $fieldsToReturn champs à retourner
     * @since 11/03/2014
     * @return array|RefPlan[] tableau d'objets RefPlan
     */

    function findFreePlans($fieldsToReturn = array());

    /**
     * - Retrouver le(s) refPlan payants, à savoir ceux dont le prix est > à 0
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array $fieldsToReturn champs à retourner
     * @since 11/03/2014
     * @return array|RefPlan[] tableau d'objets RefPlan
     */

    function findPremiumPlans($fieldsToReturn = array());

    /**
     * - Retrouver l'ensemble des refPlan
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array $fieldsToReturn champs à retourner
     * @since 11/03/2014
     * @return array|RefPlan[] tableau d'objets RefPlan
     */

    function findAll($fieldsToReturn = array());

    /**
     * - Retrouver un RefPlan selon certains critères et le modifier/supprimer
     * - Récupérer ce RefPlan ou sa version modifiée
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|RefPlan $searchQuery critères de recherche
     * @param array|RefPlan $updateCriteria les modifications à réaliser
     * @param array|NULL $fieldsToReturn pour ne récupérer que certains champs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return array|RefPlan
     */

    function findAndModify($searchQuery, $updateCriteria, $fieldsToReturn = NULL, $options = NULL);

    /**
     * - Ajoute un refPlan en base de données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|RefPlan $document
     * @param array $options
     * @since 12/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    function create($document, $options = array('w' => 1));

    /**
     * Fonction d'update utilisant celle de {@see AbstractPdoManager}
     * @author Alban Truc
     * @param array|RefPlan $criteria description des entrées à modifier
     * @param array|RefPlan $update nouvelles valeurs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    function update($criteria, $update, $options = array('w' => 1));

    /**
     * - Supprime un/des plan(s) correspondant à des critères données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|RefPlan $criteria ce qu'il faut supprimer
     * @param array $options
     * @since 11/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    function remove($criteria, $options = array('w' => 1));
}
?>