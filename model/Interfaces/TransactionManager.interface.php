<?php
/**
 * Created by PhpStorm.
 * User: Crocell
 * Date: 15/04/14
 * Time: 13:53
 */

/**
 * Interface TransactionManagerInterface
 * @interface
 * @author Alban Truc
 */
interface TransactionManagerInterface
{
    /**
     * Retrouver une transaction selon des critères donnés
     * @author Alban Truc
     * @param array|Transaction $criteria critères de recherche
     * @param array $fieldsToReturn champs à récupérer
     * @since 29/03/2014
     * @return array|Transaction[]
     */

    function find($criteria, $fieldsToReturn = array());

    /**
     * Retourne la première transaction correspondant au(x) critère(s) donné(s)
     * @author Alban Truc
     * @param array|Transaction $criteria critère(s) de recherche
     * @param array $fieldsToReturn champs à retourner
     * @since 29/03/2014
     * @return array|Transaction
     */

    function findOne($criteria, $fieldsToReturn = array());

    /**
     * - Retrouver une transaction par son ID.
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param string|MongoId $id Identifiant unique de la transaction à trouver
     * @param array $fieldsToReturn champs à retourner
     * @since 02/2014
     * @return Transaction|array contenant le message d'erreur
     */

    function findById($id, $fieldsToReturn = array());

    /**
     * - Retrouver l'ensemble des transactions
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array $fieldsToReturn champs à retourner
     * @since 11/03/2014
     * @return array|Transaction[] tableau d'objets Transaction
     */

    function findAll($fieldsToReturn = array());

    /**
     * - Retrouver une transaction selon certains critères et le modifier/supprimer
     * - Récupérer cette transaction ou sa version modifiée
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|Transaction $searchQuery critères de recherche
     * @param array|Transaction $updateCriteria les modifications à réaliser
     * @param array|NULL $fieldsToReturn pour ne récupérer que certains champs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return array|Transaction
     */

    function findAndModify($searchQuery, $updateCriteria, $fieldsToReturn = NULL, $options = NULL);

    /**
     * - Ajoute une transaction en base de données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|Transaction $document
     * @param array $options
     * @since 12/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    function create($document, $options = array('w' => 1));

    /**
     * Fonction d'update utilisant celle de {@see AbstractPdoManager}
     * @author Alban Truc
     * @param array|Transaction $criteria description des entrées à modifier
     * @param array|Transaction $update nouvelles valeurs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    function update($criteria, $update, $options = array('w' => 1));

    /**
     * - Supprime une/des transaction(s) correspondant à des critères données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|Transaction $criteria ce qu'il faut supprimer
     * @param array $options
     * @since 11/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    function remove($criteria, $options = array('w' => 1));
}
?>