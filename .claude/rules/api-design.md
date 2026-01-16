# REST API Design Rules

## HTTP Methods & Resource Naming

### Use Proper HTTP Verbs
- `GET` - Retrieve resources (read-only, idempotent, cacheable)
- `POST` - Create new resources or trigger actions
- `PUT` - Full replacement of a resource
- `PATCH` - Partial update of a resource
- `DELETE` - Remove a resource

### Resource Naming Conventions
- Use **plural nouns** for collections: `/users`, `/groups`, `/events`
- Use **kebab-case** for multi-word resources: `/event-participants`, `/subscription-plans`
- Avoid verbs in URLs - let HTTP methods define the action
  - ✅ `POST /users` (create user)
  - ❌ `POST /createUser`
  - ✅ `GET /users/123/orders` (get user's orders)
  - ❌ `GET /getUserOrders?userId=123`

### Nested Resources
- Use nesting to show relationships, but limit to 2 levels maximum
  - ✅ `GET /groups/123/participants`
  - ✅ `POST /groups/123/participants`
  - ❌ `GET /organizations/1/groups/2/participants/3/events` (too deep)

## Status Codes

### Success Responses
- `200 OK` - Successful GET, PUT, PATCH, or DELETE
- `201 Created` - Successful POST that creates a resource (include `Location` header)
- `204 No Content` - Successful request with no response body (typically DELETE)

### Client Error Responses
- `400 Bad Request` - Invalid request payload or parameters
- `401 Unauthorized` - Missing or invalid authentication
- `403 Forbidden` - Authenticated but lacks permission
- `404 Not Found` - Resource doesn't exist
- `422 Unprocessable Entity` - Validation errors

### Server Error Responses
- `500 Internal Server Error` - Unexpected server error
- `503 Service Unavailable` - Server temporarily unavailable

## Request & Response Format

### Request Bodies
- Use JSON for request bodies
- Validate all input data using Form Request classes
- Use snake_case for JSON properties to match Laravel conventions

### Response Structure
- Always return consistent JSON structure
- Use Eloquent API Resources for formatting responses
- Include appropriate metadata for lists (pagination, totals)

Example successful response:
```json
{
  "data": {
    "id": 123,
    "name": "Soccer Group",
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

Example collection response with pagination:
```json
{
  "data": [
    { "id": 1, "name": "Item 1" },
    { "id": 2, "name": "Item 2" }
  ],
  "meta": {
    "current_page": 1,
    "total": 50,
    "per_page": 15
  },
  "links": {
    "first": "https://api.example.com/items?page=1",
    "last": "https://api.example.com/items?page=4",
    "next": "https://api.example.com/items?page=2"
  }
}
```

### Error Response Structure
- Always return errors in a consistent format
- Include a human-readable message
- Provide error codes for programmatic handling
- Include validation errors when applicable

Example error response:
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

## Filtering, Sorting, and Pagination

### Filtering
- Use query parameters for filtering: `GET /events?status=active&sport=soccer`
- Support common operators when needed: `GET /events?min_participants=10`

### Sorting
- Use `sort` parameter: `GET /events?sort=created_at`
- Use `-` prefix for descending: `GET /events?sort=-created_at`
- Support multiple sort fields: `GET /events?sort=-created_at,name`

### Pagination
- Always paginate collection endpoints
- Use `page` and `per_page` query parameters
- Default to reasonable page size (15-25 items)
- Include pagination metadata in response

## Versioning

### API Versioning Strategy
- Use URL path versioning: `/api/v1/users`, `/api/v2/users`
- Group routes by version in `routes/api/v1.php`, `routes/api/v2.php`
- Maintain backward compatibility within the same major version
- Document breaking changes when introducing new versions

## Authentication & Authorization

### Authentication
- Use Laravel Sanctum for API token authentication
- Include token in `Authorization: Bearer {token}` header
- Return `401 Unauthorized` for missing or invalid tokens

### Authorization
- Check permissions using Laravel Policies
- Return `403 Forbidden` when user lacks permission
- Validate ownership before allowing operations on resources

## Performance & Optimization

### Eager Loading
- Always use eager loading to prevent N+1 queries
- Load only the relationships needed for the response
- Use `with()` in controller or service layer

### Rate Limiting
- Implement rate limiting on API routes
- Return `429 Too Many Requests` when limit exceeded
- Include `X-RateLimit-*` headers in responses

### Caching
- Cache frequently accessed, slow-changing data
- Use appropriate cache TTLs
- Invalidate cache when data changes
- Use Cache tags for granular invalidation

## Security Best Practices

### Input Validation
- Validate all input using Form Request classes
- Never trust client data
- Sanitize user input to prevent XSS
- Use parameterized queries (Eloquent) to prevent SQL injection

### Data Exposure
- Never expose sensitive data (passwords, tokens, internal IDs)
- Use API Resources to control what fields are returned
- Hide implementation details and internal structure

### CORS
- Configure CORS properly for your frontend domains
- Don't use wildcard (*) in production
- Specify allowed origins, methods, and headers

## Documentation

### API Documentation Requirements
- Document all endpoints with request/response examples
- Include required/optional parameters
- Document possible error responses
- Specify authentication requirements
- Keep documentation in sync with implementation

### Headers
- Always specify `Content-Type: application/json` for JSON APIs
- Support `Accept` header for content negotiation
- Include `X-Request-ID` for request tracing in logs

## Laravel-Specific Best Practices

### Use API Resources
- Create dedicated API Resource classes for all models
- Use Resource Collections for lists
- Transform data consistently across all endpoints

### Use Form Requests
- Create dedicated Form Request classes for validation
- Include custom error messages
- Keep validation logic out of controllers

### Route Organization
- Group API routes in `routes/api.php` or versioned files
- Apply middleware groups (`api`, `auth:sanctum`)
- Use route model binding for cleaner controllers
- Name all routes for easier reference

### Controller Methods
- Keep controllers thin - delegate to services
- Use single responsibility per controller action
- Return consistent response formats
- Use HTTP status codes correctly

## Testing

### API Testing Requirements
- Write feature tests for all API endpoints
- Test successful responses (200, 201, 204)
- Test error cases (400, 401, 403, 404, 422)
- Test authentication and authorization
- Test validation rules
- Test edge cases and boundary conditions
- Mock external services (WAHA API)

### Test Structure
```php
public function test_user_can_create_event(): void
{
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/api/v1/events', [
            'name' => 'Soccer Match',
            'date' => '2024-12-25',
            'max_participants' => 10,
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => ['id', 'name', 'date', 'max_participants']
        ]);

    $this->assertDatabaseHas('events', [
        'name' => 'Soccer Match',
        'user_id' => $user->id,
    ]);
}
```