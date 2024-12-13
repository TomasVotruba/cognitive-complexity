<?php

declare(strict_types=1);

namespace TomasVotruba\CognitiveComplexity\Enum;

final class RuleIdentifier
{
    public const string DEPENDENCY_TREE = 'complexity.dependencyTree';

    public const string FUNCTION_COMPLEXITY = 'complexity.functionLike';

    public const string CLASS_LIKE_COMPLEXITY = 'complexity.classLike';
}
