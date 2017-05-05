@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="level">
                            <span class="flex">
                            <a href="{{ route('profile', $thread->creator) }}">{{ $thread->creator->name }}</a> posted:
                                {{ $thread->title }}
                            </span>

                            <form action="{{ $thread->path() }}" method="POST">
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}
                                <button class="btn btn-link">Delete Thread</button>
                            </form>
                        </div>
                    </div>

                    <div class="panel-body">
                        {{ $thread->body }}
                    </div>
                </div>

                @foreach($replies as $reply)
                    @include('threads.reply')
                @endforeach

                {{ $replies->links() }}

                @if(auth()->check())
                    <form action="{{ $thread->path() . '/replies' }}" method="POST">
                        {{ csrf_field() }}
                        <textarea name="body" id="body" cols="30" rows="5" class="form-control"
                                  placeholder="Have something to say?"></textarea>
                        <button class="btn btn-default">Post</button>
                    </form>
                @else
                    <p class="text-center">
                        <a href="{{ route('login') }}">Please sign in to participate in this discussion.</a>
                    </p>
                @endif
            </div>
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-body">
                        This thead was publisehd {{ $thread->created_at->diffForHumans() }} by
                        <a href="#">{{ $thread->creator->name }}</a>, and currenty
                        has {{ $thread->replies_count }} {{ str_plural('comment', $thread->replies_count) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
