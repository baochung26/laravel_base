# Queue and Scheduler Runtime

This project ships with dedicated Docker services for queue workers and scheduler.

## Docker services

- `queue`: runs `php artisan queue:work --sleep=1 --tries=3 --timeout=300`
- `scheduler`: runs `php artisan schedule:work`

## Start

```bash
docker compose up -d
```

## Observe logs

```bash
docker compose logs -f queue
docker compose logs -f scheduler
```

## Restart workers after deployment changes

```bash
docker compose restart queue scheduler
```

## Environment reminders

- Use `QUEUE_CONNECTION=redis` for async jobs.
- Ensure Redis service is healthy before queue processing.
- Configure failed-jobs handling and monitoring for production.
