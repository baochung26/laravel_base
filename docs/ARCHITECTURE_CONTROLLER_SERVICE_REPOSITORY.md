# Controller-Service-Repository Architecture

This project follows a layered architecture to keep responsibilities clear and testable.

## Layer responsibilities

- Controller layer:
  - Accept request input
  - Delegate business flow to services
  - Return HTTP responses/resources
- Service layer:
  - Implement business rules and orchestration
  - Coordinate repositories, storage, mail, queue
  - Throw domain/application exceptions
- Repository layer:
  - Encapsulate data access and query details
  - Return models/collections/paginators

## Typical flow

1. A Form Request validates input.
2. Controller calls a Service method.
3. Service uses one or more Repositories.
4. Service returns data to Controller.
5. Controller formats output (resource/API envelope).

## Why this structure

- Reduces fat controllers
- Keeps data-access concerns centralized
- Improves unit-testability of business logic
- Makes extension/refactoring easier in a public base template

## Main locations

- Controllers: `app/Http/Controllers`
- Services: `app/Services`
- Repositories: `app/Repositories`
- Form Requests: `app/Http/Requests`
