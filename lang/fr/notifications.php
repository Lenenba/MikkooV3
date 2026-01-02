<?php

return [
    'user_fallback' => 'Utilisateur',
    'child' => [
        'age' => ':age ans',
    ],
    'welcome' => [
        'subject' => 'Bienvenue sur Mikoo',
    ],
    'reservation' => [
        'requested_subject' => 'Nouvelle reservation recue',
        'status_subject' => [
            'confirmed' => 'Reservation confirmee',
            'completed' => 'Reservation terminee',
            'canceled' => 'Reservation annulee',
            'updated' => 'Mise a jour de votre reservation',
        ],
    ],
    'invoice' => [
        'issued_subject' => 'Facture :number',
    ],
    'rating' => [
        'received_subject' => 'Nouvelle evaluation recue',
    ],
    'announcement' => [
        'match_subject' => 'Nouvelle annonce disponible',
        'application_submitted_subject' => 'Candidature envoyee',
        'application_received_subject' => 'Nouvelle candidature recue',
        'application_status_subject' => [
            'accepted' => 'Candidature acceptee',
            'rejected' => 'Candidature refusee',
            'expired' => 'Candidature expiree',
            'withdrawn' => 'Candidature retiree',
            'updated' => 'Mise a jour de candidature',
        ],
    ],
];
