<?php

namespace App\Tests\Service\Auth\DataProviders;

use App\Entity\GraphQL\DTO\Auth\Input\refreshInputDTO;
use PHPUnit\Framework\Constraint\RegularExpression;

trait refreshDataProvider
{
    protected const auth_refresh_query = <<<EOD
query refresh(\$refreshInfo: refreshInputDTO!) {
    refresh(refreshInfo: \$refreshInfo) {
        token
        refresh_token
    }
}
EOD;

    public static function provideRefreshData(): iterable
    {
        yield 'refresh.valid' => [
            'variables' => ['refreshInfo' => new refreshInputDTO(
                [
                    'refresh_token' => 'example-refresh-token',
                ]
            )],
            'expectedErrors'     => [
                new RegularExpression('/Invalid refresh token/'),
            ],
            'analyzers'          => [],
        ];
    }
}