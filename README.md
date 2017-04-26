# Lesson 1
To create few fake threads use
```$threads = factory('App\Thread', 50)->create()```
Remember to create model factories before.
Later you can attach responses to threads using
```$threads->each(
   function ($thread) { 
   	factory('App\Reply', 10)->create(['thread_id' => $thread->id]);
});
```

# Lesson 2
To use sqlite in tests, set two properties in phpunit.xml
```
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```
Then use `DatabaseMigrations` trait in your test. It'll migrate all the chnges and rollback everything after completion.

# Lesson 3
To create new unit test use: ```php artisan make:test ReplyTest --unit```

When you change the method returning entity name, remember to specify foreign key column name:
```class Reply extends Model
   {
       public function owner()
       {
           return $this->belongsTo(User::class, 'user_id');
       }
   }
```

