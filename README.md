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

# Lesson 10

When you expect errors on post, use `assertSessionHasErrors` method
```php
function a_thread_requires_a_title()
{
    $this->withExceptionHandling()->signIn();

    $thread = make('App\Thread', ['title' => null]);

    $this->post('/threads', $thread->toArray())
        ->assertSessionHasErrors('title');
}
```

The validation `exists` checks if the specified value exists in some table colum
```php
$this->validate($request, [
    'title' => 'required',
    'body' => 'required',
    'channel_id' => 'required|exists:channels,id'
]);
```

# Lesson 11
Laravel provides some syntax sugar while searching. The following lines result in the same.
```php
$channelId = Channel::whereSlug($channelSlug)->first()->id;
$channelId = Channel::where('slug', $channelSlug)->first()->id;
```

To use other than id column to fetch entities in router use `getRouteKeyName` method
```php
class Channel extends Model
{
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
```

# Leson 13
We can provide some variables in templates this way:
```php
class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }

    public function register()
    {
        \View::composer('threads.create', function($view) {
            $view->with('channels', \App\Channel::all());
        });
    }
}
```
To provide it in multiple views use asterisk:
```php
 \View::composer('*', function($view) {
    $view->with('channels', \App\Channel::all());
});
```
Alternativelly we can share variable in boot method:
```php
class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        \View::share('channels', \App\Channel::all());
    }

    public function register()
    {
    }
}
```
You may consider creating your own view service provider with:
```
php artisan make:provider ViewServiceProvider
```

# Lesson 14
Providing variables in `boot` method may result in errors during testing

# Lesson 15
Consider extracting some queries into external classes:
```php
(new ThreadsQuery)->get()
```

# Lesson 16
There is a difference whether you access the collection via function name or function execution. This method will fetch all the elements and then counts them
```php
{{ $thread->replies->count() }}
```
This method will not fetch replies but will count them
```php
{{ $thread->replies()->count() }}
```
To fetch entity with some counted objects use:
```php
Thread::withCount('replies')->first()
```
You may also want to add some global variables added to every thread:
```php
class Thread extends Model
{
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('replyCount', function ($builer) {
            $builer->withCount('replies');
        });
    }
}
```

To add pagination return collection using `paginate` method
```php
return view('threads.show', [
    'thread' => $thread,
    'replies' => $thread->replies()->paginate(25)
]);
```

and then return result of `links()` method and interate over all the items
```php
@foreach($replies as $reply)
    @include('threads.reply')
@endforeach

{{ $replies->links() }}
```

# Lesson 17
Instead of fetching html response using `$response = $this->get('threads?popularity=1');`, you can fetch json response with `$this->getJson('threads?popularity=1')->json();`
To return json return the object
```php
if (request()->wantsJson()) {
    return $threads;
}
```

If you want to clear orders of the query, use:
```php
$this->bilder->getQuery()->orders = [];
```

# Lesson 18
When you have polymorfic relation:
```php
public function favorites()
{
    return $this->morphMany(Favorite::class, 'favorited');
}
```

You can create objects like this:
```php
$reply->favorites()->create(['user_id' => auth()->id()]);
```
The required fields will be filled in automatically.


