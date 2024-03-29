<?php

declare(strict_types=1);

namespace TomasVotruba\CognitiveComplexity;

use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeFinder;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PHPStan\Reflection\ClassReflection;

final readonly class ClassReflectionParser
{
    private Parser $phpParser;

    private NodeFinder $nodeFinder;

    public function __construct()
    {
        $parserFactory = new ParserFactory();
        $this->phpParser = $parserFactory->create(ParserFactory::PREFER_PHP7);

        $this->nodeFinder = new NodeFinder();
    }

    public function parse(ClassReflection $classReflection): ?Class_
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

        $foundClass = $this->nodeFinder->findFirstInstanceOf($stmts, Class_::class);
        if (! $foundClass instanceof Class_) {
            return null;
        }

        return $foundClass;
    }
}
