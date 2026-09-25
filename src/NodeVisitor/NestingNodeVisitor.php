<?php

declare(strict_types=1);

namespace TomasVotruba\CognitiveComplexity\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\While_;
use PhpParser\NodeVisitorAbstract;
use TomasVotruba\CognitiveComplexity\DataCollector\CognitiveComplexityDataCollector;
use TomasVotruba\CognitiveComplexity\NodeAnalyzer\ComplexityAffectingNodeFinder;

final class NestingNodeVisitor extends NodeVisitorAbstract
{
    /**
     * B3. Nesting level increments; "else" and "elseif" are children of If_, so they share its level
     *
     * @var array<class-string<Node>>
     */
    private const array NESTING_NODE_TYPES = [
        If_::class,
        Switch_::class,
        Match_::class,
        For_::class,
        Foreach_::class,
        While_::class,
        Do_::class,
        Catch_::class,
        Ternary::class,
        Closure::class,
        ArrowFunction::class,
    ];

    private int $nestingLevel = 0;

    public function __construct(
        private readonly CognitiveComplexityDataCollector $cognitiveComplexityDataCollector,
        private readonly ComplexityAffectingNodeFinder $complexityAffectingNodeFinder,
    ) {
    }

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->nestingLevel = 0;

        return null;
    }

    /**
     * @param Node|int $node On PHP 8.5 with php-parser v5, BackedEnumCase values may be passed as int
     */
    public function enterNode(Node|int $node): ?Node
    {
        if (! $node instanceof Node || ! $this->isNestingNode($node)) {
            return null;
        }

        // B3. Nesting increment, closures and arrow functions only raise the level
        if ($this->complexityAffectingNodeFinder->isIncrementingNode($node)) {
            $this->cognitiveComplexityDataCollector->increaseNesting($this->nestingLevel);
        }

        ++$this->nestingLevel;

        return null;
    }

    /**
     * @param Node|int $node On PHP 8.5 with php-parser v5, BackedEnumCase values may be passed as int
     */
    public function leaveNode(Node|int $node): ?Node
    {
        if ($node instanceof Node && $this->isNestingNode($node)) {
            --$this->nestingLevel;
        }

        return null;
    }

    private function isNestingNode(Node $node): bool
    {
        return array_any(self::NESTING_NODE_TYPES, fn (string $nestingNodeType): bool => $node instanceof $nestingNodeType);
    }
}
