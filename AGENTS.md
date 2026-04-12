# AGENTS.md — Binding Vocabulary Library

## Previous Development
Previously, the binding engine parser was developed with substantial assistance from another A.I Agent.
- You MUST read HANDOVER.md to inform your approach to this project.
- The README.md contains important notes on the nature of the project, and the library

## Purpose

This repository implements the **vocabulary and validation layer** of the Conundrum Codex Binding Engine system.

It operates on the AST produced by the parser and is responsible for determining whether bindings are **semantically valid**.

This library is **not a parser** and must not perform parsing logic.

---

## Architectural Position

This library is one stage in a multi-layer pipeline:

1. Parser (external dependency)
    - Produces AST + syntax diagnostics

2. Vocabulary / Validation (this library)
    - Defines binding types and attributes
    - Validates AST nodes
    - Produces semantic diagnostics

3. Inference (future)
    - Derives additional meaning

4. Projection (future)
    - Produces application-facing models

Do not implement inference or projection logic here.

---

## Core Principles

### 1. Strict Separation of Concerns

- Do NOT re-parse source text
- Do NOT inspect raw strings when AST nodes are available
- Operate only on AST nodes and their properties

---

### 2. Validation, Not Transformation

- This library validates input
- It MUST NOT mutate AST nodes
- It MUST NOT produce derived or inferred data structures

---

### 3. Deterministic Behaviour

- Given the same AST and vocabulary, validation MUST produce identical diagnostics
- No randomness, no hidden state

---

### 4. Diagnostics Over Exceptions

- Validation errors MUST be reported as diagnostics
- Exceptions should only be used for programmer errors (invalid construction, invariants)

---

### 5. Parser Is the Source of Truth for Syntax

- Do NOT duplicate regex patterns unless explicitly required
- Reuse parser-defined constraints where appropriate
- Do NOT reinterpret syntax at this layer

---

## Key Concepts

### Vocabulary

Defines the allowed structure of bindings:

- Binding types (e.g. `event`, `person`)
- Attribute definitions
- Payload expectations

---

### AttributeDefinition

Defines:

- identifier
- value type
- required / optional
- repeatable / non-repeatable
- allowed values (optional)

---

### BindingTypeDefinition

Defines:

- binding type name
- allowed attributes
- required attributes
- payload shape (shorthand vs attribute list)

---

### Validator

Consumes:

- `DocumentNode` (from parser)
- `Vocabulary`

Produces:

- list of diagnostics

---

## Constraints

### DO

- Use AST node types (`BindingNode`, `AttributeListPayloadNode`, etc.)
- Use spans from nodes when creating diagnostics
- Keep validation rules explicit and readable
- Write Pest tests for all validation behaviour

---

### DO NOT

- Do not modify AST nodes
- Do not introduce parsing logic
- Do not access raw source text unless via `SourceSpan::extract`
- Do not assume inference (e.g. relationships between bindings)
- Do not silently ignore invalid states

---

## Diagnostics

- Use the shared `Diagnostic` class from the parser
- Always include:
    - message
    - code
    - severity
    - source span (if available)

Diagnostics should be:

- precise
- stable
- predictable (important for UI highlighting)

---

## Testing Guidelines

- Use Pest
- Prefer small, focused tests
- Cover:
    - valid cases
    - invalid cases
    - edge cases (duplicates, missing attributes, etc.)

- When possible:
    - assert on diagnostic codes
    - assert on spans via `extract()`

---

## Naming Conventions

- Use kebab-case identifiers for binding types and attributes
- Follow parser constraints for identifiers
- Keep naming consistent with parser terminology

---

## Extensibility

This library is expected to be extended by consumers:

- Do not hardcode domain-specific binding types
- Do not assume specific vocabularies (e.g. `event`, `person`)
- Keep APIs generic and composable

---

## Future Considerations

This library will later integrate with:

- inference layer
- projection layer
- link resolution

Do not pre-emptively implement those concerns here.

---

## Summary

This library answers:

> “Is this binding valid according to the defined vocabulary?”

It must remain:

- pure
- deterministic
- side-effect free
- independent of higher-level semantics

## Coding Standards
Coding standards are contained within the `./codingstandards/` subdirectory, and MUST be followed.