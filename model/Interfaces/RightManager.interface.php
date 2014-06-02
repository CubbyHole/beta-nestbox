<?php
/**
 * Created by PhpStorm.
 * User: Crocell
 * Date: 25/04/14
 * Time: 14:02
 */

/**
 * Interface RightManagerInterface
 * @interface
 * @author Alban Truc
 */
interface RightManagerInterface
{
    /**
     * Retrouver un Right selon des critères donnés
     * @author Alban Truc
     * @param array|Right $criteria critères de recherche
     * @param array $fieldsToReturn champs à récupérer
     * @since 29/03/2014
     * @return array|Right[]
     */

    function find($criteria, $fieldsToReturn = array());

    /**
     * Retourne le premier Right correspondant au(x) critère(s) donné(s)
     * @author Alban Truc
     * @param array|Right $criteria critère(s) de recherche
     * @param array $fieldsToReturn champs à retourner
     * @since 29/03/2014
     * @return array|Right
     */

    function findOne($criteria, $fieldsToReturn = array());

    /**
     * - Retrouver l'ensemble des Right
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array $fieldsToReturn champs à retourner
     * @since 11/03/2014
     * @return array|Right[]
     */

    function findAll($fieldsToReturn = array());

    /**
     * - Retrouver un Right par son ID.
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param string|MongoId $id Identifiant unique du Right à trouver
     * @param array $fieldsToReturn champs à retourner
     * @since 02/2014
     * @return Right|array contenant le message d'erreur
     */

    function findById($id, $fieldsToReturn = array());

    /**
     * - Retrouver un Right selon certains critères et le modifier/supprimer
     * - Récupérer ce Right ou sa version modifiée
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|Right $searchQuery critères de recherche
     * @param array|Right $updateCriteria les modifications à réaliser
     * @param array|NULL $fieldsToReturn pour ne récupérer que certains champs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return array|Right
     */

    function findAndModify($searchQuery, $updateCriteria, $fieldsToReturn = NULL, $options = NULL);

    /**
     * - Insère un nouveau droit en base.
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|Right $right
     * @param array $options
     * @since 31/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    function create($right, $options = array('w' => 1));

    /**
     * Fonction d'update utilisant celle de {@see AbstractPdoManager}
     * @author Alban Truc
     * @param array|Right $criteria description des entrées à modifier
     * @param array|Right $update nouvelles valeurs
     * @param array|NULL $options
     * @since 31/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    function update($criteria, $update, $options = array('w' => 1));

    /**
     * - Supprime un/des droit(s) correspondant à des critères données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|Right $criteria ce qu'il faut supprimer
     * @param array $options
     * @since 31/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    function remove($criteria, $options = array('w' => 1));

    /**
     * Indique si l'utilisateur donné a les droits voulus sur l'élément donné
     * @author Alban Truc
     * @param MongoId|string $idUser
     * @param MongoId|string $idElement
     * @param array $refRightCode
     * @since 15/05/2014
     * @return bool|array
     */

    function hasRightOnElement($idUser, $idElement, $refRightCode);
}