<?php

declare(strict_types=1);

namespace TomasVotruba\CognitiveComplexity\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use TomasVotruba\CognitiveComplexity\DataCollector\CognitiveComplexityDataCollector;
use TomasVotruba\CognitiveComplexity\NodeAnalyzer\ComplexityAffectingNodeFinder;

final class ComplexityNodeVisitor extends NodeVisitorAbstract
{
    public function __construct(
        private readonly CognitiveComplexityDataCollector $cognitiveComplexityDataCollector,
        private readonly ComplexityAffectingNodeFinder $complexityAffectingNodeFinder
    ) {
    }

    /**
     * @param Node|int $node On PHP 8.5 with php-parser v5, BackedEnumCase values may be passed as int
     */
    public function enterNode(Node|int $node): ?Node
    {
        if (! $node instanceof Node) {
            return null;
        }

        if (! $this->complexityAffectingNodeFinder->isIncrementingNode($node)) {
            return null;
        }

        $this->cognitiveComplexityDataCollector->increaseOperation();

        return null;
    }
}
