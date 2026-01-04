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
            'in_progress' => 'Reservation en cours',
            'completed' => 'Reservation terminee',
            'canceled' => 'Reservation annulee',
            'updated' => 'Mise a jour de votre reservation',
        ],
        'media_request_subject' => 'Demande de media recue',
        'media_request_body' => 'Merci de partager des photos ou videos pour la reservation :reference.',
        'media_request_fulfilled_subject' => 'Demande de media terminee',
        'media_request_fulfilled_body' => 'Nouveau media disponible pour la reservation :reference.',
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
