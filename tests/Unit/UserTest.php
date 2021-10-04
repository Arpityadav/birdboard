<?php

namespace Tests\Unit;

use Facades\Tests\Setup\ProjectsFactory;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

     /** @test */
     public function a_user_has_many_projects()
     {
        $user = factory('App\User')->create();

        $this->assertInstanceOf(Collection::class, $user->projects);
     }

     /** @test */
     public function a_user_can_access_all_projects()
     {
         //Given we have a user
         $john = factory('App\User')->create();

         ProjectsFactory::ownedBy($john)->create();

         $this->assertCount(1, $john->accessibleProjects());

         //When they are invited
         $sally = factory('App\User')->create();
         $nick = factory('App\User')->create();

         $project = ProjectsFactory::ownedBy($sally)->create();
         $project->invite($john);
         //they should have 2 projects

         $this->assertCount(2, $john->accessibleProjects());

         $project->invite($nick);

         $this->assertCount(2, $john->accessibleProjects());

     }
}
