<?php

namespace App\GraphQL\Resolver;

use App\Service\GraphQL\User\UserMutationService;
use App\Service\GraphQL\User\UserQueryService;
use ArrayObject;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use App\Service\DTO\DTOService;

class UserResolverMap extends ResolverMap
{
    public function __construct(
        private readonly UserQueryService $userQueryService,
        private readonly UserMutationService $userMutationService,
        private readonly DTOService $DTOService
    ) {}

    /**
     * @inheritDoc
     */
    protected function map(): array
    {
        return [
            'UserQuery'    => [
                self::RESOLVE_FIELD => function (
                    $value,
                    ArgumentInterface $args,
                    ArrayObject $context,
                    ResolveInfo $info
                ) {

                    $dto = $this->DTOService->convertGraphQLToDTO($info);

                    return match ($info->fieldName) {
                        'user'  => $this->userQueryService->user(),
                        'users' => $this->userQueryService->users($dto['pagination']),
                        default => null
                    };
                },
            ],
            'UserMutation'    => [
                self::RESOLVE_FIELD => function (
                    $value,
                    ArgumentInterface $args,
                    ArrayObject $context,
                    ResolveInfo $info
                ) {

                    $dto = $this->DTOService->convertGraphQLToDTO($info);

                    return match ($info->fieldName) {
                        'userCreate'    => $this->userMutationService->userCreate($dto['user']),
                        'userUpdate'    => $this->userMutationService->userUpdate($dto['id'], $dto['user']),
                        default => null
                    };
                },
            ]
        ];
    }
}