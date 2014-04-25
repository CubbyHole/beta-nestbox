<?php
/**
 * Created by PhpStorm.
 * User: Crocell
 * Date: 25/04/14
 * Time: 13:26
 */

/**
 * Interface ConnectionManagerInterface
 * @interface
 * @author Alban Truc
 */
interface ConnectionManagerInterface
{
    /**
     * Retrouver une connexion selon des critères donnés
     * @author Alban Truc
     * @param array|Connection $criteria critères de recherche
     * @param array $fieldsToReturn champs à récupérer
     * @since 29/03/2014
     * @return array|Connection[]
     */

    function find($criteria, $fieldsToReturn = array());

    /**
     * Retourne la première connexion correspondant au(x) critère(s) donné(s)
     * @author Alban Truc
     * @param array|Connection $criteria critère(s) de recherche
     * @param array $fieldsToReturn champs à retourner
     * @since 29/03/2014
     * @return array|Connection
     */

    function findOne($criteria, $fieldsToReturn = array());

    /**
     * - Retrouver l'ensemble des  onnexion
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array $fieldsToReturn champs à retourner
     * @since 11/03/2014
     * @return array|Connection[]
     */

    function findAll($fieldsToReturn = array());

    /**
     * - Retrouver une connexion par son ID.
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param string|MongoId $id Identifiant unique de la connexion à trouver
     * @param array $fieldsToReturn champs à retourner
     * @since 02/2014
     * @return Connection|array contenant le message d'erreur
     */

    function findById($id, $fieldsToReturn = array());

    /**
     * - Retrouver une connexion selon certains critères et le modifier/supprimer
     * - Récupérer cette connexion ou sa version modifiée
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|Connection $searchQuery critères de recherche
     * @param array|Connection $updateCriteria les modifications à réaliser
     * @param array|NULL $fieldsToReturn pour ne récupérer que certains champs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return array|Connection
     */

    function findAndModify($searchQuery, $updateCriteria, $fieldsToReturn = NULL, $options = NULL);

    /**
     * - Insère une nouvelle connexion en base.
     * - Gestion des exceptions et des erreurs
     * - On n'insert pas de nouveau refPlan, ceux-ci sont déjà définis en base.
     * @author Alban Truc
     * @param array|Connection $connexion
     * @param array $options
     * @since 31/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    function create($connexion, $options = array('w' => 1));

    /**
     * Fonction d'update utilisant celle de {@see AbstractPdoManager}
     * @author Alban Truc
     * @param array|Connection $criteria description des entrées à modifier
     * @param array|Connection $update nouvelles valeurs
     * @param array|NULL $options
     * @since 31/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    function update($criteria, $update, $options = array('w' => 1));

    /**
     * - Supprime un/des connexion(s) correspondant à des critères données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|Connection $criteria ce qu'il faut supprimer
     * @param array $options
     * @since 31/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    function remove($criteria, $options = array('w' => 1));
}