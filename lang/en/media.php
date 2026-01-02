<?php

return [
    'upload' => [
        'title' => 'Upload media',
    ],
    'collection' => [
        'label' => 'Collection name',
        'label_short' => 'Collection',
        'profile' => 'Profile',
        'guard' => 'Care collection',
    ],
    'add_photos' => 'Add photos (max :max, :min_widthx:min_heightpx min, :max_sizeMB max per image)',
    'preview_alt' => 'Preview :index',
    'helper' => [
        'images_helpful' => 'Shoppers find images more helpful than text alone.',
    ],
    'actions' => [
        'remove_photo' => 'Remove photo',
        'set_profile' => 'Set as profile',
    ],
    'errors' => [
        'max_photos' => 'You can only upload up to :max photos.',
        'invalid_type' => 'File :name is not an image.',
        'invalid_image' => 'File :name could not be read as an image.',
        'min_dimensions' => 'Image :name dimensions must be at least :widthx:heightpx.',
        'max_size' => 'File :name exceeds the maximum size of :sizeMB.',
    ],
    'empty' => 'No media uploaded yet.',
    'status' => [
        'active' => 'Active',
    ],
    'confirm_delete' => 'Are you sure you want to delete this image? (:collection #:id)',
];
