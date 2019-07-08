<?php
use AppBundle\Entity\User;
use GuzzleHttp\Client;
use JMS\Serializer\Serializer;
use AppBundle\Security\GithubUserProvider;
use PHPUnit\Framework\TestCase;

class GithubUserProviderTest extends TestCase {

    public function setUp(): void
    {
        $this->response = $this->getMockBuilder(\Psr\Http\Message\ResponseInterface::class)
                                ->disableOriginalConstructor()
                                ->getMock();

        $this->streamResponse = $this->getMockBuilder(\Psr\Http\Message\StreamInterface::class)
                                        ->disableOriginalConstructor()
                                        ->getMock();

        $this->client = $this->getMockBuilder(Client::class)
                                ->disableOriginalConstructor()
                                ->setMethods(['get'])
                                ->getMock();

        $this->serializer = $this->getMockBuilder(Serializer::class)
                                    ->disableOriginalConstructor()
                                    ->setMethods(['deserialize'])
                                    ->getMock();
    }

    public function tearDown(): void
    {
        $this->response       = null;
        $this->streamResponse = null;
        $this->client         = null;
        $this->serializer     = null;
    }

    public function testLoadUserByUsername()
    {
        $this->response->method('getBody')
                        ->willReturn($this->streamResponse);

        $this->client->expects($this->once())
                        ->method('get')
                        ->willReturn($this->response);

        $userData = [
            'login'      => 'login',
            'name'       => 'name',
            'email'      => 'email',
            'avatar_url' => 'avatar_url',
            'html_url'   => 'html_url'
        ];

        $this->serializer->expects($this->once())
                            ->method('deserialize')
                            ->willReturn($userData);

        $githubUserProvider = new GithubUserProvider($this->client, $this->serializer);

        $user = $githubUserProvider->loadUserByUsername('userName');

        $expectedUser = new User(
            $userData['login'],
            $userData['name'],
            $userData['email'],
            $userData['avatar_url'],
            $userData['html_url']
        );

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($expectedUser, $user);
    }

    public function testLoadUserByUsernameWillThrowExceptionWhenNoUserData()
    {
        $this->response->method('getBody')
                        ->willReturn($this->streamResponse);

        $this->client->expects($this->once())
                        ->method('get')
                        ->willReturn($this->response);

        $this->serializer->expects($this->once())
                            ->method('deserialize')
                            ->willReturn(null);

        $this->expectException('LogicException');

        $githubUserProvider = new GithubUserProvider($this->client, $this->serializer);

        $githubUserProvider->loadUserByUsername('userName');
    }

    public function testRefreshUser()
    {
        $githubUserProvider = new GithubUserProvider($this->client, $this->serializer);

        $user = new User('login', 'name', 'email', 'avatar_url', 'html_url');

        $githubUserProvider->refreshUser($user);

        $this->assertInstanceOf(User::class, $user);
    }

    public function testRefreshUserWillThrowExceptionWhenNoUserClass()
    {
        $githubUserProvider = $this->getMockBuilder(GithubUserProvider::class)
                                    ->disableOriginalConstructor()
                                    ->setMethods(['supportsClass'])
                                    ->getMock();

        $githubUserProvider->method('supportsClass')
                            ->willReturn(false);

        $this->expectException('Symfony\Component\Security\Core\Exception\UnsupportedUserException');

        $user = new User('login', 'name', 'email', 'avatar_url', 'html_url');

        $githubUserProvider->refreshUser($user);
    }

    /**
     * @dataProvider supportsClassProvider
     */
    public function testSupportsClass($class, $expect)
    {
        $client = $this->getMockBuilder(Client::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        $serializer = $this->getMockBuilder(Serializer::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $githubUserProvider = new GithubUserProvider($client, $serializer);

        $result = $githubUserProvider->supportsClass($class);

        $this->assertEquals($expect, $result);
    }

    public function supportsClassProvider()
    {
        return [
          ['AppBundle\Entity\User1', false],
          ['AppBundle\Entity\User', true],
          [null, false]
        ];
    }
}
