<?php

declare(strict_types=1);

namespace TomasVotruba\CognitiveComplexity\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use TomasVotruba\CognitiveComplexity\AstCognitiveComplexityAnalyzer;
use TomasVotruba\CognitiveComplexity\ClassReflectionParser;
use TomasVotruba\CognitiveComplexity\Configuration;
use TomasVotruba\CognitiveComplexity\Enum\RuleIdentifier;

/**
 * @implements Rule<InClassNode>
 *
 * Find classes with complex constructor dependency tree = current class complexity + complexity of all __construct() dependencies.
 */
final readonly class ClassDependencyTreeRule implements Rule
{
    public const string ERROR_MESSAGE = 'Dependency tree complexity %d is over %d. Refactor __construct() dependencies or split up.';

    public function __construct(
        private AstCognitiveComplexityAnalyzer $astCognitiveComplexityAnalyzer,
        private ClassReflectionParser $classReflectionParser,
        private Configuration $configuration,
        private ReflectionProvider $reflectionProvider
    ) {
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

        $extendedParametersAcceptor = $classReflection->getConstructor()
            ->getOnlyVariant();

        $totalDependencyTreeComplexity = $this->astCognitiveComplexityAnalyzer->analyzeClassLike($originalClassLike);

        foreach ($extendedParametersAcceptor->getParameters() as $extendedParameterReflection) {
            $dependencyClass = $this->resolveParameterTypeClass($extendedParameterReflection);
            if (! $dependencyClass instanceof Class_) {
                continue;
            }

            $dependencyComplexity = $this->astCognitiveComplexityAnalyzer->analyzeClassLike($dependencyClass);
            $totalDependencyTreeComplexity += $dependencyComplexity;
        }

        if ($totalDependencyTreeComplexity <= $this->configuration->getMaxDependencyTreeComplexity()) {
            return [];
        }

        $message = sprintf(
            self::ERROR_MESSAGE,
            $totalDependencyTreeComplexity,
            $this->configuration->getMaxDependencyTreeComplexity()
        );

        return [RuleErrorBuilder::message($message)->identifier(RuleIdentifier::DEPENDENCY_TREE)->build()];
    }

    private function isTypeToAnalyse(ClassReflection $classReflection): bool
    {
        foreach ($this->configuration->getDependencyTreeTypes() as $dependencyTreeType) {
            if (! $this->reflectionProvider->hasClass($dependencyTreeType)) {
                continue;
            }

            $dependencyTreeClassReflection = $this->reflectionProvider->getClass($dependencyTreeType);
            if ($classReflection->isSubclassOfClass($dependencyTreeClassReflection)) {
                return true;
            }
        }

        return false;
    }

    private function resolveParameterTypeClass(ParameterReflection $parameterReflection): ?Class_
    {
        $parameterType = $parameterReflection->getType();
        $classReflections = $parameterType->getObjectClassReflections();
        // XXX add support for union types
        if (count($classReflections) !== 1) {
            return null;
        }

        return $this->classReflectionParser->parse($classReflections[0]);
    }
}
