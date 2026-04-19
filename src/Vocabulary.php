<?php

declare(strict_types=1);

namespace ConundrumCodex\BindingEngine\Vocabulary;

use ConundrumCodex\BindingEngine\Vocabulary\Exceptions\InvalidVocabularyException;
use ConundrumCodex\BindingEngine\Vocabulary\Interfaces\BindingTypeDefinitionInterface;
use ConundrumCodex\BindingEngine\Vocabulary\Interfaces\VocabularyInterface;

readonly class Vocabulary implements VocabularyInterface
{
    /**
     * @param list<BindingTypeDefinitionInterface> $bindingTypeDefinitions
     *
     * @throws InvalidVocabularyException
     */
    public function __construct(
        private array $bindingTypeDefinitions,
    ) {
        $this->guard();
    }

    /**
     * @return list<BindingTypeDefinitionInterface>
     */
    public function getBindingTypeDefinitions(): array
    {
        return $this->bindingTypeDefinitions;
    }

    public function hasBindingTypeDefinition(string $identifier): bool
    {
        return $this->getBindingTypeDefinition($identifier) !== null;
    }

    public function getBindingTypeDefinition(string $identifier): ?BindingTypeDefinitionInterface
    {
        return array_find($this->bindingTypeDefinitions, fn ($bindingTypeDefinition) => $bindingTypeDefinition->getIdentifier() === $identifier);

    }

    /**
     * @throws InvalidVocabularyException
     */
    private function guard(): void
    {
        $seenBindingTypeIdentifiers = [];

        foreach ($this->bindingTypeDefinitions as $bindingTypeDefinition) {
            $identifier = $bindingTypeDefinition->getIdentifier();

            if (isset($seenBindingTypeIdentifiers[$identifier])) {
                throw new InvalidVocabularyException(
                    sprintf(
                        'Vocabulary contains duplicate binding type definition "%s".',
                        $identifier,
                    )
                );
            }

            $seenBindingTypeIdentifiers[$identifier] = true;
        }
    }
}
