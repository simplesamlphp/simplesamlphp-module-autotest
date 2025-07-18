<?php

declare(strict_types=1);

namespace SimpleSAML\Test\Module\autotest\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Auth;
use SimpleSAML\Configuration;
use SimpleSAML\Module\autotest\Controller;
use SimpleSAML\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function call_user_func;

/**
 * Set of tests for the controllers in the "autotest" module.
 */
#[CoversClass(Controller\Autotest::class)]
final class AutotestTest extends TestCase
{
    /** @var \SimpleSAML\Configuration */
    protected Configuration $config;

    /** @var \SimpleSAML\Session */
    protected Session $session;


    /**
     * Set up for each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->config = Configuration::loadFromArray(
            [
                'module.enable' => ['autotest' => true],
            ],
            '[ARRAY]',
            'simplesaml',
        );

        $this->session = Session::getSessionFromRequest();

        Configuration::setPreLoadedConfig($this->config, 'config.php');
    }


    /**
     * Test that accessing the attributes-endpoint without being authenticated results in an error-response
     *
     * @return void
     */
    public function testAttributesNotAuthenticated(): void
    {
        $request = Request::create(
            '/attributes',
            'GET',
            ['SourceID' => 'admin'],
        );

        $c = new Controller\Autotest($this->config, $this->session);
        $c->setAuthSimple(new class ('admin') extends Auth\Simple {
            public function isAuthenticated(): bool
            {
                return false;
            }

            /** @param array<mixed> $params */
            public function login(array $params = []): void
            {
                // stub
            }
        });

        $response = $c->attributes($request);

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertEquals('Not authenticated.', $response->data['message']);
    }


    /**
     * Test that accessing the login-endpoint while not authenticated results in a success-response
     *
     * @return void
     */
    public function testLoginNotAuthenticated(): void
    {
        $request = Request::create(
            '/login',
            'GET',
            ['SourceID' => 'admin'],
        );

        $c = new Controller\Autotest($this->config, $this->session);
        $c->setAuthSimple(new class ('admin') extends Auth\Simple {
            public function isAuthenticated(): bool
            {
                return false;
            }

            /** @param array<mixed> $params */
            public function requireAuth(array $params = []): void
            {
                // stub
            }
        });

        $response = $c->login($request);

        $this->assertTrue($response->isSuccessful());
    }


    /**
     * Test that accessing the login-endpoint while authenticated results in a success-response
     *
     * @return void
     */
    public function testLoginAuthenticated(): void
    {
        $request = Request::create(
            '/login',
            'GET',
            ['SourceID' => 'admin'],
        );

        $c = new Controller\Autotest($this->config, $this->session);
        $c->setAuthSimple(new class ('admin') extends Auth\Simple {
            public function isAuthenticated(): bool
            {
                return true;
            }

            /** @param array<mixed> $params */
            public function login(array $params = []): void
            {
                // stub
            }
        });

        $response = $c->login($request);

        $this->assertTrue($response->isSuccessful());
    }


    /**
     * Test that accessing the logout-endpoint while authenticated results in a success-response
     *
     * @return void
     */
    public function testLogoutAuthenticated(): void
    {
        $request = Request::create(
            '/logout',
            'GET',
            ['SourceID' => 'admin'],
        );

        $c = new Controller\Autotest($this->config, $this->session);
        $c->setAuthSimple(new class ('admin') extends Auth\Simple {
            public function isAuthenticated(): bool
            {
                return true;
            }

            /** @param array<mixed>|string|null $params */
            public function logout(array|string|null $params = null): void
            {
                // stub
            }
        });

        $response = $c->logout($request);

        $this->assertTrue($response->isSuccessful());
    }


    /**
     * Test that accessing the attributes-endpoint while authenticated results in a
     *  success-response with attribute-values
     *
     * @return void
     */
    public function testAttributesAuthenticated(): void
    {
        $request = Request::create(
            '/attributes',
            'GET',
            ['SourceID' => 'admin'],
        );

        $c = new Controller\Autotest($this->config, $this->session);
        $c->setAuthSimple(new class ('admin') extends Auth\Simple {
            public function isAuthenticated(): bool
            {
                return true;
            }

            /** @return array<mixed> */
            public function getAttributes(): array
            {
                return ['some' => ['multi', 'valued', 'attribute'], 'something' => ['else']];
            }
        });

        $response = $c->attributes($request);
        $this->assertTrue($response->isSuccessful());

        $content = $response->getTwig()->render('@autotest/success.twig', $response->data);
        $this->assertStringContainsString('OK', $content);
        $this->assertStringContainsString('some', $content);
        $this->assertStringContainsString('multi', $content);
        $this->assertStringContainsString('valued', $content);
        $this->assertStringContainsString('attribute', $content);
        $this->assertStringContainsString('something', $content);
        $this->assertStringContainsString('else', $content);
    }


    /**
     * Test that a missing SourceID results in an error-response
     *
     * @param string $endpoint
     * @return void
     */
    #[DataProvider('endpoints')]
    public function testMissingSourceId(string $endpoint): void
    {
        $request = Request::create(
            '/' . $endpoint,
            'GET',
        );

        $c = new Controller\Autotest($this->config, $this->session);
        $callable = [$c, $endpoint];
        $this->assertIsCallable($callable);

        /** @var \SimpleSAML\XHTML\Template $response */
        $response = call_user_func($callable, $request);

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertEquals(
            "BADREQUEST('%REASON%' => 'Missing required SourceID query parameter.')",
            $response->data['message'],
        );
    }


    /**
     * @return array<mixed>
     */
    public static function endpoints(): array
    {
        return [
            ['attributes'],
            ['login'],
            ['logout'],
        ];
    }
}
