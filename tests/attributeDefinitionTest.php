<?php

declare(strict_types=1);

use ConundrumCodex\BindingEngine\Vocabulary\Enums\AttributeValueTypeEnum;
use ConundrumCodex\BindingEngine\Vocabulary\AttributeDefinition;
use ConundrumCodex\BindingEngine\Vocabulary\Exceptions\InvalidAttributeDefinitionException;

\it(
    'constructs correctly for non enum types',
    function () {
        $identifier = "test";
        $label = "Label";
        $description = "Test description";
        $valueType = AttributeValueTypeEnum::String;
        $required = true;
        $repeatable = true;

        $attributeDefinition = new AttributeDefinition(
            identifier: $identifier,
            label: $label,
            description: $description,
            valueType: $valueType,
            required: $required,
            repeatable: $repeatable,
            allowedValues: null
        );

        \expect($attributeDefinition->getIdentifier())->toBe($identifier);
        \expect($attributeDefinition->getLabel())->toBe($label);
        \expect($attributeDefinition->getDescription())->toBe($description);
        \expect($attributeDefinition->getValueType())->toBe($valueType);
        \expect($attributeDefinition->isRequired())->toBe($required);
        \expect($attributeDefinition->isRepeatable())->toBe($repeatable);
        \expect($attributeDefinition->getAllowedValues())->toBeNull();
        \expect($attributeDefinition->hasAllowedValues())->toBeFalse();

    }
);

\it(
    'constructs correctly for enum types',
    function () {
        $identifier = "test";
        $label = "Label";
        $description = "Test description";
        $valueType = AttributeValueTypeEnum::Enum;
        $required = true;
        $repeatable = false;
        $allowedValues = [
            'alpha',
            'beta',
            'gamma',
            'delta'
        ];
        $attributeDefinition = new AttributeDefinition(
            identifier: $identifier,
            label: $label,
            description: $description,
            valueType: $valueType,
            required: $required,
            repeatable: $repeatable,
            allowedValues: $allowedValues
        );

        \expect($attributeDefinition->getIdentifier())->toBe($identifier);
        \expect($attributeDefinition->getLabel())->toBe($label);
        \expect($attributeDefinition->getDescription())->toBe($description);
        \expect($attributeDefinition->getValueType())->toBe($valueType);
        \expect($attributeDefinition->isRequired())->toBe($required);
        \expect($attributeDefinition->isRepeatable())->toBe($repeatable);
        \expect($attributeDefinition->getAllowedValues())->toBe($allowedValues);
        \expect($attributeDefinition->hasAllowedValues())->toBeTrue();

    }
);

\it(
    'rejects invalid identifiers',
    function (string $invalidIdentifier) {
        \expect(function () use ($invalidIdentifier) {
            new AttributeDefinition(
                identifier: $invalidIdentifier,
                label: "Label",
                description: "Test description",
                valueType: AttributeValueTypeEnum::String
            );
        })->toThrow(InvalidAttributeDefinitionException::class);
    }
)->with(
    function (): iterable {
        yield 'empty string' => '';
        yield 'all whitespace' => '    ';
        yield 'starts with digit' => '1type';
        yield 'starts with hyphen' => '-type';
        yield 'contains underscore' => 'event_type';
        yield 'contains symbol' => 'type!';
        yield 'contains hash' => 'type#';
        yield 'contains slash' => 'type/name';
        yield 'contains dot' => 'type.name';
        yield 'contains spaces' => 'event type';
        yield 'leading space' => ' type';
        yield 'trailing space' => 'type ';
        yield 'double hyphen' => 'event--type';
        yield 'trailing hyphen' => 'event-';
        yield 'leading hyphen after trim' => '-event';
        yield 'too short one character' => 'a';
        yield 'too short two characters' => 'ab';
        yield 'too long' => str_repeat('a', 65);
        yield 'non ascii' => 'événement';
        yield 'emoji' => 'type🔥';
        yield 'camelCase' => 'eventType';
    }
);

\it(
    'rejects invalid labels',
    function (string $invalidLabel) {
        \expect(function () use ($invalidLabel) {
            new AttributeDefinition(
                identifier: 'test-identifier',
                label: $invalidLabel,
                description: "Test description",
                valueType: AttributeValueTypeEnum::String
            );
        })->toThrow(InvalidAttributeDefinitionException::class);
    }
)->with(
    function (): iterable {
        yield 'empty string' => '';
        yield 'all whitespace' => '    ';
    }
);

\it(
    'rejects invalid descriptions',
    function (string $invalidDescription) {
        \expect(function () use ($invalidDescription) {
            new AttributeDefinition(
                identifier: 'test-identifier',
                label: 'Label',
                description: $invalidDescription,
                valueType: AttributeValueTypeEnum::String
            );
        })->toThrow(InvalidAttributeDefinitionException::class);
    }
)->with(
    function (): iterable {
        yield 'empty string' => '';
        yield 'all whitespace' => '    ';
    }
);

\it(
    'rejects empty allowed values arrays',
    function () {
        expect(function () {
            new AttributeDefinition(
                identifier: 'identifier',
                label: 'Label',
                description: 'Description',
                valueType: AttributeValueTypeEnum::Enum,
                required: true,
                repeatable: false,
                allowedValues: []
            );
        })->toThrow(
            InvalidAttributeDefinitionException::class,
            'Allowed values must not be an empty array.'
        );
    }
);

\it(
    'rejects duplicate allowed values',
    function () {
        \expect(function () {
            new AttributeDefinition(
                identifier: 'identifier',
                label: 'Label',
                description: 'Description',
                valueType: AttributeValueTypeEnum::Enum,
                required: true,
                repeatable: false,
                allowedValues: [
                    'alpha',
                    'alpha'
                ]
            );
        })->toThrow(InvalidAttributeDefinitionException::class);
    }
);

\it(
    'rejects enums without allowed values',
    function () {
        \expect(function () {
            new AttributeDefinition(
                identifier: 'identifier',
                label: 'Label',
                description: 'Description',
                valueType: AttributeValueTypeEnum::Enum,
                required: true,
                repeatable: false
            );
        })->toThrow(InvalidAttributeDefinitionException::class, 'Enum attributes must define allowed values.');
    }
);

\it(
    'rejects non-enums with allowed values',
    function () {
        \expect(function () {
            new AttributeDefinition(
                identifier: 'identifier',
                label: 'Label',
                description: 'Description',
                valueType: AttributeValueTypeEnum::String,
                required: true,
                repeatable: false,
                allowedValues: [
                    'alpha',
                    'beta'
                ]
            );
        })->toThrow(InvalidAttributeDefinitionException::class, 'Allowed values may only be defined for enum value types.');
    }
);


\it(
    'rejects empty string allowed values',
    function () {
        \expect(function () {
            new AttributeDefinition(
                identifier: 'identifier',
                label: 'Label',
                description: 'Description',
                valueType: AttributeValueTypeEnum::Enum,
                required: true,
                repeatable: false,
                allowedValues: [
                    'alpha',
                    '   '
                ]
            );
        })->toThrow(InvalidAttributeDefinitionException::class);
    }
);