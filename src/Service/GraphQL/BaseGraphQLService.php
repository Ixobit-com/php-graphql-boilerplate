<?php

declare(strict_types=1);

namespace App\Service\GraphQL;

use App\Service\DTO\DTOService;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BaseGraphQLService
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly Security $security,
        protected UserPasswordHasherInterface $passwordHasher,
        protected DTOService $DTOService,
        protected JWTTokenManagerInterface $JWTManager,
        protected RefreshTokenGeneratorInterface $refreshTokenGenerator,
        protected ParameterBagInterface $configuration,
        protected LoggerInterface $logger,
        protected readonly RoleHierarchyInterface $roleHierarchy,
        protected readonly ValidatorInterface $validator,
    ) {
    }
}
