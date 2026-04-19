<?php

declare(strict_types=1);

use ConundrumCodex\BindingEngine\Parser\Ast\Exceptions\SourceSpanConstructionException;
use ConundrumCodex\BindingEngine\Parser\Ast\Nodes\Exceptions\InvalidAttributeAssignmentNodeException;
use ConundrumCodex\BindingEngine\Parser\Ast\Nodes\Exceptions\InvalidAttributeListPayloadNodeException;
use ConundrumCodex\BindingEngine\Parser\Ast\Nodes\Exceptions\InvalidBindingNodeException;
use ConundrumCodex\BindingEngine\Parser\Ast\Nodes\Exceptions\InvalidShorthandPayloadNodeException;
use ConundrumCodex\BindingEngine\Parser\Ast\Nodes\Exceptions\InvalidTextNodeException;
use ConundrumCodex\BindingEngine\Parser\Diagnostics\Exceptions\DiagnosticConstructionException;
use ConundrumCodex\BindingEngine\Parser\Parser;
use ConundrumCodex\BindingEngine\Vocabulary\AttributeDefinition;
use ConundrumCodex\BindingEngine\Vocabulary\BindingTypeDefinition;
use ConundrumCodex\BindingEngine\Vocabulary\Enums\AttributeValueTypeEnum;
use ConundrumCodex\BindingEngine\Vocabulary\Enums\BindingPayloadShapeEnum;
use ConundrumCodex\BindingEngine\Vocabulary\Exceptions\InvalidBindingTypeDefinitionException;
use ConundrumCodex\BindingEngine\Vocabulary\Exceptions\InvalidVocabularyException;
use ConundrumCodex\BindingEngine\Vocabulary\Validator;
use ConundrumCodex\BindingEngine\Vocabulary\Vocabulary;

it(
    'validates a correct shorthand binding without diagnostics',
    /**
     * @throws InvalidVocabularyException
     * @throws DiagnosticConstructionException
     * @throws InvalidBindingNodeException
     * @throws InvalidAttributeListPayloadNodeException
     * @throws InvalidShorthandPayloadNodeException
     * @throws InvalidBindingTypeDefinitionException
     * @throws InvalidTextNodeException
     * @throws InvalidAttributeAssignmentNodeException
     * @throws SourceSpanConstructionException
     */
    function () {
        $parser = new Parser();

        $parseResult = $parser->parse('@person[jane-austen](Jane Austen)');

        $vocabulary = new Vocabulary([
            new BindingTypeDefinition(
                identifier: 'person',
                label: 'Person',
                description: 'A person binding.',
                allowedPayloadShapes: [
                    BindingPayloadShapeEnum::Shorthand,
                ],
                attributeDefinitions: [],
            ),
        ]);

        $validator = new Validator($vocabulary);
        $validationResult = $validator->validate($parseResult->getDocument());

        expect($validationResult->getDiagnostics())->toBe([])
            ->and($validationResult->hasDiagnostics())->toBeFalse()
            ->and($validationResult->hasErrors())->toBeFalse();
    }
);

it(
    'produces a diagnostic for an unknown binding type',
    /**
     * @throws InvalidVocabularyException
     * @throws DiagnosticConstructionException
     * @throws InvalidBindingNodeException
     * @throws InvalidAttributeListPayloadNodeException
     * @throws InvalidShorthandPayloadNodeException
     * @throws InvalidTextNodeException
     * @throws InvalidAttributeAssignmentNodeException
     * @throws SourceSpanConstructionException
     */
    function () {
        $parser = new Parser();

        $parseResult = $parser->parse('@unknown[something](Something)');

        $validator = new Validator(new Vocabulary([]));
        $validationResult = $validator->validate($parseResult->getDocument());

        expect($validationResult->hasDiagnostics())->toBeTrue()
            ->and($validationResult->hasErrors())->toBeTrue()
            ->and($validationResult->getDiagnostics())->toHaveCount(1);

        $diagnostic = $validationResult->getDiagnostics()[0];

        expect($diagnostic->getCode())->toBe('vocabulary.binding_type.unknown')
            ->and($diagnostic->getMessage())->toBe('Unknown binding type "unknown".')
            ->and($diagnostic->getSourceSpan())->not->toBeNull()
            ->and($diagnostic->getSourceSpan()->extract('@unknown[something](Something)'))
            ->toBe('@unknown[something](Something)');
    }
);

