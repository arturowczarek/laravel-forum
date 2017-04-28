# Lesson 1
To create few fake threads use
```$threads = factory('App\Thread', 50)->create()```
Remember to create model factories before.
Later you can attach responses to threads using
```php
$threads->each(
   function ($thread) { 
   	factory('App\Reply', 10)->create(['thread_id' => $thread->id]);
});
```

# Lesson 2
To use sqlite in tests, set two properties in phpunit.xml
```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```
Then use `DatabaseMigrations` trait in your test. It'll migrate all the chnges and rollback everything after completion.

# Lesson 3
To create new unit test use: ```php artisan make:test ReplyTest --unit```

When you change the method returning entity name, remember to specify foreign key column name:
```php
class Reply extends Model
   {
       public function owner()
       {
           return $this->belongsTo(User::class, 'user_id');
       }
   }
```

# Lesson 4
To have exceptions on console during testing you may be interested in modifying `App\Handler->render` method to show exception instead of rendering page with error.
```php
public function render($request, Exception $exception)
    {
        if (app()->environment() === 'testing') throw $exception;
        return parent::render($request, $exception);
    }
```

To assign exceptions during test use:
```php
/** @test */
function unauthenticated_users_may_not_add_replies()
{
    $this->expectException('Illuminate\Auth\AuthenticationException');
    $this->post('threads/1/replies', []);
}
```

Factory method `make` makes an object but unlike the `create` method don't persist it.

# Lesson 5
To obtain url of named route use `route(routeName)` helper function:
```php
<p><a href="{{ route('login') }}">Please sign in to participate in this discussion.</a></p>```
```

# Lesson 6
The method `make` of factory creates onject. The metho `raw` returns only array with the values. We can use id when posting requests during testing:
```php
$thread = factory('App\Thread')->raw();
        $this->post('/threads', $thread);
```

To apply middleware only to specific methods use
```php
public function __construct()
    {
        $this->middleware('auth')->only('store');
    }
```

# Lesson 7
To add helper method to all tests edit `composer.json` and add the files you want to be loaded
```php
"autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        },
        "files": ["tests/utilities/functions.php"]
    },
```
Specify all the helper methods there and run `composer dump-autoload` to refresh loading files

# Lesson 8
Instead of applying middleware using
```php
$this->middleware('auth')->only(['create', 'store']);
```
You can give exceptional usage with
```php
$this->middleware('auth')->except(['index', 'show']);
```

To selectively capture exceptions during testing use this gist: `https://gist.github.com/adamwathan/125847c7e3f16b88fa33a9f8b42333da`

# Lesson 9
Foreign keys pointing to increment id's can be done with ```$table->unsignedInteger('user_id');```

You can use double quotes to build strings: ```"/threads/{$this->channel->slug}/{$this->id}"```
