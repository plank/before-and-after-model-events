<p align="center"><a href="https://plank.co"><img src="art/before-and-after-events.png" width="100%"></a></p>

<p align="center">
<a href="https://packagist.org/packages/plank/before-and-after-model-events"><img src="https://img.shields.io/packagist/php-v/plank/before-and-after-model-events?color=%23fae370&label=php&logo=php&logoColor=%23fff" alt="PHP Version Support"></a>
<a href="https://laravel.com/docs/11.x/releases#support-policy"><img src="https://img.shields.io/badge/laravel-10.x,%2011.x-%2343d399?color=%23f1ede9&logo=laravel&logoColor=%23ffffff" alt="PHP Version Support"></a>
<a href="https://github.com/plank/before-and-after-model-events/actions?query=workflow%3Arun-tests"><img src="https://img.shields.io/github/actions/workflow/status/plank/before-and-after-model-events/run-tests.yml?branch=main&&color=%23bfc9bd&label=run-tests&logo=github&logoColor=%23fff" alt="GitHub Workflow Status"></a>
</p>

# Before and After Model Events

This package adds **before** and **after** events for every Laravel Eloquent model event, giving you complete control over your model's lifecycle. It works with all standard Laravel model events (`creating`, `created`, `updating`, `updated`, `deleting`, `deleted`, etc.) and any custom events you define.

## Features

- 🚀 **Zero Configuration** - Just add the trait and start using before/after events
- 🎯 **Works with ALL Events** - Standard Laravel events AND custom events  
- 🔒 **Event Prevention** - Before events can prevent the main event from firing
- 🏗️ **Clean API** - Static methods for registering event listeners with full IDE support
- 🧪 **Fully Tested** - Comprehensive test suite with 22 tests and 68 assertions
- ⚡ **Performance Focused** - Minimal overhead with dynamic event registration
- 🔧 **Laravel Integration** - Works seamlessly with existing Laravel event systems

## Event Flow

When working with model events, this package ensures the following execution order:

**For standard Laravel events** (like saving a model):
1. `beforeCreating` → `creating` → `afterCreating` 
2. `beforeSaving` → `saving` → `afterSaving`
3. `beforeCreated` → `created` → `afterCreated`
4. `beforeSaved` → `saved` → `afterSaved`

**For custom events** (like publishing a post):
1. `beforePublishing` → `publishing` → `afterPublishing`

## Installation

You can install the package via composer:

```bash
composer require plank/before-and-after-model-events
```

## Usage

Simply add the `BeforeAndAfterEvents` trait to any Eloquent model:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Plank\BeforeAndAfterModelEvents\Concerns\BeforeAndAfterEvents;

class User extends Model
{
    use BeforeAndAfterEvents;
    
    protected $fillable = ['name', 'email'];
}
```

## Basic Usage

### 1. Add the Trait

Add the `BeforeAndAfterEvents` trait to any Eloquent model:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Plank\BeforeAndAfterModelEvents\Concerns\BeforeAndAfterEvents;

class Post extends Model
{
    use BeforeAndAfterEvents;
    
    protected $fillable = ['title', 'content', 'status'];
}
```

### 2. Register Event Listeners

Use the static methods to register before and after event listeners:

```php
use App\Models\Post;

// Standard Laravel events
Post::beforeEvent('creating', function ($post) {
    $post->slug = Str::slug($post->title);
    $post->status = 'draft';
});

Post::afterEvent('created', function ($post) {
    Cache::tags(['posts'])->flush();
    Log::info("New post created: {$post->title}");
});

Post::beforeEvent('updating', function ($post) {
    if ($post->isDirty('title')) {
        $post->slug = Str::slug($post->title);
    }
});

Post::afterEvent('deleted', function ($post) {
    Storage::delete($post->image_path);
});
```

## Advanced Features

### Event Prevention

Before events can prevent the main event (and subsequent after events) from firing by returning `false`:

```php
Post::beforeEvent('deleting', function ($post) {
    if ($post->is_protected) {
        // Prevent deletion of protected posts
        return false;
    }
});

// This will fail silently if the post is protected
$post->delete(); // Returns false, post not deleted
```

### Custom Events

The package works seamlessly with any custom events you fire on your models:

```php
class Post extends Model
{
    use BeforeAndAfterEvents;
    
    public function publish()
    {
        // Fire custom event with before/after support
        if ($this->fireModelEvent('publishing') === false) {
            return false;
        }
        
        $this->status = 'published';
        $this->published_at = now();
        $this->save();
        
        return true;
    }
}

// Register listeners for the custom event
Post::beforeEvent('publishing', function ($post) {
    if (!$post->isReadyForPublishing()) {
        return false; // Prevent publishing
    }
    
    $post->seo_title = $post->seo_title ?: $post->title;
});

Post::afterEvent('publishing', function ($post) {
    Mail::to($post->author)->send(new PostPublishedNotification($post));
    Cache::tags(['published-posts'])->flush();
});
```

