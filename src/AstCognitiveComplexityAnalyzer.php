<?php

declare(strict_types=1);

namespace TomasVotruba\CognitiveComplexity;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use TomasVotruba\CognitiveComplexity\DataCollector\CognitiveComplexityDataCollector;
use TomasVotruba\CognitiveComplexity\NodeVisitor\ComplexityNodeVisitor;
use TomasVotruba\CognitiveComplexity\NodeVisitor\NestingNodeVisitor;

/**
 * @see \TomasVotruba\CognitiveComplexity\Tests\AstCognitiveComplexityAnalyzer\AstCognitiveComplexityAnalyzerTest
 *
 * implements the concept described in https://www.sonarsource.com/resources/white-papers/cognitive-complexity/
 */
final readonly class AstCognitiveComplexityAnalyzer
{
    public function __construct(
        private CognitiveComplexityDataCollector $cognitiveComplexityDataCollector,
        private NestingNodeVisitor $nestingNodeVisitor,
        private ComplexityNodeVisitor $complexityNodeVisitor
    ) {
    }

    public function analyzeClassLike(Class_ $class): int
    {
        $totalCognitiveComplexity = 0;
        foreach ($class->getMethods() as $classMethod) {
            $totalCognitiveComplexity += $this->analyzeFunctionLike($classMethod);
        }

        return $totalCognitiveComplexity;
    }

    /**
     * @api
     */
    public function analyzeFunctionLike(Function_ | ClassMethod $functionLike): int
    {
        $this->cognitiveComplexityDataCollector->reset();
        $this->nestingNodeVisitor->reset();

        $this->traverseNode($functionLike);

        return $this->cognitiveComplexityDataCollector->getCognitiveComplexity();
    }

    /**
     * Manual recursive traversal to avoid PHPStan phar-bundled PHP-Parser
     * compatibility issues with PhpParser\NodeTraverser.
     *
     * @see https://github.com/TomasVotruba/cognitive-complexity/issues/14
     */
    private function traverseNode(Node $node): void
    {
        $this->nestingNodeVisitor->enterNode($node);
        $this->complexityNodeVisitor->enterNode($node);

        foreach ($node->getSubNodeNames() as $name) {
            $this->traverseSubNode($node->{$name});
        }

        $this->nestingNodeVisitor->leaveNode($node);
        $this->complexityNodeVisitor->leaveNode($node);
    }

    /**
     * @param mixed $subNode
     */
    private function traverseSubNode(mixed $subNode): void
    {
        if ($subNode instanceof Node) {
            $this->traverseNode($subNode);
            return;
        }

        if (! is_array($subNode)) {
            return;
        }

        foreach ($subNode as $item) {
            if ($item instanceof Node) {
                $this->traverseNode($item);
            }
        }
    }
}
