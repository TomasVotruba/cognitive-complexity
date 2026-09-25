<?php

declare(strict_types=1);

namespace TomasVotruba\CognitiveComplexity\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\LogicalAnd;
use PhpParser\Node\Expr\BinaryOp\LogicalOr;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Goto_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\While_;

final class ComplexityAffectingNodeFinder
{
    /**
     * B1. Increments
     *
     * @var array<class-string<Stmt>>
     */
    private const array BREAKING_NODE_TYPES = [Continue_::class, Goto_::class, Break_::class];

    /**
     * B1. Increments
     *
     * @var array<class-string<Node>>
     */
    private const array INCREASING_NODE_TYPES = [
        If_::class,
        Else_::class,
        ElseIf_::class,
        Switch_::class,
        Match_::class,
        For_::class,
        Foreach_::class,
        While_::class,
        Do_::class,
        Catch_::class,
        Ternary::class,
    ];

    /**
     * B1. Increments once per sequence of like operators
     *
     * @var array<class-string<Expr>>
     */
    private const array BOOLEAN_OPERATOR_TYPES = [
        BooleanAnd::class,
        BooleanOr::class,
        LogicalAnd::class,
        LogicalOr::class,
    ];

    public function isIncrementingNode(Node $node): bool
    {
        if ($this->isInstanceOf($node, self::INCREASING_NODE_TYPES)) {
            return true;
        }

        return $this->isBreakingNode($node);
    }

    public function isBooleanOperator(Node $node): bool
    {
        return $this->isInstanceOf($node, self::BOOLEAN_OPERATOR_TYPES);
    }

    public function isBreakingNode(Node $node): bool
    {
        // B1. goto LABEL, break LABEL, continue LABEL
        if ($this->isInstanceOf($node, self::BREAKING_NODE_TYPES)) {
            // skip empty breaks
            if ($node instanceof Goto_) {
                return true;
            }

            if (
                ($node instanceof Break_ || $node instanceof Continue_)
                && $node->num instanceof Expr
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param class-string[] $types
     */
    private function isInstanceOf(Node $node, array $types): bool
    {
        return array_any($types, fn (string $type): bool => $node instanceof $type);
    }
}
