<?php

return [
    'title' => 'Parametres',
    'description' => "Gerer votre profil et les details d'inscription",
    'nav' => [
        'profile' => 'Profil',
        'password' => 'Mot de passe',
        'appearance' => 'Apparence',
        'media' => 'Media',
    ],
    'profile' => [
        'title' => 'Parametres du profil',
        'breadcrumb' => 'Parametres du profil',
        'tabs' => [
            'account' => 'Compte',
            'address' => 'Adresse',
            'children' => 'Enfants',
            'availability' => 'Disponibilite',
            'media' => 'Media',
            'gallery' => 'Galerie',
        ],
        'sections' => [
            'account' => [
                'title' => 'Compte',
                'description' => 'Mettez a jour votre nom et votre email',
            ],
            'details' => [
                'title' => 'Details du profil',
                'description' => 'Mettez a jour vos informations personnelles',
            ],
            'address' => [
                'title' => 'Adresse',
                'description' => "Mettez a jour les details de votre adresse",
            ],
            'children' => [
                'title' => 'Enfants',
                'description' => 'Gerer le profil de chaque enfant',
            ],
            'availability' => [
                'title' => 'Disponibilite',
                'description' => 'Mettez a jour vos disponibilites',
            ],
            'media' => [
                'title' => 'Media',
                'description' => 'Mettez a jour votre avatar et votre galerie',
            ],
            'gallery' => [
                'title' => 'Galerie',
                'description' => 'Parcourez vos photos televersees',
            ],
        ],
        'account' => [
            'unverified' => "Votre adresse email n'est pas verifiee.",
            'resend' => "Cliquez ici pour renvoyer l'email de verification.",
            'link_sent' => 'Un nouveau lien de verification a ete envoye a votre adresse email.',
        ],
        'children' => [
            'hint' => 'Ajoutez les details de chaque enfant, avec une photo.',
            'default_name' => 'Enfant :index',
            'age_line' => 'Age : :age',
            'allergies_line' => 'Allergies : :allergies',
            'photo_preview_alt' => 'Apercu photo enfant',
            'default_photo_alt' => 'Photo par defaut enfant',
            'photo_alt' => 'Photo enfant',
        ],
        'actions' => [
            'save_profile' => 'Enregistrer le profil',
            'save_address' => "Enregistrer l'adresse",
            'save_child' => "Enregistrer l'enfant",
            'save_children' => 'Enregistrer les enfants',
            'save_availability' => 'Enregistrer la disponibilite',
            'remove_photo' => 'Retirer la photo',
            'update_profile' => 'Mettre a jour les details du profil',
        ],
        'delete' => [
            'title' => 'Supprimer le compte',
            'description' => 'Supprimez votre compte et toutes ses ressources',
            'warning_title' => 'Attention',
            'warning_body' => 'Veuillez proceder avec prudence, ceci est irreversible.',
            'dialog_title' => 'Voulez-vous vraiment supprimer votre compte ?',
            'dialog_description' => 'Une fois votre compte supprime, toutes vos donnees seront supprimees definitivement. Veuillez saisir votre mot de passe pour confirmer la suppression.',
        ],
    ],
    'password' => [
        'title' => 'Parametres du mot de passe',
        'heading' => [
            'title' => 'Mettre a jour le mot de passe',
            'description' => 'Assurez-vous d utiliser un mot de passe long et aleatoire pour rester en securite',
        ],
        'labels' => [
            'current_password' => 'Mot de passe actuel',
            'new_password' => 'Nouveau mot de passe',
            'confirm_password' => 'Confirmer le mot de passe',
        ],
        'actions' => [
            'save' => 'Enregistrer le mot de passe',
        ],
    ],
    'media' => [
        'title' => 'Parametres media',
        'description' => 'Gerez vos medias televerses ici',
        'body' => [
            'line_1' => 'Vous pouvez televerser des images, videos et autres fichiers media ici. Vous pouvez aussi definir une photo de profil.',
            'line_2' => "Vous pouvez televerser jusqu'a 5 images a la fois. La taille maximale est de 5MB par image.",
        ],
    ],
    'appearance' => [
        'title' => "Parametres d'apparence",
        'description' => "Mettez a jour les parametres d'apparence de votre compte",
    ],
    'profile_details' => [
        'title' => 'Parametres des details du profil',
        'heading' => [
            'title' => 'Informations de profil',
            'description' => 'Mettez a jour vos informations personnelles',
        ],
        'address' => [
            'title' => "Informations d'adresse",
            'description' => "Mettez a jour les details de votre adresse",
        ],
    ],
    'services' => [
        'title' => 'Services',
        'kpis' => [
            'none' => 'Aucun service',
            'top_service' => ':name (:count)',
            'count' => ':count services',
            'offered' => 'Services proposes',
            'booked' => 'Services reserves',
            'top_requested' => 'Plus sollicite',
            'total_suffix' => 'au total',
            'top_suffix' => 'le plus demande',
        ],
        'dialog' => [
            'create_title' => 'Ajouter un service',
            'edit_title' => 'Modifier le service',
            'create_description' => 'Ajoutez un service que vous proposez.',
            'edit_description' => 'Mettez a jour les informations de votre service.',
            'submit_create' => 'Ajouter le service',
        ],
        'form' => [
            'name_label' => 'Nom du service',
            'price_label' => 'Prix (CAD)',
        ],
        'confirm_delete' => 'Supprimer le service ":name" ?',
        'columns' => [
            'requests' => 'Demandes',
        ],
        'catalog' => [
            'title' => 'Catalogue de services',
            'description' => 'Selectionnez un service connu puis ajustez votre prix, ou creez votre propre service.',
            'search' => 'Rechercher un service du catalogue...',
            'empty' => 'Aucun service catalogue disponible.',
            'added' => 'Ajoute',
        ],
        'table' => [
            'search' => 'Rechercher un service...',
            'empty' => 'Aucun service ajoute pour le moment.',
        ],
        'actions' => [
            'new' => 'Nouveau service',
        ],
    ],
];
