@extends ('layouts.app')

@section('content')
    <header class="flex items-center mb-3 py-4">
        <div class="flex justify-between items-end w-full">
            <p class="text-default text-sm font-normal">
                <a href="/projects" class="text-default text-sm font-normal no-underline hover:underline">My Projects</a> / {{ $project->title }}
            </p>

            <div class="flex items-center">
                @foreach($project->members as $member)
                    <img src="{{ get_avatar($member->email) }}" alt="{{ $member->name }}'s avatar" class="rounded-full w-8 mr-2">
                @endforeach

                <img src="{{ get_avatar($project->owner->email) }}" alt="{{ $project->owner->name }}'s avatar" class="rounded-full w-8 mr-2">

                <a href="{{ $project->path().'/edit' }}" class="button ml-4">Edit Project</a>
            </div>

        </div>
    </header>

    <main>
        <div class="lg:flex -mx-3">
            <div class="lg:w-3/4 px-3 mb-6">
                <div class="mb-8">
                    <h2 class="text-lg text-default font-normal mb-3">Tasks</h2>

                    @foreach($project->tasks as $task)

                        <div class="card mb-3">

                            <form method="POST" action="{{ $project->path() . '/tasks/' .  $task->id}}">
                                @method('PATCH')
                                @csrf

                                <div class="flex">
                                    <input type="text" value="{{ $task->body }}" class="text-default bg-card w-full {{ $task->completed ? 'line-through text-muted' : '' }}" name="body">
                                    <input type="checkbox" name="completed" onchange="this.form.submit()" {{ $task->completed ? 'checked' : '' }}>
                                </div>
                            </form>

                        </div>

                    @endforeach

                    <form action="{{ $project->path() . '/tasks' }}" method="post">
                        @csrf
                        <input type="text" class="card mb-3 w-full bg-card text-default" name="body" placeholder="Enter a task...">
                    </form>
                </div>

                <div>
                    <h2 class="text-lg text-default font-normal mb-3">General Notes</h2>

                    {{-- general notes --}}
                    <form method="POST" action="{{ $project->path() }}">
                        @method('PATCH')
                        @csrf

                        <textarea class="card text-default w-full mb-4"
                            name="notes"
                            style="min-height: 200px"
                            placeholder="Anything special you want to make note of?"
                        >{{ $project->notes }}</textarea>

                        <button type="submit" class="button">Save</button>
                    </form>

                    @include('errors')

                </div>
            </div>

            <div class="lg:w-1/4 px-3 lg:py-8">
                @include ("projects.card")
                @include ("projects.activity.activity")

                @can('manage', $project)
                    @include ("projects.invite")
                @endcan
            </div>
        </div>
    </main>


@endsection
