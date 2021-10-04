<?php

namespace Tests\Feature;

use App\Task;
use Facades\Tests\Setup\ProjectsFactory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TriggerActivityTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function creating_a_project()
    {
        $project = ProjectsFactory::create();

        $this->assertCount(1, $project->activity);



        tap($project->activity->last(), function ($activity) {
            $this->assertEquals('created_project', $activity->description);

            $this->assertNull($activity->changes);
        });
    }
    
    /** @test */
    public function updating_a_project()
    {
        $project = ProjectsFactory::create();

        $originalTitle = $project->title;

        $project->update(['title' => 'Changed']);
        $this->assertCount(2, $project->activity);

        tap($project->activity->last(), function ($activity) use ($originalTitle) {
            $this->assertEquals('updated_project', $activity->description);

            $changes = [
                'before' => [ 'title' => $originalTitle],
                'after' => [ 'title' => 'Changed']
            ];

            $this->assertEquals($activity->changes, $changes);
        });
    }

    /** @test */
    public function creating_a_task()
    {
        $project = ProjectsFactory::create();

        $project->addTask('Task Created');

        $this->assertCount(2, $project->activity);

        tap($project->activity->last(), function ($activity) {
            $this->assertInstanceOf(Task::class, $activity->subject);
            $this->assertEquals('created_task', $activity->description);
            $this->assertEquals('Task Created', $activity->subject->body);
        });

    }

    /** @test */
    public function completing_a_task()
    {
        $project = ProjectsFactory::withTasks(1)->create();

        $this->actingAs($project->owner)
            ->patch($project->tasks->first()->path(), [
                'body' => 'Changed',
                'completed' => true
            ]);

        $this->assertCount(3, $project->activity);

        tap($project->activity->last(), function ($activity) {
            $this->assertEquals('completed_task', $activity->description);
            $this->assertInstanceOf(Task::class, $activity->subject);
        });
        ;
    }

    /** @test */
    public function incompleting_a_task()
    {
        $project = ProjectsFactory::withTasks(1)->create();

        $this->actingAs($project->owner)
            ->patch($project->tasks->first()->path(), [
                'body' => 'Changed',
                'completed' => true
            ]);
        $this->assertCount(3, $project->activity);

        $this->patch($project->tasks->first()->path(), [
                'body' => 'Changed',
                'completed' => false
            ]);

        $project->refresh();

        $this->assertCount(4, $project->activity);
        $this->assertEquals('incompleted_task', $project->activity->last()->description);
    }
    
    /** @test */
    public function deleting_a_task()
    {
        $project = ProjectsFactory::withTasks(1)->create();

        $project->tasks->first()->delete();

        $this->assertCount(3, $project->activity);

    }

}
