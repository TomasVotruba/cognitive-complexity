<?php

declare(strict_types=1);

namespace TomasVotruba\CognitiveComplexity\Tests\Rules\ClassLikeCognitiveComplexityRule;

use Iterator;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use TomasVotruba\CognitiveComplexity\Rules\ClassLikeCognitiveComplexityRule;

final class ClassLikeCognitiveComplexityRuleTest extends RuleTestCase
{
    /**
     * @param list<array{0: string, 1: int, 2?: string|null}> $expectedErrorMessagesWithLines
     */
    #[DataProvider('provideDataForTest')]
    public function test(string $filePath, array $expectedErrorMessagesWithLines): void
    {
        $this->analyse([$filePath], $expectedErrorMessagesWithLines);
    }

    public static function provideDataForTest(): Iterator
    {
        $errorMessage = sprintf(ClassLikeCognitiveComplexityRule::ERROR_MESSAGE, 54, 50);
        yield [__DIR__ . '/Fixture/ClassWithManyComplexMethods.php', [[$errorMessage, 7]]];

        // complexity: 9
        yield [__DIR__ . '/Fixture/SimpleCommand.php', []];
    }

    /**
     * @return string[]
     */
    #[Override]
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/config/configured_rule.neon'];
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(ClassLikeCognitiveComplexityRule::class);
    }
}
