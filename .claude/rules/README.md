# Project Rules

This directory contains detailed coding guidelines and best practices for this WhatsApp Bot Platform project.

## Rule Files

### 📐 [api-design.md](./api-design.md)
REST API design principles and standards:
- HTTP methods and resource naming
- Status codes and error handling
- Request/response formats
- Filtering, sorting, pagination
- API versioning strategy
- Authentication & authorization
- Testing requirements

### 🏗️ [backend-architecture.md](./backend-architecture.md)
Service layer architecture and design patterns:
- Controller, Service, Repository layers
- SOLID principles implementation
- Interfaces and contracts usage
- Design patterns (Strategy, Factory, Observer, Command)
- Laravel Events & Listeners
- Data Transfer Objects (DTOs)
- Dependency injection

### 📚 [documentation.md](./documentation.md)
Documentation standards and organization:
- Business documentation in `./docs`
- Technical specs in `./specs`
- Feature documentation structure
- Workflow documentation
- Business rules documentation
- Task specifications
- Branch planning

### ✨ [code-quality.md](./code-quality.md)
Code quality standards and practices:
- DRY (Don't Repeat Yourself) principles
- Clean code guidelines
- Testing standards and patterns
- Performance best practices
- Laravel-specific conventions
- Security best practices

### 💬 [whatsapp-integration.md](./whatsapp-integration.md)
WhatsApp/WAHA integration patterns:
- Service architecture
- Queue jobs for messaging
- Webhook handling
- Message formatting
- Bot command processing
- Testing strategies
- Error handling & logging

## How to Use These Rules

### For AI Assistants
- Read relevant rule files before implementing features
- Follow all patterns and conventions defined
- Reference examples provided in each file
- These rules override general conventions

### For Developers
- Review rules before starting new features
- Use as reference during code reviews
- Keep rules updated as patterns evolve
- Propose rule changes via pull requests

## Rule Hierarchy

1. **Project-specific rules** (these files) - Highest priority
2. **Laravel Boost Guidelines** (CLAUDE.md) - Framework-level
3. **General best practices** - Fallback

When rules conflict, project-specific rules always take precedence.

## Quick Reference

| Task | Relevant Rules |
|------|---------------|
| Building API endpoints | `api-design.md`, `backend-architecture.md` |
| WhatsApp integration | `whatsapp-integration.md`, `backend-architecture.md` |
| Writing services | `backend-architecture.md`, `code-quality.md` |
| Writing tests | `code-quality.md`, `api-design.md` |
| Documenting features | `documentation.md` |
| Planning new features | `documentation.md`, `backend-architecture.md` |

## Contributing

When adding or modifying rules:
1. Ensure consistency with existing rules
2. Provide clear examples
3. Explain the "why" behind the rule
4. Update this README if adding new files