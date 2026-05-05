<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\Vocabulary;

use ConsolidatedWitchcraft\BindingEngine\Parser\Language\IdentifierPatterns;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Enums\BindingPayloadShapeEnum;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Exceptions\InvalidBindingTypeDefinitionException;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Interfaces\AttributeDefinitionInterface;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Interfaces\BindingTypeDefinitionInterface;

readonly class BindingTypeDefinition implements BindingTypeDefinitionInterface
{
    /**
     * @param list<BindingPayloadShapeEnum> $allowedPayloadShapes
     * @param list<AttributeDefinitionInterface> $attributeDefinitions
     *
     * @throws InvalidBindingTypeDefinitionException
     */
    public function __construct(
        private string $identifier,
        private string $label,
        private string $description,
        private array $allowedPayloadShapes,
        private array $attributeDefinitions = [],
    ) {
        $this->guard();
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return list<BindingPayloadShapeEnum>
     */
    public function getAllowedPayloadShapes(): array
    {
        return $this->allowedPayloadShapes;
    }

    public function allowsPayloadShape(BindingPayloadShapeEnum $payloadShape): bool
    {
        return array_any($this->allowedPayloadShapes, fn ($allowedPayloadShape) => $allowedPayloadShape === $payloadShape);

    }

    /**
     * @return list<AttributeDefinitionInterface>
     */
    public function getAttributeDefinitions(): array
    {
        return $this->attributeDefinitions;
    }

    public function hasAttributeDefinition(string $identifier): bool
    {
        return $this->getAttributeDefinition($identifier) !== null;
    }

    public function getAttributeDefinition(string $identifier): ?AttributeDefinitionInterface
    {
        return array_find($this->attributeDefinitions, fn ($attributeDefinition) => $attributeDefinition->getIdentifier() === $identifier);

    }

    /**
     * @return list<AttributeDefinitionInterface>
     */
    public function getRequiredAttributeDefinitions(): array
    {
        $requiredAttributeDefinitions = [];

        foreach ($this->attributeDefinitions as $attributeDefinition) {
            if ($attributeDefinition->isRequired()) {
                $requiredAttributeDefinitions[] = $attributeDefinition;
            }
        }

        return $requiredAttributeDefinitions;
    }

    /**
     * @throws InvalidBindingTypeDefinitionException
     */
    private function guard(): void
    {
        if (trim($this->identifier) === '') {
            throw new InvalidBindingTypeDefinitionException(
                'Binding type identifier must not be empty.'
            );
        }

        if (preg_match(IdentifierPatterns::BINDING_TYPE, $this->identifier) !== 1) {
            throw new InvalidBindingTypeDefinitionException(
                sprintf(
                    'Invalid binding type identifier "%s".',
                    $this->identifier,
                )
            );
        }

        if (trim($this->label) === '') {
            throw new InvalidBindingTypeDefinitionException(
                'Binding type label must not be empty.'
            );
        }

        if (trim($this->description) === '') {
            throw new InvalidBindingTypeDefinitionException(
                'Binding type description must not be empty.'
            );
        }

        if ($this->allowedPayloadShapes === []) {
            throw new InvalidBindingTypeDefinitionException(
                'Binding type definition must allow at least one payload shape.'
            );
        }

        $seenAttributeIdentifiers = [];

        foreach ($this->attributeDefinitions as $attributeDefinition) {
            $attributeIdentifier = $attributeDefinition->getIdentifier();

            if (isset($seenAttributeIdentifiers[$attributeIdentifier])) {
                throw new InvalidBindingTypeDefinitionException(
                    sprintf(
                        'Binding type definition contains duplicate attribute definition "%s".',
                        $attributeIdentifier,
                    )
                );
            }

            $seenAttributeIdentifiers[$attributeIdentifier] = true;
        }
    }
}
