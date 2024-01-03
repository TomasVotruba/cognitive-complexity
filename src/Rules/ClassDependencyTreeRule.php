<?php

declare(strict_types=1);

namespace TomasVotruba\CognitiveComplexity\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Rules\Rule;
use PHPStan\Type\TypeWithClassName;
use TomasVotruba\CognitiveComplexity\AstCognitiveComplexityAnalyzer;
use TomasVotruba\CognitiveComplexity\ClassReflectionParser;
use TomasVotruba\CognitiveComplexity\Configuration;

/**
 * @implements Rule<InClassNode>
 *
 * Find classes with complex constructor dependency tree = current class complexity + complexity of all __construct() dependencies.
 */
final class ClassDependencyTreeRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Dependency tree complexity %d is over %d. Refactor __construct() dependencies or split up.';

    /**
     * @readonly
     * @var \TomasVotruba\CognitiveComplexity\AstCognitiveComplexityAnalyzer
     */
    private $astCognitiveComplexityAnalyzer;

    /**
     * @readonly
     * @var \TomasVotruba\CognitiveComplexity\ClassReflectionParser
     */
    private $classReflectionParser;

    /**
     * @readonly
     * @var \TomasVotruba\CognitiveComplexity\Configuration
     */
    private $configuration;

    public function __construct(AstCognitiveComplexityAnalyzer $astCognitiveComplexityAnalyzer, ClassReflectionParser $classReflectionParser, Configuration $configuration)
    {
        $this->astCognitiveComplexityAnalyzer = $astCognitiveComplexityAnalyzer;
        $this->classReflectionParser = $classReflectionParser;
        $this->configuration = $configuration;
    }

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    /**
     * @param InClassNode $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $this->configuration->isDependencyTreeEnabled()) {
            return [];
        }

        $classReflection = $node->getClassReflection();

        // nothing to check
        if (! $classReflection->hasConstructor()) {
            return [];
        }

        // only check
        $originalClassLike = $node->getOriginalNode();
        if (! $originalClassLike instanceof Class_) {
            return [];
        }

        if (! $this->isTypeToAnalyse($classReflection)) {
            return [];
        }

        $extendedMethodReflection = $classReflection->getConstructor();

        $parametersAcceptorWithPhpDocs = ParametersAcceptorSelector::selectSingle(
            $extendedMethodReflection->getVariants()
        );

        $totaDependencyTreeComplexity = $this->astCognitiveComplexityAnalyzer->analyzeClassLike($originalClassLike);

        foreach ($parametersAcceptorWithPhpDocs->getParameters() as $parameterReflectionWithPhpDoc) {
            $dependencyClass = $this->resolveParameterTypeClass($parameterReflectionWithPhpDoc);
            if (! $dependencyClass instanceof Class_) {
                continue;
            }

            $dependencyComplexity = $this->astCognitiveComplexityAnalyzer->analyzeClassLike($dependencyClass);
            $totaDependencyTreeComplexity += $dependencyComplexity;
        }

        if ($totaDependencyTreeComplexity <= $this->configuration->getMaxDependencyTreeComplexity()) {
            return [];
        }

        return [
            sprintf(
                self::ERROR_MESSAGE,
                $totaDependencyTreeComplexity,
                $this->configuration->getMaxDependencyTreeComplexity()
            ),
        ];
    }

    private function isTypeToAnalyse(ClassReflection $classReflection): bool
    {
        foreach ($this->configuration->getDependencyTreeTypes() as $dependencyTreeType) {
            if ($classReflection->isSubclassOf($dependencyTreeType)) {
                return true;
            }
        }

        return false;
    }

    private function resolveParameterTypeClass(ParameterReflection $parameterReflection): ?Class_
    {
        $parameterType = $parameterReflection->getType();
        if (! $parameterType instanceof TypeWithClassName) {
            return null;
        }

        $parameterClassReflection = $parameterType->getClassReflection();
        if (! $parameterClassReflection instanceof ClassReflection) {
            return null;
        }

        return $this->classReflectionParser->parse($parameterClassReflection);
    }
}
