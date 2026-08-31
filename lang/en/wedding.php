<?php

return [
    'title' => 'Wedding invitation',
    'greeting' => 'You are invited to our wedding. Join us in celebrating love!',

    'setup' => [
        'title' => 'Set up your wedding',
        'intro' => [
            'heading' => 'Let’s set up your wedding invitation',
            'description' => 'We will go through the most important details first. You can return and change anything later.',
            'duration' => 'This usually takes about 3 minutes.',
        ],
        'steps' => [
            'basics' => [
                'title' => 'Basic details',
                'description' => 'Names and wedding date',
                'heading' => 'Start with the basics',
                'help' => 'These details are needed to create the foundation of your invitation.',
            ],
            'appearance' => [
                'title' => 'Invitation look',
                'description' => 'Photo and welcome message',
                'heading' => 'Give your invitation a personal touch',
                'help' => 'Choose the photo and message your guests will see first.',
                'image_callout' => [
                    'heading' => 'An important step for your main image',
                    'description' => 'The image must use one of the available aspect ratios: 9:16 or 4:5. After uploading it, open the image editor, choose the format that works best for you, and crop the image so the important part stays in frame.',
                ],
            ],
            'guest_info' => [
                'title' => 'Guest responses',
                'description' => 'RSVP deadline',
                'heading' => 'Set the response deadline',
                'help' => 'Choose when guests should confirm or decline their attendance.',
            ],
        ],
        'publish_action' => 'Finish and publish invitation',
        'dashboard_action' => 'Back to dashboard',
    ],

    'manage_wedding' => [
        'help_action' => 'More information',
        'basic_information' => [
            'callout' => [
                'description' => 'Enter the names and dates that will appear on the invitation. Use the names exactly as you want guests to see them, and make sure the RSVP deadline is before the wedding date.',
            ],
            'bride_name_placeholder' => 'e.g. Ana',
            'groom_name_placeholder' => 'e.g. Marko',
            'wedding_date_placeholder' => 'Choose the wedding date and time',
            'rsvp_deadline_placeholder' => 'Choose when guest responses close',
            'rsvp_deadline_help' => 'Guests can confirm or decline their attendance until this date and time.',
        ],
        'main_image_description' => 'This image is displayed at the top of the invitation.',
        'main_image_help' => 'Choose the main photo guests will see first. You can crop it after uploading.',
        'invitation_text' => [
            'description' => 'Write the welcome message guests will read on the invitation.',
            'help' => 'This is the main message on your invitation. Use the editor to add emphasis or lists if needed.',
            'welcome_text_placeholder' => 'e.g. We are happy to invite you to celebrate our special day...',
        ],
        'schedule' => [
            'description' => 'Add the events in the order they will happen.',
            'help' => 'Add one item for each part of the celebration. Guests will see only the items marked as visible.',
            'event_name_placeholder' => 'e.g. Ceremony',
            'time_placeholder' => 'e.g. 16:00',
            'address_placeholder' => 'e.g. Church of Saint Sava',
            'map_link_placeholder' => 'Paste a Google Maps link',
            'icon_help' => 'Choose an icon that helps guests recognize this event.',
            'visibility_help' => 'Turn this off if you want to hide this event from guests without deleting it.',
            'visibility_toggle' => 'Visible to guests',
            'visibility_inactive_label' => 'Inactive',
            'visibility_all' => 'Visible to all groups',
            'visibility_custom' => 'Custom group visibility',
            'visibility_manage' => 'Choose which groups can see this event.',
            'visibility_save_first' => 'Save the timeline item before choosing groups.',
            'visibility_modal_heading' => 'Choose groups for :event',
            'visibility_modal_description' => 'New timeline items are visible to every group by default. Uncheck groups that should not see this event.',
            'visibility_save' => 'Save visibility',
            'visible_groups' => 'Groups that can see this event',
            'visibility_select_all' => 'Select all',
            'visibility_deselect_all' => 'Deselect all',
        ],
        'memory_wall' => [
            'callout' => [
                'description' => 'Let guests share photos and messages from your celebration. When enabled, they can use the link or QR code, and you can set how long submissions remain open.',
            ],
            'enable_help' => 'Turn this on to create a page where guests can share photos and memories.',
            'open_until_placeholder' => 'Choose when photo and message sharing closes',
            'open_until_help' => 'The date must be after the wedding and within the allowed period.',
            'url_help' => 'This link is created automatically. Copy it and share it with your guests.',
            'qr_code_help' => 'Print or show this QR code so guests can quickly open the memory wall.',
        ],
        'meta' => [
            'description' => 'Used to generate the link preview when the invitation is shared.',
            'image_fallback' => 'Optional. If left empty, the main image above will be used.',
            'help' => 'These settings affect the title, text and image shown when the invitation link is shared in a chat or social network.',
            'title_help' => 'This is the title shown in the link preview. Leave it empty to use the default wedding title.',
            'description_help' => 'This is the text shown below the title in the link preview. Leave it empty to use the default greeting.',
            'image_help' => 'Optional. Leave it empty to use the main invitation image.',
            'preview' => [
                'heading' => 'Link preview',
                'description' => 'A representative preview of how the invitation may appear when shared in a chat application.',
                'tabs_label' => 'Link preview applications',
                'empty' => 'No link preview applications are selected.',
                'callout' => [
                    'heading' => 'Preview note',
                    'description' => 'This is only a preview. Each chat or social app may implement link previews differently, including the image crop and aspect ratio, so the shared invitation can look different there.',
                ],
                'platforms' => [
                    'whatsapp' => [
                        'label' => 'WhatsApp',
                    ],
                    'viber' => [
                        'label' => 'Viber',
                    ],
                    'messenger' => [
                        'label' => 'Messenger',
                    ],
                    'telegram' => [
                        'label' => 'Telegram',
                    ],
                ],
                'unsaved' => 'Save your changes to update this preview and the shared link.',
                'unsaved_tooltip' => 'There are unsaved changes',
                'image_alt' => 'Invitation preview image',
                'image_placeholder' => 'Invitation image',
            ],
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
        'quick_create' => [
            'title' => 'Create group invitations',
            'intro' => [
                'heading' => 'Create an invitation without the complicated steps',
                'description' => 'Create a group first, then add guests one at a time. You can edit everything else later.',
            ],
            'steps' => [
                'label' => 'Invitation creation steps',
                'group' => '1. Create a group',
                'guests' => '2. Add guests',
            ],
            'group' => [
                'heading' => 'First, create a group',
                'description' => 'One group gets one invitation link. Use a name that helps you recognize who the invitation is for, such as a family or a group of friends.',
                'create_action' => 'Create group and continue',
            ],
            'guest' => [
                'heading' => 'Now add guests',
                'description' => 'Enter one guest at a time. After saving, the fields will be ready for the next guest.',
                'form_description' => 'All you need is the first name, last name, and age group. You can add other details later.',
                'current_group' => 'Current group: :name',
                'added_count' => 'Guests added: :count',
                'empty' => 'You have not added any guests yet.',
                'add_action' => 'Add guest',
                'finish_action' => 'Finish this group',
                'another_action' => 'Create another invitation',
            ],
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
        'wedding_status' => [
            'heading' => 'Public access',
            'rsvp' => [
                'open' => 'Guests can confirm or decline attendance.',
                'closed' => 'Guest responses are no longer accepted.',
                'not_set' => 'Set the RSVP deadline in Wedding Details.',
            ],
            'memory_wall' => [
                'open' => 'Guests can share photos and messages.',
                'closed' => 'Photo and message sharing is currently closed.',
                'not_set' => 'Enable the Memory Wall in Wedding Details.',
            ],
        ],
        'group_views' => [
            'heading' => 'Views by Groups',
        ],
        'guest_age_chart' => [
            'heading' => 'Distribution by Age',
        ],
        'guest_demographics' => [
            'heading' => 'Guest demographics',
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
            'heading' => 'RSVP responses',
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
            'heading' => 'Invitation overview',
            'sent_invitations' => [
                'description' => 'Total number of groups to which the invitation was sent',
            ],
            'total_views' => [
                'description' => 'How many times all invitations have been opened in total',
            ],
        ],
        'invitation_creator' => [
            'heading' => 'Create invitations',
            'description' => 'Click here to create a group and add guests one at a time.',
        ],
        'wedding_setup' => [
            'heading' => 'Set up your wedding',
            'stat' => 'Wedding invitation',
            'status' => 'Not published yet',
            'description' => 'Continue the setup to unlock guests, groups and the rest of the dashboard.',
        ],
        'guest_gender_chart' => [
            'heading' => 'Distribution by Gender',
        ],
    ],

    'memory_wall' => [
        'title' => 'Memory wall',
        'upload' => [
            'title' => 'Share a memory',
            'description' => 'Upload photos and videos from the celebration. Each file can be up to 1 GB.',
            'dropzone' => 'Drop photos or videos here',
            'browse' => 'Choose files',
            'dropzone_hint' => 'JPG, PNG, HEIC, MP4, MOV and more',
            'video_label' => 'Video',
            'selected' => 'files selected',
            'upload_action' => 'Upload files',
            'uploading' => 'Uploading',
            'queued' => 'Ready to upload',
            'completed' => 'Uploaded',
            'failed' => 'Upload failed',
            'retry' => 'Try again',
            'cancel' => 'Cancel',
            'remove' => 'Remove',
            'max_files' => 'You can select up to :count files at once.',
            'max_file_size' => 'Each file can be up to 1 GB.',
            'empty' => 'No files selected yet.',
            'network_error' => 'The upload could not be completed. Check your connection and try again.',
            'completed_summary' => 'Uploaded files remain visible below.',
        ],
        'gallery' => [
            'title' => 'A glimpse of the memories',
            'empty' => 'Be the first to share a memory.',
            'image_alt' => 'Shared memory',
            'video_label' => 'Video',
        ],
        'status' => [
            'uploading' => 'Uploading',
            'processing' => 'Processing',
            'completed' => 'Uploaded',
            'failed' => 'Failed',
        ],
        'validation' => [
            'file_size' => 'The file size must be between 1 byte and 1 GB.',
            'file_type' => 'This file type is not supported.',
            'parts_incomplete' => 'The upload is incomplete. Please try again.',
            'upload_size_mismatch' => 'The uploaded file size does not match the original file.',
            'processing_failed' => 'The upload could not be processed. Please try again.',
            'completed_upload_cannot_be_cancelled' => 'An uploaded file cannot be cancelled.',
        ],
    ],

    'notifications' => [
        'attendance_confirmation_success' => 'Attendance confirmed successfully.',
        'group_created' => 'Group created. Now add guests one at a time.',
        'guest_added' => 'Guest added.',
        'wedding_published' => 'Your wedding invitation is published.',
    ],
];
