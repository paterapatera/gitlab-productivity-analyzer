# Task Generation Rules

## Core Principles

### 1. Natural Language Descriptions
Focus on capabilities and outcomes, not code structure.

**Describe**:
- What functionality to achieve
- Business logic and behavior
- Features and capabilities
- Domain language and concepts
- Data relationships and workflows

**Avoid**:
- File paths and directory structure
- Function/method names and signatures
- Type definitions and interfaces
- Class names and API contracts
- Specific data structures

**Rationale**: Implementation details (files, methods, types) are defined in design.md. Tasks describe the functional work to be done.

### 2. Task Integration & Progression

**Every task must**:
- Build on previous outputs (no orphaned code)
- Connect to the overall system (no hanging features)
- Progress incrementally (no big jumps in complexity)
- Validate core functionality early in sequence
- Respect architecture boundaries defined in design.md (Architecture Pattern & Boundary Map)
- Honor interface contracts documented in design.md
- Use major task summaries sparingly—omit detail bullets if the work is fully captured by child tasks.

**End with integration tasks** to wire everything together.

### 3. Flexible Task Sizing

**Guidelines**:
- **Major tasks**: As many sub-tasks as logically needed (group by cohesion)
- **Sub-tasks**: 1-3 hours each, 3-10 details per sub-task
- Balance between too granular and too broad

**Don't force arbitrary numbers** - let logical grouping determine structure.

### 4. Requirements Mapping

**End each task detail section with**:
- `_Requirements: X.X, Y.Y_` listing **only numeric requirement IDs** (comma-separated). Never append descriptive text, parentheses, translations, or free-form labels.
- For cross-cutting requirements, list every relevant requirement ID. All requirements MUST have numeric IDs in requirements.md. If an ID is missing, stop and correct requirements.md before generating tasks.
- Reference components/interfaces from design.md when helpful (e.g., `_Contracts: AuthService API`)

### 5. Code-Only Focus

**Include ONLY**:
- Coding tasks (implementation)
- Testing tasks (unit, integration, E2E)
- Technical setup tasks (infrastructure, configuration)

**Exclude**:
- Deployment tasks
- User testing
- Marketing/business activities

**Exception - Final Documentation & Validation**:
- **MUST include** a final section for documentation updates and validation:
  - README.md update task
  - `npm run pre-push` execution task
- These tasks should be placed at the end of the task list as the final major task group
- Format: Create a major task (e.g., "N. ドキュメント更新と最終確認") with sub-tasks for each documentation/validation item

### Optional Test Coverage Tasks

- When the design already guarantees functional coverage and rapid MVP delivery is prioritized, mark purely test-oriented follow-up work (e.g., baseline rendering/unit tests) as **optional** using the `- [ ]*` checkbox form.
- Only apply the optional marker when the sub-task directly references acceptance criteria from requirements.md in its detail bullets.
- Never mark implementation work or integration-critical verification as optional—reserve `*` for auxiliary/deferrable test coverage that can be revisited post-MVP.

### 6. Test-Driven Development (TDD) Guidelines

**Core Principle**: Follow TDD methodology (RED → GREEN → REFACTOR) for implementation tasks, but focus on **testing behavior, not implementation details**.

**What to Test**:
- **Behavior and functionality**: Test what the code does, not how it does it
- **Business logic**: Test domain logic, use cases, and workflows
- **Integration points**: Test how components work together
- **Edge cases and error handling**: Test boundary conditions and error scenarios

**What NOT to Test** (Exceptions to TDD):
- **Interfaces (Ports)**: Interface definitions are type contracts only—no implementation to test. Test the implementing classes instead (e.g., `CommitRepository` interface has no test, but `EloquentCommitRepository` implementation is tested).
- **Service Provider bindings**: Binding configuration is verified through actual behavior tests, not dedicated binding tests.
- **Implementation details**: Internal structure, private methods, and implementation-specific details should not be tested directly.
- **Type definitions**: Type signatures and interface contracts are enforced by the type system—no need for reflection-based signature tests.