it(
    'produces a diagnostic when shorthand payload is used for an attribute-list-only binding type',
    /**
     * @throws InvalidVocabularyException
     * @throws DiagnosticConstructionException
     * @throws InvalidBindingNodeException
     * @throws InvalidAttributeListPayloadNodeException
     * @throws InvalidShorthandPayloadNodeException
     * @throws InvalidTextNodeException
     * @throws InvalidAttributeAssignmentNodeException
     * @throws SourceSpanConstructionException
     * @throws InvalidBindingTypeDefinitionException
     */
    function () {
        $parser = new Parser();

        $source = '@event[battle-of-somewhere](Battle of Somewhere)';
        $parseResult = $parser->parse($source);

        $vocabulary = new Vocabulary([
            new BindingTypeDefinition(
                identifier: 'event',
                label: 'Event',
                description: 'An event binding.',
                allowedPayloadShapes: [
                    BindingPayloadShapeEnum::AttributeList,
                ],
                attributeDefinitions: [
                    new AttributeDefinition(
                        identifier: 'type',
                        label: 'Type',
                        description: 'The event type.',
                        valueType: AttributeValueTypeEnum::String,
                        required: true,
                        repeatable: false,
                        allowedValues: null,
                    ),
                ],
            ),
        ]);

        $validator = new Validator($vocabulary);
        $validationResult = $validator->validate($parseResult->getDocument());

        expect($validationResult->hasDiagnostics())->toBeTrue()
            ->and($validationResult->hasErrors())->toBeTrue()
            ->and($validationResult->getDiagnostics())->toHaveCount(1);

        $diagnostic = $validationResult->getDiagnostics()[0];

        expect($diagnostic->getCode())->toBe('vocabulary.payload.invalid_shape')
            ->and($diagnostic->getMessage())->toBe('Binding type "event" does not allow payload shape "shorthand".')
            ->and($diagnostic->getSourceSpan())->not->toBeNull()
            ->and($diagnostic->getSourceSpan()->extract($source))->toBe($source);
    }
);

it(
    'produces a diagnostic for an unknown attribute',
    /**
     * @throws InvalidVocabularyException
     * @throws DiagnosticConstructionException
     * @throws InvalidBindingNodeException
     * @throws InvalidAttributeListPayloadNodeException
     * @throws InvalidShorthandPayloadNodeException
     * @throws InvalidTextNodeException
     * @throws InvalidAttributeAssignmentNodeException
     * @throws SourceSpanConstructionException
     * @throws InvalidBindingTypeDefinitionException
     */
    function () {
        $parser = new Parser();

        $source = '@event[type:birth, subject:jane-austen]';
        $parseResult = $parser->parse($source);

        $vocabulary = new Vocabulary([
            new BindingTypeDefinition(
                identifier: 'event',
                label: 'Event',
                description: 'An event binding.',
                allowedPayloadShapes: [
                    BindingPayloadShapeEnum::AttributeList,
                ],
                attributeDefinitions: [
                    new AttributeDefinition(
                        identifier: 'type',
                        label: 'Type',
                        description: 'The event type.',
                        valueType: AttributeValueTypeEnum::String,
                        required: true,
                        repeatable: false,
                        allowedValues: null,
                    ),
                ],
            ),
        ]);

        $validator = new Validator($vocabulary);
        $validationResult = $validator->validate($parseResult->getDocument());

        expect($validationResult->hasDiagnostics())->toBeTrue()
            ->and($validationResult->hasErrors())->toBeTrue()
            ->and($validationResult->getDiagnostics())->toHaveCount(1);

        $diagnostic = $validationResult->getDiagnostics()[0];

        expect($diagnostic->getCode())->toBe('vocabulary.attribute.unknown')
            ->and($diagnostic->getMessage())->toBe('Unknown attribute "subject" for binding type "event".')
            ->and($diagnostic->getSourceSpan())->not->toBeNull()
            ->and($diagnostic->getSourceSpan()->extract($source))->toBe('subject:jane-austen');
    }
);

it(
    'produces a diagnostic for a duplicate non-repeatable attribute',
    /**
     * @throws InvalidVocabularyException
     * @throws DiagnosticConstructionException
     * @throws InvalidBindingNodeException
     * @throws InvalidAttributeListPayloadNodeException
     * @throws InvalidShorthandPayloadNodeException
     * @throws InvalidTextNodeException
     * @throws InvalidAttributeAssignmentNodeException
     * @throws SourceSpanConstructionException
     * @throws InvalidBindingTypeDefinitionException
     */
    function () {
        $parser = new Parser();

        $source = '@event[type:birth, type:death]';
        $parseResult = $parser->parse($source);

        $vocabulary = new Vocabulary([
            new BindingTypeDefinition(
                identifier: 'event',
                label: 'Event',
                description: 'An event binding.',
                allowedPayloadShapes: [
                    BindingPayloadShapeEnum::AttributeList,
                ],
                attributeDefinitions: [
                    new AttributeDefinition(
                        identifier: 'type',
                        label: 'Type',
                        description: 'The event type.',
                        valueType: AttributeValueTypeEnum::String,
                        required: true,
                        repeatable: false,
                        allowedValues: null,
                    ),
                ],
            ),
        ]);

        $validator = new Validator($vocabulary);
        $validationResult = $validator->validate($parseResult->getDocument());

        expect($validationResult->hasDiagnostics())->toBeTrue()
            ->and($validationResult->hasErrors())->toBeTrue()
            ->and($validationResult->getDiagnostics())->toHaveCount(1);

        $diagnostic = $validationResult->getDiagnostics()[0];

        expect($diagnostic->getCode())->toBe('vocabulary.attribute.duplicate')
            ->and($diagnostic->getMessage())->toBe('Attribute "type" is not repeatable for binding type "event".')
            ->and($diagnostic->getSourceSpan())->not->toBeNull()
            ->and($diagnostic->getSourceSpan()->extract($source))->toBe('type:death');
    }
);

