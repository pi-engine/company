<?php

namespace Company\Middleware;

use Company\Service\CompanyService;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Digits;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use User\Handler\ErrorHandler;

class MemberAccessMiddleware implements MiddlewareInterface
{
    /** @var ResponseFactoryInterface */
    protected ResponseFactoryInterface $responseFactory;

    /** @var StreamFactoryInterface */
    protected StreamFactoryInterface $streamFactory;

    /** @var ErrorHandler */
    protected ErrorHandler $errorHandler;

    /** @var CompanyService */
    protected CompanyService $companyService;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        ErrorHandler $errorHandler,
        CompanyService $companyService
    ) {
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
        $this->errorHandler    = $errorHandler;
        $this->companyService  = $companyService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authorization = $request->getAttribute('company_authorization');
        $requestBody   = $request->getParsedBody();

        // Validate data
        $userId = new Input('user_id');
        $userId->getValidatorChain()->attach(new Digits);

        $inputFilter = new InputFilter();
        $inputFilter->add($userId);
        $inputFilter->setData($requestBody);
        if (!$inputFilter->isValid()) {
            $message = [];
            foreach ($inputFilter->getInvalidInput() as $error) {
                $message[$error->getName()] = $error->getName() . ': ' . implode(', ', $error->getMessages());
            }

            $request = $request->withAttribute('status', StatusCodeInterface::STATUS_BAD_REQUEST);
            $request = $request->withAttribute(
                'error',
                [
                    'message' => implode(', ', $message),
                    'code'    => StatusCodeInterface::STATUS_BAD_REQUEST,
                ]
            );
            return $this->errorHandler->handle($request);
        }

        // Get member
        $member = $this->companyService->getMember((int)$requestBody['user_id'], ['company_id' => $authorization['company_id']]);
        if (empty($member)) {
            $request = $request->withAttribute('status', StatusCodeInterface::STATUS_BAD_REQUEST);
            $request = $request->withAttribute(
                'error',
                [
                    'message' => 'This is not your company member !',
                    'code'    => StatusCodeInterface::STATUS_BAD_REQUEST,
                ]
            );
            return $this->errorHandler->handle($request);
        }

        $request = $request->withAttribute('member', $member);

        return $handler->handle($request);
    }
}