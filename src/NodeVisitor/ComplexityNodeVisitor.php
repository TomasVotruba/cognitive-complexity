<?php

declare(strict_types=1);

namespace TomasVotruba\CognitiveComplexity\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\NodeVisitorAbstract;
use TomasVotruba\CognitiveComplexity\DataCollector\CognitiveComplexityDataCollector;
use TomasVotruba\CognitiveComplexity\NodeAnalyzer\ComplexityAffectingNodeFinder;

final class ComplexityNodeVisitor extends NodeVisitorAbstract
{
    /**
     * Object ids of boolean operators that continue a sequence of the same operator
     *
     * @var array<int, true>
     */
    private array $sequenceContinuationIds = [];

    public function __construct(
        private readonly CognitiveComplexityDataCollector $cognitiveComplexityDataCollector,
        private readonly ComplexityAffectingNodeFinder $complexityAffectingNodeFinder
    ) {
    }

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->sequenceContinuationIds = [];

        return null;
    }

    /**
     * @param Node|int $node On PHP 8.5 with php-parser v5, BackedEnumCase values may be passed as int
     */
    public function enterNode(Node|int $node): ?Node
    {
        if (! $node instanceof Node) {
            return null;
        }

        if ($this->complexityAffectingNodeFinder->isIncrementingNode($node)) {
            $this->cognitiveComplexityDataCollector->increaseOperation();
            return null;
        }

        if ($node instanceof BinaryOp && $this->complexityAffectingNodeFinder->isBooleanOperator($node)) {
            $this->enterBooleanOperator($node);
        }

        return null;
    }

    /**
     * B1. Increment once per sequence of like boolean operators, e.g. "$a && $b && $c" is +1
     */
    private function enterBooleanOperator(BinaryOp $binaryOp): void
    {
        if (! isset($this->sequenceContinuationIds[spl_object_id($binaryOp)])) {
            $this->cognitiveComplexityDataCollector->increaseOperation();
        }

        foreach ([$binaryOp->left, $binaryOp->right] as $operand) {
            if ($operand::class === $binaryOp::class) {
                $this->sequenceContinuationIds[spl_object_id($operand)] = true;
            }
        }
    }
}
