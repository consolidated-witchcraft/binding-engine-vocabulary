<?php

use ConsolidatedWitchcraft\BindingEngine\Vocabulary\AttributeDefinition;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\BindingTypeDefinition;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Enums\AttributeValueTypeEnum;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Enums\BindingPayloadShapeEnum;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Exceptions\InvalidBindingTypeDefinitionException;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Exceptions\InvalidVocabularyException;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Vocabulary;

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

        $identifier = 'test-vocabulary';
        $label = 'Test Label';
        $version = '0.1.0';
        $vocabulary = new Vocabulary(
            identifier: $identifier,
            label: $label,
            version: $version,
            bindingTypeDefinitions:$bindingTypeDefinitions
        );
        \expect($vocabulary)->toBeInstanceOf(Vocabulary::class);
        \expect($vocabulary->getBindingTypeDefinitions())->toBe($bindingTypeDefinitions);
        \expect($vocabulary->hasBindingTypeDefinition('identifier2'))->toBeTrue();
        \expect($vocabulary->hasBindingTypeDefinition('identifier'))->toBeTrue();
        \expect($vocabulary->getBindingTypeDefinition('identifier'))->toBe($definitionOne);
        \expect($vocabulary->getBindingTypeDefinition('identifier2'))->toBe($definitionTwo);
        \expect($vocabulary->hasBindingTypeDefinition('missing'))->toBeFalse();
        \expect($vocabulary->getBindingTypeDefinition('missing'))->toBeNull();
        \expect($vocabulary->getIdentifier())->toBe($identifier);
        \expect($vocabulary->getLabel())->toBe($label);
        \expect($vocabulary->getVersion())->toBe($version);
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
                new Vocabulary(
                    identifier: 'test-vocabulary',
                    label: 'Test Label',
                    version: '0.1.0',
                    bindingTypeDefinitions: [
                        $definitionOne,
                        $definitionTwo
                    ]
                );
            }
        )->toThrow(InvalidVocabularyException::class, 'Vocabulary contains duplicate binding type definition "identifier".');
    }
);

\it(
    'constructs correctly with no binding type definitions',
    /**
     * @throws InvalidVocabularyException
     */
    function () {
        $vocabulary = new Vocabulary(
            identifier: 'test-vocabulary',
            label: 'Test Label',
            version: '0.1.0',
            bindingTypeDefinitions: []
        );

        expect($vocabulary)->toBeInstanceOf(Vocabulary::class)
            ->and($vocabulary->getBindingTypeDefinitions())->toBe([])
            ->and($vocabulary->hasBindingTypeDefinition('missing'))->toBeFalse()
            ->and($vocabulary->getBindingTypeDefinition('missing'))->toBeNull();
    }
);

\it(
    'accepts identifier and label at the maximum allowed length',
    /**
     * @throws InvalidVocabularyException
     */
    function () {
        $identifier = str_repeat('a', 64);
        $label = str_repeat('A', 64);

        $vocabulary = new Vocabulary(
            identifier: $identifier,
            label: $label,
            version: '0.1.0',
            bindingTypeDefinitions: []
        );

        expect($vocabulary)->toBeInstanceOf(Vocabulary::class)
            ->and($vocabulary->getIdentifier())->toBe($identifier)
            ->and($vocabulary->getLabel())->toBe($label);
    }
);

\it(
    'rejects invalid identifiers',
    /**
     * @throws InvalidVocabularyException
     */
    function (string $invalidIdentifier) {
        \expect(function () use ($invalidIdentifier) {
            new Vocabulary(
                identifier: $invalidIdentifier,
                label: 'Test Label',
                version: '0.1.0',
                bindingTypeDefinitions: []
            );
        })->toThrow(InvalidVocabularyException::class);
    }
)->with(function (): iterable {
    yield 'empty string' => '';
    yield 'all whitespace' => '     ';
    yield 'contains Uppercase character' => 'AnIdentifier';
    yield 'contains spaces' => 'An Identifier ';
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
});

