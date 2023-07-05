<?php

namespace Company\Factory\Handler\Api\Member;

use Company\Handler\Api\Member\UpdateHandler;
use Company\Service\CompanyService;
use Interop\Container\Containerinterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class  UpdateHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): UpdateHandler
    {
        return new UpdateHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(CompanyService::class)
        );
    }
}