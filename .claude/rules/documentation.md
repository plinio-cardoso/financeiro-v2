# Documentation Rules

## Documentation Structure

This project maintains two types of documentation:

### 1. Business Documentation (`./docs`)
- **Purpose**: Document business logic, features, and domain concepts
- **Audience**: Developers, product team, future maintainers
- **Format**: Markdown files organized by subject
- **What to include**: Business rules, feature descriptions, workflows, use cases
- **What NOT to include**: Technical implementation details, API endpoints, code examples

### 2. Technical Specifications (`./specs`)
- **Purpose**: Track tasks, AI instructions, and branch specifications
- **Audience**: AI assistants, developers planning work
- **Format**: Structured markdown or task lists
- **What to include**: Task breakdowns, implementation specs, branch planning, AI context

## Business Documentation Guidelines

### Location & Organization
```
docs/
├── features/              # Feature documentation by domain
│   ├── event-management.md
│   ├── participant-lists.md
│   └── whatsapp-bot.md
├── workflows/            # Business process flows
│   ├── event-creation-flow.md
│   └── participant-signup-flow.md
└── business-rules/       # Core business rules and constraints
    ├── capacity-rules.md
    └── notification-rules.md
```

### What to Document

#### Feature Documentation
Document each major feature with:
- **Overview**: What the feature does (1-2 paragraphs)
- **Business Value**: Why this feature exists
- **User Roles**: Who can use this feature
- **Business Rules**: Constraints, validations, edge cases
- **Workflows**: Step-by-step user flows
- **Future Considerations**: Known limitations or planned enhancements

**Example Structure:**
```markdown
# Event Management

## Overview
The event management feature allows group administrators to create, update,
and manage sports events within WhatsApp groups. Events track participants,
manage capacity limits, and send automated notifications.

## Business Value
Simplifies coordination of sports activities by automating participant
management and reducing manual tracking in chat messages.

## User Roles
- **Group Admin**: Can create, edit, and cancel events
- **Participant**: Can join, leave, and view event details
- **Bot**: Automatically manages lists and sends notifications

## Business Rules
- Events must have a maximum participant limit (1-100)
- Participants can join until capacity is reached
- Waitlist is created automatically when event is full
- Events can only be edited by the creator or group admins
- Participants are notified 24 hours before event start

## Workflows
### Creating an Event
1. Admin sends command in group: "/create-event"
2. Bot prompts for event details (name, date, capacity)
3. Admin provides details via messages
4. Bot creates event and announces to group
5. Participants can start joining immediately

## Future Considerations
- Recurring events (weekly soccer matches)
- Payment integration for paid events
- Automatic reminders based on user preferences
```

#### Workflow Documentation
Document key business processes:
- Start and end states
- Actors involved
- Decision points
- Alternative flows
- Error scenarios

**Example:**
```markdown
# Participant Signup Flow

## Main Flow
1. User sends message indicating interest (e.g., "I'm in", "+1")
2. Bot analyzes message intent
3. Bot checks event capacity
4. If space available:
   - Add user to participants list
   - Send confirmation message
   - Update group with current count
5. If event full:
   - Add user to waitlist
   - Notify user of waitlist position

## Alternative Flows
- User already registered → Bot sends "already registered" message
- Event cancelled → Bot notifies user and prevents signup
- User not in group → Bot ignores (private message)

## Error Scenarios
- WhatsApp API unavailable → Queue confirmation for retry
- Duplicate signup detected → Dedup and confirm once
```

#### Business Rules Documentation
Document constraints and validation rules:
- Data validation rules
- Business constraints
- Calculation formulas
- Access control rules

**Example:**
```markdown
# Capacity Rules

## Event Capacity
- Minimum participants: 1
- Maximum participants: 100
- Default capacity: 20 (if not specified)

## Waitlist Rules
- Waitlist activated when event reaches capacity
- No limit on waitlist size
- FIFO (First In, First Out) when spots open
- Users notified within 5 minutes of spot availability

## Spot Allocation
When a participant cancels:
1. First person on waitlist is automatically promoted
2. Promoted user has 1 hour to confirm
3. If no confirmation, next person on waitlist is offered spot
4. Original participant cannot rejoin (goes to end of waitlist)
```

### Documentation Standards

#### Writing Style
- Write in clear, simple language
- Use present tense
- Be concise but thorough
- Use bullet points for lists
- Use numbered steps for sequences
- Include examples where helpful

#### Maintenance
- Update documentation when features change
- Mark deprecated features clearly
- Add dates to major updates
- Review documentation quarterly

#### What NOT to Include
- ❌ Code snippets or implementation details
- ❌ Database schema or table structures
- ❌ API endpoint documentation
- ❌ Configuration instructions
- ❌ Deployment procedures
- ❌ Technical architecture details

Keep technical details in code comments, README files, or separate technical documentation.

## Technical Specifications (`./specs`)