**Rationale**:
- **Interfaces have no behavior**: They are type definitions only. The implementing classes contain the actual behavior that needs testing.
- **Type system enforces contracts**: The compiler/type checker already validates that implementations match interfaces.
- **Behavior tests catch interface changes**: If an interface changes, implementation tests will fail naturally, providing sufficient feedback.
- **Focus on value**: Tests should provide value by catching bugs and regressions. Testing implementation details adds maintenance cost without proportional benefit.

**When generating test tasks**:
- Generate tests for **implementing classes** (e.g., `EloquentCommitRepository`, `CollectCommits` service)
- Generate tests for **domain entities** and **value objects** (they contain business logic)
- Generate tests for **controllers** and **services** (they orchestrate behavior)
- **Do NOT** generate tests for interfaces, type definitions, or service provider bindings
- **Do NOT** generate reflection-based tests that only verify method signatures exist

**Example**:
- ✅ **Generate**: "Test `EloquentCommitCollectionHistoryRepository::saveOrUpdate()` method"
- ❌ **Do NOT generate**: "Test `CommitCollectionHistoryRepository` interface method signatures"

## Task Hierarchy Rules

### Maximum 2 Levels
- **Level 1**: Major tasks (1, 2, 3, 4...)
- **Level 2**: Sub-tasks (1.1, 1.2, 2.1, 2.2...)
- **No deeper nesting** (no 1.1.1)
- If a major task would contain only a single actionable item, collapse the structure and promote the sub-task to the major level (e.g., replace `1.1` with `1.`).
- When a major task exists purely as a container, keep the checkbox description concise and avoid duplicating detailed bullets—reserve specifics for its sub-tasks.

### Sequential Numbering
- Major tasks MUST increment: 1, 2, 3, 4, 5...
- Sub-tasks reset per major task: 1.1, 1.2, then 2.1, 2.2...
- Never repeat major task numbers

### Parallel Analysis (default)
- Assume parallel analysis is enabled unless explicitly disabled (e.g. `--sequential` flag).
- Identify tasks that can run concurrently when **all** conditions hold:
  - No data dependency on other pending tasks
  - No shared file or resource contention
  - No prerequisite review/approval from another task
- Validate that identified parallel tasks operate within separate boundaries defined in the Architecture Pattern & Boundary Map.
- Confirm API/event contracts from design.md do not overlap in ways that cause conflicts.
- Append `(P)` immediately after the task number for each parallel-capable task:
  - Example: `- [ ] 2.1 (P) Build background worker`
  - Apply to both major tasks and sub-tasks when appropriate.
- If sequential mode is requested, omit `(P)` markers entirely.
- Group parallel tasks logically (same parent when possible) and highlight any ordering caveats in detail bullets.
- Explicitly call out dependencies that prevent `(P)` even when tasks look similar.

### Checkbox Format
```markdown
- [ ] 1. Major task description
- [ ] 1.1 Sub-task description
  - Detail item 1
  - Detail item 2
  - _Requirements: X.X_

- [ ] 1.2 Sub-task description
  - Detail items...
  - _Requirements: Y.Y_

- [ ] 1.3 Sub-task description
  - Detail items...
  - _Requirements: Z.Z, W.W_

- [ ] 2. Next major task (NOT 1 again!)
- [ ] 2.1 Sub-task...
```

## Requirements Coverage

**Mandatory Check**:
- ALL requirements from requirements.md MUST be covered
- Cross-reference every requirement ID with task mappings
- If gaps found: Return to requirements or design phase
- No requirement should be left without corresponding tasks

Use `N.M`-style numeric requirement IDs where `N` is the top-level requirement number from requirements.md (for example, Requirement 1 → 1.1, 1.2; Requirement 2 → 2.1, 2.2), and `M` is a local index within that requirement group.

Document any intentionally deferred requirements with rationale.
