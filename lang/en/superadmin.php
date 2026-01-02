<?php

return [
    'consultations' => [
        'title' => 'Consultations',
        'description' => 'Global view of the platform for the super admin.',
        'cards' => [
            'users' => [
                'title' => 'Users',
                'parents' => 'Parents: :count',
                'babysitters' => 'Babysitters: :count',
                'superadmins' => 'Super admins: :count',
            ],
            'reservations' => [
                'title' => 'Reservations',
                'pending' => 'Pending: :count',
                'confirmed' => 'Confirmed: :count',
                'action' => 'View all reservations',
            ],
            'invoices' => [
                'title' => 'Invoices',
                'issued' => 'Issued: :count',
                'paid' => 'Paid: :count',
                'action' => 'Review invoices',
            ],
            'announcements' => [
                'title' => 'Announcements',
                'open' => 'Open: :count',
                'total' => 'Total: :count',
                'action' => 'View announcements',
            ],
        ],
    ],
];
