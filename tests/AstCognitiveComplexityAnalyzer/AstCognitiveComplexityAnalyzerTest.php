<?php

declare(strict_types=1);

namespace TomasVotruba\CognitiveComplexity\Tests\AstCognitiveComplexityAnalyzer;

use Iterator;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PHPStan\DependencyInjection\ContainerFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TomasVotruba\CognitiveComplexity\AstCognitiveComplexityAnalyzer;
use TomasVotruba\CognitiveComplexity\Exception\ShouldNotHappenException;

final class AstCognitiveComplexityAnalyzerTest extends TestCase
{
    private AstCognitiveComplexityAnalyzer $astCognitiveComplexityAnalyzer;

    protected function setUp(): void
    {
        $phpstanContainerFactory = new ContainerFactory(getcwd());

        $tempFile = sys_get_temp_dir() . '/cognitive_complexity';
        $container = $phpstanContainerFactory->create($tempFile, [__DIR__ . '/config/configured_service.neon'], []);

        $this->astCognitiveComplexityAnalyzer = $container->getByType(AstCognitiveComplexityAnalyzer::class);
    }

    #[DataProvider('provideTokensAndExpectedCognitiveComplexity')]
    public function test(string $filePath, int $expectedCognitiveComlexity): void
    {
        /** @var string $fileContents */
        $fileContents = file_get_contents($filePath);

        $functionLike = $this->parseFileToFirstFunctionLike($fileContents);
        $cognitiveComplexity = $this->astCognitiveComplexityAnalyzer->analyzeFunctionLike($functionLike);

        $this->assertSame($expectedCognitiveComlexity, $cognitiveComplexity);
    }

    public static function provideTokensAndExpectedCognitiveComplexity(): Iterator
    {
        yield [__DIR__ . '/Fixture/function_9.php.inc', 9];
        yield [__DIR__ . '/Fixture/function_6.php.inc', 6];
        yield [__DIR__ . '/Fixture/switch_1.php.inc', 1];
        yield [__DIR__ . '/Fixture/closure_2.php.inc', 2];
        yield [__DIR__ . '/Fixture/interface_0.php.inc', 0];
        yield [__DIR__ . '/Fixture/for_7.php.inc', 7];
        yield [__DIR__ . '/Fixture/ternary_3.php.inc', 3];
    }

    private function parseFileToFirstFunctionLike(string $fileContent): ClassMethod | Function_
    {
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->create(ParserFactory::ONLY_PHP7);
        $nodes = $parser->parse($fileContent);

        $nodeFinder = new NodeFinder();
        $firstFunctionlike = $nodeFinder->findFirst(
            (array) $nodes,
            static fn (Node $node): bool => $node instanceof ClassMethod || $node instanceof Function_
        );

        if ($firstFunctionlike instanceof ClassMethod) {
            return $firstFunctionlike;
        }

        if ($firstFunctionlike instanceof Function_) {
            return $firstFunctionlike;
        }

        throw new ShouldNotHappenException();
    }
}
