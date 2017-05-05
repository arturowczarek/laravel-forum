<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $guarded = [];

    public static function feed($user)
    {
        return $user
            ->activity()
            ->latest()
            ->with('subject')
            ->take(50)
            ->get()
            ->groupBy(function ($activity) {
                return $activity->created_at->format('Y-m-d');
            });
    }

    public function subject()
    {
        return $this->morphTo();
    }
}
