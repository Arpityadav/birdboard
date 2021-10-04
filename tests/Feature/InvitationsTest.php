<?php

namespace Tests\Feature;

use Facades\Tests\Setup\ProjectsFactory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvitationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_project_can_invite_a_user()
    {
        $project = ProjectsFactory::create();

        $userToInvite = factory('App\User')->create();

        $this->actingAs($project->owner)
            ->post($project->path() . '/invitations', [
                'email' => $userToInvite->email
            ]);

        $this->assertTrue($project->members->contains($userToInvite));
    }
    
    /** @test */
    public function non_owners_cannot_invite_a_user()
    {
        $project = ProjectsFactory::create();

        $user = factory('App\User')->create();

        $assertInvitationForbidden = function () use ($user, $project) {
            $this->actingAs($user)
                ->post($project->path() . '/invitations')
                ->assertStatus(403);
        };

        $assertInvitationForbidden();

        $project->invite($user);

        $assertInvitationForbidden();
    }
    
    /** @test */
    public function the_invited_email_must_be_a_valid_birdboard_account()
    {
        $project = ProjectsFactory::create();

        $this->actingAs($project->owner)
            ->post($project->path() . '/invitations', [
                'email' => 'notauser@example.com'
            ])
        ->assertSessionHasErrors([
            'email' => 'The user you are inviting must have a valid Birdboard account.'
        ], null, 'invitations');
    }
    
    /** @test */
    public function invited_users_can_update_a_project()
    {
        $project = ProjectsFactory::create();

        $project->invite($newUser = factory('App\User')->create());

        $this->signIn($newUser);

        $this->post($project->path().'/tasks', $task = ['body' => 'Foo body']);

        $this->assertDatabaseHas('tasks', $task);
    }
}
