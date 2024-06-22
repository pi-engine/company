<?php

return [
    'admin' => [
        [
            'title'       => 'Company list',
            'module'      => 'company',
            'section'     => 'admin',
            'package'     => 'general',
            'handler'     => 'list',
            'permissions' => 'company-list',
            'role'        => [
                'admin',
            ],
        ],
        [
            'title'       => 'Company add',
            'module'      => 'company',
            'section'     => 'admin',
            'package'     => 'general',
            'handler'     => 'add',
            'permissions' => 'company-add',
            'role'        => [
                'admin',
            ],
        ],
        [
            'title'       => 'Company update',
            'module'      => 'company',
            'section'     => 'admin',
            'package'     => 'general',
            'handler'     => 'update',
            'permissions' => 'company-update',
            'role'        => [
                'admin',
            ],
        ],
        [
            'title'       => 'Company update time and status',
            'module'      => 'company',
            'section'     => 'admin',
            'package'     => 'general',
            'handler'     => 'package',
            'permissions' => 'company-time-status',
            'role'        => [
                'admin',
            ],
        ],
        [
            'title'       => 'Company package add',
            'module'      => 'company',
            'section'     => 'admin',
            'package'     => 'package',
            'handler'     => 'add',
            'permissions' => 'company-package-add',
            'role'        => [
                'admin',
            ],
        ],
        [
            'title'       => 'Company package update',
            'module'      => 'company',
            'section'     => 'admin',
            'package'     => 'package',
            'handler'     => 'update',
            'permissions' => 'company-package-update',
            'role'        => [
                'admin',
            ],
        ],
        [
            'title'       => 'Company package list',
            'module'      => 'company',
            'section'     => 'admin',
            'package'     => 'package',
            'handler'     => 'list',
            'permissions' => 'company-package-list',
            'role'        => [
                'admin',
            ],
        ],
    ],

    'api' => [
        [
            'title'       => 'Company member list',
            'module'      => 'company',
            'section'     => 'api',
            'package'     => 'member',
            'handler'     => 'list',
            'permissions' => 'company-member-list',
            'role'        => [
                'companyadmin',
                'companysuperuser',
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
                'companysuperuser',
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
                'companysuperuser',
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
                'companysuperuser',
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
                'companysuperuser',
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
                'companysuperuser',
                'member',
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
                'companysuperuser',
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
                'companysuperuser',
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
                'companysuperuser',
            ],
        ],
        [
            'title'       => 'Company package current',
            'module'      => 'company',
            'section'     => 'api',
            'package'     => 'package',
            'handler'     => 'current',
            'permissions' => 'company-package-current',
            'role'        => [
                'companyadmin',
                'companysuperuser',
                'member',
            ],
        ],
        [
            'title'       => 'Company package list',
            'module'      => 'company',
            'section'     => 'api',
            'package'     => 'package',
            'handler'     => 'list',
            'permissions' => 'company-package-list',
            'role'        => [
                'companyadmin',
                'companysuperuser',
                'member',
            ],
        ],
    ],
];