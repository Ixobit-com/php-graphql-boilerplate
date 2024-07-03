<?php

namespace App\GraphQL\Resolver;

use App\GraphQL\Language\AST\Node\DateTimeType;
use App\Service\GraphQL\Library\LibraryMutationService;
use App\Service\GraphQL\Library\LibraryQueryService;
use ArrayObject;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

class LibraryResolverMap extends ResolverMap
{
    public function __construct(
        private readonly LibraryQueryService $queryService,
        private readonly LibraryMutationService $mutationService
    ) {}

    /**
     * @inheritDoc
     */
    protected function map(): array
    {
        return [
            'LibraryQuery'    => [
                self::RESOLVE_FIELD => function (
                    $value,
                    ArgumentInterface $args,
                    ArrayObject $context,
                    ResolveInfo $info
                ) {
                    return match ($info->fieldName) {
                        'author' => $this->queryService->findAuthor((int)$args['id']),
                        'authors' => $this->queryService->getAllAuthors((int)$args['page']),
                        'findBooksByAuthor' => $this->queryService->findBooksByAuthor($args['name']),
                        'books' => $this->queryService->findAllBooks(),
                        'findBooksByGenre' => $this->queryService->findBooksByGenre($args['genre']),
                        'book' => $this->queryService->findBookById((int)$args['id']),
                        default => null
                    };
                },
            ],
            'LibraryMutation' => [
                self::RESOLVE_FIELD => function (
                    $value,
                    ArgumentInterface $args,
                    ArrayObject $context,
                    ResolveInfo $info
                ) {
                    return match ($info->fieldName) {
                        'createAuthor' => $this->mutationService->createAuthor($args['author']),
                        'updateBook' => $this->mutationService->updateBook((int)$args['id'], $args['book']),
                        default => null
                    };
                },
            ],
            'DateTime'  => [ self::SCALAR_TYPE => fn () => new DateTimeType() ]
        ];
    }
}