it(
    'produces a diagnostic for a missing required attribute',
    /**
     * @throws InvalidVocabularyException
     * @throws DiagnosticConstructionException
     * @throws InvalidBindingNodeException
     * @throws InvalidAttributeListPayloadNodeException
     * @throws InvalidShorthandPayloadNodeException
     * @throws InvalidTextNodeException
     * @throws InvalidAttributeAssignmentNodeException
     * @throws SourceSpanConstructionException
     * @throws InvalidBindingTypeDefinitionException
     */
    function () {
        $parser = new Parser();

        $source = '@event[type:birth]';
        $parseResult = $parser->parse($source);

        $vocabulary = new Vocabulary([
            new BindingTypeDefinition(
                identifier: 'event',
                label: 'Event',
                description: 'An event binding.',
                allowedPayloadShapes: [
                    BindingPayloadShapeEnum::AttributeList,
                ],
                attributeDefinitions: [
                    new AttributeDefinition(
                        identifier: 'type',
                        label: 'Type',
                        description: 'The event type.',
                        valueType: AttributeValueTypeEnum::String,
                        required: true,
                        repeatable: false,
                        allowedValues: null,
                    ),
                    new AttributeDefinition(
                        identifier: 'subject',
                        label: 'Subject',
                        description: 'The event subject.',
                        valueType: AttributeValueTypeEnum::String,
                        required: true,
                        repeatable: false,
                        allowedValues: null,
                    ),
                ],
            ),
        ]);

        $validator = new Validator($vocabulary);
        $validationResult = $validator->validate($parseResult->getDocument());

        expect($validationResult->hasDiagnostics())->toBeTrue()
            ->and($validationResult->hasErrors())->toBeTrue()
            ->and($validationResult->getDiagnostics())->toHaveCount(1);

        $diagnostic = $validationResult->getDiagnostics()[0];

        expect($diagnostic->getCode())->toBe('vocabulary.attribute.missing')
            ->and($diagnostic->getMessage())->toBe('Missing required attribute "subject" for binding type "event".')
            ->and($diagnostic->getSourceSpan())->not->toBeNull()
            ->and($diagnostic->getSourceSpan()->extract($source))->toBe('type:birth');
    }
);

it(
    'produces multiple diagnostics when multiple validation failures are present',
    /**
     * @throws InvalidVocabularyException
     * @throws DiagnosticConstructionException
     * @throws InvalidBindingNodeException
     * @throws InvalidAttributeListPayloadNodeException
     * @throws InvalidShorthandPayloadNodeException
     * @throws InvalidTextNodeException
     * @throws InvalidAttributeAssignmentNodeException
     * @throws SourceSpanConstructionException
     * @throws InvalidBindingTypeDefinitionException
     */
    function () {
        $parser = new Parser();

        $source = '@event[type:birth, type:death, location:somewhere]';
        $parseResult = $parser->parse($source);

        $vocabulary = new Vocabulary([
            new BindingTypeDefinition(
                identifier: 'event',
                label: 'Event',
                description: 'An event binding.',
                allowedPayloadShapes: [
                    BindingPayloadShapeEnum::AttributeList,
                ],
                attributeDefinitions: [
                    new AttributeDefinition(
                        identifier: 'type',
                        label: 'Type',
                        description: 'The event type.',
                        valueType: AttributeValueTypeEnum::String,
                        required: true,
                        repeatable: false,
                        allowedValues: null,
                    ),
                    new AttributeDefinition(
                        identifier: 'subject',
                        label: 'Subject',
                        description: 'The event subject.',
                        valueType: AttributeValueTypeEnum::String,
                        required: true,
                        repeatable: false,
                        allowedValues: null,
                    ),
                ],
            ),
        ]);

        $validator = new Validator($vocabulary);
        $validationResult = $validator->validate($parseResult->getDocument());

        expect($validationResult->hasDiagnostics())->toBeTrue()
            ->and($validationResult->hasErrors())->toBeTrue()
            ->and($validationResult->getDiagnostics())->toHaveCount(3);

        $codes = array_map(
            static fn ($diagnostic) => $diagnostic->getCode(),
            $validationResult->getDiagnostics(),
        );

        expect($codes)->toBe([
            'vocabulary.attribute.duplicate',
            'vocabulary.attribute.unknown',
            'vocabulary.attribute.missing',
        ]);
    }
);

