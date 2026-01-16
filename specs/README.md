# Technical Specifications

This directory contains technical specifications, task planning, and AI context for development work.

## Purpose

Document the **how** of implementation - technical planning, task breakdowns, and development context.

## Organization

### 📝 [tasks/](./tasks/)
Task breakdowns and implementation planning:
- Feature development tasks
- Bug fix specifications
- Refactoring plans
- Technical approach
- Acceptance criteria
- Effort estimates


### 🌿 [branches/](./branches/)
Branch-specific planning:
- Branch purpose and scope
- Files to change/create
- Testing requirements
- Dependencies
- Special considerations

## Task Specification Template

```markdown
# Task: [Feature/Fix Name]

## Objective
What needs to be built/fixed

## Context
Why this is needed

## Requirements
- [ ] Specific deliverable 1
- [ ] Specific deliverable 2

## Technical Approach
High-level implementation plan

## Acceptance Criteria
How to verify completion

## Dependencies
What must be done first

## Estimated Effort
Complexity estimate
```

## Branch Specification Template

```markdown
# Branch: [branch-name]

## Purpose
What this branch accomplishes

## Scope
Features/fixes included

## Files to Change
- path/to/file.php (what changes)

## New Files to Create
- path/to/new-file.php

## Testing Requirements
What tests are needed

## Special Considerations
Gotchas, risks, or notes
```

## AI Context Guidelines

When documenting patterns for AI:
- Provide concrete code examples
- Explain the "why" behind patterns
- Document common pitfalls
- Show correct vs incorrect approaches
- Keep examples minimal and focused

## Workflow

### When Starting New Work
1. Check existing specs for similar tasks
2. Create task spec if significant work
3. Create branch spec for context
4. Update as you learn during implementation

### When Completing Work
1. Update spec with lessons learned
2. Archive completed task specs
3. Move reusable patterns to ai-context
4. Clean up outdated information

## Examples

### Good Task Spec
```markdown
# Task: Add Waitlist Auto-Promotion

## Objective
Automatically promote users from waitlist when spots open

## Requirements
- [ ] Detect event cancellations
- [ ] Promote first waitlist user
- [ ] Send WhatsApp notification
- [ ] Update event participant count

## Technical Approach
- Use Laravel Event when participant cancels
- Queue job to process promotion
- Call WhatsApp service via interface

## Acceptance Criteria
- User promoted within 5 minutes
- Confirmation sent via WhatsApp
- Position updated in database
```

### Good AI Context
```markdown
# Pattern: WhatsApp Message Sending

Always use queued jobs, never send synchronously:

✅ Correct:
SendWhatsAppMessageJob::dispatch($chatId, $message);

❌ Wrong:
$whatsAppService->sendMessage($chatId, $message);

Reason: Prevents blocking requests and enables retries
```

## Maintenance

- Archive completed task specs monthly
- Keep only relevant AI context
- Update patterns as they evolve
- Remove outdated branch specs after merge
