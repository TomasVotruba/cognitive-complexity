<?php

declare(strict_types=1);

namespace TomasVotruba\CognitiveComplexity;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use TomasVotruba\CognitiveComplexity\DataCollector\CognitiveComplexityDataCollector;
use TomasVotruba\CognitiveComplexity\NodeTraverser\ComplexityNodeTraverserFactory;

/**
 * @see \TomasVotruba\CognitiveComplexity\Tests\AstCognitiveComplexityAnalyzer\AstCognitiveComplexityAnalyzerTest
 *
 * implements the concept described in https://www.sonarsource.com/resources/white-papers/cognitive-complexity/
 */
final class AstCognitiveComplexityAnalyzer
{
    private ComplexityNodeTraverserFactory $complexityNodeTraverserFactory;

    private CognitiveComplexityDataCollector $cognitiveComplexityDataCollector;

    public function __construct(
        ComplexityNodeTraverserFactory $complexityNodeTraverserFactory,
        CognitiveComplexityDataCollector $cognitiveComplexityDataCollector
    ) {
        $this->complexityNodeTraverserFactory = $complexityNodeTraverserFactory;
        $this->cognitiveComplexityDataCollector = $cognitiveComplexityDataCollector;
    }

    public function analyzeClassLike(ClassLike $classLike): int
    {
        $totalCognitiveComplexity = 0;
        foreach ($classLike->getMethods() as $classMethod) {
            $totalCognitiveComplexity += $this->analyzeFunctionLike($classMethod);
        }

        return $totalCognitiveComplexity;
    }

    /**
     * @api
     *
     * @param Function_|ClassMethod $functionLike
     */
    public function analyzeFunctionLike(FunctionLike $functionLike): int
    {
        $this->cognitiveComplexityDataCollector->reset();

        $nodeTraverser = $this->complexityNodeTraverserFactory->create();
        $nodeTraverser->traverse([$functionLike]);

        return $this->cognitiveComplexityDataCollector->getCognitiveComplexity();
    }
}
