<?php

declare(strict_types=1);

namespace ConundrumCodex\BindingEngine\Vocabulary;

use ConundrumCodex\BindingEngine\Parser\Ast\Nodes\AttributeListPayloadNode;
use ConundrumCodex\BindingEngine\Parser\Ast\Nodes\BindingNode;
use ConundrumCodex\BindingEngine\Parser\Ast\Nodes\DocumentNode;
use ConundrumCodex\BindingEngine\Parser\Diagnostics\Diagnostic;
use ConundrumCodex\BindingEngine\Parser\Diagnostics\Enums\DiagnosticSeverityEnum;
use ConundrumCodex\BindingEngine\Parser\Diagnostics\Exceptions\DiagnosticConstructionException;
use ConundrumCodex\BindingEngine\Vocabulary\Enums\BindingPayloadShapeEnum;
use ConundrumCodex\BindingEngine\Vocabulary\Interfaces\BindingTypeDefinitionInterface;
use ConundrumCodex\BindingEngine\Vocabulary\Interfaces\ValidatorInterface;
use ConundrumCodex\BindingEngine\Vocabulary\Interfaces\VocabularyInterface;

final readonly class Validator implements ValidatorInterface
{
    public function __construct(
        private VocabularyInterface $vocabulary,
    ) {
    }

    public function validate(DocumentNode $document): ValidationResult
    {
        $diagnostics = [];

        foreach ($document->getChildren() as $child) {
            if (!$child instanceof BindingNode) {
                continue;
            }

            $bindingDiagnostics = $this->validateBinding($child);

            foreach ($bindingDiagnostics as $diagnostic) {
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
}
