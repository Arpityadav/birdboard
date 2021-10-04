<div class="card flex flex-col mt-3">

    <h3 class="font-normal text-xl py-4 -ml-5 mb-3 border-l-4 border-accent-light pl-4">
        <a href="{{ $project->path() }}" class="text-default no-underline">Invite a User</a>
    </h3>


    <form action="{{ $project->path() . '/invitations'}}" method="POST">
        @csrf

        <div class="mb-3">
            <input type="email" name="email" class="border border-muted-light rounded w-full py-2 px-3" placeholder="Email Address">
        </div>

        <button type="submit" class="button">Invite</button>
    </form>

    @include('errors', [
        $bag = 'invitations',
    ])
</div>
