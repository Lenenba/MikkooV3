<?php

namespace App\Http\Controllers\Traits;

use App\Models\User;
use App\Models\ReferenceNumero;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\DB;

trait HasReferenceNumero
{
    /**
     * S'exécute lorsque le trait est booté pour le modèle.
     * Attache un observateur pour générer le numéro de référence lors de la création du modèle.
     */
    public static function bootHasReferenceNumero(): void
    {
        static::created(function (Model $modelInstance) {
            // Vérifie si la méthode existe pour permettre une logique plus complexe si nécessaire
            if (method_exists($modelInstance, 'genererEntreeReferenceNumero')) {
                $modelInstance->genererEntreeReferenceNumero();
            }
        });
    }

    /**
     * Définit la relation polymorphique "morphOne" vers ReferenceNumero.
     * Un modèle (ex: Reservation) a un seul numéro de référence.
     */
    public function referenceNumero(): MorphOne
    {
        return $this->morphOne(ReferenceNumero::class, 'referenceable');
    }

    /**
     * Méthode pour obtenir le préfixe de référence.
     * À surcharger dans le modèle si le préfixe par défaut n'est pas souhaité.
     */
    protected function getPrefixeReference(): string
    {
        // Par défaut, utilise les 3 premières lettres du nom de la classe en majuscules
        return strtoupper(substr(class_basename($this), 0, 3)); // Ex: "RES" pour "Reservation"
    }

    /**
     * Méthode pour obtenir l'utilisateur contextuel pour la séquence de référence.
     * Doit être adaptée ou surchargée en fonction de la structure de votre modèle.
     * Par exemple, pour une Réservation, ce serait le parent.
     */
    protected function getUtilisateurPourReference(): ?User
    {
        // Exemple pour un modèle qui a un champ 'user_id' direct
        if (isset($this->user_id)) {
            return $this->user; // Suppose une relation 'user' définie ou charge l'utilisateur par $this->user_id
        }
        // Exemple spécifique pour votre modèle Reservation qui a 'parent_id'
        if (isset($this->parent_id) && method_exists($this, 'parent')) {
            return $this->parent; // Suppose une relation 'parent' définie dans Reservation.php
        }
        // Ajoutez d'autres logiques si nécessaire pour d'autres modèles
        // throw new \Exception("Impossible de déterminer l'utilisateur pour la référence sur " . get_class($this));
        return null; // Ou gérer l'erreur autrement
    }

    /**
     * Génère et sauvegarde l'entrée de numéro de référence pour ce modèle.
     */
    public function genererEntreeReferenceNumero(): ?ReferenceNumero
    {
        // Empêche la re-création si un numéro de référence existe déjà pour cet objet
        if ($this->referenceNumero()->exists()) {
            return $this->referenceNumero; // Retourne l'existant
        }

        $utilisateur = $this->getUtilisateurPourReference();
        $prefixe = $this->getPrefixeReference();

        if (!$utilisateur) {
            // Logique de gestion d'erreur si l'utilisateur n'est pas trouvé
            // Vous pourriez logger une erreur ou lancer une exception.
            // Pour l'instant, on retourne null.
            \Illuminate\Support\Facades\Log::error("Utilisateur non trouvé pour la génération de référence.", ['model_id' => $this->id, 'model_type' => get_class($this)]);
            return null;
        }

        // Utilisation d'une transaction et d'un verrou pessimiste pour gérer la concurrence
        return DB::transaction(function () use ($utilisateur, $prefixe) {
            // Trouve le dernier numéro de séquence pour cet utilisateur et ce préfixe
            $dernierNumeroSequence = ReferenceNumero::where('user_id', $utilisateur->id)
                ->where('prefixe', $prefixe)
                ->lockForUpdate() // Verrouille les lignes pour éviter les "race conditions"
                ->max('numero_sequence');

            $nouveauNumeroSequence = ($dernierNumeroSequence ?? 0) + 1;

            // Formate la chaîne de référence (ex: RES-001, RES-002, etc.)
            // Adaptez le padding (nombre de zéros) selon vos besoins.
            $chaineReference = sprintf('%s-%03d', $prefixe, $nouveauNumeroSequence);

            // Crée l'enregistrement dans la table reference_numeros
            return $this->referenceNumero()->create([
                'user_id'           => $utilisateur->id,
                'prefixe'           => $prefixe,
                'numero_sequence'   => $nouveauNumeroSequence,
                'chaine_reference'  => $chaineReference,
            ]);
        });
    }

    /**
     * Accesseur pratique pour obtenir directement la chaîne de référence.
     * Permet d'accéder via $model->chaine_reference_affichee
     */
    public function getChaineReferenceAfficheeAttribute(): ?string
    {
        return $this->referenceNumero?->chaine_reference;
    }
}
