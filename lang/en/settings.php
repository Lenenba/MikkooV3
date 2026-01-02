<?php

return [
    'title' => 'Settings',
    'description' => 'Manage your profile and onboarding details',
    'nav' => [
        'profile' => 'Profile',
        'password' => 'Password',
        'appearance' => 'Appearance',
        'media' => 'Media',
    ],
    'profile' => [
        'title' => 'Profile settings',
        'breadcrumb' => 'Profile settings',
        'tabs' => [
            'account' => 'Account',
            'address' => 'Address',
            'children' => 'Children',
            'availability' => 'Availability',
            'media' => 'Media',
            'gallery' => 'Gallery',
        ],
        'sections' => [
            'account' => [
                'title' => 'Account',
                'description' => 'Update your name and email address',
            ],
            'details' => [
                'title' => 'Profile details',
                'description' => 'Update your personal profile information',
            ],
            'address' => [
                'title' => 'Address',
                'description' => 'Update your address details',
            ],
            'children' => [
                'title' => 'Children',
                'description' => 'Manage each child profile',
            ],
            'availability' => [
                'title' => 'Availability',
                'description' => 'Update your availability details',
            ],
            'media' => [
                'title' => 'Media',
                'description' => 'Update your avatar and gallery',
            ],
            'gallery' => [
                'title' => 'Gallery',
                'description' => 'Browse your uploaded photos',
            ],
        ],
        'account' => [
            'unverified' => 'Your email address is unverified.',
            'resend' => 'Click here to resend the verification email.',
            'link_sent' => 'A new verification link has been sent to your email address.',
        ],
        'children' => [
            'hint' => 'Add details for each child, including a photo.',
            'default_name' => 'Child :index',
            'age_line' => 'Age: :age',
            'allergies_line' => 'Allergies: :allergies',
            'photo_preview_alt' => 'Child photo preview',
            'default_photo_alt' => 'Default child photo',
            'photo_alt' => 'Child photo',
        ],
        'actions' => [
            'save_profile' => 'Save profile',
            'save_address' => 'Save address',
            'save_child' => 'Save child',
            'save_children' => 'Save children',
            'save_availability' => 'Save availability',
            'remove_photo' => 'Remove photo',
            'update_profile' => 'Update profile details',
        ],
        'delete' => [
            'title' => 'Delete account',
            'description' => 'Delete your account and all of its resources',
            'warning_title' => 'Warning',
            'warning_body' => 'Please proceed with caution, this cannot be undone.',
            'dialog_title' => 'Are you sure you want to delete your account?',
            'dialog_description' => 'Once your account is deleted, all of its resources and data will also be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.',
        ],
    ],
    'password' => [
        'title' => 'Password settings',
        'heading' => [
            'title' => 'Update password',
            'description' => 'Ensure your account is using a long, random password to stay secure',
        ],
        'labels' => [
            'current_password' => 'Current password',
            'new_password' => 'New password',
            'confirm_password' => 'Confirm password',
        ],
        'actions' => [
            'save' => 'Save password',
        ],
    ],
    'media' => [
        'title' => 'Media settings',
        'description' => 'Manage your uploaded media here',
        'body' => [
            'line_1' => 'You can upload images, videos, and other media files here. You can also set a profile photo.',
            'line_2' => 'You can upload up to 5 images at a time. The maximum file size is 5MB per image.',
        ],
    ],
    'appearance' => [
        'title' => 'Appearance settings',
        'description' => "Update your account's appearance settings",
    ],
    'profile_details' => [
        'title' => 'Profile details settings',
        'heading' => [
            'title' => 'Profile information',
            'description' => 'Update your personal details',
        ],
        'address' => [
            'title' => 'Address information',
            'description' => 'Update your address details',
        ],
    ],
    'services' => [
        'title' => 'Services',
        'kpis' => [
            'none' => 'No service',
            'top_service' => ':name (:count)',
            'count' => ':count services',
            'offered' => 'Services offered',
            'booked' => 'Services booked',
            'top_requested' => 'Most requested',
            'total_suffix' => 'total',
            'top_suffix' => 'most requested',
        ],
        'dialog' => [
            'create_title' => 'Add service',
            'edit_title' => 'Edit service',
            'create_description' => 'Add a service you offer.',
            'edit_description' => 'Update your service details.',
            'submit_create' => 'Add service',
        ],
        'form' => [
            'name_label' => 'Service name',
            'price_label' => 'Price (CAD)',
        ],
        'confirm_delete' => 'Delete service ":name"?',
        'columns' => [
            'requests' => 'Requests',
        ],
        'catalog' => [
            'title' => 'Service catalog',
            'description' => 'Pick a known service and adjust your price, or create your own service.',
            'search' => 'Search catalog service...',
            'empty' => 'No catalog service available.',
            'added' => 'Added',
        ],
        'table' => [
            'search' => 'Search a service...',
            'empty' => 'No service added yet.',
        ],
        'actions' => [
            'new' => 'New service',
        ],
    ],
];