\it(
    'rejects invalid labels',
    /**
     * @throws InvalidVocabularyException
     */
    function (string $invalidLabel) {
        \expect(function () use ($invalidLabel) {
            new Vocabulary(
                identifier: 'test-vocabulary',
                label: $invalidLabel,
                version: '0.1.0',
                bindingTypeDefinitions: []
            );
        })->toThrow(InvalidVocabularyException::class);
    }
)->with(function (): iterable {
    yield 'empty string' => '';
    yield 'all whitespace' => '     ';
    yield 'contains symbol' => 'type!';
    yield 'too short two characters' => 'ab';
    yield 'too long' => str_repeat('a', 65);
    yield 'non ascii' => 'événement';
    yield 'emoji' => 'type🔥';
});

\it(
    'rejects invalid versions',
    /**
     * @throws InvalidVocabularyException
     */
    function (string $invalidVersion) {
        \expect(function () use ($invalidVersion) {
            new Vocabulary(
                identifier: 'test-vocabulary',
                label: 'Test Label',
                version: $invalidVersion,
                bindingTypeDefinitions: []
            );
        })->toThrow(InvalidVocabularyException::class);
    }
)->with(function (): iterable {
    yield 'empty string' => '';
    yield 'all whitespace' => '     ';
    yield 'missing patch is rejected' => '1.2';
    yield 'trailing dot after minor version is rejected' => '1.2.';
    yield 'leading v prefix is rejected' => 'v1.2.3';
    yield 'leading zero in major version is rejected' => '01.2.3';
    yield 'leading zero in minor version is rejected' => '1.02.3';
    yield 'leading zero in patch version is rejected' => '1.2.03';
    yield 'numeric prerelease with leading zero is rejected' => '1.2.3-01';
    yield 'empty prerelease identifier is rejected' => '1.2.3-alpha..1';
    yield 'dangling prerelease separator is rejected' => '1.2.3-';
    yield 'dangling build separator is rejected' => '1.2.3+';
    yield 'empty prerelease before build metadata is rejected' => '1.2.3-+build';
    yield 'empty build identifier is rejected' => '1.2.3+build..1';
    yield 'spaces are rejected' => '1.2.3 alpha';
    yield 'underscore in version core is rejected' => '1.2.3_beta';
    yield 'non ascii prerelease character is rejected' => '1.2.3-ä';
    yield 'non ascii build character is rejected' => '1.2.3+ä';
    yield 'trailing dot after patch version is rejected' => '1.2.3.';
    yield 'extra numeric segment is rejected' => '1.2.3.4';
});

\it(
    'accepts valid labels',
    /**
     * @throws InvalidVocabularyException
     */
    function (string $validLabel) {
        $vocabulary = new Vocabulary(
            identifier: 'test-vocabulary',
            label: $validLabel,
            version: '0.1.0',
            bindingTypeDefinitions: []
        );

        expect($vocabulary->getLabel())->toBe($validLabel);
    }
)->with(function (): iterable {
    yield 'letters only' => 'TestLabel';
    yield 'letters and spaces' => 'Test Label';
    yield 'letters and numbers' => 'Test Label 2';
    yield 'hyphen' => 'Sword-and-Sorcery';
    yield 'apostrophe' => "Adventurer's Guide";
    yield 'ampersand' => 'Research & Development';
    yield 'comma' => 'City, State';
    yield 'parentheses' => 'Event (Annual)';
    yield 'ends with closing parenthesis' => 'Event (Annual Edition)';
    yield 'colon' => 'Chapter: One';
    yield 'full stop' => 'Version 2.0';
    yield 'mixed allowed punctuation' => "Guild & Co. (North) - Chapter 2: Founder's Edition";
    yield 'minimum length' => 'Abc';
    yield 'maximum length' => str_repeat('A', 64);
});

it(
    'accepts valid versions',
    /**
     * @throws InvalidVocabularyException
     */
    function (string $validVersion) {
        /**
         * @throws InvalidVocabularyException
         */
        $vocabulary = new Vocabulary(
            identifier: 'test-vocabulary',
            label: 'Test Label',
            version: $validVersion,
            bindingTypeDefinitions: []
        );

        expect($vocabulary->getVersion())->toBe($validVersion);
    }
)->with(function (): iterable {
    yield 'simple semantic version' => '1.2.3';
    yield 'zero semantic version' => '0.1.0';
    yield 'prerelease' => '1.2.3-alpha';
    yield 'prerelease with dot segments' => '1.2.3-alpha.1';
    yield 'build metadata' => '1.2.3+build.1';
    yield 'prerelease and build metadata' => '1.2.3-alpha.1+build.1';
});