### Multiple Listeners

You can register multiple listeners for the same event:

```php
Post::beforeEvent('creating', function ($post) {
    $post->author_id = auth()->id();
});

Post::beforeEvent('creating', function ($post) {
    $post->reading_time = $this->calculateReadingTime($post->content);
});

Post::beforeEvent('creating', function ($post) {
    if (!$post->excerpt) {
        $post->excerpt = Str::limit(strip_tags($post->content), 150);
    }
});
```

### Soft Deletes Support

The package works perfectly with Laravel's soft deletes:

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use BeforeAndAfterEvents, SoftDeletes;
}

// Handle soft delete events
Post::beforeEvent('deleting', function ($post) {
    $post->deleted_by = auth()->id();
});

// Handle restoration events
Post::beforeEvent('restoring', function ($post) {
    Log::info("Restoring post: {$post->title}");
});

Post::afterEvent('restored', function ($post) {
    Cache::forget("deleted_posts");
});
```

### Integration with Existing Event Systems

The package works alongside existing Laravel event dispatchers and observers:

```php
class Post extends Model
{
    use BeforeAndAfterEvents;
    
    // Existing Laravel event dispatchers still work
    protected $dispatchesEvents = [
        'saved' => PostSavedEvent::class,
    ];
}

// Both systems work together
Post::beforeEvent('saving', function ($post) {
    // Runs before the 'saving' event and PostSavedEvent
});
```

## Event Reference

### Standard Laravel Events

All standard Laravel model events are supported:

- `retrieved` - Before/after model is retrieved from database
- `creating` - Before/after model is being created  
- `created` - Before/after model has been created
- `updating` - Before/after model is being updated
- `updated` - Before/after model has been updated
- `saving` - Before/after model is being saved (create or update)
- `saved` - Before/after model has been saved
- `deleting` - Before/after model is being deleted
- `deleted` - Before/after model has been deleted
- `restoring` - Before/after soft-deleted model is being restored
- `restored` - Before/after soft-deleted model has been restored
- `replicating` - Before/after model is being replicated
- `forceDeleting` - Before/after model is being force deleted
- `forceDeleted` - Before/after model has been force deleted

### Custom Events

Any event name can be used - the package will automatically register the before/after variants:

```php
// These all work automatically
Post::beforeEvent('publishing', $callback);
Post::beforeEvent('archiving', $callback);  
Post::beforeEvent('featuring', $callback);
Post::beforeEvent('customBusinessLogic', $callback);
```

## How It Works

The package uses a simple but powerful approach:

1. **Dynamic Event Registration**: When you call `beforeEvent()` or `afterEvent()`, the package registers the base event (e.g., `publishing`) in a static registry and adds the before/after variants (`beforePublishing`, `afterPublishing`) to the model's observable events.

2. **Event Interception**: The trait overrides the `fireModelEvent()` method to intercept all model events and fire the before/after events at the appropriate times.

3. **Minimal Overhead**: Events are only registered when actually used, and the trait adds minimal performance overhead to your models.

## Requirements

- PHP 8.3 or higher
- Laravel 10.0, 11.0, or 12.0

## Testing

The package includes a comprehensive test suite with 22 tests covering:

- Standard Laravel events (creating, updating, deleting, etc.)
- Custom events and dynamic registration  
- Event prevention and flow control
- Soft deletes and restoration
- Multiple listeners per event
- Edge cases and error handling
- Integration with existing Laravel event systems

Run the tests:

```bash
composer test
```

Run tests with coverage:

```bash  
composer test-coverage
```

Run static analysis:

```bash
composer analyse
```

&nbsp;

## Credits

- [Kurt Friars](https://github.com/kfriars)
- [All Contributors](../../contributors)

&nbsp;

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

&nbsp;

## Security Vulnerabilities

If you discover a security vulnerability within siren, please send an e-mail to [security@plank.co](mailto:security@plank.co). All security vulnerabilities will be promptly addressed.

&nbsp;

## Check Us Out!

<a href="https://plank.co/open-source/learn-more-image">
    <img src="https://plank.co/open-source/banner">
</a>

&nbsp;

Plank focuses on impactful solutions that deliver engaging experiences to our clients and their users. We're committed to innovation, inclusivity, and sustainability in the digital space. [Learn more](https://plank.co/open-source/learn-more-link) about our mission to improve the web.
