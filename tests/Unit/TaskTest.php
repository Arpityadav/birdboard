<?php

namespace Tests\Unit;

use App\Project;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskTest extends TestCase
{
   use RefreshDatabase;

    /** @test */
    public function it_belongs_to_project()
    {
        $task = factory('App\Task')->create();

        $this->assertInstanceOf(Project::class, $task->project);
    }

   /** @test */
   public function it_has_a_path()
   {
        $task = factory('App\Task')->create();

        $this->assertEquals('/projects/' . $task->project->id . '/tasks/' . $task->id, $task->path());
   }

   /** @test */
   public function it_can_be_completed()
   {
       $task = factory('App\Task')->create();
       $this->assertfalse($task->completed);

       $task->complete();

       $this->assertTrue($task->fresh()->completed);
   }

    /** @test */
    public function it_can_be_marked_as_incompleted()
    {
        $task = factory('App\Task')->create(['completed' => true]);
        $this->assertTrue($task->completed);

        $task->incomplete();

        $this->assertFalse($task->fresh()->completed);
    }
}
