<?php

declare(strict_types=1);

namespace App\Tests\Service\User;

use App\Entity\GraphQL\DTO\paginationInputDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\GraphQL\User\UserQueryService;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserQueryServiceTest extends WebTestCase
{
    protected ?UserQueryService $userService;
    protected ?UserRepository $userRepository;
    protected ?User $currentUser;
    protected ?KernelBrowser $client;

    public function setUp(): void
    {
        $this->client                           = static::createClient();
        $this->userRepository                   = static::getContainer()->get(UserRepository::class);
        $this->currentUser                      = $this->userRepository->findOneBy(['login' => 'admin']);
        $this->client->loginUser($this->currentUser);

        $this->userService         = static::getContainer()->get(UserQueryService::class);

        parent::setUp();
    }

    public function testUser(): void
    {
        $user = $this->userService->user();
        $this->assertEquals($this->currentUser->getUserIdentifier(), $user->getUserIdentifier());
    }

    public function testUsers(): void
    {
        $paginationInputDTO         = new paginationInputDTO();
        $paginationInputDTO->offset = 0;
        $paginationInputDTO->limit  = 10;

        $users = $this->userService->users($paginationInputDTO);

        $this->assertCount($paginationInputDTO->limit, $users);
    }

    public function tearDown(): void
    {
        restore_exception_handler(); // some vendor(s) forgot to reset exception handler...
        parent::tearDown();
    }
}
