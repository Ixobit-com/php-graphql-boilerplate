<?php

namespace App\GraphQL\Resolver;

use App\Service\GraphQL\User\UserMutationService;
use App\Service\GraphQL\User\UserQueryService;
use ArrayObject;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

class UserResolverMap extends ResolverMap
{
    public function __construct(
        private readonly UserQueryService $userQueryService,
        private readonly UserMutationService $userMutationService
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
                    return match ($info->fieldName) {
                        'profile' => $this->userQueryService->profile(),
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
                    return match ($info->fieldName) {
                        'profileUpdate' => $this->userMutationService->profileUpdate($args['user']),
                        default => null
                    };
                },
            ]
        ];
    }
}