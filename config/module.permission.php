<?php

return [
    'api'   => [
        [
            'title'       => 'Company member list',
            'module'      => 'company',
            'section'     => 'api',
            'package'     => 'member',
            'handler'     => 'list',
            'permissions' => 'company-member-list',
            'role'        => [
                'companyadmin',
                'companymanager',
                'companyaudit',
                'companymember',
                'companyexternal',
                'companyviewer',
            ],
        ],
        [
            'title'       => 'Company member add',
            'module'      => 'company',
            'section'     => 'api',
            'package'     => 'member',
            'handler'     => 'add',
            'permissions' => 'company-member-add',
            'role'        => [
                'companyadmin',
                'companymanager',
            ],
        ],
        [
            'title'       => 'Company member view',
            'module'      => 'company',
            'section'     => 'api',
            'package'     => 'member',
            'handler'     => 'view',
            'permissions' => 'company-member-view',
            'role'        => [
                'companyadmin',
                'companymanager',
            ],
        ],
        [
            'title'       => 'Company member update',
            'module'      => 'company',
            'section'     => 'api',
            'package'     => 'member',
            'handler'     => 'update',
            'permissions' => 'company-member-update',
            'role'        => [
                'companyadmin',
                'companymanager',
            ],
        ],
        [
            'title'       => 'Company member role',
            'module'      => 'company',
            'section'     => 'api',
            'package'     => 'member',
            'handler'     => 'role',
            'permissions' => 'company-member-role',
            'role'        => [
                'companyadmin',
                'companymanager',
            ],
        ],
        [
            'title'       => 'Company profile view',
            'module'      => 'company',
            'section'     => 'api',
            'package'     => 'profile',
            'handler'     => 'view',
            'permissions' => 'company-profile-view',
            'role'        => [
                'companyadmin',
                'companymanager',
            ],
        ],
        [
            'title'       => 'Company profile update',
            'module'      => 'company',
            'section'     => 'api',
            'package'     => 'profile',
            'handler'     => 'update',
            'permissions' => 'company-profile-update',
            'role'        => [
                'companyadmin',
                'companymanager',
            ],
        ],
        [
            'title'       => 'Company profile update context',
            'module'      => 'company',
            'section'     => 'api',
            'package'     => 'profile',
            'handler'     => 'context',
            'permissions' => 'company-profile-context',
            'role'        => [
                'companyadmin',
                'companymanager',
            ],
        ],
        [
            'title'       => 'Company profile update setting',
            'module'      => 'company',
            'section'     => 'api',
            'package'     => 'profile',
            'handler'     => 'setting',
            'permissions' => 'company-profile-setting',
            'role'        => [
                'companyadmin',
                'companymanager',
            ],
        ],
    ],
];