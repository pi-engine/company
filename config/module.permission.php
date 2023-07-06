<?php

return [
    'api'   => [
        [
            'module'      => 'company',
            'section'     => 'api',
            'package'     => 'member',
            'handler'     => 'list',
            'permissions' => 'company-member-list',
            'role'        => [
                'companyadmin',
            ],
        ],
        [
            'module'      => 'company',
            'section'     => 'api',
            'package'     => 'member',
            'handler'     => 'add',
            'permissions' => 'company-member-add',
            'role'        => [
                'companyadmin',
            ],
        ],
        [
            'module'      => 'company',
            'section'     => 'api',
            'package'     => 'member',
            'handler'     => 'view',
            'permissions' => 'company-member-view',
            'role'        => [
                'companyadmin',
            ],
        ],
        [
            'module'      => 'company',
            'section'     => 'api',
            'package'     => 'member',
            'handler'     => 'update',
            'permissions' => 'company-member-update',
            'role'        => [
                'companyadmin',
            ],
        ],
        [
            'module'      => 'company',
            'section'     => 'api',
            'package'     => 'profile',
            'handler'     => 'update',
            'permissions' => 'company-profile-update',
            'role'        => [
                'companyadmin',
            ],
        ],
    ],
];