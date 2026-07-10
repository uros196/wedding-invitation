<?php

return [
    'group_views' => [
        'heading' => 'Views by Groups',
        'columns' => [
            'name' => 'Group',
            'views_count' => 'Views Count',
        ],
    ],
    'guest_age_chart' => [
        'heading' => 'Distribution by Age',
        'unknown' => 'Unknown',
        'dataset_label' => 'Guests',
    ],
    'guest_demographics' => [
        'total_guests' => [
            'label' => 'Total Guests',
            'description' => 'Total number of all guests in the database',
        ],
        'age_structure' => [
            'label' => 'Age Structure',
            'description' => 'Adults / Children / Babies',
        ],
        'gender_structure' => [
            'label' => 'Gender Structure',
            'description' => 'Male / Female',
        ],
    ],
    'guest_gender_chart' => [
        'heading' => 'Distribution by Gender',
        'unknown' => 'Unknown',
        'dataset_label' => 'Guests',
    ],
    'guest_status' => [
        'confirmed' => [
            'label' => 'Confirmed',
            'description' => 'Guests who are coming',
        ],
        'declined' => [
            'label' => 'Declined',
            'description' => 'Guests who are not coming',
        ],
        'pending' => [
            'label' => 'Pending',
            'description' => 'Guests who have not yet responded',
        ],
    ],
    'invitation_stats' => [
        'sent_invitations' => [
            'label' => 'Sent Invitations',
            'description' => 'Total number of groups to which the invitation was sent',
        ],
        'total_views' => [
            'label' => 'Total Views',
            'description' => 'How many times all invitations have been opened in total',
        ],
    ],
];
