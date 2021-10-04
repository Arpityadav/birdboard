<?php

namespace Tests\Feature;

use App\Project;
use Facades\Tests\Setup\ProjectsFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ManageProjectsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function guests_cannot_manage_a_project()
    {

        $project = factory('App\Project')->create();

        $this->get('/projects')->assertRedirect('/login');
        $this->get($project->path().'/edit')->assertRedirect('/login');
        $this->get('/projects/create')->assertRedirect('/login');
        $this->get($project->path())->assertRedirect('/login');
        $this->post('/projects', $project->toArray())->assertRedirect('/login');
    }

    /** @test */
    public function a_project_can_be_updated()
    {
        $project = ProjectsFactory::withTasks(1)->create();

        $this->actingAs($project->owner)
            ->patch($project->path(), $attributes = [
                'title' => 'Title Changed',
                'description' => 'Description Changed',
                'notes' => 'Changed'])
            ->assertRedirect($project->path());

        $this->get($project->path().'/edit')->assertOk();

        $this->assertDatabaseHas('projects', $attributes);
    }

    /** @test */
    public function a_project_notes_can_be_updated()
    {
        $project = ProjectsFactory::withTasks(1)->create();

        $this->actingAs($project->owner)
            ->patch($project->path(), $attributes = ['notes' => 'Changed']);

        $this->assertDatabaseHas('projects', $attributes);
    }

    /** @test */
    public function a_user_can_see_all_projects_that_they_have_been_invited_to_on_their_dashboard()
    {
        $project = tap(ProjectsFactory::create())->invite($this->signIn());

        $this->get('/projects')->assertSee($project->title);
    }

    /** @test */
    public function an_authenticated_user_can_create_a_project()
    {
        $this->signIn();

        $this->get('/projects/create')->assertStatus(200);

        $this->followingRedirects()
            ->post('/projects', $attributes = factory('App\Project')->raw())
            ->assertSee($attributes['title'])
            ->assertSee($attributes['description'])
            ->assertSee($attributes['notes']);
    }

    /** @test */
    public function tasks_can_be_included_in_the_creation_of_a_project()
    {
        $this->withoutExceptionHandling();
        $this->signIn();

        $attributes = factory('App\Project')->raw();

        $attributes['tasks'] = [
            ['body' => 'body 1'],
            ['body' => 'body 2']
        ];

        $this->post('/projects', $attributes);

        $this->assertCount(2, Project::first()->tasks);

    }

    /** @test */
    public function authorized_user_can_delete_a_project()
    {
        $project = ProjectsFactory::create();

        $this->actingAs($project->owner)
            ->delete($project->path())
            ->assertRedirect('/projects');

        $this->assertDatabaseMissing('projects', $project->only('id'));
    }

    /** @test */
    public function unauthorized_user_can_delete_a_project()
    {
        $project = ProjectsFactory::create();

        $this->delete($project->path())
            ->assertRedirect('/login');

        $user = $this->signIn();

        $this->delete($project->path())
            ->assertStatus(403);

        $project->invite($user);

        $this->delete($project->path())
            ->assertStatus(403);

    }

    /** @test */
    public function a_project_requires_a_title()
    {
        $this->signIn();

        $project = factory('App\Project')->raw(['title' => '']);
        $this->post('/projects', $project)->assertSessionHasErrors('title');
    }

    /** @test */
    public function a_project_requires_a_description()
    {
        $this->signIn();

        $project = factory('App\Project')->raw(['description' => '']);
        $this->post('/projects', $project)->assertSessionHasErrors('description');
    }


     /** @test */
    public function only_owner_can_view_a_project()
    {
        $this->signIn();

        $project = factory('App\Project')->create(['owner_id' => auth()->id()]);

        $this->get($project->path())
            ->assertSee($project->title)
            ->assertSee($project->description);
    }

    /** @test */
    public function non_owners_cannot_view_a_projects()
    {
        $this->signIn();

        $project = factory('App\Project')->create();

        $this->get($project->path())
            ->assertStatus(403);
    }
}