### Location & Organization
```
specs/
├── tasks/                # Task breakdowns for development
│   ├── event-feature.md
│   └── whatsapp-integration.md
├── ai-context/          # Context for AI assistants
│   ├── project-setup.md
│   └── coding-patterns.md
└── branches/            # Branch specifications and planning
    ├── feature-event-management.md
    └── fix-notification-bug.md
```

### Task Specifications

Document planned work with:
- **Objective**: What needs to be built/fixed
- **Context**: Why this is needed
- **Requirements**: Specific deliverables
- **Technical Approach**: High-level implementation plan
- **Acceptance Criteria**: How to verify completion
- **Dependencies**: Other tasks that must complete first
- **Estimated Effort**: Rough complexity estimate

**Example:**
```markdown
# Task: Implement Waitlist Management

## Objective
Build automatic waitlist functionality for events that reach capacity.

## Context
Currently, when events are full, users are simply rejected. We need
a waitlist system that automatically promotes users when spots open.

## Requirements
- [ ] Waitlist model and database table
- [ ] Auto-add to waitlist when event full
- [ ] Auto-promote from waitlist when spot opens
- [ ] Notification system for promotions
- [ ] Admin view of waitlist in dashboard

## Technical Approach
- Create Waitlist model with position tracking
- Use Laravel Events to detect cancellations
- Queue job to process waitlist promotions
- Send WhatsApp notification via WAHA API

## Acceptance Criteria
- User added to waitlist when event full
- User promoted automatically on cancellation
- WhatsApp confirmation sent within 5 minutes
- Position visible in participant list
- Feature tests cover all scenarios

## Dependencies
- Event management feature complete
- WhatsApp notification system functional

## Estimated Effort
Medium (3-5 hours)
```

### Branch Specifications

Document branch planning for AI context:
- Branch purpose and scope
- Files that will be changed
- New files to be created
- Testing requirements
- Special considerations

**Example:**
```markdown
# Branch: feature/waitlist-management

## Purpose
Implement automatic waitlist management for full events.

## Scope
- Backend API for waitlist operations
- WhatsApp bot commands for waitlist
- Dashboard UI for viewing waitlist
- Automated promotion system

## Files to Change
- app/Models/Event.php (add waitlist relationship)
- app/Services/EventService.php (add waitlist logic)
- routes/api.php (add waitlist endpoints)

## New Files to Create
- app/Models/Waitlist.php
- app/Services/WaitlistService.php
- app/Events/ParticipantCancelled.php
- app/Listeners/PromoteFromWaitlist.php
- app/Jobs/ProcessWaitlistPromotion.php
- database/migrations/create_waitlists_table.php
- tests/Feature/WaitlistManagementTest.php

## Testing Requirements
- Feature tests for all waitlist operations
- Test auto-promotion on cancellation
- Test notification sending
- Test edge cases (empty waitlist, simultaneous cancellations)

## Special Considerations
- Handle race conditions (multiple cancellations)
- Queue promotion jobs to avoid blocking
- Log all waitlist operations for debugging
```

### AI Context Documentation

Provide context for AI assistants:
- Project architecture overview
- Coding patterns and conventions
- Common tasks and how to approach them
- Gotchas and known issues

**Example:**
```markdown
# AI Context: WhatsApp Integration Patterns

## WAHA API Communication
All WhatsApp messages must be sent via queued jobs, never synchronously.

### Sending Messages
```php
// ✅ Correct - via queue
SendWhatsAppMessageJob::dispatch($sessionId, $to, $message);

// ❌ Wrong - synchronous
$whatsAppService->sendMessage($sessionId, $to, $message);
```

## Webhook Processing
Webhooks from WAHA are received at `/api/webhooks/waha`.
Process heavy operations asynchronously.

## Testing WhatsApp Features
Always mock WAHA API in tests using Http::fake().
Never make real API calls in tests.
```

## Documentation Workflow

### When Creating a New Feature
1. Write business documentation in `./docs/features/`
2. Document workflows in `./docs/workflows/`
3. Document business rules in `./docs/business-rules/`
4. Create task specification in `./specs/tasks/`
5. Update as you build and learn

### When Working with AI
1. Review existing specs in `./specs/` for context
2. Create branch spec if starting new feature
3. Update spec as requirements change
4. Document patterns in `./specs/ai-context/` for reuse

### Regular Maintenance
- Review docs monthly for accuracy
- Update when business rules change
- Archive old specs when branches merge
- Keep only current, relevant documentation

## Documentation Review Checklist

Before committing documentation:
- [ ] Is it in the correct location? (docs vs specs)
- [ ] Is it business logic or technical detail?
- [ ] Is it clear and understandable?
- [ ] Does it follow the structure guidelines?
- [ ] Are examples helpful and accurate?
- [ ] Is it dated (if needed)?
- [ ] Does it reference related docs?