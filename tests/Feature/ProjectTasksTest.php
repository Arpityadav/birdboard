<?php

namespace Tests\Feature;

use Tests\TestCase;
use Facades\Tests\Setup\ProjectsFactory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectTasksTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_project_can_have_tasks()
    {
        $project = ProjectsFactory::withTasks(1)->create();

        $this->actingAs($project->owner)
            ->post($project->path().'/tasks', ['body' => 'Test Case']);

        $this->get($project->path())
            ->assertSee('Test Case');
    }

    /** @test */
    public function a_task_can_be_updated()
    {
        $project = ProjectsFactory::withTasks(1)->create();

        $this->actingAs($project->owner)
            ->patch($project->tasks->first()->path(), $attributes = ['body' => 'changed']);

        $this->assertDatabaseHas('tasks', $attributes);
    }

    /** @test */
    public function a_task_can_be_completed()
    {
        $project = ProjectsFactory::withTasks(1)->create();

        $this->actingAs($project->owner)
            ->patch($project->tasks->first()->path(), [
                'body' => 'changed',
                'completed' => true
            ]);

        $this->assertDatabaseHas('tasks', [
            'body' => 'changed',
            'completed' => true
        ]);
    }


    /** @test */
    public function a_task_can_be_marked_as_incompleted()
    {
        $project = ProjectsFactory::withTasks(1)->create();

        $this->actingAs($project->owner)
            ->patch($project->tasks->first()->path(), [
                'body' => 'changed',
                'completed' => true
            ]);

        $this->actingAs($project->owner)
            ->patch($project->tasks->first()->path(), [
                'body' => 'changed',
                'completed' => false
            ]);

        $this->assertDatabaseHas('tasks', [
            'body' => 'changed',
            'completed' => false
        ]);
    }

    /** @test */
    public function only_the_owner_of_the_project_can_update_tasks()
    {
        $project = ProjectsFactory::withTasks(1)->create();

        $this->patch($project->tasks->first()->path(), ['body' => 'changed'])
            ->assertStatus(403);

        $this->assertDatabaseMissing('tasks', ['body' => 'changed']);
    }

    /** @test */
    public function only_the_owner_of_the_project_can_add_tasks()
    {
        $this->signIn();

        $project = factory('App\Project')->create();

        $this->post($project->path().'/tasks', ['body' => 'Test Case'])
            ->assertStatus(403);

        $this->assertDatabaseMissing('tasks', ['body' => 'Test Case']);
    }


    /** @test */
    public function a_task_requires_a_body()
    {
        $project = ProjectsFactory::withTasks(1)->create();

        $task = factory('App\Task')->raw(['body' => '']);
        $this->actingAs($project->owner)
            ->post($project->path() . '/tasks', $task)->assertSessionHasErrors('body');
    }
}
