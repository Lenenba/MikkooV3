<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\DB;

trait GeneratesSequentialNumber
{
    /**
     * Generate a sequential number for a model, scoped by a user.
     *
     * @param  int    $userId    The ID of the user for whom the number is generated.
     * @param  string $prefix    The prefix to use for the number (e.g., 'Cust', 'Quote', etc.).
     * @param  int    $padding   The number of digits to pad (default: 3).
     * @return string            The generated number with the prefix.
     */
    public static function generateNumber(int $userId, string $prefix, int $padding = 3): string
    {
        return DB::transaction(function () use ($userId, $prefix, $padding) {
            // Count the number of records for the user
            $count = self::where('babysitter_id', $userId)->lockForUpdate()->count();

            // Generate the sequential number
            $nextNumber = str_pad($count + 1, $padding, '0', STR_PAD_LEFT);

            return "{$prefix}{$nextNumber}";
        });
    }

    /**
     * Generate the next number in a sequence based on the calling controller.
     *
     * @param  string|null $lastNumber The last number in the sequence.
     * @return string                  The next number in the sequence.
     * @throws \Exception
     */
    public static function generateNextNumber($lastNumber): string
    {
        // Déterminer dynamiquement le préfixe basé sur le contrôleur appelant
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $callerClass = $trace[1]['class'] ?? null;

        // Définir un préfixe par défaut
        $prefix = 'X'; // 'X' peut être un préfixe générique si aucun n'est trouvé

        if ($callerClass) {
            // Extraire la première lettre majuscule du nom du contrôleur
            if (preg_match('/(\w+)Controller$/', class_basename($callerClass), $matches)) {
                $prefix = strtoupper(substr($matches[1], 0, 1)); // Première lettre du nom du contrôleur
            }
        }

        // Si aucun numéro précédent, retourner le premier avec le bon préfixe
        if (is_null($lastNumber)) {
            return $prefix . '001';
        }

        // Extraire la partie numérique du dernier numéro
        preg_match('/[A-Z](\d+)/', $lastNumber, $matches);

        if (!isset($matches[1])) {
            throw new \Exception("Invalid number format: $lastNumber");
        }

        $lastNumericPart = (int) $matches[1];

        // Incrémenter la partie numérique
        $nextNumericPart = $lastNumericPart + 1;

        // Générer le nouveau numéro en format "<Prefix>" suivi de 3 chiffres
        return $prefix . str_pad($nextNumericPart, 3, '0', STR_PAD_LEFT);
    }
}
