<?php

use AppBundle\Security\GithubAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use PHPUnit\Framework\TestCase;

class GithubAuthenticatorTest extends TestCase {

    public function setUp(): void
    {
        $this->client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['post'])
            ->getMock();

        $this->router = $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->response = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->stream = $this->getMockBuilder(StreamInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->parameterBag = $this->getMockBuilder(ParameterBag::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $this->token = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->providerKey = 'provider_key';
        $clientId = 'client_id';
        $clientSecret = 'client_secret';

        $this->githubAuthenticator = new GithubAuthenticator($this->client, $clientId, $clientSecret, $this->router);
    }

    public function tearDown(): void
    {
        $this->client = null;
        $this->router = null;
        $this->response = null;
        $this->stream = null;
        $this->request = null;
        $this->parameterBag = null;
        $this->token = null;
    }

    public function testCreateTokenWillThrowExceptionWhenResponseError()
    {
        $this->parameterBag->expects($this->once())
                            ->method('get')
                            ->willReturn('request_code');

        $this->request->query = $this->parameterBag;

        $this->router->expects($this->once())
                        ->method('generate')
                        ->willReturn('https://site.com');

        $this->stream->expects($this->once())
                        ->method('getContents')
                        ->willReturn("error=connection failed&url'}");

        $this->response->expects($this->once())
                        ->method('getBody')
                        ->willReturn($this->stream);

        $this->client->expects($this->once())
                        ->method('post')
                        ->willReturn($this->response);

        $this->expectException('Symfony\Component\Security\Core\Exception\BadCredentialsException');

        $this->githubAuthenticator->createToken($this->request, $this->providerKey);
    }

    public function testCreateTokenWillReturnResponse()
    {
        $this->parameterBag->expects($this->once())
            ->method('get')
            ->willReturn('request_code');

        $this->request->query = $this->parameterBag;

        $this->router->expects($this->once())
            ->method('generate')
            ->willReturn('https://site.com');

        $this->stream->expects($this->once())
            ->method('getContents')
            ->willReturn("key=some_provider_key");

        $this->response->expects($this->once())
            ->method('getBody')
            ->willReturn($this->stream);

        $this->client->expects($this->once())
            ->method('post')
            ->willReturn($this->response);

        $result = $this->githubAuthenticator->createToken($this->request, $this->providerKey);

        $expectedObject = new PreAuthenticatedToken('anon.', 'some_provider_key', $this->providerKey);

        $this->assertInstanceOf('Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken', $result);
        $this->assertEquals($expectedObject, $result);
    }

    public function testAuthenticateToken()
    {
        $this->token->expects($this->once())
                    ->method('getCredentials')
                    ->willReturn('toto');

        $user = $this->getMockBuilder(UserInterface::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $userProvider = $this->getMockBuilder(UserProviderInterface::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $userProvider->expects($this->once())
                    ->method('loadUserByUsername')
                    ->willReturn($user);

        $result = $this->githubAuthenticator->authenticateToken($this->token, $userProvider, $this->providerKey);
        $expectedObject = new PreAuthenticatedToken(
            $user,
            'toto',
            $this->providerKey,
            ['ROLE_USER']
        );

        $this->assertInstanceOf('Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken', $result);
        $this->assertEquals($expectedObject, $result);

    }

    /**
     * @dataProvider SupportsTokenDataProvider
     */
    public function testSupportsToken($expect, $token, $providerKey)
    {
        $this->assertEquals($expect, $this->githubAuthenticator->supportsToken($token, $providerKey));
    }

    public function SupportsTokenDataProvider()
    {
        $token1 = $this->getMockBuilder(PreAuthenticatedToken::class)
                        ->disableOriginalConstructor()
                        ->getMock();
        $token1->method('getProviderKey')
                ->willReturn('providerKey1');

        // TODO $token3 = mock of an object that implements TokenInterface and different of PreAuthenticatedToken '

        return [
          [true, $token1, 'providerKey1'],
          [false, $token1, 'providerKey2'],
          //[false, $token3, providerKey1]
        ];
    }

    public function testOnAuthenticationFailure()
    {
        $exception = $this->getMockBuilder(AuthenticationException::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $result = $this->githubAuthenticator->onAuthenticationFailure($this->request, $exception);

        $expectedResult = new Response("Authentication Failed :(", 403);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $result);
        $this->assertEquals($expectedResult, $result);
    }
}
