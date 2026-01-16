# Business Documentation

This directory contains business-focused documentation about features, workflows, and business rules.

## Purpose

Document the **what** and **why** of the application, not the **how**. This documentation is for:
- Understanding business requirements
- Onboarding new team members
- Product planning and decisions
- Stakeholder communication

## Organization

### 📋 [features/](./features/)
Documentation for each major feature:
- What the feature does
- Why it exists (business value)
- Who can use it (user roles)
- Business rules and constraints
- User workflows
- Future considerations

### 🔄 [workflows/](./workflows/)
Business process documentation:
- Step-by-step user flows
- Decision points
- Alternative flows
- Error scenarios
- Integration points

### 📜 [business-rules/](./business-rules/)
Core business constraints and logic:
- Validation rules
- Business calculations
- Access control policies
- Capacity and limits
- Pricing and billing rules

## What NOT to Include

This is **business documentation**, not technical documentation. Don't include:
- ❌ Code examples or snippets
- ❌ API endpoint documentation
- ❌ Database schemas
- ❌ Configuration instructions
- ❌ Deployment procedures
- ❌ Technical architecture diagrams

Keep technical details in code comments, README files, or `../specs/`.

## Writing Guidelines

1. **Use clear, simple language** - Avoid jargon when possible
2. **Be concise but thorough** - Cover essentials without over-explaining
3. **Use examples** - Illustrate complex rules with scenarios
4. **Keep it current** - Update docs when features change
5. **Link related docs** - Help readers navigate related information

## Examples

**Good:** "Events can have 1-100 participants. When capacity is reached, new signups are added to a waitlist."

**Bad:** "The `max_participants` column in the `events` table must be validated between 1 and 100 using a FormRequest class."

## Template

When documenting a new feature, use this structure:

```markdown
# Feature Name

## Overview
Brief description (1-2 paragraphs)

## Business Value
Why this feature exists

## User Roles
Who can use this feature

## Business Rules
Constraints and validations

## Workflows
Key user flows

## Future Considerations
Limitations or planned enhancements
```

## Maintenance

- Review quarterly for accuracy
- Update when features change
- Archive outdated documentation
- Mark deprecated features clearly
