<?php
/**
 * Created by Notepad++.
 * User: Alban Truc
 * Date: 30/01/14
 * Time: 14:51
 */

/**
 * Interface UserManagerInterface
 * @interface
 * @author Alban Truc
 */
interface UserManagerInterface
{
    /**
     * Retrouver un User selon des critères donnés
     * @author Alban Truc
     * @param array|User $criteria critères de recherche
     * @param array $fieldsToReturn champs à récupérer
     * @since 31/03/2014
     * @return array|User[]
     */

    function find($criteria, $fieldsToReturn = array());

    /**
     * Retourne le premier User correspondant au(x) critère(s) donné(s)
     * @author Alban Truc
     * @param array|User $criteria critère(s) de recherche
     * @param array $fieldsToReturn champs à retourner
     * @since 31/03/2014
     * @return array|User
     */

    function findOne($criteria, $fieldsToReturn = array());

    /**
     * - Retrouver l'ensemble des User
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array $fieldsToReturn champs à retourner
     * @since 11/03/2014
     * @return array|User[] tableau d'objets User
     */

    function findAll($fieldsToReturn = array());

    /**
     * - Retrouver un user par son ID.
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array $fieldsToReturn champs à retourner
     * @param string|MongoId $id Identifiant unique de l'user à trouver
     * @since 02/2014
     * @return User|array contenant le message d'erreur
     */

    function findById($id, $fieldsToReturn = array());

    /**
     * - Retrouver un User selon certains critères et le modifier/supprimer
     * - Récupérer cet User ou sa version modifiée
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|User $searchQuery critères de recherche
     * @param array|User $updateCriteria les modifications à réaliser
     * @param array|NULL $fieldsToReturn pour ne récupérer que certains champs
     * @param array|NULL $options
     * @since 11/03/2014
     * @return array|User
     */

    function findAndModify($searchQuery, $updateCriteria, $fieldsToReturn = NULL, $options = NULL);

    /**
     * - Insère un nouvel utilisateur en base.
     * - Gestion des exceptions et des erreurs.
     * @author Alban Truc
     * @param array|User $user
     * @param array $options
     * @since 31/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    function create($user, $options = array('w' => 1));

    /**
     * Fonction d'update utilisant celle de {@see AbstractPdoManager}
     * @author Alban Truc
     * @param array|User $criteria description des entrées à modifier
     * @param array|User $update nouvelles valeurs
     * @param array|NULL $options
     * @since 31/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    function update($criteria, $update, $options = array('w' => 1));

    /**
     * - Supprime un/des utilisateur(s) correspondant à des critères données
     * - Gestion des exceptions et des erreurs
     * @author Alban Truc
     * @param array|User $criteria ce qu'il faut supprimer
     * @param array $options
     * @since 31/03/2014
     * @return TRUE|array contenant le message d'erreur dans un indexe 'error'
     */

    function remove($criteria, $options = array('w' => 1));

    /**
     * - Insère un compte gratuit.
     * - Insère l'utilisateur qui va posséder ce compte.
     * - Gestion des exceptions
     * - Gestion des erreurs, avec notamment:
     *       Annulation de l'insertion du compte gratuit si l'insertion de l'utilisateur a échoué
     * @author Alban Truc
     * @param string $name
     * @param string $firstName
     * @param string $email
     * @param string $password
     * @param string $geolocation
     * @since 02/2014
     * @return bool TRUE si l'insertion a réussi, FALSE sinon
     */

    function addFreeUser($name, $firstName, $email, $password, $geolocation);

    /**
     * Authentifier un utilisateur:
     * - Récupère l'utilisateur inscrit avec l'e-mail indiquée. S'il y en a un:
     *  - Vérifie le mot de passe. S'il correspond:
     *      - Récupère son compte
     * @author Alban Truc
     * @param string $email
     * @param string $password
     * @since 02/2014
     * @return User|array contenant le message d'erreur
     */

    function authenticate($email, $password);

    /**
     * Vérifier la disponibilité d'une adresse e-mail
     * @author Alban Truc
     * @param string $email
     * @since 02/2014
     * @return bool FALSE si email déjà prise, TRUE sinon
     */

    function checkEmailAvailability($email);

    /**
     * Inscription:
     * - Vérifie certains critères sur les paramètres fournis
     * - Appelle la fonction de vérification de disponibilité de l'e-mail
     * - Appelle la fonction d'ajout d'un free user
     * - Appelle la fonction d'envoi du mail d'inscription
     * @author Alban Truc
     * @param string $name
     * @param string $firstName
     * @param string $email
     * @param string $password
     * @param string $passwordConfirmation
     * @param string $geolocation
     * @since 02/2014
     * @return User|array contenant le message d'erreur
     *
     * IMPORTANT: ne pas oublier de gérer l'envoi d'e-mail d'inscription!
     */

    function register($name, $firstName, $email, $password, $passwordConfirmation, $geolocation = 'Not specified');

    /**
     * Ajoute en base un token pour la validation de l'inscription
     * @author Alban Truc
     * @param int $state
     * @param string $email
     * @since 16/04/2014
     * @return array|TRUE
     */

    public function createValidationToken($state, $email);

    /**
     * Envoie un mail pour demander à l'utilisateur de confirmer son inscription.
     * @author Alban Truc
     * @param string $email
     * @since 16/04/2014
     * @return TRUE|FALSE
     */

    function sendRegistrationMail($email);

    /**
     * Confirme l'inscription de l'utilisateur. Celui-ci pourra à présent se connecter.
     * @author Alban Truc
     * @param $email
     * @param $token
     * @since 16/04/2014
     * @return array|string
     */

    function validateRegistration($email, $token);

    /**
     * Pour changer de mot de passe.
     * @author Alban Truc
     * @param string $email
     * @param string $oldPassword
     * @param string $newPassword
     * @param string $newPasswordConfirmation
     * @since 17/04/2014
     * @return array|TRUE
     */

    function changePassword($email, $oldPassword, $newPassword, $newPasswordConfirmation);
}
?>