it(
    'validates a correct attribute-list binding without diagnostics',
    /**
     * @throws InvalidVocabularyException
     * @throws DiagnosticConstructionException
     * @throws InvalidBindingNodeException
     * @throws InvalidAttributeListPayloadNodeException
     * @throws InvalidShorthandPayloadNodeException
     * @throws InvalidTextNodeException
     * @throws InvalidAttributeAssignmentNodeException
     * @throws SourceSpanConstructionException
     * @throws InvalidBindingTypeDefinitionException
     */
    function () {
        $parser = new Parser();

        $source = '@event[type:birth, subject:jane-austen]';
        $parseResult = $parser->parse($source);

        $vocabulary = new Vocabulary([
            new BindingTypeDefinition(
                identifier: 'event',
                label: 'Event',
                description: 'An event binding.',
                allowedPayloadShapes: [
                    BindingPayloadShapeEnum::AttributeList,
                ],
                attributeDefinitions: [
                    new AttributeDefinition(
                        identifier: 'type',
                        label: 'Type',
                        description: 'The event type.',
                        valueType: AttributeValueTypeEnum::String,
                        required: true,
                        repeatable: false,
                        allowedValues: null,
                    ),
                    new AttributeDefinition(
                        identifier: 'subject',
                        label: 'Subject',
                        description: 'The event subject.',
                        valueType: AttributeValueTypeEnum::String,
                        required: true,
                        repeatable: false,
                        allowedValues: null,
                    ),
                ],
            ),
        ]);

        $validator = new Validator($vocabulary);
        $validationResult = $validator->validate($parseResult->getDocument());

        expect($validationResult->getDiagnostics())->toBe([])
            ->and($validationResult->hasDiagnostics())->toBeFalse()
            ->and($validationResult->hasErrors())->toBeFalse();
    }
);

it(
    'does not produce a diagnostic for duplicate repeatable attributes',
    /**
     * @throws InvalidVocabularyException
     * @throws DiagnosticConstructionException
     * @throws InvalidBindingNodeException
     * @throws InvalidAttributeListPayloadNodeException
     * @throws InvalidShorthandPayloadNodeException
     * @throws InvalidTextNodeException
     * @throws InvalidAttributeAssignmentNodeException
     * @throws SourceSpanConstructionException
     * @throws InvalidBindingTypeDefinitionException
     */
    function () {
        $parser = new Parser();

        $source = '@event[tag:war, tag:historic]';
        $parseResult = $parser->parse($source);

        $vocabulary = new Vocabulary([
            new BindingTypeDefinition(
                identifier: 'event',
                label: 'Event',
                description: 'An event binding.',
                allowedPayloadShapes: [
                    BindingPayloadShapeEnum::AttributeList,
                ],
                attributeDefinitions: [
                    new AttributeDefinition(
                        identifier: 'tag',
                        label: 'Tag',
                        description: 'A tag.',
                        valueType: AttributeValueTypeEnum::String,
                        required: false,
                        repeatable: true,
                        allowedValues: null,
                    ),
                ],
            ),
        ]);

        $validator = new Validator($vocabulary);
        $validationResult = $validator->validate($parseResult->getDocument());

        expect($validationResult->getDiagnostics())->toBe([])
            ->and($validationResult->hasDiagnostics())->toBeFalse()
            ->and($validationResult->hasErrors())->toBeFalse();
    }
);

it(
    'ignores plain text without diagnostics',
    /**
     * @throws InvalidVocabularyException
     * @throws DiagnosticConstructionException
     * @throws InvalidBindingNodeException
     * @throws InvalidAttributeListPayloadNodeException
     * @throws InvalidShorthandPayloadNodeException
     * @throws InvalidTextNodeException
     * @throws InvalidAttributeAssignmentNodeException
     * @throws SourceSpanConstructionException
     */
    function () {
        $parser = new Parser();
        $parseResult = $parser->parse('Just some ordinary prose.');
        $validator = new Validator(new Vocabulary([]));
        $validationResult = $validator->validate($parseResult->getDocument());
        expect($validationResult->getDiagnostics())->toBe([])
            ->and($validationResult->hasDiagnostics())->toBeFalse()
            ->and($validationResult->hasErrors())->toBeFalse();
    }
);
