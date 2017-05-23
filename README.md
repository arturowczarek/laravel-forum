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

# Lesson 20
https://github.com/barryvdh/laravel-debugbar

To register something only in local environment use `isLocal` method
```php
public function register()
{
    if($this->app->isLocal()) {
        $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class,);
    }
}
```

When you fetch some data multiple times you may consider caching it. In this example we fetch channels in multiple views and don't want to fetch it multiple times.
```php
\View::composer('*', function($view) {
    $channels = \Cache::rememberForever('channels', function () {
        return Channel::all();
    });
    $view->with('channels', $channels);
});
```

In relationship we can fetch count or other data in relations
```php
public function replies()
{
    return $this->hasMany(Reply::class)
        ->withCount('favorites')
        ->with('owner');
}
```

# Lesson 21
If we want to fetch field with every entity query, we should override `$with` field in model
```php
protected $with = ['owner'];
```

If you want to fetch entity without globals use:
```php
\App\Thread::withoutGlobalScopes()->first();
```
It does not work with `$with` field of the model

# Lesson 22
When you have named route:
```php
Route::get('/profiles/{user}', 'ProfilesController@show')->name('profile');
```

You can reference it with
```php
{{ route('profile', $reply->owner) }}
```

# Lesson 23
To submit json request use
```php
$this->json('DELETE', $thread->path());
```

To check if something is not present in the database:
```php
$this->assertDatabaseMissing('threads', ['id' => $thread->id]);
```

To return error http code with no content use:
```php
return response([], 204);
```

When you want to delete some subentities when deliting entity, you can attach callback in deleting function:
```php
protected static function boot()
{
    parent::boot();

    static::deleting(function ($thread) {
        $thread->replies()->delete();
    });
}
```

# Lesson 24
When there are no objects for foreach we can resort to `@forelse`:
```php
@forelse($threads as $thread)
    <p>{{ $thread->body }}</p>
@empty
    <p>There are  no relevant results</p>
@endforelse
```

To create new policy use ```php artisan make:policy ThreadPolicy```
Remember to register it in `AuthServiceProvider`:
```php
protected $policies = [
    'App\Thread' => 'App\Policies\ThreadPolicy',
];
```
Then you can authorize operations with:
```php
$this->authorize('update', $thread);
```
or
```php
@can('update', $thread)
   ...
@endcan
```

To override policy you can write before method:
```php
class ThreadPolicy
{
    use HandlesAuthorization;

    public function before($user)
    {
        if ($user->name == 'Artur') return true;;
    }
    ...
}
```
You can also add it to all the policies in `AuthServiceProvider`
```php
public function boot()
{
    $this->registerPolicies();

    Gate::before(function ($user) {
        if ($user->name === 'Artur') return true;;
    });
}
```

# Lesson 25
If you want some trait function to be run during booting use method named bootTraitName, eg:
```php
trait RecordsActivity
{
    protected static function bootRecordsActivity()
    {
        // this will be invoked
    }
}
```

# Lesson 26
You can use function to group entities:
```php
$activities = $user
            ->activity()
            ->latest()
            ->with('subject')
            ->get()
            ->groupBy(function ($activity) {
                return $activity->created_at->format('Y-m-d');
            });
```

To override variable name within partial pass array with new variables as a second parameter
```php
@foreach($activities as $date => $activity)
    @foreach ($activity as $record)
        @include("profiles.activities.{$record->type}", ['activity' => $record])
    @endforeach
@endforeach
```

# Lesson 28
Instead of
```php
$thread->replies->each(function ($reply) {
    $reply->delete();
});
```
One can write:
```php
$thread->replies->each->delete();

```

# Lesson 32
To prevent blinking the element until loading use `v-cloak` directive
```html
<reply :attributes="{{  $reply }}" inline-template v-cloak>
</reply>
```
and then add css rule to hide elements with v-cloak attribute
```css
[v-cloak] { display: none; }
```
Once the attribute is loaded, vue will remove v-cloak attribute

# Lesson 33
Axios can send post/delete/patch request without csrf errors, because the csrf tokens are added in bootstrap.js file

# Lesson 34
When we have custom attributes:
```php
trait Favoritable
{
    public function getFavoritesCountAttribute()
    {
        return $this->favorites->count();
    }
}
```
We can add them when casting to array or json with:
```php
class Reply extends Model
{
    use Favoritable;

    protected $appends = ['favoritesCount'];
```

To refresh entity use `fresh()` method:
```php
$reply->fresh()->favorites
```

# Lesson 35
If you want to fire deletion callbacks don't write
```php
$this->favorites()->where($attributes)->delete();
```
It performs SQL query
You have to fetch all the entities and on each perform delete function:
```php
$this->favorites()->where($attributes)->get()->each(function ($favorite) {
    $favorite->delete();
});
```
You can alternatively take advantage of higher order collection and write:
```php
$this->favorites()->where($attributes)->get()->each->delete();
```

# Lesson 36
To have access to some common properties, add them as window properties in layout file:
```html
window.App = {!! json_encode([
    'csrfToken' => csrf_token(),
    'user' => Auth::user(),
    'signedIn' => Auth::check()
]) !!};
```

You han utilize some trick to perform authorization. Add authorize method which will apply some checking handler on user object:
```javascript
window.Vue.prototype.authorize = function (handler) {
    return handler(window.App.user);
};
```
Then you can write for example:
```javascript 1.8
canUpdate() {
    return this.authorize(user => this.data.user_id == user.id);
}
```

# Lesson 38
Add rel attributes to pagination elements to help google with pagination:
```html
<ul class="pagination">
    <li>
        <a href="#" aria-label="Previous" rel="prev">
            <span aria-hidden="true">&laquo; Previous</span>
        </a>
    </li>
    <li>
        <a href="#" aria-label="Next" rel="next">
            <span aria-hidden="true">&raquo; Next</span>
        </a>
    </li>
</ul>
```

To prevent link from going somewhere on click use:
```php
<a href="#" aria-label="Next" rel="next" @click.prevent="page++">
    <span aria-hidden="true">Next &raquo;</span>
</a>
```

To modify url use:
```javascript
history.pushState(null, null, '?page=' + this.page);
```


