<?php

return [
    'title' => 'Wedding invitation',
    'greeting' => 'You are invited to our wedding. Join us in celebrating love!',

    'manage_wedding' => [
        'main_image_description' => 'This image is displayed at the top of the invitation.',
        'meta' => [
            'description' => 'Used to generate the link preview when the invitation is shared.',
            'image_fallback' => 'Optional. If left empty, the main image above will be used.',
        ],
        'timeline' => [
            'not_defined' => 'Timeline is not defined yet.',
        ],
    ],

    'groups' => [
        'form' => [
            'basic_info_description' => 'Enter group name and personalized message.',
            'name_placeholder' => 'e.g. Petrović Family',
        ],
        'meta' => [
            'description' => 'Personalize the link preview for this group. If left empty, the wedding meta data will be used.',
            'fallback_description' => 'Optional. If left empty, the wedding meta data will be used.',
        ],
        'invitation' => [
            'personalized_message_placeholder' => 'This message will be displayed at the top of their invitation...',
        ],
        'plus_one' => [
            'label' => 'Has Plus One',
            'description' => 'Allow the guest to add a plus one.',
            'disabled_helper' => 'This option is only available for groups with one guest.',
            'allowed' => 'Plus One Allowed',
        ],
        'timeline' => [
            'description' => 'Manage which timeline items are visible for this group.',
            'all_items_visible' => 'All timeline items visible',
            'hidden_items' => 'Hidden Timeline Items',
        ],
    ],

    'guests' => [
        'form' => [
            'notes_placeholder' => 'e.g. Allergies, special wishes...',
        ],
        'companions' => [
            'select_or_create' => 'Select an existing guest or create a new one.',
            'guest_required' => 'You must select a guest or create a new one.',
            'remove_confirmation' => 'Are you sure you want to remove this companion from the list?',
            'description' => 'People coming with this guest.',
            'empty' => 'No additional companions',
            'add_description' => 'Add visitors coming with this guest.',
        ],
        'empty' => 'No guest information',
        'create_description' => 'Create a guest and add visitors coming with them.',
    ],

    'widgets' => [
        'group_views' => [
            'heading' => 'Views by Groups',
        ],
        'guest_age_chart' => [
            'heading' => 'Distribution by Age',
        ],
        'guest_demographics' => [
            'total_guests' => [
                'description' => 'Total number of all guests in the database',
            ],
            'age_structure' => [
                'description' => 'Adults / Children / Babies',
            ],
            'gender_structure' => [
                'description' => 'Male / Female',
            ],
        ],
        'guest_status' => [
            'confirmed' => [
                'description' => 'Guests who are coming',
            ],
            'declined' => [
                'description' => 'Guests who are not coming',
            ],
            'pending' => [
                'description' => 'Guests who have not yet responded',
            ],
        ],
        'invitation_stats' => [
            'sent_invitations' => [
                'description' => 'Total number of groups to which the invitation was sent',
            ],
            'total_views' => [
                'description' => 'How many times all invitations have been opened in total',
            ],
        ],
        'guest_gender_chart' => [
            'heading' => 'Distribution by Gender',
        ],
    ],

    'notifications' => [
        'attendance_confirmation_success' => 'Attendance confirmed successfully.',
    ],
];
