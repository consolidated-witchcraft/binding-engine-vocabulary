<?php

use ConundrumCodex\BindingEngine\Vocabulary\AttributeDefinition;
use ConundrumCodex\BindingEngine\Vocabulary\BindingTypeDefinition;
use ConundrumCodex\BindingEngine\Vocabulary\Enums\AttributeValueTypeEnum;
use ConundrumCodex\BindingEngine\Vocabulary\Enums\BindingPayloadShapeEnum;
use ConundrumCodex\BindingEngine\Vocabulary\Exceptions\InvalidBindingTypeDefinitionException;

\it('constructs correctly', function () {
    $identifier = 'identifier';
    $label = 'Label';
    $description = 'Description';
    $allowedPayloadShapes = [
        BindingPayloadShapeEnum::Shorthand,
    ];
    $attributeDefinitions = [
        new AttributeDefinition(
            identifier: 'test',
            label: 'Test Attribute Label',
            description: 'test Attribute Description',
            valueType: AttributeValueTypeEnum::String,
            required: true,
            repeatable: false,
            allowedValues: null
        )
    ];

    $definition = new BindingTypeDefinition(
        identifier: $identifier,
        label: $label,
        description: $description,
        allowedPayloadShapes: $allowedPayloadShapes,
        attributeDefinitions: $attributeDefinitions
    );
    \expect($definition)->toBeInstanceOf(BindingTypeDefinition::class);
    \expect($definition->getIdentifier())->toBe($identifier);
    \expect($definition->getLabel())->toBe($label);
    \expect($definition->getDescription())->toBe($description);
    \expect($definition->getAllowedPayloadShapes())->toBe($allowedPayloadShapes);
    \expect($definition->getAttributeDefinitions())->toBe($definition->getAttributeDefinitions());
});

\it('rejects empty identifiers', function ($emptyIdentifier) {
    \expect(function () use ($emptyIdentifier) {
        new BindingTypeDefinition(
            identifier: $emptyIdentifier,
            label: 'Label',
            description: 'Description',
            allowedPayloadShapes:  [
                BindingPayloadShapeEnum::Shorthand,
            ],
            attributeDefinitions: [
                new AttributeDefinition(
                    identifier: 'test',
                    label: 'Test Attribute Label',
                    description: 'test Attribute Description',
                    valueType: AttributeValueTypeEnum::String,
                    required: true,
                    repeatable: false,
                    allowedValues: null
                )
            ]
        );
    })->toThrow(new InvalidBindingTypeDefinitionException(), 'Binding type identifier must not be empty.');
})->with(function (): iterable {
    yield 'empty string' => '';
    yield 'all whitespace' => '   ';
});

\it('rejects invalid identifiers', function (string $invalidIdentifier) {
    \expect(function () use ($invalidIdentifier) {
        new BindingTypeDefinition(
            identifier: $invalidIdentifier,
            label: 'Label',
            description: 'Description',
            allowedPayloadShapes:  [
                BindingPayloadShapeEnum::Shorthand,
            ],
            attributeDefinitions: [
                new AttributeDefinition(
                    identifier: 'test',
                    label: 'Test Attribute Label',
                    description: 'test Attribute Description',
                    valueType: AttributeValueTypeEnum::String,
                    required: true,
                    repeatable: false,
                    allowedValues: null
                )
            ]
        );
    })->toThrow(new InvalidBindingTypeDefinitionException(), sprintf(
        'Invalid binding type identifier "%s".',
        $invalidIdentifier,
    ));
})->with(function (): iterable {
    yield 'too short one character' => 'a';
    yield 'too short two characters' => 'ab';
    yield 'too long' => str_repeat('a', 65);
    yield 'starts with digit' => '1event';
    yield 'starts with hyphen' => '-event';
    yield 'ends with hyphen' => 'event-';
    yield 'double hyphen' => 'event--type';
    yield 'contains uppercase letters' => 'eventType';
    yield 'contains spaces' => 'event type';
    yield 'leading space' => ' event';
    yield 'trailing space' => 'event ';
    yield 'contains underscore' => 'event_type';
    yield 'contains dot' => 'event.type';
    yield 'contains slash' => 'event/type';
    yield 'contains hash' => 'event#type';
    yield 'contains symbol' => 'event!type';
    yield 'non ASCII' => 'événement';
    yield 'emoji' => 'event🔥';
    yield 'only hyphens' => '---';
    yield 'hyphen after trim' => ' -event';
    yield 'numeric only' => '123';
    yield 'mixed invalid characters' => 'ev@nt-ty#pe';
});
