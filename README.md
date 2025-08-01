# Laravel Before and After Model Events

[![Latest Version on Packagist](https://img.shields.io/packagist/v/plank/before-and-after-model-events.svg?style=flat-square)](https://packagist.org/packages/plank/before-and-after-model-events)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/plank/before-and-after-model-events/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/plank/before-and-after-model-events/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/plank/before-and-after-model-events/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/plank/before-and-after-model-events/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/plank/before-and-after-model-events.svg?style=flat-square)](https://packagist.org/packages/plank/before-and-after-model-events)

This package adds **before** and **after** events for each existing Laravel Eloquent model event, giving you finer control over your model's lifecycle. Every standard Laravel model event (`creating`, `created`, `updating`, `updated`, etc.) gets corresponding `before` and `after` events that fire at the appropriate times.

For example, when creating a model, the events fire in this order:
1. `beforeSaving` → `saving` → `afterSaving`
2. `beforeCreating` → `creating` → `afterCreating` 
3. `beforeCreated` → `created` → `afterCreated`
4. `beforeSaved` → `saved` → `afterSaved`

## Installation

You can install the package via composer:

```bash
composer require plank/before-and-after-model-events
```

## Usage

Simply add the `AddBeforeAndAfterEvents` trait to any Eloquent model:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Plank\BeforeAndAfterModelEvents\Concerns\AddBeforeAndAfterEvents;

class User extends Model
{
    use AddBeforeAndAfterEvents;
    
    protected $fillable = ['name', 'email'];
}
```

### Basic Event Listeners

You can now register listeners for any before/after event:

```php
use App\Models\User;

// Listen to before events
User::beforeCreating(function ($user) {
    // Runs before the 'creating' event
    $user->slug = Str::slug($user->name);
});

User::afterCreated(function ($user) {
    // Runs after the 'created' event
    Mail::to($user)->send(new WelcomeEmail($user));
});

// Works with all model events
User::beforeUpdating(function ($user) {
    $user->updated_by = auth()->id();
});

User::afterDeleted(function ($user) {
    Log::info("User {$user->name} was deleted");
});
```

### Available Events

The trait adds before/after events for all standard Laravel model events:

| Standard Event | Before Event | After Event |
|---------------|--------------|-------------|
| `creating` | `beforeCreating` | `afterCreating` |
| `created` | `beforeCreated` | `afterCreated` |
| `updating` | `beforeUpdating` | `afterUpdating` |
| `updated` | `beforeUpdated` | `afterUpdated` |
| `saving` | `beforeSaving` | `afterSaving` |
| `saved` | `beforeSaved` | `afterSaved` |
| `deleting` | `beforeDeleting` | `afterDeleting` |
| `deleted` | `beforeDeleted` | `afterDeleted` |
| `restoring` | `beforeRestoring` | `afterRestoring` |
| `restored` | `beforeRestored` | `afterRestored` |

### Event Order Example

When you create a model, events fire in this specific order:

```php
User::beforeSaving(fn($user) => logger('1. beforeSaving'));
User::saving(fn($user) => logger('2. saving'));
User::afterSaving(fn($user) => logger('3. afterSaving'));
User::beforeCreating(fn($user) => logger('4. beforeCreating'));
User::creating(fn($user) => logger('5. creating'));
User::afterCreating(fn($user) => logger('6. afterCreating'));
User::beforeCreated(fn($user) => logger('7. beforeCreated'));
User::created(fn($user) => logger('8. created'));
User::afterCreated(fn($user) => logger('9. afterCreated'));
User::beforeSaved(fn($user) => logger('10. beforeSaved'));
User::saved(fn($user) => logger('11. saved'));
User::afterSaved(fn($user) => logger('12. afterSaved'));

User::create(['name' => 'John Doe']);
// Logs: 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12
```

### Preventing Operations

You can prevent model operations by returning `false` from any **before** event:

```php
User::beforeCreating(function ($user) {
    if ($user->email === 'blocked@example.com') {
        return false; // Prevents the creation
    }
});

User::beforeDeleting(function ($user) {
    if ($user->role === 'admin') {
        return false; // Prevents the deletion
    }
});

$result = User::create(['email' => 'blocked@example.com']);
// $result will be false, no user created

$admin = User::where('role', 'admin')->first();
$result = $admin->delete();
// $result will be false, admin not deleted
```

### Soft Deletes Support

The trait works seamlessly with soft deletes:

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use AddBeforeAndAfterEvents, SoftDeletes;
}

// Soft delete events
Post::beforeDeleting(fn($post) => logger('Before soft delete'));
Post::afterDeleted(fn($post) => logger('After soft delete'));

// Restore events  
Post::beforeRestoring(fn($post) => logger('Before restore'));
Post::afterRestored(fn($post) => logger('After restore'));

// Force delete still triggers deleting/deleted events
Post::beforeDeleting(fn($post) => logger('Before force delete'));
Post::afterDeleted(fn($post) => logger('After force delete'));
```

## Important Nuances and Considerations

### 1. Event Order with Existing Listeners

If your model already has event listeners (defined in `boot()` method or elsewhere), the trait's events will integrate seamlessly:

```php
class User extends Model
{
    use AddBeforeAndAfterEvents;
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($user) {
            logger('Model creating event');
        });
    }
}

User::beforeCreating(fn($user) => logger('Before creating'));
User::afterCreating(fn($user) => logger('After creating'));

// Order: beforeCreating → creating → afterCreating
```

### 2. Performance Considerations

Each before/after event adds a small overhead. For high-throughput applications:

```php
// Consider using standard events for simple cases
User::creating(function ($user) {
    $user->slug = Str::slug($user->name);
});

// Use before/after events when you need precise timing
User::beforeCreating(function ($user) {
    // Runs before any creating listeners
    $user->prepare();
});

User::afterCreated(function ($user) {
    // Runs after all created listeners
    $user->finalize();
});
```

### 3. Event Prevention Behavior

When a before event returns `false`:
- The operation is prevented
- No subsequent events fire for that operation
- The model method returns `false`

```php
User::beforeCreating(fn() => false);
User::creating(fn() => logger('This will not run'));
User::afterCreating(fn() => logger('Neither will this'));

$result = User::create(['name' => 'John']);
// $result is false, no events after beforeCreating fire
```

### 4. Restore Events Include Save Events

Restoring a soft-deleted model internally calls `save()`, so you'll see both restore and save events:

```php
$user = User::create(['name' => 'John']);
$user->delete();

// These will all fire during restore:
User::beforeRestoring(fn() => logger('Before restoring'));
User::beforeSaving(fn() => logger('Before saving'));
User::beforeUpdating(fn() => logger('Before updating')); 
// ... and their corresponding after events

$user->restore();
```

### 5. Observer Compatibility

The trait works alongside model observers:

```php
class UserObserver
{
    public function creating($user) {
        logger('Observer creating');
    }
}

User::observe(UserObserver::class);

User::beforeCreating(fn() => logger('Trait before'));
User::afterCreating(fn() => logger('Trait after'));

// Order: beforeCreating → Observer creating → afterCreating
```

### 6. Testing Considerations

When testing, you can verify event firing:

```php
/** @test */
public function it_fires_before_and_after_events()
{
    $events = [];
    
    User::beforeCreating(fn() => $events[] = 'before');
    User::afterCreated(fn() => $events[] = 'after');
    
    User::create(['name' => 'Test']);
    
    $this->assertEquals(['before', 'after'], $events);
}
```

## Common Use Cases

### 1. Audit Logging
```php
User::beforeUpdating(function ($user) {
    $user->previous_values = $user->getOriginal();
});

User::afterUpdated(function ($user) {
    AuditLog::create([
        'model' => get_class($user),
        'model_id' => $user->id,
        'changes' => $user->getChanges(),
        'previous' => $user->previous_values,
    ]);
});
```

### 2. Cache Invalidation
```php
Post::afterSaved(function ($post) {
    Cache::forget("post.{$post->id}");
    Cache::forget('posts.latest');
});
```

### 3. Validation and Business Logic
```php
Order::beforeCreating(function ($order) {
    if (!$order->isValid()) {
        throw new InvalidOrderException();
    }
});

Order::afterCreated(function ($order) {
    $order->sendConfirmationEmail();
    $order->updateInventory();
});
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Kurt Friars](https://github.com/kfriars)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.