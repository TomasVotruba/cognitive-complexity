<?php

declare(strict_types=1);

namespace TomasVotruba\CognitiveComplexity;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PHPStan\Reflection\ClassReflection;

final class ClassReflectionParser
{
    private readonly Parser $phpParser;

    private readonly NodeFinder $nodeFinder;

    /**
     * @var array<string, Class_|null>
     */
    private array $classesByName = [];

    public function __construct()
    {
        $parserFactory = new ParserFactory();
        $this->phpParser = $parserFactory->createForHostVersion();

        $this->nodeFinder = new NodeFinder();
    }

    public function parse(ClassReflection $classReflection): ?Class_
    {
        $className = $classReflection->getName();
        if (array_key_exists($className, $this->classesByName)) {
            return $this->classesByName[$className];
        }

        $this->classesByName[$className] = $this->parseClass($classReflection);

        return $this->classesByName[$className];
    }

    private function parseClass(ClassReflection $classReflection): ?Class_
    {
        $fileName = $classReflection->getFileName();
        if (! is_string($fileName)) {
            return null;
        }

        /** @var string $fileContents */
        $fileContents = file_get_contents($fileName);

        $stmts = $this->phpParser->parse($fileContents);
        if ($stmts === null) {
            return null;
        }

        $nodeTraverser = new NodeTraverser(new NameResolver());
        $stmts = $nodeTraverser->traverse($stmts);

        $class = $this->nodeFinder->findFirst(
            $stmts,
            static fn (Node $node): bool => $node instanceof Class_
                && $node->namespacedName?->toString() === $classReflection->getName()
        );

        return $class instanceof Class_ ? $class : null;
    }
}
