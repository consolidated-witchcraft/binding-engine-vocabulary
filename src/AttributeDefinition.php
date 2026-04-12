<?php

declare(strict_types=1);

namespace ConundrumCodex\BindingEngine\Vocabulary;


use ConundrumCodex\BindingEngine\Parser\Language\IdentifierPatterns;

use ConundrumCodex\BindingEngine\Vocabulary\Enums\AttributeValueTypeEnum;
use ConundrumCodex\BindingEngine\Vocabulary\Exceptions\InvalidAttributeDefinitionException;
use ConundrumCodex\BindingEngine\Vocabulary\Interfaces\AttributeDefinitionInterface;

readonly class AttributeDefinition implements AttributeDefinitionInterface
{
    /**
     * @param list<string>|null $allowedValues
     *
     * @throws InvalidAttributeDefinitionException
     */
    public function __construct(
        private string $identifier,
        private string $label,
        private string $description,
        private AttributeValueTypeEnum $valueType,
        private bool $required = false,
        private bool $repeatable = false,
        private ?array $allowedValues = null,
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

    public function getValueType(): AttributeValueTypeEnum
    {
        return $this->valueType;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function isRepeatable(): bool
    {
        return $this->repeatable;
    }

    /**
     * @return list<string>|null
     */
    public function getAllowedValues(): ?array
    {
        return $this->allowedValues;
    }

    public function hasAllowedValues(): bool
    {
        return $this->allowedValues !== null;
    }

    /**
     * @throws InvalidAttributeDefinitionException
     */
    private function guard(): void
    {
        if (trim($this->identifier) === '') {
            throw new InvalidAttributeDefinitionException(
                'Attribute identifier must not be empty.'
            );
        }

        if (preg_match(IdentifierPatterns::ATTRIBUTE_IDENTIFIER, $this->identifier) !== 1) {
            throw new InvalidAttributeDefinitionException(
                sprintf('Invalid attribute identifier "%s".', $this->identifier)
            );
        }

        if (trim($this->label) === '') {
            throw new InvalidAttributeDefinitionException(
                'Attribute label must not be empty.'
            );
        }

        if (trim($this->description) === '') {
            throw new InvalidAttributeDefinitionException(
                'Attribute description must not be empty.'
            );
        }

        if ($this->allowedValues !== null) {
            if ($this->allowedValues === []) {
                throw new InvalidAttributeDefinitionException(
                    'Allowed values must not be an empty array.'
                );
            }

            $seen = [];

            foreach ($this->allowedValues as $value) {
                if (!is_string($value) || trim($value) === '') {
                    throw new InvalidAttributeDefinitionException(
                        'Allowed values must be non-empty strings.'
                    );
                }

                if (isset($seen[$value])) {
                    throw new InvalidAttributeDefinitionException(
                        sprintf('Duplicate allowed value "%s".', $value)
                    );
                }

                $seen[$value] = true;
            }

            if ($this->valueType !== AttributeValueTypeEnum::Enum) {
                throw new InvalidAttributeDefinitionException(
                    'Allowed values may only be defined for enum value types.'
                );
            }
        }

        if ($this->valueType === AttributeValueTypeEnum::Enum && $this->allowedValues === null) {
            throw new InvalidAttributeDefinitionException(
                'Enum attributes must define allowed values.'
            );
        }
    }
}