<?php

namespace Pi\Company;

use Laminas\Mvc\Middleware\PipeSpec;
use Laminas\Router\Http\Literal;
use Pi\Company\Middleware\CompanyMiddleware;
use Pi\Company\Middleware\MemberAccessMiddleware;
use Pi\Company\Middleware\PackageMiddleware;
use Pi\Core\Middleware\InstallerMiddleware;
use Pi\Core\Middleware\RequestPreparationMiddleware;
use Pi\Core\Middleware\SecurityMiddleware;
use Pi\Logger\Middleware\LoggerRequestMiddleware;
use Pi\User\Middleware\AuthenticationMiddleware;
use Pi\User\Middleware\AuthorizationMiddleware;

return [
    'service_manager' => [
        'aliases'   => [
            Repository\CompanyRepositoryInterface::class => Repository\CompanyRepository::class,
        ],
        'factories' => [
            Repository\CompanyRepository::class              => Factory\Repository\CompanyRepositoryFactory::class,
            Service\CompanyService::class                    => Factory\Service\CompanyServiceFactory::class,
            Middleware\CompanyMiddleware::class              => Factory\Middleware\CompanyMiddlewareFactory::class,
            Middleware\MemberAccessMiddleware::class         => Factory\Middleware\MemberAccessMiddlewareFactory::class,
            Middleware\PackageMiddleware::class              => Factory\Middleware\PackageMiddlewareFactory::class,
            Middleware\TeamAccessMiddleware::class           => Factory\Middleware\TeamAccessMiddlewareFactory::class,
            Middleware\TeamMemberAccessMiddleware::class     => Factory\Middleware\TeamMemberAccessMiddlewareFactory::class,
            Handler\Admin\AddHandler::class                  => Factory\Handler\Admin\AddHandlerFactory::class,
            Handler\Admin\UpdateHandler::class               => Factory\Handler\Admin\UpdateHandlerFactory::class,
            Handler\Admin\ListHandler::class                 => Factory\Handler\Admin\ListHandlerFactory::class,
            Handler\Admin\PackageUpdateHandler::class        => Factory\Handler\Admin\PackageUpdateHandlerFactory::class,
            Handler\Admin\ReportHandler::class               => Factory\Handler\Admin\ReportHandlerFactory::class,
            Handler\Admin\Package\AddHandler::class          => Factory\Handler\Admin\Package\AddHandlerFactory::class,
            Handler\Admin\Package\ListHandler::class         => Factory\Handler\Admin\Package\ListHandlerFactory::class,
            Handler\Admin\Package\UpdateHandler::class       => Factory\Handler\Admin\Package\UpdateHandlerFactory::class,
            Handler\Admin\Member\AddHandler::class           => Factory\Handler\Admin\Member\AddHandlerFactory::class,
            Handler\Admin\Member\ListHandler::class          => Factory\Handler\Admin\Member\ListHandlerFactory::class,
            Handler\Admin\Member\UpdateHandler::class        => Factory\Handler\Admin\Member\UpdateHandlerFactory::class,
            Handler\Admin\Member\ViewHandler::class          => Factory\Handler\Admin\Member\ViewHandlerFactory::class,
            Handler\Admin\Team\ListHandler::class            => Factory\Handler\Admin\Team\ListHandlerFactory::class,
            Handler\Admin\Team\Member\ListHandler::class     => Factory\Handler\Admin\Team\Member\ListHandlerFactory::class,
            Handler\Api\Authentication\CheckHandler::class   => Factory\Handler\Api\Authentication\CheckHandlerFactory::class,
            Handler\Api\Authentication\ListHandler::class    => Factory\Handler\Api\Authentication\ListHandlerFactory::class,
            Handler\Api\Authentication\SwitchHandler::class  => Factory\Handler\Api\Authentication\SwitchHandlerFactory::class,
            Handler\Api\Authentication\RefreshHandler::class => Factory\Handler\Api\Authentication\RefreshHandlerFactory::class,
            Handler\Api\Member\ListHandler::class            => Factory\Handler\Api\Member\ListHandlerFactory::class,
            Handler\Api\Member\AddHandler::class             => Factory\Handler\Api\Member\AddHandlerFactory::class,
            Handler\Api\Member\ViewHandler::class            => Factory\Handler\Api\Member\ViewHandlerFactory::class,
            Handler\Api\Member\UpdateHandler::class          => Factory\Handler\Api\Member\UpdateHandlerFactory::class,
            Handler\Api\Member\RoleHandler::class            => Factory\Handler\Api\Member\RoleHandlerFactory::class,
            Handler\Api\Profile\ViewHandler::class           => Factory\Handler\Api\Profile\ViewHandlerFactory::class,
            Handler\Api\Profile\UpdateHandler::class         => Factory\Handler\Api\Profile\UpdateHandlerFactory::class,
            Handler\Api\Profile\ContextHandler::class        => Factory\Handler\Api\Profile\ContextHandlerFactory::class,
            Handler\Api\Profile\SettingHandler::class        => Factory\Handler\Api\Profile\SettingHandlerFactory::class,
            Handler\Api\Package\CurrentHandler::class        => Factory\Handler\Api\Package\CurrentHandlerFactory::class,
            Handler\Api\Package\ListHandler::class           => Factory\Handler\Api\Package\ListHandlerFactory::class,
            Handler\Api\Team\ListHandler::class              => Factory\Handler\Api\Team\ListHandlerFactory::class,
            Handler\Api\Team\AddHandler::class               => Factory\Handler\Api\Team\AddHandlerFactory::class,
            Handler\Api\Team\UpdateHandler::class            => Factory\Handler\Api\Team\UpdateHandlerFactory::class,
            Handler\Api\Team\Member\ListHandler::class       => Factory\Handler\Api\Team\Member\ListHandlerFactory::class,
            Handler\Api\Team\Member\AddHandler::class        => Factory\Handler\Api\Team\Member\AddHandlerFactory::class,
            Handler\Api\Team\Member\UpdateHandler::class     => Factory\Handler\Api\Team\Member\UpdateHandlerFactory::class,
            Handler\Api\Team\Member\DeleteHandler::class     => Factory\Handler\Api\Team\Member\DeleteHandlerFactory::class,
            Handler\InstallerHandler::class                  => Factory\Handler\InstallerHandlerFactory::class,
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
                            'check'   => [
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
                                            RequestPreparationMiddleware::class,
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            CompanyMiddleware::class,
                                            LoggerRequestMiddleware::class,
                                            Handler\Api\Authentication\CheckHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'list'    => [
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
                                            RequestPreparationMiddleware::class,
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            CompanyMiddleware::class,
                                            LoggerRequestMiddleware::class,
                                            Handler\Api\Authentication\ListHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'switch'  => [
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
                                            RequestPreparationMiddleware::class,
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            LoggerRequestMiddleware::class,
                                            Handler\Api\Authentication\SwitchHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'refresh' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/refresh-token',
                                    'defaults' => [
                                        'module'     => 'company',
                                        'section'    => 'api',
                                        'package'    => 'authentication',
                                        'handler'    => 'refresh',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            RequestPreparationMiddleware::class,
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            CompanyMiddleware::class,
                                            LoggerRequestMiddleware::class,
                                            Handler\Api\Authentication\RefreshHandler::class
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
                                            RequestPreparationMiddleware::class,
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
                                            RequestPreparationMiddleware::class,
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
                                            RequestPreparationMiddleware::class,
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
                                            RequestPreparationMiddleware::class,
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
                                            RequestPreparationMiddleware::class,
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
                                            RequestPreparationMiddleware::class,
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
                                            RequestPreparationMiddleware::class,
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
                                            RequestPreparationMiddleware::class,
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
                                            RequestPreparationMiddleware::class,
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
                                            RequestPreparationMiddleware::class,
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
                            'list'    => [
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
                                            RequestPreparationMiddleware::class,
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
                    // team section
                    'team'           => [
                        'type'         => Literal::class,
                        'options'      => [
                            'route'    => '/team',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'list'   => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/list',
                                    'defaults' => [
                                        'title'       => 'Company team list',
                                        'module'      => 'company',
                                        'section'     => 'api',
                                        'package'     => 'team',
                                        'handler'     => 'list',
                                        'permissions' => 'company-team-list',
                                        'controller'  => PipeSpec::class,
                                        'middleware'  => new PipeSpec(
                                            RequestPreparationMiddleware::class,
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            CompanyMiddleware::class,
                                            LoggerRequestMiddleware::class,
                                            Handler\Api\Team\ListHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'add'    => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/add',
                                    'defaults' => [
                                        'title'       => 'Company team add',
                                        'module'      => 'company',
                                        'section'     => 'api',
                                        'package'     => 'team',
                                        'handler'     => 'add',
                                        'permissions' => 'company-team-add',
                                        'controller'  => PipeSpec::class,
                                        'middleware'  => new PipeSpec(
                                            RequestPreparationMiddleware::class,
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            CompanyMiddleware::class,
                                            LoggerRequestMiddleware::class,
                                            Handler\Api\Team\AddHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'update' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/update',
                                    'defaults' => [
                                        'title'       => 'Company team update',
                                        'module'      => 'company',
                                        'section'     => 'api',
                                        'package'     => 'team',
                                        'handler'     => 'update',
                                        'permissions' => 'company-team-update',
                                        'controller'  => PipeSpec::class,
                                        'middleware'  => new PipeSpec(
                                            RequestPreparationMiddleware::class,
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            CompanyMiddleware::class,
                                            Middleware\TeamAccessMiddleware::class,
                                            LoggerRequestMiddleware::class,
                                            Handler\Api\Team\UpdateHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'member' => [
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
                                                'title'       => 'Company team member list',
                                                'module'      => 'company',
                                                'section'     => 'api',
                                                'package'     => 'team-member',
                                                'handler'     => 'list',
                                                'permissions' => 'company-team-member-list',
                                                'controller'  => PipeSpec::class,
                                                'middleware'  => new PipeSpec(
                                                    RequestPreparationMiddleware::class,
                                                    SecurityMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    CompanyMiddleware::class,
                                                    LoggerRequestMiddleware::class,
                                                    Handler\Api\Team\Member\ListHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                    'add'    => [
                                        'type'    => Literal::class,
                                        'options' => [
                                            'route'    => '/add',
                                            'defaults' => [
                                                'title'       => 'Company team member add',
                                                'module'      => 'company',
                                                'section'     => 'api',
                                                'package'     => 'team-member',
                                                'handler'     => 'add',
                                                'permissions' => 'company-team-member-add',
                                                'controller'  => PipeSpec::class,
                                                'middleware'  => new PipeSpec(
                                                    RequestPreparationMiddleware::class,
                                                    SecurityMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    CompanyMiddleware::class,
                                                    LoggerRequestMiddleware::class,
                                                    Handler\Api\Team\Member\AddHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                    'update' => [
                                        'type'    => Literal::class,
                                        'options' => [
                                            'route'    => '/update',
                                            'defaults' => [
                                                'title'       => 'Company team member update',
                                                'module'      => 'company',
                                                'section'     => 'api',
                                                'package'     => 'team-member',
                                                'handler'     => 'update',
                                                'permissions' => 'company-team-member-update',
                                                'controller'  => PipeSpec::class,
                                                'middleware'  => new PipeSpec(
                                                    RequestPreparationMiddleware::class,
                                                    SecurityMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    CompanyMiddleware::class,
                                                    Middleware\TeamMemberAccessMiddleware::class,
                                                    LoggerRequestMiddleware::class,
                                                    Handler\Api\Team\Member\UpdateHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                    'delete' => [
                                        'type'    => Literal::class,
                                        'options' => [
                                            'route'    => '/delete',
                                            'defaults' => [
                                                'title'       => 'Company team member delete',
                                                'module'      => 'company',
                                                'section'     => 'api',
                                                'package'     => 'team-member',
                                                'handler'     => 'delete',
                                                'permissions' => 'company-team-member-delete',
                                                'controller'  => PipeSpec::class,
                                                'middleware'  => new PipeSpec(
                                                    RequestPreparationMiddleware::class,
                                                    SecurityMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    CompanyMiddleware::class,
                                                    Middleware\TeamMemberAccessMiddleware::class,
                                                    LoggerRequestMiddleware::class,
                                                    Handler\Api\Team\Member\DeleteHandler::class
                                                ),
                                            ],
                                        ],
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
                    // Admin general section
                    'list'           => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/list',
                            'defaults' => [
                                'title'       => 'Company list',
                                'module'      => 'company',
                                'section'     => 'admin',
                                'package'     => 'general',
                                'handler'     => 'list',
                                'permissions' => 'admin-company-list',
                                'controller'  => PipeSpec::class,
                                'middleware'  => new PipeSpec(
                                    RequestPreparationMiddleware::class,
                                    SecurityMiddleware::class,
                                    AuthenticationMiddleware::class,
                                    AuthorizationMiddleware::class,
                                    Handler\Admin\ListHandler::class
                                ),
                            ],
                        ],
                    ],
                    'add'            => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/add',
                            'defaults' => [
                                'title'       => 'Company add',
                                'module'      => 'company',
                                'section'     => 'admin',
                                'package'     => 'general',
                                'handler'     => 'add',
                                'permissions' => 'admin-company-add',
                                'controller'  => PipeSpec::class,
                                'middleware'  => new PipeSpec(
                                    RequestPreparationMiddleware::class,
                                    SecurityMiddleware::class,
                                    AuthenticationMiddleware::class,
                                    AuthorizationMiddleware::class,
                                    Handler\Admin\AddHandler::class
                                ),
                            ],
                        ],
                    ],
                    'update'         => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/update',
                            'defaults' => [
                                'title'       => 'Company update',
                                'module'      => 'company',
                                'section'     => 'admin',
                                'package'     => 'general',
                                'handler'     => 'update',
                                'permissions' => 'admin-company-update',
                                'controller'  => PipeSpec::class,
                                'middleware'  => new PipeSpec(
                                    RequestPreparationMiddleware::class,
                                    SecurityMiddleware::class,
                                    AuthenticationMiddleware::class,
                                    AuthorizationMiddleware::class,
                                    Handler\Admin\UpdateHandler::class
                                ),
                            ],
                        ],
                    ],
                    'package-update' => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/package-update',
                            'defaults' => [
                                'title'       => 'Company update time and status',
                                'module'      => 'company',
                                'section'     => 'admin',
                                'package'     => 'general',
                                'handler'     => 'package',
                                'permissions' => 'admin-company-time-status',
                                'controller'  => PipeSpec::class,
                                'middleware'  => new PipeSpec(
                                    RequestPreparationMiddleware::class,
                                    SecurityMiddleware::class,
                                    AuthenticationMiddleware::class,
                                    AuthorizationMiddleware::class,
                                    Handler\Admin\PackageUpdateHandler::class
                                ),
                            ],
                        ],
                    ],
                    'report'         => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/report',
                            'defaults' => [
                                'title'       => 'Company report',
                                'module'      => 'company',
                                'section'     => 'admin',
                                'package'     => 'general',
                                'handler'     => 'package',
                                'permissions' => 'admin-company-report',
                                'controller'  => PipeSpec::class,
                                'middleware'  => new PipeSpec(
                                    RequestPreparationMiddleware::class,
                                    SecurityMiddleware::class,
                                    AuthenticationMiddleware::class,
                                    AuthorizationMiddleware::class,
                                    Handler\Admin\ReportHandler::class
                                ),
                            ],
                        ],
                    ],
                    // Admin package section
                    'package'        => [
                        'type'         => Literal::class,
                        'options'      => [
                            'route'    => '/package',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'add'    => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/add',
                                    'defaults' => [
                                        'title'       => 'Company package add',
                                        'module'      => 'company',
                                        'section'     => 'admin',
                                        'package'     => 'package',
                                        'handler'     => 'add',
                                        'permissions' => 'admin-company-package-add',
                                        'controller'  => PipeSpec::class,
                                        'middleware'  => new PipeSpec(
                                            RequestPreparationMiddleware::class,
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            Handler\Admin\Package\AddHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'update' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/update',
                                    'defaults' => [
                                        'title'       => 'Company package update',
                                        'module'      => 'company',
                                        'section'     => 'admin',
                                        'package'     => 'package',
                                        'handler'     => 'update',
                                        'permissions' => 'admin-company-package-update',
                                        'controller'  => PipeSpec::class,
                                        'middleware'  => new PipeSpec(
                                            RequestPreparationMiddleware::class,
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            Handler\Admin\Package\UpdateHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'list'   => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/list',
                                    'defaults' => [
                                        'title'       => 'Company package list',
                                        'module'      => 'company',
                                        'section'     => 'admin',
                                        'package'     => 'package',
                                        'handler'     => 'list',
                                        'permissions' => 'admin-company-package-list',
                                        'controller'  => PipeSpec::class,
                                        'middleware'  => new PipeSpec(
                                            RequestPreparationMiddleware::class,
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            Handler\Admin\Package\ListHandler::class
                                        ),
                                    ],
                                ],
                            ],

                        ],
                    ],
                    // Admin member section
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
                                        'section'     => 'admin',
                                        'package'     => 'member',
                                        'handler'     => 'list',
                                        'permissions' => 'admin-company-member-list',
                                        'controller'  => PipeSpec::class,
                                        'middleware'  => new PipeSpec(
                                            RequestPreparationMiddleware::class,
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            Handler\Admin\Member\ListHandler::class
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
                                        'section'     => 'admin',
                                        'package'     => 'member',
                                        'handler'     => 'add',
                                        'permissions' => 'admin-company-member-add',
                                        'controller'  => PipeSpec::class,
                                        'middleware'  => new PipeSpec(
                                            RequestPreparationMiddleware::class,
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            Handler\Admin\Member\AddHandler::class
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
                                        'section'     => 'admin',
                                        'package'     => 'member',
                                        'handler'     => 'update',
                                        'permissions' => 'admin-company-member-update',
                                        'controller'  => PipeSpec::class,
                                        'middleware'  => new PipeSpec(
                                            RequestPreparationMiddleware::class,
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            Handler\Admin\Member\UpdateHandler::class
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
                                        'section'     => 'admin',
                                        'package'     => 'member',
                                        'handler'     => 'view',
                                        'permissions' => 'admin-company-member-view',
                                        'controller'  => PipeSpec::class,
                                        'middleware'  => new PipeSpec(
                                            RequestPreparationMiddleware::class,
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            Handler\Admin\Member\ViewHandler::class
                                        ),
                                    ],
                                ],
                            ],
                        ],
                    ],
                    // Admin team section
                    'team'           => [
                        'type'         => Literal::class,
                        'options'      => [
                            'route'    => '/team',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'list'   => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/list',
                                    'defaults' => [
                                        'title'       => 'Company team list',
                                        'module'      => 'company',
                                        'section'     => 'admin',
                                        'package'     => 'team',
                                        'handler'     => 'list',
                                        'permissions' => 'admin-company-team-list',
                                        'controller'  => PipeSpec::class,
                                        'middleware'  => new PipeSpec(
                                            RequestPreparationMiddleware::class,
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            Handler\Admin\Team\ListHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'member' => [
                                'type'         => Literal::class,
                                'options'      => [
                                    'route'    => '/member',
                                    'defaults' => [],
                                ],
                                'child_routes' => [
                                    'list' => [
                                        'type'    => Literal::class,
                                        'options' => [
                                            'route'    => '/list',
                                            'defaults' => [
                                                'title'       => 'Company team member list',
                                                'module'      => 'company',
                                                'section'     => 'admin',
                                                'package'     => 'team-member',
                                                'handler'     => 'list',
                                                'permissions' => 'admin-company-team-member-list',
                                                'controller'  => PipeSpec::class,
                                                'middleware'  => new PipeSpec(
                                                    RequestPreparationMiddleware::class,
                                                    SecurityMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    Handler\Admin\Team\ListHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    // Admin installer
                    'installer'      => [
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
                                    RequestPreparationMiddleware::class,
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