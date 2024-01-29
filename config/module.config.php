<?php

namespace Company;

use Company\Middleware\CompanyMiddleware;
use Company\Middleware\MemberAccessMiddleware;
use Company\Middleware\PackageMiddleware;
use Laminas\Mvc\Middleware\PipeSpec;
use Laminas\Router\Http\Literal;
use Logger\Middleware\LoggerRequestMiddleware;
use User\Middleware\AuthenticationMiddleware;
use User\Middleware\AuthorizationMiddleware;
use User\Middleware\InstallerMiddleware;
use User\Middleware\SecurityMiddleware;

return [
    'service_manager' => [
        'aliases'   => [
            Repository\CompanyRepositoryInterface::class => Repository\CompanyRepository::class,
        ],
        'factories' => [
            Repository\CompanyRepository::class             => Factory\Repository\CompanyRepositoryFactory::class,
            Service\CompanyService::class                   => Factory\Service\CompanyServiceFactory::class,
            Middleware\CompanyMiddleware::class             => Factory\Middleware\CompanyMiddlewareFactory::class,
            Middleware\MemberAccessMiddleware::class        => Factory\Middleware\MemberAccessMiddlewareFactory::class,
            Middleware\PackageMiddleware::class             => Factory\Middleware\PackageMiddlewareFactory::class,
            Handler\Api\Authentication\CheckHandler::class  => Factory\Handler\Api\Authentication\CheckHandlerFactory::class,
            Handler\Api\Authentication\ListHandler::class   => Factory\Handler\Api\Authentication\ListHandlerFactory::class,
            Handler\Api\Authentication\SwitchHandler::class => Factory\Handler\Api\Authentication\SwitchHandlerFactory::class,
            Handler\Api\Member\ListHandler::class           => Factory\Handler\Api\Member\ListHandlerFactory::class,
            Handler\Api\Member\AddHandler::class            => Factory\Handler\Api\Member\AddHandlerFactory::class,
            Handler\Api\Member\ViewHandler::class           => Factory\Handler\Api\Member\ViewHandlerFactory::class,
            Handler\Api\Member\UpdateHandler::class         => Factory\Handler\Api\Member\UpdateHandlerFactory::class,
            Handler\Api\Profile\ViewHandler::class          => Factory\Handler\Api\Profile\ViewHandlerFactory::class,
            Handler\Api\Profile\UpdateHandler::class        => Factory\Handler\Api\Profile\UpdateHandlerFactory::class,
            Handler\Api\Profile\ContextHandler::class       => Factory\Handler\Api\Profile\ContextHandlerFactory::class,
            Handler\Api\Profile\SettingHandler::class       => Factory\Handler\Api\Profile\SettingHandlerFactory::class,
            Handler\Api\Package\CurrentHandler::class       => Factory\Handler\Api\Package\CurrentHandlerFactory::class,
            Handler\Api\Package\ListHandler::class          => Factory\Handler\Api\Package\ListHandlerFactory::class,
            Handler\InstallerHandler::class                 => Factory\Handler\InstallerHandlerFactory::class,
        ],
    ],
    'router'          => [
        'routes' => [
            // Api section
            'api_company'   => [
                'type'         => Literal::class,
                'options'      => [
                    'route'    => '/company',
                    'defaults' => [],
                ],
                'child_routes' => [
                    // authentication section
                    'authentication' => [
                        'type'         => Literal::class,
                        'options'      => [
                            'route'    => '/authentication',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'check'  => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/check',
                                    'defaults' => [
                                        'module'     => 'company',
                                        'section'    => 'api',
                                        'package'    => 'authentication',
                                        'handler'    => 'check',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            CompanyMiddleware::class,
                                            LoggerRequestMiddleware::class,
                                            Handler\Api\Authentication\CheckHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'list'   => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/list',
                                    'defaults' => [
                                        'module'     => 'company',
                                        'section'    => 'api',
                                        'package'    => 'authentication',
                                        'handler'    => 'list',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            CompanyMiddleware::class,
                                            LoggerRequestMiddleware::class,
                                            Handler\Api\Authentication\ListHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'switch' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/switch',
                                    'defaults' => [
                                        'module'     => 'company',
                                        'section'    => 'api',
                                        'package'    => 'authentication',
                                        'handler'    => 'switch',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            LoggerRequestMiddleware::class,
                                            Handler\Api\Authentication\SwitchHandler::class
                                        ),
                                    ],
                                ],
                            ],
                        ],
                    ],
                    // member section
                    'member'         => [
                        'type'         => Literal::class,
                        'options'      => [
                            'route'    => '/member',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'list'   => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/list',
                                    'defaults' => [
                                        'title'       => 'Company member list',
                                        'module'      => 'company',
                                        'section'     => 'api',
                                        'package'     => 'member',
                                        'handler'     => 'list',
                                        'permissions' => 'company-member-list',
                                        'controller'  => PipeSpec::class,
                                        'middleware'  => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            CompanyMiddleware::class,
                                            LoggerRequestMiddleware::class,
                                            Handler\Api\Member\ListHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'add'    => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/add',
                                    'defaults' => [
                                        'title'       => 'Company member add',
                                        'module'      => 'company',
                                        'section'     => 'api',
                                        'package'     => 'member',
                                        'handler'     => 'add',
                                        'permissions' => 'company-member-add',
                                        'controller'  => PipeSpec::class,
                                        'middleware'  => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            CompanyMiddleware::class,
                                            PackageMiddleware::class,
                                            LoggerRequestMiddleware::class,
                                            Handler\Api\Member\AddHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'view'   => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/view',
                                    'defaults' => [
                                        'title'       => 'Company member view',
                                        'module'      => 'company',
                                        'section'     => 'api',
                                        'package'     => 'member',
                                        'handler'     => 'view',
                                        'permissions' => 'company-member-view',
                                        'controller'  => PipeSpec::class,
                                        'middleware'  => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            CompanyMiddleware::class,
                                            PackageMiddleware::class,
                                            MemberAccessMiddleware::class,
                                            LoggerRequestMiddleware::class,
                                            Handler\Api\Member\ViewHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'update' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/update',
                                    'defaults' => [
                                        'title'       => 'Company member update',
                                        'module'      => 'company',
                                        'section'     => 'api',
                                        'package'     => 'member',
                                        'handler'     => 'update',
                                        'permissions' => 'company-member-update',
                                        'controller'  => PipeSpec::class,
                                        'middleware'  => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            CompanyMiddleware::class,
                                            PackageMiddleware::class,
                                            MemberAccessMiddleware::class,
                                            LoggerRequestMiddleware::class,
                                            Handler\Api\Member\UpdateHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'role'   => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/role',
                                    'defaults' => [
                                        'title'       => 'Company member role',
                                        'module'      => 'company',
                                        'section'     => 'api',
                                        'package'     => 'member',
                                        'handler'     => 'role',
                                        'permissions' => 'company-member-role',
                                        'controller'  => PipeSpec::class,
                                        'middleware'  => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            CompanyMiddleware::class,
                                            PackageMiddleware::class,
                                            LoggerRequestMiddleware::class,
                                            Handler\Api\Member\RoleHandler::class
                                        ),
                                    ],
                                ],
                            ],
                        ],
                    ],
                    // profile section
                    'profile'        => [
                        'type'         => Literal::class,
                        'options'      => [
                            'route'    => '/profile',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'view'    => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/view',
                                    'defaults' => [
                                        'title'       => 'Company profile view',
                                        'module'      => 'company',
                                        'section'     => 'api',
                                        'package'     => 'profile',
                                        'handler'     => 'view',
                                        'permissions' => 'company-profile-view',
                                        'controller'  => PipeSpec::class,
                                        'middleware'  => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            CompanyMiddleware::class,
                                            LoggerRequestMiddleware::class,
                                            Handler\Api\Profile\ViewHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'update'  => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/update',
                                    'defaults' => [
                                        'title'       => 'Company profile update',
                                        'module'      => 'company',
                                        'section'     => 'api',
                                        'package'     => 'profile',
                                        'handler'     => 'update',
                                        'permissions' => 'company-profile-update',
                                        'controller'  => PipeSpec::class,
                                        'middleware'  => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            CompanyMiddleware::class,
                                            LoggerRequestMiddleware::class,
                                            Handler\Api\Profile\UpdateHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'context' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/context',
                                    'defaults' => [
                                        'title'       => 'Company profile update context',
                                        'module'      => 'company',
                                        'section'     => 'api',
                                        'package'     => 'profile',
                                        'handler'     => 'context',
                                        'permissions' => 'company-profile-context',
                                        'controller'  => PipeSpec::class,
                                        'middleware'  => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            CompanyMiddleware::class,
                                            LoggerRequestMiddleware::class,
                                            Handler\Api\Profile\ContextHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'setting' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/setting',
                                    'defaults' => [
                                        'title'       => 'Company profile update setting',
                                        'module'      => 'company',
                                        'section'     => 'api',
                                        'package'     => 'profile',
                                        'handler'     => 'setting',
                                        'permissions' => 'company-profile-setting',
                                        'controller'  => PipeSpec::class,
                                        'middleware'  => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            CompanyMiddleware::class,
                                            LoggerRequestMiddleware::class,
                                            Handler\Api\Profile\SettingHandler::class
                                        ),
                                    ],
                                ],
                            ],
                        ],
                    ],
                    // package section
                    'package'        => [
                        'type'         => Literal::class,
                        'options'      => [
                            'route'    => '/package',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'current' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/current',
                                    'defaults' => [
                                        'title'       => 'Company package current',
                                        'module'      => 'company',
                                        'section'     => 'api',
                                        'package'     => 'package',
                                        'handler'     => 'current',
                                        'permissions' => 'company-package-current',
                                        'controller'  => PipeSpec::class,
                                        'middleware'  => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            CompanyMiddleware::class,
                                            LoggerRequestMiddleware::class,
                                            Handler\Api\Package\CurrentHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'list' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/list',
                                    'defaults' => [
                                        'title'       => 'Company package list',
                                        'module'      => 'company',
                                        'section'     => 'api',
                                        'package'     => 'package',
                                        'handler'     => 'list',
                                        'permissions' => 'company-package-list',
                                        'controller'  => PipeSpec::class,
                                        'middleware'  => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            CompanyMiddleware::class,
                                            LoggerRequestMiddleware::class,
                                            Handler\Api\Package\ListHandler::class
                                        ),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            // Admin section
            'admin_company' => [
                'type'         => Literal::class,
                'options'      => [
                    'route'    => '/admin/company',
                    'defaults' => [],
                ],
                'child_routes' => [

                    // Admin installer
                    'installer' => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/installer',
                            'defaults' => [
                                'module'     => 'company',
                                'section'    => 'admin',
                                'package'    => 'installer',
                                'handler'    => 'installer',
                                'controller' => PipeSpec::class,
                                'middleware' => new PipeSpec(
                                    SecurityMiddleware::class,
                                    AuthenticationMiddleware::class,
                                    InstallerMiddleware::class,
                                    LoggerRequestMiddleware::class,
                                    Handler\InstallerHandler::class
                                ),
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'view_manager'    => [
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
];