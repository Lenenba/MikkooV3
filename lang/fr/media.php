<?php

return [
    'upload' => [
        'title' => 'Televerser des medias',
    ],
    'collection' => [
        'label' => 'Nom de la collection',
        'label_short' => 'Collection',
        'profile' => 'Profil',
        'guard' => 'Collection de garde',
    ],
    'add_photos' => 'Ajouter des photos (max :max, :min_widthx:min_heightpx min, :max_sizeMB max par image)',
    'preview_alt' => 'Apercu :index',
    'helper' => [
        'images_helpful' => 'Les images aident plus que le texte seul.',
    ],
    'actions' => [
        'remove_photo' => 'Retirer la photo',
        'set_profile' => 'Mettre en profil',
    ],
    'errors' => [
        'max_photos' => "Vous pouvez televerser jusqu'a :max photos.",
        'invalid_type' => "Le fichier :name n'est pas une image.",
        'invalid_image' => "Le fichier :name ne peut pas etre lu comme une image.",
        'min_dimensions' => "L'image :name doit faire au moins :widthx:heightpx.",
        'max_size' => 'Le fichier :name depasse la taille maximale de :sizeMB.',
    ],
    'empty' => 'Aucun media televerse pour le moment.',
    'status' => [
        'active' => 'Actif',
    ],
    'confirm_delete' => 'Etes-vous sur de vouloir supprimer cette image ? (:collection #:id)',
];
