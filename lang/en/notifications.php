<?php

return [
    'user_fallback' => 'User',
    'child' => [
        'age' => ':age years',
    ],
    'welcome' => [
        'subject' => 'Welcome to Mikoo',
    ],
    'reservation' => [
        'requested_subject' => 'New reservation received',
        'status_subject' => [
            'confirmed' => 'Reservation confirmed',
            'completed' => 'Reservation completed',
            'canceled' => 'Reservation canceled',
            'updated' => 'Your reservation has been updated',
        ],
    ],
    'invoice' => [
        'issued_subject' => 'Invoice :number',
    ],
    'rating' => [
        'received_subject' => 'New rating received',
    ],
    'announcement' => [
        'match_subject' => 'New announcement available',
        'application_submitted_subject' => 'Application sent',
        'application_received_subject' => 'New application received',
        'application_status_subject' => [
            'accepted' => 'Application accepted',
            'rejected' => 'Application rejected',
            'expired' => 'Application expired',
            'withdrawn' => 'Application withdrawn',
            'updated' => 'Application update',
        ],
    ],
];
