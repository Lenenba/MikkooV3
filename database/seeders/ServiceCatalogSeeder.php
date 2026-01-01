<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceCatalogSeeder extends Seeder
{
    /**
     * Seed the services catalog (global services with no babysitter).
     */
    public function run(): void
    {
        $catalog = [
            [
                'name' => 'Garde reguliere',
                'description' => 'Garde reguliere en semaine ou apres ecole.',
                'price' => 0,
            ],
            [
                'name' => "Sortie d'ecole",
                'description' => "Prise en charge apres l'ecole et retour a la maison.",
                'price' => 0,
            ],
            [
                'name' => 'Garde de nuit',
                'description' => 'Surveillance et presence pendant la nuit.',
                'price' => 0,
            ],
            [
                'name' => 'Garde week-end',
                'description' => 'Garde le samedi ou dimanche selon le besoin.',
                'price' => 0,
            ],
            [
                'name' => 'Aide aux devoirs',
                'description' => 'Soutien scolaire et aide aux devoirs.',
                'price' => 0,
            ],
            [
                'name' => 'Preparation repas',
                'description' => 'Preparation de repas simples pour les enfants.',
                'price' => 0,
            ],
            [
                'name' => 'Bain et coucher',
                'description' => 'Routine du soir, bain et mise au lit.',
                'price' => 0,
            ],
            [
                'name' => 'Garde de bebe',
                'description' => 'Soins adaptes aux nourrissons.',
                'price' => 0,
            ],
            [
                'name' => 'Jeux educatifs',
                'description' => 'Activites ludiques et educatives.',
                'price' => 0,
            ],
            [
                'name' => 'Accompagnement activites',
                'description' => 'Accompagnement aux activites extrascolaires.',
                'price' => 0,
            ],
            [
                'name' => 'Garde d urgence',
                'description' => 'Disponibilite rapide pour un besoin urgent.',
                'price' => 0,
            ],
            [
                'name' => 'Garde temps partiel',
                'description' => 'Garde flexible sur quelques heures.',
                'price' => 0,
            ],
            [
                'name' => 'Garde temps plein',
                'description' => 'Garde a temps plein sur la journee.',
                'price' => 0,
            ],
        ];

        foreach ($catalog as $item) {
            $service = Service::withTrashed()->firstOrCreate(
                [
                    'user_id' => null,
                    'name' => $item['name'],
                ],
                [
                    'description' => $item['description'],
                    'price' => $item['price'],
                ]
            );

            if ($service->trashed()) {
                $service->restore();
            }

            $service->update([
                'description' => $item['description'],
                'price' => $item['price'],
            ]);
        }
    }
}
