<?php

declare(strict_types=1);

namespace SimpleSAML\Module\autotest\Controller;

use SimpleSAML\Auth;
use SimpleSAML\Configuration;
use SimpleSAML\Error;
use SimpleSAML\Session;
use SimpleSAML\XHTML\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller class for the autotest module.
 *
 * This class serves the different views available in the module.
 *
 * @package simplesamlphp/simplesamlphp-module-autotest
 */
class Autotest
{
    /**
     * @var \SimpleSAML\Auth\Simple|class-string
     */
    protected $authSimple = Auth\Simple::class;


    /**
     * Controller constructor.
     *
     * It initializes the global configuration and session for the controllers implemented here.
     *
     * @param \SimpleSAML\Configuration $config The configuration to use by the controllers.
     * @param \SimpleSAML\Session $session The session to use by the controllers.
     *
     * @throws \Exception
     */
    public function __construct(
        protected Configuration $config,
        protected Session $session,
    ) {
    }


    /**
     * Inject the \SimpleSAML\Auth\Simple dependency.
     *
     * @param \SimpleSAML\Auth\Simple $authSimple
     */
    public function setAuthSimple(Auth\Simple $authSimple): void
    {
        $this->authSimple = $authSimple;
    }


    /**
     * Test attributes.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The current request.
     *
     * @return \SimpleSAML\XHTML\Template
     */
    public function attributes(Request $request): Template
    {
        try {
            $as = $this->getAuthSource($request);
            if (!$as->isAuthenticated()) {
                throw new Error\Exception('Not authenticated.');
            }
        } catch (Error\Exception $e) {
            return $this->sendFailure($e);
        }

        $attributes = $as->getAttributes();
        return $this->sendSuccess($attributes);
    }


    /**
     * Test login.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The current request.
     *
     * @return \SimpleSAML\XHTML\Template
     */
    public function login(Request $request): Template
    {
        try {
            $as = $this->getAuthSource($request);
        } catch (Error\Exception $e) {
            return $this->sendFailure($e);
        }

        if (!$as->isAuthenticated()) {
            $as->requireAuth();
        }

        return $this->sendSuccess();
    }


    /**
     * Test logout.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The current request.
     *
     * @return \SimpleSAML\XHTML\Template
     */
    public function logout(Request $request): Template
    {
        try {
            $as = $this->getAuthSource($request);
        } catch (Error\Exception $e) {
            return $this->sendFailure($e);
        }

        if ($as->isAuthenticated()) {
            $as->logout();
        }

        return $this->sendSuccess();
    }


    /**
     * Get the AuthSource given by the SourceID parameter from the request
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \SimpleSAML\Auth\Simple
     *
     * @throws \SimpleSAML\Error\BadRequest if SourceID is not part of the query parameters
     *
     */
    private function getAuthSource(Request $request): Auth\Simple
    {
        $sourceId = $request->query->get('SourceID', null);

        if ($sourceId === null) {
            throw new Error\BadRequest('Missing required SourceID query parameter.');
        }

        return new $this->authSimple($sourceId);
    }


    /**
     * Generate a response for success
     *
     * @param array<mixed> $attributes  The attributes to include in the response
     * @return \SimpleSAML\XHTML\Template
     *
     */
    private function sendSuccess(array $attributes = []): Template
    {
        $t = new Template($this->config, 'autotest:success.twig');

        $t->headers->set('Content-Type', 'text/plain; charset=utf-8');
        $t->data['attributes'] = $attributes;

        return $t;
    }


    /**
     * Generate a response for failure
     *
     * @param \SimpleSAML\Error\Exception $e  The exception that was raised
     * @return \SimpleSAML\XHTML\Template
     *
     */
    private function sendFailure(Error\Exception $e): Template
    {
        $t = new Template($this->config, 'autotest:failure.twig');

        $t->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $t->headers->set('Content-Type', 'text/plain; charset=utf-8');
        $t->data['message'] = $e->getMessage();

        return $t;
    }
}
