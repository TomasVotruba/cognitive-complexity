<?php

declare(strict_types=1);

namespace TomasVotruba\CognitiveComplexity\NodeTraverser;

use PhpParser\NodeTraverser;
use TomasVotruba\CognitiveComplexity\NodeVisitor\ComplexityNodeVisitor;
use TomasVotruba\CognitiveComplexity\NodeVisitor\NestingNodeVisitor;

final class ComplexityNodeTraverserFactory
{
    public function __construct(
        private NestingNodeVisitor $nestingNodeVisitor,
        private ComplexityNodeVisitor $complexityNodeVisitor
    ) {
    }

    public function create(): NodeTraverser
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->nestingNodeVisitor);
        $nodeTraverser->addVisitor($this->complexityNodeVisitor);

        return $nodeTraverser;
    }
}
