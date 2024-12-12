<?php

declare(strict_types=1);

namespace TomasVotruba\CognitiveComplexity\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use TomasVotruba\CognitiveComplexity\AstCognitiveComplexityAnalyzer;
use TomasVotruba\CognitiveComplexity\Configuration;

/**
 * @see \TomasVotruba\CognitiveComplexity\Tests\Rules\ClassLikeCognitiveComplexityRule\ClassLikeCognitiveComplexityRuleTest
 */
final readonly class ClassLikeCognitiveComplexityRule implements Rule
{
    /**
     * @api used in tests
     * @var string
     */
    public const ERROR_MESSAGE = 'Class cognitive complexity is %d, keep it under %d';

    public function __construct(
        private AstCognitiveComplexityAnalyzer $astCognitiveComplexityAnalyzer,
        private Configuration $configuration
    ) {
    }

    /**
     * @return class-string<Node>
     */
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    /**
     * @param InClassNode $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $classLike = $node->getOriginalNode();
        if (! $classLike instanceof Class_) {
            return [];
        }

        $measuredCognitiveComplexity = $this->astCognitiveComplexityAnalyzer->analyzeClassLike($classLike);
        if ($measuredCognitiveComplexity <= $this->configuration->getMaxClassCognitiveComplexity()) {
            return [];
        }

        $message = sprintf(
            self::ERROR_MESSAGE,
            $measuredCognitiveComplexity,
            $this->configuration->getMaxClassCognitiveComplexity()
        );

        return [RuleErrorBuilder::message($message)->identifier('complexity.classLike')->build()];
    }
}
