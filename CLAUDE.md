# Obsrv - Laravel Livewire Application

This is a multi-tenant Laravel application built with Livewire and Flux UI Pro components for business management and monitoring.

## Technology Stack

- **Laravel 12.x** - PHP framework
- **Livewire 3.x** with Volt - Full-stack framework for dynamic interfaces
- **Flux UI Pro 2.x** - Premium component library for modern UI
- **TailwindCSS 4.x** - Utility-first CSS framework
- **Vite** - Frontend build tool
- **SQLite** - Database (development)
- **Pest** - Testing framework
- **Laravel Pint** - Code style fixer

## Application Architecture

### Multi-tenant Structure
The application follows a hierarchical multi-tenant structure:
- **Users** can belong to multiple **Companies**
- **Companies** contain **Sites** (business locations)
- **Sites** can be organized into **Site Groups**
- Each level has its own access control and routing

### Key Components

#### Authentication & Authorization
- Laravel Sanctum for API authentication
- Custom middleware: `company.access`, `site.access`
- Route model binding with security checks

#### Navigation System
- **Company Context**: `/app/company/{id}/*` routes
- **Site Context**: `/app/site/{id}/*` routes
- **Global Settings**: `/settings/*` routes
- Breadcrumb navigation with hierarchical context
- SPA-style navigation using `wire:navigate`

#### UI Framework
- **Flux UI Pro** components throughout
- Consistent design patterns:
  - Flux navbar for sub-navigation
  - Flux modals for CRUD operations
  - Flux badges for status/grouping
  - Flux buttons with consistent variants

## Development Commands

### Setup & Installation
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

### Development Server
```bash
# Start all services (recommended)
composer dev

# Individual services
php artisan serve          # Laravel server
php artisan queue:listen    # Queue worker
php artisan pail           # Log viewer
npm run dev                # Vite dev server
```

### Testing & Code Quality
```bash
composer test              # Run Pest tests
php artisan pint          # Fix code style
```

### Database
```bash
php artisan migrate        # Run migrations
php artisan tinker        # Laravel REPL
```

## File Structure

### Core Application
- `/app/Livewire/` - Livewire components
  - `/Company/` - Company-scoped components
  - `/Site/` - Site-scoped components
- `/app/Models/` - Eloquent models
- `/routes/web.php` - Web routes

### Views & Templates
- `/resources/views/livewire/` - Livewire component templates
- `/resources/views/components/` - Blade components
- `/resources/views/layouts/` - Layout templates

### Frontend Assets
- `/resources/css/` - Stylesheets
- `/resources/js/` - JavaScript files

## Key Features

### Company Management
- **Dashboard**: Company overview and metrics
- **Sites Management**: Business location tracking
- **Site Groups**: Organize sites by category/region
- **Teams & Users**: User management within company
- **Tickets**: Support ticket system
- **Appointments**: Scheduling system
- **Maintenance**: Maintenance tracking
- **Monitoring**: System monitoring
- **Billing**: Subscription and billing management

### Site Management
- **Dashboard**: Site-specific overview
- **Support**: Ticket system for individual sites
- **Appointments**: Site-specific scheduling
- **Monitoring**: Site monitoring and alerts
- **Settings**: Site configuration

### Navigation Patterns

#### Breadcrumb System
Located in `/resources/views/components/breadcrumbs.blade.php`:
- Automatic context detection based on route names
- Hierarchical navigation (Home → Company → Sites → Individual Site)
- All breadcrumbs use `wire:navigate` for SPA navigation

#### Sub-navigation
Consistent pattern using Flux navbar components:
```php
<flux:navbar>
    <flux:navbar.item
        :href="route('company.sites', ['company' => $company->id])"
        :current="request()->routeIs('company.sites')"
        icon="building-office"
        wire:navigate
    >
        Manage Sites
    </flux:navbar.item>
</flux:navbar>
```

## Common Development Patterns

### Livewire Components
- Use `#[Layout('layouts.app')]` for layout
- Implement route model binding in `mount()` method
- Security checks for tenant access:
```php
public function mount(Company $company, Site $site)
{
    if ($site->company_id !== $company->id) {
        abort(403);
    }
    // ...
}
```

### Modal Patterns
- Use Flux modals with `wire:model.self` for state management
- Consistent modal structure with heading, form, and action buttons
- Error handling with `$errorMessage` property

### Route Organization
- Company routes: `Route::middleware(['company.access'])->group()`
- Site routes: `Route::middleware(['site.access'])->group()`
- Nested resource routing with parameter constraints

### State Management
- Livewire properties for component state
- `#[Url]` attribute for URL-bound parameters
- Proper cleanup in modal close methods

## Security Considerations

### Access Control
- Always verify tenant relationships in component mount methods
- Use middleware for route-level protection
- Implement proper authorization checks

### Data Protection
- Never expose sensitive data in Livewire properties
- Validate all user inputs
- Use Laravel's built-in CSRF protection

## Styling Guidelines

### Component Structure
- Use Flux UI Pro components consistently
- Follow established color schemes (primary, danger, subtle variants)
- Implement proper dark mode support

### Layout Patterns
- Consistent padding and margins using Tailwind classes
- Responsive design with mobile-first approach
- Proper spacing with flex and grid layouts

## Debugging & Development

### Useful Tools
- **Laravel Pail**: Real-time log monitoring (`php artisan pail`)
- **Tinker**: Interactive PHP shell (`php artisan tinker`)
- **Browser DevTools**: Livewire DevTools extension recommended

### Common Issues
- **Livewire State**: Check component properties and wire:model bindings
- **Routes**: Verify middleware and parameter constraints
- **Permissions**: Check tenant access in mount methods
- **UI Components**: Ensure Flux UI Pro license is active

## Deployment Notes

### Environment Configuration
- Set proper database configuration
- Configure queue driver for production
- Set up proper logging and monitoring

### Performance Considerations
- Use Laravel's caching mechanisms
- Optimize database queries with eager loading
- Consider CDN for static assets

## Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Livewire Documentation](https://livewire.laravel.com)
- [Flux UI Pro Documentation](https://fluxui.dev)
- [TailwindCSS Documentation](https://tailwindcss.com)

## Project-Specific Notes

### Recent Updates
- Workspace selector moved from header to company sidebar
- Navigation converted from tabs to Flux navbar for better UX
- Added individual site view functionality with breadcrumb integration
- All navigation components use `wire:navigate` for SPA-style transitions

### URL Structure
- Company sites: `/app/company/{id}/sites`
- Site groups: `/app/company/{id}/sites/groups`
- Individual site view: `/app/company/{id}/sites/{siteId}`

This documentation serves as a reference for understanding the codebase architecture, development workflows, and established patterns within the Obsrv application.