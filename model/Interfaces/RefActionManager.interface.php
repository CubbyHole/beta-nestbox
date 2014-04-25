<?php
/**
 * Created by PhpStorm.
 * User: Crocell
 * Date: 15/04/14
 * Time: 13:13
 */
/**
 * Interface RefActionManagerInterface
 * @interface
 * @author Alban Truc
 */
interface RefActionManagerInterface
{
    /**
     * Retrouver un refAction selon des critères donnés
     * @author Alban Truc
     * @param array|RefAction $criteria critères de recherche
     * @param array $fieldsToReturn champs à récupérer
     * @since 29/03/2014
     * @return array|RefAction[]
     */

    function find($criteria, $fieldsToReturn = array());

    /**
     * Retourne le premier refAction correspondant au(x) critère(s) donné(s)
     * @author Alban Truc
     * @param array|RefAction $criteria critère(s) de recherche
     * @param array $fieldsToReturn champs à retourner
     * @since 29/03/2014
     * @return array|RefAction
     */

    function findOne($criteria, $fieldsToReturn = array());

    /**
     * - Retrouver un refAction par son ID.
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param string|MongoId $id Identifiant unique du refAction à trouver
     * @param array $fieldsToReturn champs à retourner
     * @since 02/2014
     * @return RefAction|array contenant le message d'erreur
     */

    function findById($id, $fieldsToReturn = array());

    /**
     * - Retrouver l'ensemble des refAction
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array $fieldsToReturn champs à retourner
     * @since 11/03/2014
     * @return array|RefAction[] tableau d'objets RefAction
     */

    function findAll($fieldsToReturn = array());

    /**
     * - Retrouver un refAction selon certains critères et le modifier/supprimer
     * - Récupérer ce refAction ou sa version modifiée
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|RefAction $searchQuery critères de recherche
     * @param array|RefAction $updateCriteria les modifications à réaliser
     * @param array|NULL $fieldsToReturn pour ne récupérer que certains champs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return array|RefAction
     */

    function findAndModify($searchQuery, $updateCriteria, $fieldsToReturn = NULL, $options = NULL);

    /**
     * - Ajoute un refAction en base de données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|RefAction $document
     * @param array $options
     * @since 12/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    function create($document, $options = array('w' => 1));

    /**
     * Fonction d'update utilisant celle de {@see AbstractPdoManager}
     * @author Alban Truc
     * @param array|RefAction $criteria description des entrées à modifier
     * @param array|RefAction $update nouvelles valeurs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    function update($criteria, $update, $options = array('w' => 1));

    /**
     * - Supprime un/des refAction(s) correspondant à des critères données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|RefAction $criteria ce qu'il faut supprimer
     * @param array $options
     * @since 11/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    function remove($criteria, $options = array('w' => 1));
}
?>