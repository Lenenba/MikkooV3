<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ReferenceNumero extends Model // Le nom de la classe PHP peut rester ReferenceNumero
{
    use HasFactory;

    /**
     * Le nom de la table associée au modèle.
     * Changement ici pour pointer vers la table 'reference'.
     *
     * @var string
     */
    protected $table = 'reference';

    protected $fillable = [
        'user_id',
        'referenceable_id',
        'referenceable_type',
        'prefixe',
        'numero_sequence',
        'chaine_reference',
    ];

    public function referenceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
