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
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\TypeWithClassName;
use TomasVotruba\CognitiveComplexity\AstCognitiveComplexityAnalyzer;
use TomasVotruba\CognitiveComplexity\ClassReflectionParser;
use TomasVotruba\CognitiveComplexity\Configuration;

/**
 * @implements Rule<InClassNode>
 *
 * Find classes with complex constructor dependency tree = current class complexity + complexity of all __construct() dependencies.
 */
final readonly class ClassDependencyTreeRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Dependency tree complexity %d is over %d. Refactor __construct() dependencies or split up.';

    public function __construct(
        private AstCognitiveComplexityAnalyzer $astCognitiveComplexityAnalyzer,
        private ClassReflectionParser $classReflectionParser,
        private Configuration $configuration
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

        $message = sprintf(
            self::ERROR_MESSAGE,
            $totaDependencyTreeComplexity,
            $this->configuration->getMaxDependencyTreeComplexity()
        );

        return [RuleErrorBuilder::message($message)->identifier('complexity.dependencyTree')->build()];
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
        $classReflections = $parameterType->getObjectClassReflections();
        // XXX add support for union types
        if (count($classReflections) !== 1) {
            return null;
        }

        return $this->classReflectionParser->parse($classReflections[0]);
    }
}
