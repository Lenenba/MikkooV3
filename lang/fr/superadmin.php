<?php

return [
    'consultations' => [
        'title' => 'Consultations',
        'description' => 'Vue globale de la plateforme pour le super admin.',
        'cards' => [
            'users' => [
                'title' => 'Utilisateurs',
                'parents' => 'Parents : :count',
                'babysitters' => 'Babysitters : :count',
                'superadmins' => 'Super admins : :count',
            ],
            'reservations' => [
                'title' => 'Reservations',
                'pending' => 'En attente : :count',
                'confirmed' => 'Confirmees : :count',
                'action' => 'Voir toutes les reservations',
            ],
            'invoices' => [
                'title' => 'Factures',
                'issued' => 'Emises : :count',
                'paid' => 'Payees : :count',
                'action' => 'Consulter les factures',
            ],
            'announcements' => [
                'title' => 'Annonces',
                'open' => 'Ouvertes : :count',
                'total' => 'Au total : :count',
                'action' => 'Voir les annonces',
            ],
        ],
    ],
];
