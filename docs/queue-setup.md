# Queue Setup for Background Email Processing

## Overview

Magic link emails and other notification emails are now processed in the background using Laravel's queue system. This improves user experience by eliminating waiting time for email sending.

## Configuration

### Queue Driver
The application is configured to use the `database` queue driver by default (see `config/queue.php`).

### Jobs Table
The required `jobs` table migration already exists and should be migrated:
```bash
php artisan migrate
```

## Development Setup

### Running the Queue Worker
To process queued emails in development, run:

```bash
php artisan queue:work
```

For processing a single job (useful for testing):
```bash
php artisan queue:work --once
```

### Monitoring Queue Jobs
View pending jobs:
```bash
php artisan queue:monitor
```

Clear failed jobs:
```bash
php artisan queue:flush
```

## Production Setup

### Supervisor Configuration
For production, use Supervisor to keep the queue worker running continuously.

Create `/etc/supervisor/conf.d/laravel-worker.conf`:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/worker.log
stopwaitsecs=3600
```

Start the supervisor process:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

### Queue Monitoring
Monitor queue performance:
```bash
php artisan queue:monitor default --max=100
```

Set up queue monitoring in your application monitoring tools to track:
- Failed jobs
- Queue length
- Processing time

## What's Now Queued

The following emails are now processed in the background:

1. **Magic Link Authentication** (`MagicLinkMail`)
   - Login magic links
   - User invitations (company & site)
   - Device authentication

2. **Password Reset** (`ForgotPasswordMail`)
   - Password removal confirmations

## Benefits

- **Faster Response Times**: Users don't wait for email sending
- **Better Error Handling**: Failed emails can be retried automatically
- **Scalability**: Multiple workers can process emails concurrently
- **Monitoring**: Track email sending performance and failures

## Troubleshooting

### Jobs Not Processing
1. Ensure queue worker is running: `php artisan queue:work`
2. Check failed jobs: `php artisan queue:failed`
3. Verify mail configuration in `.env`

### Failed Jobs
Retry failed jobs:
```bash
php artisan queue:retry all
```

Clear all failed jobs:
```bash
php artisan queue:flush
```

### Performance Issues
- Increase number of worker processes in Supervisor
- Consider using Redis for better queue performance
- Monitor memory usage of queue workers 
