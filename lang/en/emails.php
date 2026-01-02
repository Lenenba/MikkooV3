<?php

return [
    'common' => [
        'greeting' => 'Hello:name,',
        'footer' => ':year :app. All rights reserved.',
    ],
    'welcome' => [
        'intro' => 'Thanks for signing up for :app.',
        'steps' => [
            'address' => 'Complete your address',
            'preferences' => 'Add your preferences',
            'availability' => 'Set your availability',
        ],
        'cta' => 'Complete my registration',
        'help' => 'If you need help, reply to this message or email :email.',
    ],
    'reservations' => [
        'request' => [
            'heading' => 'New reservation received',
            'intro' => 'A new reservation was requested by :parent.',
            'button' => 'View reservation',
        ],
        'confirmed' => [
            'heading' => 'Reservation confirmed',
            'intro' => 'We are informing you that your reservation on :date from :start to :end with :babysitter has been confirmed.',
            'details' => 'Reservation details',
            'button' => 'View my reservations',
            'message' => [
                'confirmed' => 'Thank you for your trust, we look forward to serving you!',
                'canceled' => 'We are sorry for the inconvenience. Feel free to reschedule your reservation at your convenience.',
            ],
            'subcopy' => 'If you run into an issue or have questions, contact us at :email.',
        ],
        'status' => [
            'heading' => 'Reservation :status',
            'intro' => 'Your reservation :reference has been :status.',
            'button' => 'View reservation',
            'status_labels' => [
                'confirmed' => 'confirmed',
                'completed' => 'completed',
                'canceled' => 'canceled',
                'updated' => 'updated',
            ],
        ],
    ],
    'invoices' => [
        'issued' => [
            'heading' => 'Invoice :number',
            'intro' => 'Your invoice for the period from :start to :end is available.',
            'tax_label' => 'VAT (:rate%)',
            'table' => [
                'service' => 'Service',
                'date' => 'Date',
                'total' => 'Total',
            ],
            'button' => 'View my invoices',
        ],
    ],
    'ratings' => [
        'received' => [
            'heading' => 'New rating received',
            'intro' => ':reviewer left a rating for reservation :reference.',
            'rating_label' => 'Rating',
            'comment_label' => 'Comment',
            'button' => 'View reservation',
        ],
    ],
    'announcements' => [
        'labels' => [
            'title' => 'Title',
            'announcement' => 'Announcement',
            'service' => 'Service',
            'parent' => 'Parent',
            'city' => 'City',
            'child' => 'Child',
            'notes' => 'Notes',
            'details' => 'Details',
            'date' => 'Date',
            'time' => 'Time',
            'message' => 'Message',
            'your_message' => 'Your message',
        ],
        'match' => [
            'heading' => 'New announcement available',
            'intro' => 'A new announcement matches your services.',
            'button' => 'View announcement',
        ],
        'application_submitted' => [
            'heading' => 'Application sent',
            'intro' => 'Your proposal has been sent. The parent will review your application.',
            'button' => 'View announcement',
        ],
        'application_received' => [
            'heading' => 'New application received',
            'intro' => ':babysitter applied to your announcement.',
            'button' => 'View applications',
        ],
        'application_status' => [
            'status_labels' => [
                'accepted' => 'Application accepted',
                'rejected' => 'Application rejected',
                'expired' => 'Application expired',
                'withdrawn' => 'Application withdrawn',
                'updated' => 'Application update',
            ],
            'message' => [
                'accepted' => 'Your application was accepted by :parent.',
                'rejected' => 'The parent selected another babysitter.',
                'expired' => 'Your application expired without a response.',
                'withdrawn' => 'Your application was withdrawn.',
                'updated' => 'Your application has been updated.',
            ],
            'action' => [
                'reservation' => 'View reservation',
                'announcement' => 'View announcement',
            ],
        ],
    ],
];
