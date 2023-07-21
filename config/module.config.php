<?php

namespace Company;

use Company\Middleware\CheckMiddleware;
use Laminas\Mvc\Middleware\PipeSpec;
use Laminas\Router\Http\Literal;
use User\Middleware\AuthenticationMiddleware;
use User\Middleware\AuthorizationMiddleware;
use User\Middleware\InstallerMiddleware;
use User\Middleware\SecurityMiddleware;

return [
    'service_manager' => [
        'aliases'   => [
            Repository\HiveRepositoryInterface::class => Repository\HiveRepository::class,
        ],
        'factories' => [
            Repository\HiveRepository::class               => Factory\Repository\CompanyRepositoryFactory::class,
            Service\CompanyService::class                  => Factory\Service\CompanyServiceFactory::class,
            Middleware\CheckMiddleware::class              => Factory\Middleware\CheckMiddlewareFactory::class,
            Handler\Api\Authentication\CheckHandler::class => Factory\Handler\Api\Authentication\CheckHandlerFactory::class,
            Handler\Api\Member\ListHandler::class          => Factory\Handler\Api\Member\ListHandlerFactory::class,
            Handler\Api\Member\AddHandler::class           => Factory\Handler\Api\Member\AddHandlerFactory::class,
            Handler\Api\Member\ViewHandler::class          => Factory\Handler\Api\Member\ViewHandlerFactory::class,
            Handler\Api\Member\UpdateHandler::class        => Factory\Handler\Api\Member\UpdateHandlerFactory::class,
            Handler\Api\Profile\ViewHandler::class         => Factory\Handler\Api\Profile\ViewHandlerFactory::class,
            Handler\Api\Profile\UpdateHandler::class       => Factory\Handler\Api\Profile\UpdateHandlerFactory::class,
            Handler\InstallerHandler::class                => Factory\Handler\InstallerHandlerFactory::class,
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
                            'check' => [
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
                                            CheckMiddleware::class,
                                            Handler\Api\Authentication\CheckHandler::class
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
                                            CheckMiddleware::class,
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
                                            CheckMiddleware::class,
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
                                            CheckMiddleware::class,
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
                                            CheckMiddleware::class,
                                            Handler\Api\Member\UpdateHandler::class
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
                            'view'   => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/view',
                                    'defaults' => [
                                        'module'     => 'company',
                                        'section'    => 'api',
                                        'package'    => 'profile',
                                        'handler'    => 'view',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            CheckMiddleware::class,
                                            Handler\Api\Profile\ViewHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'update' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/update',
                                    'defaults' => [
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
                                            CheckMiddleware::class,
                                            Handler\Api\Profile\UpdateHandler::class
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