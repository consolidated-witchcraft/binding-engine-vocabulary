<?php

use ConundrumCodex\BindingEngine\Vocabulary\AttributeDefinition;
use ConundrumCodex\BindingEngine\Vocabulary\BindingTypeDefinition;
use ConundrumCodex\BindingEngine\Vocabulary\Enums\AttributeValueTypeEnum;
use ConundrumCodex\BindingEngine\Vocabulary\Enums\BindingPayloadShapeEnum;
use ConundrumCodex\BindingEngine\Vocabulary\Exceptions\InvalidBindingTypeDefinitionException;
use ConundrumCodex\BindingEngine\Vocabulary\Exceptions\InvalidVocabularyException;
use ConundrumCodex\BindingEngine\Vocabulary\Vocabulary;

\it(
    'constructs correctly',

    /**
     * @throws InvalidBindingTypeDefinitionException
     * @throws InvalidVocabularyException
     */
    function () {
        $definitionOne = new BindingTypeDefinition(
            identifier: 'identifier',
            label: 'Label',
            description: 'description',
            allowedPayloadShapes: [
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

        $definitionTwo = new BindingTypeDefinition(
            identifier: 'identifier2',
            label: 'Label',
            description: 'description',
            allowedPayloadShapes: [
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
        $bindingTypeDefinitions = [
            $definitionOne,
            $definitionTwo
        ];
        $vocabulary = new Vocabulary($bindingTypeDefinitions);
        \expect($vocabulary)->toBeInstanceOf(Vocabulary::class);
        \expect($vocabulary->getBindingTypeDefinitions())->toBe($bindingTypeDefinitions);
        \expect($vocabulary->hasBindingTypeDefinition('identifier2'))->toBeTrue();
        \expect($vocabulary->hasBindingTypeDefinition('identifier'))->toBeTrue();
        \expect($vocabulary->getBindingTypeDefinition('identifier'))->toBe($definitionOne);
        \expect($vocabulary->getBindingTypeDefinition('identifier2'))->toBe($definitionTwo);
        \expect($vocabulary->hasBindingTypeDefinition('missing'))->toBeFalse();
        \expect($vocabulary->getBindingTypeDefinition('missing'))->toBeNull();
    }
);

\it(
    'rejects duplicate definitions',
    function () {
        $definitionOne = new BindingTypeDefinition(
            identifier: 'identifier',
            label: 'Label',
            description: 'description',
            allowedPayloadShapes: [
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
        $definitionTwo = clone $definitionOne;
        \expect(
            /**
             * @throws InvalidVocabularyException
             */
            function () use ($definitionOne, $definitionTwo) {
                new Vocabulary([
                    $definitionOne,
                    $definitionTwo
                ]);
            }
        )->toThrow(InvalidVocabularyException::class);
    }
);

\it(
    'constructs correctly with no binding type definitions',
    /**
     * @throws InvalidVocabularyException
     */
    function () {
        $vocabulary = new Vocabulary([]);

        expect($vocabulary)->toBeInstanceOf(Vocabulary::class)
            ->and($vocabulary->getBindingTypeDefinitions())->toBe([])
            ->and($vocabulary->hasBindingTypeDefinition('missing'))->toBeFalse()
            ->and($vocabulary->getBindingTypeDefinition('missing'))->toBeNull();
    }
);
