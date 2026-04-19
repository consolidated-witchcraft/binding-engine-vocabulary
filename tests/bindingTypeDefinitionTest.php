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
    \expect($definition->getAttributeDefinitions())->toBe($attributeDefinitions);
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
    })->toThrow(InvalidBindingTypeDefinitionException::class, 'Binding type identifier must not be empty.');
})->with(function (): iterable {
    yield 'empty string' => '';
    yield 'all whitespace' => '   ';
});

\it('rejects empty labels', function ($emptyLabel) {
    \expect(function () use ($emptyLabel) {
        new BindingTypeDefinition(
            identifier: 'test',
            label: $emptyLabel,
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
    })->toThrow(InvalidBindingTypeDefinitionException::class, 'Binding type label must not be empty.');
})->with(function (): iterable {
    yield 'empty string' => '';
    yield 'all whitespace' => '   ';
});

\it('rejects empty descriptions', function ($emptyDescription) {
    \expect(function () use ($emptyDescription) {
        new BindingTypeDefinition(
            identifier: 'test',
            label: 'Label',
            description: $emptyDescription,
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
    })->toThrow(InvalidBindingTypeDefinitionException::class, 'Binding type description must not be empty.');
})->with(function (): iterable {
    yield 'empty string' => '';
    yield 'all whitespace' => '   ';
});

\it(
    'rejects empty payload shapes',
    function () {
        \expect(function () {
            new BindingTypeDefinition(
                identifier: 'test',
                label: 'Label',
                description: 'Description',
                allowedPayloadShapes:  [
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
        })->toThrow(InvalidBindingTypeDefinitionException::class, 'Binding type definition must allow at least one payload shape.');
    }
);

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
    })->toThrow(InvalidBindingTypeDefinitionException::class, sprintf(
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

\it('rejects duplicate attributes', function () {
    \expect(function () {
        new BindingTypeDefinition(
            identifier: 'identifier',
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
                ),
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
    })->toThrow(InvalidBindingTypeDefinitionException::class, 'Binding type definition contains duplicate attribute definition "test".');
});

it('constructs correctly with no attribute definitions', function () {
    $definition = new BindingTypeDefinition(
        identifier: 'person',
        label: 'Person',
        description: 'Person description',
        allowedPayloadShapes: [BindingPayloadShapeEnum::Shorthand],
        attributeDefinitions: []
    );

    expect($definition->getAttributeDefinitions())->toBe([])
        ->and($definition->getRequiredAttributeDefinitions())->toBe([]);
});

it('exposes attribute and payload shape lookups correctly', function () {
    $requiredAttribute = new AttributeDefinition(
        identifier: 'type',
        label: 'Type',
        description: 'Type description',
        valueType: AttributeValueTypeEnum::String,
        required: true,
        repeatable: false,
        allowedValues: null
    );

    $optionalAttribute = new AttributeDefinition(
        identifier: 'subject',
        label: 'Subject',
        description: 'Subject description',
        valueType: AttributeValueTypeEnum::String,
        required: false,
        repeatable: false,
        allowedValues: null
    );

    $definition = new BindingTypeDefinition(
        identifier: 'event',
        label: 'Event',
        description: 'Event description',
        allowedPayloadShapes: [
            BindingPayloadShapeEnum::Shorthand,
            BindingPayloadShapeEnum::AttributeList,
        ],
        attributeDefinitions: [
            $requiredAttribute,
            $optionalAttribute,
        ]
    );

    expect($definition->allowsPayloadShape(BindingPayloadShapeEnum::Shorthand))->toBeTrue()
        ->and($definition->allowsPayloadShape(BindingPayloadShapeEnum::AttributeList))->toBeTrue()
        ->and($definition->hasAttributeDefinition('type'))->toBeTrue()
        ->and($definition->hasAttributeDefinition('subject'))->toBeTrue()
        ->and($definition->hasAttributeDefinition('location'))->toBeFalse()
        ->and($definition->getAttributeDefinition('type'))->toBe($requiredAttribute)
        ->and($definition->getAttributeDefinition('location'))->toBeNull()
        ->and($definition->getRequiredAttributeDefinitions())->toBe([$requiredAttribute]);
});
