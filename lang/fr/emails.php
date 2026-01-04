<?php

return [
    'common' => [
        'greeting' => 'Bonjour:name,',
        'footer' => ':year :app. Tous droits reserves.',
    ],
    'welcome' => [
        'intro' => 'Merci pour votre inscription sur :app.',
        'steps' => [
            'address' => 'Completez votre adresse',
            'preferences' => 'Ajoutez vos preferences',
            'availability' => 'Definissez vos disponibilites',
        ],
        'cta' => 'Completer mon inscription',
        'help' => "Si vous avez besoin d'aide, repondez a ce message ou ecrivez a :email.",
    ],
    'reservations' => [
        'request' => [
            'heading' => 'Nouvelle reservation recue',
            'intro' => 'Une nouvelle reservation a ete demandee par :parent.',
            'button' => 'Voir la reservation',
        ],
        'confirmed' => [
            'heading' => 'Reservation confirmee',
            'intro' => 'Nous vous informons que votre reservation du :date de :start a :end avec :babysitter a ete confirmee.',
            'details' => 'Details de la reservation',
            'button' => 'Voir mes reservations',
            'message' => [
                'confirmed' => 'Merci de votre confiance, nous avons hate de vous servir !',
                'canceled' => "Nous sommes desoles pour ce contretemps. N'hesitez pas a reprogrammer votre reservation a votre convenance.",
            ],
            'subcopy' => "Si vous rencontrez un probleme ou avez des questions, contactez-nous a :email.",
        ],
        'status' => [
            'heading' => 'Reservation :status',
            'intro' => 'Votre reservation :reference a ete :status.',
            'button' => 'Voir la reservation',
            'status_labels' => [
                'confirmed' => 'confirmee',
                'in_progress' => 'en cours',
                'completed' => 'terminee',
                'canceled' => 'annulee',
                'updated' => 'mise a jour',
            ],
        ],
    ],
    'invoices' => [
        'issued' => [
            'heading' => 'Facture :number',
            'intro' => 'Votre facture pour la periode du :start au :end est disponible.',
            'tax_label' => 'TVA (:rate%)',
            'table' => [
                'service' => 'Prestation',
                'date' => 'Date',
                'total' => 'Total',
            ],
            'button' => 'Voir mes factures',
        ],
    ],
    'ratings' => [
        'received' => [
            'heading' => 'Nouvelle evaluation recue',
            'intro' => ':reviewer a laisse une evaluation pour la reservation :reference.',
            'rating_label' => 'Note',
            'comment_label' => 'Commentaire',
            'button' => 'Voir la reservation',
        ],
    ],
    'announcements' => [
        'labels' => [
            'title' => 'Titre',
            'announcement' => 'Annonce',
            'service' => 'Service',
            'parent' => 'Parent',
            'city' => 'Ville',
            'child' => 'Enfant',
            'notes' => 'Notes',
            'details' => 'Details',
            'date' => 'Date',
            'time' => 'Heure',
            'message' => 'Message',
            'your_message' => 'Votre message',
        ],
        'match' => [
            'heading' => 'Nouvelle annonce disponible',
            'intro' => 'Une nouvelle annonce correspond a vos services.',
            'button' => "Voir l'annonce",
        ],
        'application_submitted' => [
            'heading' => 'Candidature envoyee',
            'intro' => 'Votre proposition a bien ete envoyee. Le parent recevra votre candidature et reviendra vers vous.',
            'button' => "Voir l'annonce",
        ],
        'application_received' => [
            'heading' => 'Nouvelle candidature recue',
            'intro' => ":babysitter s'est propose pour votre annonce.",
            'button' => 'Voir les candidatures',
        ],
        'application_status' => [
            'status_labels' => [
                'accepted' => 'Candidature acceptee',
                'rejected' => 'Candidature refusee',
                'expired' => 'Candidature expiree',
                'withdrawn' => 'Candidature retiree',
                'updated' => 'Mise a jour de candidature',
            ],
            'message' => [
                'accepted' => 'Votre candidature a ete acceptee par :parent.',
                'rejected' => 'Le parent a selectionne une autre babysitter.',
                'expired' => 'Votre candidature a expire faute de reponse.',
                'withdrawn' => 'Votre candidature a ete retiree.',
                'updated' => 'Votre candidature a ete mise a jour.',
            ],
            'action' => [
                'reservation' => 'Voir la reservation',
                'announcement' => "Voir l'annonce",
            ],
        ],
    ],
];
