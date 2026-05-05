<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\Vocabulary;

use ConsolidatedWitchcraft\BindingEngine\Parser\Ast\Nodes\AttributeAssignmentNode;
use ConsolidatedWitchcraft\BindingEngine\Parser\Ast\Nodes\AttributeListPayloadNode;
use ConsolidatedWitchcraft\BindingEngine\Parser\Ast\Nodes\BindingNode;
use ConsolidatedWitchcraft\BindingEngine\Parser\Ast\Nodes\DocumentNode;
use ConsolidatedWitchcraft\BindingEngine\Parser\Diagnostics\Diagnostic;
use ConsolidatedWitchcraft\BindingEngine\Parser\Diagnostics\Enums\DiagnosticSeverityEnum;
use ConsolidatedWitchcraft\BindingEngine\Parser\Diagnostics\Exceptions\DiagnosticConstructionException;
use ConsolidatedWitchcraft\BindingEngine\Parser\Language\IdentifierPatterns;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Enums\AttributeValueTypeEnum;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Enums\BindingPayloadShapeEnum;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Interfaces\AttributeDefinitionInterface;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Interfaces\BindingTypeDefinitionInterface;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Interfaces\ValidatorInterface;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Interfaces\VocabularyInterface;

final readonly class Validator implements ValidatorInterface
{
    public function __construct(
        private VocabularyInterface $vocabulary,
    ) {
    }

    /**
     * @throws DiagnosticConstructionException
     */
    public function validate(DocumentNode $document): ValidationResult
    {
        $diagnostics = [];

        foreach ($document->getChildren() as $child) {
            if (!$child instanceof BindingNode) {
                continue;
            }

            foreach ($this->validateBinding($child) as $diagnostic) {
                $diagnostics[] = $diagnostic;
            }
        }

        return new ValidationResult($diagnostics);
    }

    /**
     * @return list<Diagnostic>
     * @throws DiagnosticConstructionException
     */
    private function validateBinding(BindingNode $bindingNode): array
    {
        $diagnostics = [];
        $bindingTypeIdentifier = $bindingNode->getBindingType();

        $bindingTypeDefinition = $this->vocabulary->getBindingTypeDefinition($bindingTypeIdentifier);

        if ($bindingTypeDefinition === null) {
            $diagnostics[] = new Diagnostic(
                message: sprintf(
                    'Unknown binding type "%s".',
                    $bindingTypeIdentifier,
                ),
                code: 'vocabulary.binding_type.unknown',
                severity: DiagnosticSeverityEnum::Error,
                sourceSpan: $bindingNode->getSpan(),
            );

            return $diagnostics;
        }

        $payloadShape = $this->resolvePayloadShape($bindingNode);

        if (!$bindingTypeDefinition->allowsPayloadShape($payloadShape)) {
            $diagnostics[] = new Diagnostic(
                message: sprintf(
                    'Binding type "%s" does not allow payload shape "%s".',
                    $bindingTypeIdentifier,
                    $payloadShape->value,
                ),
                code: 'vocabulary.payload.invalid_shape',
                severity: DiagnosticSeverityEnum::Error,
                sourceSpan: $bindingNode->getSpan(),
            );

            return $diagnostics;
        }

        if ($payloadShape === BindingPayloadShapeEnum::AttributeList) {
            $payload = $bindingNode->getPayload();

            if ($payload instanceof AttributeListPayloadNode) {
                foreach ($this->validateAttributeListPayload($payload, $bindingTypeDefinition) as $diagnostic) {
                    $diagnostics[] = $diagnostic;
                }
            }
        }

        return $diagnostics;
    }

    private function resolvePayloadShape(BindingNode $bindingNode): BindingPayloadShapeEnum
    {
        return $bindingNode->getPayload() instanceof AttributeListPayloadNode
            ? BindingPayloadShapeEnum::AttributeList
            : BindingPayloadShapeEnum::Shorthand;
    }

    /**
     * @return list<Diagnostic>
     * @throws DiagnosticConstructionException
     */
    private function validateAttributeListPayload(
        AttributeListPayloadNode $payload,
        BindingTypeDefinitionInterface $bindingTypeDefinition,
    ): array {
        $diagnostics = [];
        $seenCounts = [];

        foreach ($payload->getAttributes() as $attributeAssignment) {
            $identifier = $attributeAssignment->getIdentifier();
            $seenCounts[$identifier] = ($seenCounts[$identifier] ?? 0) + 1;

            $attributeDefinition = $bindingTypeDefinition->getAttributeDefinition($identifier);

            if ($attributeDefinition === null) {
                $diagnostics[] = new Diagnostic(
                    message: sprintf(
                        'Unknown attribute "%s" for binding type "%s".',
                        $identifier,
                        $bindingTypeDefinition->getIdentifier(),
                    ),
                    code: 'vocabulary.attribute.unknown',
                    severity: DiagnosticSeverityEnum::Error,
                    sourceSpan: $attributeAssignment->getSpan(),
                );

                continue;
            }

            if (!$attributeDefinition->isRepeatable() && $seenCounts[$identifier] > 1) {
                $diagnostics[] = new Diagnostic(
                    message: sprintf(
                        'Attribute "%s" is not repeatable for binding type "%s".',
                        $identifier,
                        $bindingTypeDefinition->getIdentifier(),
                    ),
                    code: 'vocabulary.attribute.duplicate',
                    severity: DiagnosticSeverityEnum::Error,
                    sourceSpan: $attributeAssignment->getSpan(),
                );

                continue;
            }

            $valueDiagnostic = $this->validateAttributeValue($attributeAssignment, $attributeDefinition);

            if ($valueDiagnostic !== null) {
                $diagnostics[] = $valueDiagnostic;
            }
        }

        foreach ($bindingTypeDefinition->getRequiredAttributeDefinitions() as $requiredAttributeDefinition) {
            $identifier = $requiredAttributeDefinition->getIdentifier();

            if (($seenCounts[$identifier] ?? 0) === 0) {
                $diagnostics[] = new Diagnostic(
                    message: sprintf(
                        'Missing required attribute "%s" for binding type "%s".',
                        $identifier,
                        $bindingTypeDefinition->getIdentifier(),
                    ),
                    code: 'vocabulary.attribute.missing',
                    severity: DiagnosticSeverityEnum::Error,
                    sourceSpan: $payload->getSpan(),
                );
            }
        }

        return $diagnostics;
    }

    private function validateAttributeValue(
        AttributeAssignmentNode $attributeAssignment,
        AttributeDefinitionInterface $attributeDefinition,
    ): ?Diagnostic {
        $value = $attributeAssignment->getValue();

        return match ($attributeDefinition->getValueType()) {
            AttributeValueTypeEnum::String => null,
            AttributeValueTypeEnum::Identifier => $this->validateIdentifierValue(
                $attributeAssignment,
                $attributeDefinition,
                $value,
            ),
            AttributeValueTypeEnum::Enum => $this->validateEnumValue(
                $attributeAssignment,
                $attributeDefinition,
                $value,
            ),
        };
    }

    /**
     * @throws DiagnosticConstructionException
     */
    private function validateIdentifierValue(
        AttributeAssignmentNode $attributeAssignment,
        AttributeDefinitionInterface $attributeDefinition,
        string $value,
    ): ?Diagnostic {
        if (preg_match(IdentifierPatterns::ATTRIBUTE_IDENTIFIER, $value) === 1) {
            return null;
        }

        return new Diagnostic(
            message: sprintf(
                'Attribute "%s" must contain a valid identifier value.',
                $attributeDefinition->getIdentifier(),
            ),
            code: 'vocabulary.attribute.invalid_value',
            severity: DiagnosticSeverityEnum::Error,
            sourceSpan: $attributeAssignment->getSpan(),
        );
    }

    /**
     * @throws DiagnosticConstructionException
     */
    private function validateEnumValue(
        AttributeAssignmentNode $attributeAssignment,
        AttributeDefinitionInterface $attributeDefinition,
        string $value,
    ): ?Diagnostic {
        $allowedValues = $attributeDefinition->getAllowedValues();

        if ($allowedValues !== null && in_array($value, $allowedValues, true)) {
            return null;
        }

        return new Diagnostic(
            message: sprintf(
                'Attribute "%s" must contain one of the allowed values.',
                $attributeDefinition->getIdentifier(),
            ),
            code: 'vocabulary.attribute.invalid_value',
            severity: DiagnosticSeverityEnum::Error,
            sourceSpan: $attributeAssignment->getSpan(),
        );
    }
}
