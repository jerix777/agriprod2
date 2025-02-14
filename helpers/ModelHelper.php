<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */

namespace App\Models{
    /**
     * @property int $id
     * @property string $annee
     * @property string|null $theme
     * @property string|null $date_debut
     * @property string|null $date_fin
     * @property string $status
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Production> $productions
     * @property-read int|null $productions_count
     *
     * @method static \Illuminate\Database\Eloquent\Builder|Campagne newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Campagne newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Campagne query()
     * @method static \Illuminate\Database\Eloquent\Builder|Campagne whereAnnee($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Campagne whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Campagne whereDateDebut($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Campagne whereDateFin($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Campagne whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Campagne whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Campagne whereTheme($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Campagne whereUpdatedAt($value)
     *
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperCampagne {}
}

namespace App\Models{
    /**
     * @property int $id
     * @property string $identifiant_unique
     * @property string|null $nom_commun
     * @property string|null $nom_scientifique
     * @property string $status
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Production> $productions
     * @property-read int|null $productions_count
     *
     * @method static \Illuminate\Database\Eloquent\Builder|Culture newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Culture newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Culture query()
     * @method static \Illuminate\Database\Eloquent\Builder|Culture whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Culture whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Culture whereIdentifiantUnique($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Culture whereNomCommun($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Culture whereNomScientifique($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Culture whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Culture whereUpdatedAt($value)
     *
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperCulture {}
}

namespace App\Models{
    /**
     * @property int $id
     * @property int $poste_id
     * @property int $employer_id
     * @property string $date_depart
     * @property int $nombre_jours
     * @property string $lieu
     * @property string $motif
     * @property string $date_reprise
     * @property int $valide
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     *
     * @method static \Illuminate\Database\Eloquent\Builder|Demande newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Demande newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Demande query()
     * @method static \Illuminate\Database\Eloquent\Builder|Demande whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Demande whereDateDepart($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Demande whereDateReprise($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Demande whereEmployerId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Demande whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Demande whereLieu($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Demande whereMotif($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Demande whereNombreJours($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Demande wherePosteId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Demande whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Demande whereValide($value)
     *
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperDemande {}
}

namespace App\Models{
    /**
     * @property int $id
     * @property string $libelle
     * @property string $status
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Poste> $postes
     * @property-read int|null $postes_count
     *
     * @method static \Illuminate\Database\Eloquent\Builder|Departement newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Departement newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Departement query()
     * @method static \Illuminate\Database\Eloquent\Builder|Departement whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Departement whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Departement whereLibelle($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Departement whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Departement whereUpdatedAt($value)
     *
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperDepartement {}
}

namespace App\Models{
    /**
     * @property int $id
     * @property string $matricule
     * @property string|null $nom
     * @property string|null $prenoms
     * @property int $genre_id
     * @property string|null $fonction
     * @property string|null $email
     * @property string|null $photo
     * @property string|null $mot_de_passe
     * @property string $status
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\Poste|null $postes
     *
     * @method static \Illuminate\Database\Eloquent\Builder|Employee newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Employee newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Employee query()
     * @method static \Illuminate\Database\Eloquent\Builder|Employee whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Employee whereEmail($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Employee whereFonction($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Employee whereGenreId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Employee whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Employee whereMatricule($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Employee whereMotDePasse($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Employee whereNom($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Employee wherePhoto($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Employee wherePrenoms($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Employee whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Employee whereUpdatedAt($value)
     *
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperEmployee {}
}

namespace App\Models{
    /**
     * @property int $id
     * @property string $libelle
     * @property string $status
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\Employee|null $employers
     * @property-read \App\Models\Producteur|null $producteurs
     *
     * @method static \Illuminate\Database\Eloquent\Builder|Genre newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Genre newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Genre query()
     * @method static \Illuminate\Database\Eloquent\Builder|Genre whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Genre whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Genre whereLibelle($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Genre whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Genre whereUpdatedAt($value)
     *
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperGenre {}
}

namespace App\Models{
    /**
     * @property int $id
     * @property int $producteur_id
     * @property string $reference
     * @property string $localisation
     * @property int $superficie
     * @property string $status
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\Producteur $producteur
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Production> $productions
     * @property-read int|null $productions_count
     *
     * @method static \Illuminate\Database\Eloquent\Builder|Parcelle newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Parcelle newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Parcelle query()
     * @method static \Illuminate\Database\Eloquent\Builder|Parcelle whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Parcelle whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Parcelle whereLocalisation($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Parcelle whereProducteurId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Parcelle whereReference($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Parcelle whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Parcelle whereSuperficie($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Parcelle whereUpdatedAt($value)
     *
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperParcelle {}
}

namespace App\Models{
    /**
     * @method static \Illuminate\Database\Eloquent\Builder|Piece newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Piece newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Piece query()
     *
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperPiece {}
}

namespace App\Models{
    /**
     * @property int $id
     * @property int $employer_id
     * @property int $departement_id
     * @property int $role_id
     * @property string $nom_reseau
     * @property string $numero_de_serie
     * @property string|null $description
     * @property string $status
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\Departement $departement
     * @property-read \App\Models\Employee $employer
     *
     * @method static \Illuminate\Database\Eloquent\Builder|Poste newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Poste newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Poste query()
     * @method static \Illuminate\Database\Eloquent\Builder|Poste whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Poste whereDepartementId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Poste whereDescription($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Poste whereEmployerId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Poste whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Poste whereNomReseau($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Poste whereNumeroDeSerie($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Poste whereRoleId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Poste whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Poste whereUpdatedAt($value)
     *
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperPoste {}
}

namespace App\Models{
    /**
     * @property int $id
     * @property string $matricule
     * @property string $nom
     * @property string $prenoms
     * @property int $genre_id
     * @property string|null $date_de_naissance
     * @property string|null $contact
     * @property string|null $lieu_de_residence
     * @property string $status
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\Genre|null $genre
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Parcelle> $parcelles
     * @property-read int|null $parcelles_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Production> $productions
     * @property-read int|null $productions_count
     *
     * @method static \Illuminate\Database\Eloquent\Builder|Producteur newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Producteur newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Producteur query()
     * @method static \Illuminate\Database\Eloquent\Builder|Producteur whereContact($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Producteur whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Producteur whereDateDeNaissance($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Producteur whereGenreId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Producteur whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Producteur whereLieuDeResidence($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Producteur whereMatricule($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Producteur whereNom($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Producteur wherePrenoms($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Producteur whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Producteur whereUpdatedAt($value)
     *
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperProducteur {}
}

namespace App\Models{
    /**
     * @property int $id
     * @property string $reference
     * @property int $campagne_id
     * @property int $culture_id
     * @property int $parcelle_id
     * @property string $date_de_production
     * @property int $quantite
     * @property string|null $qualite
     * @property string $status
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \App\Models\Campagne $campagne
     * @property-read \App\Models\Culture $culture
     * @property-read \App\Models\Parcelle $parcelle
     *
     * @method static \Illuminate\Database\Eloquent\Builder|Production newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Production newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Production query()
     * @method static \Illuminate\Database\Eloquent\Builder|Production whereCampagneId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Production whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Production whereCultureId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Production whereDateDeProduction($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Production whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Production whereParcelleId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Production whereQualite($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Production whereQuantite($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Production whereReference($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Production whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder|Production whereUpdatedAt($value)
     *
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperProduction {}
}

namespace App\Models{
    /**
     * @property int $id
     * @property string $name
     * @property string $email
     * @property \Illuminate\Support\Carbon|null $email_verified_at
     * @property mixed $password
     * @property string|null $remember_token
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
     * @property-read int|null $notifications_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
     * @property-read int|null $permissions_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
     * @property-read int|null $roles_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
     * @property-read int|null $tokens_count
     *
     * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
     * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|User permission($permissions, $without = false)
     * @method static \Illuminate\Database\Eloquent\Builder|User query()
     * @method static \Illuminate\Database\Eloquent\Builder|User role($roles, $guard = null, $without = false)
     * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
     * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
     * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
     * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder|User withoutPermission($permissions)
     * @method static \Illuminate\Database\Eloquent\Builder|User withoutRole($roles, $guard = null)
     *
     * @mixin \Eloquent
     */
    #[\AllowDynamicProperties]
    class IdeHelperUser {}
}
