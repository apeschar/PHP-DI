<?php

declare(strict_types=1);

namespace DI\Definition\Resolver;

use DI\Definition\Definition;
use DI\Definition\EnvironmentVariableDefinition;
use DI\Definition\Exception\InvalidDefinition;

/**
 * Resolves a environment variable definition to a value.
 *
 * @author James Harris <james.harris@icecave.com.au>
 */
class EnvironmentVariableResolver implements DefinitionResolver
{
    public function __construct(
        private DefinitionResolver $definitionResolver,
        private $variableReader = 'getenv',
    ) {
    }

    /**
     * Resolve an environment variable definition to a value.
     *
     * @param EnvironmentVariableDefinition $definition
     */
    public function resolve(Definition $definition, array $parameters = []): mixed
    {
        $value = call_user_func($this->variableReader, $definition->getVariableName());

        if (false !== $value) {
            return $value;
        }

        if (!$definition->isOptional()) {
            throw new InvalidDefinition(sprintf(
                "The environment variable '%s' has not been defined",
                $definition->getVariableName()
            ));
        }

        $value = $definition->getDefaultValue();

        // Nested definition
        if ($value instanceof Definition) {
            return $this->definitionResolver->resolve($value);
        }

        return $value;
    }

    public function isResolvable(Definition $definition, array $parameters = []) : bool
    {
        return true;
    }
}
