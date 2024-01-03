<?php

declare(strict_types=1);

namespace TomasVotruba\CognitiveComplexity\Tests\Rules\ClassDependencyTreeRule;

use Iterator;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use TomasVotruba\CognitiveComplexity\Rules\ClassDependencyTreeRule;

final class ClassDependencyTreeRuleTest extends RuleTestCase
{
    /**
     * @param mixed[] $expectedErrorMessagesWithLines
     */
    #[DataProvider('provideDataForTest')]
    public function test(string $filePath, array $expectedErrorMessagesWithLines): void
    {
        $this->analyse([$filePath], $expectedErrorMessagesWithLines);
    }

    public static function provideDataForTest(): Iterator
    {
        $errorMessage = sprintf(ClassDependencyTreeRule::ERROR_MESSAGE, 22, 20);
        yield [__DIR__ . '/Fixture/ClassWithManyComplexTree.php', [[$errorMessage, 11]]];
    }

    /**
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/config/configured_rule.neon'];
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(ClassDependencyTreeRule::class);
    }
}
