<?php

declare(strict_types=1);

namespace TomasVotruba\CognitiveComplexity\Tests\Rules\FunctionLikeCognitiveComplexityRule\Fixture;

enum BackedEnumWithMethod: string
{
    case Admin = 'admin';
    case User = 'user';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::User => 'Regular User',
        };
    }
}
