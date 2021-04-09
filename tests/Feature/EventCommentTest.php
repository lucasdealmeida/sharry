<?php

namespace Tests\Feature;

use App\Mail\NotifyEventOwnerAboutNewComment;
use App\Models\Comment;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventCommentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_should_create_a_new_comment_on_event()
    {
        Mail::fake();

        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $event = Event::factory()
            ->for(User::factory()->create(['email' => 'john@doe.com']))
            ->create();

        $response = $this->json('post', '/api/events/' . $event->id . '/comments', [
            'nick_name' => 'JohnDoe',
            'content'   => 'Some Content',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('comments', [
            'nick_name'        => 'JohnDoe',
            'content'          => 'Some Content',
            'user_id'          => $user->id,
            'commentable_type' => Event::class,
            'commentable_id'   => $event->id,
        ]);

        Mail::assertSent(NotifyEventOwnerAboutNewComment::class, function ($mail) {
            return $mail->comment->commentable->user->email == 'john@doe.com';
        });
    }

    /** @test */
    public function guests_can_not_create_a_new_comment()
    {
        $event = Event::factory()->create();

        $this->json('post', '/api/events/' . $event->id . '/comments', [
            'nick_name' => 'JohnDoe',
            'content'   => 'Some Content',
        ])->assertUnauthorized();
    }

    /** @test */
    public function nick_name_is_required_to_create_a_new_comment()
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        $event = Event::factory()->create();

        $this->json('post', '/api/events/' . $event->id . '/comments', [
            'content' => 'Some Content',
        ])->assertJson([
            'errors' => [
                'nick_name' => [__('validation.required', ['attribute' => 'nick name'])],
            ],
        ]);
    }

    /** @test */
    public function content_is_required_to_create_a_new_comment()
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        $event = Event::factory()->create();

        $this->json('post', '/api/events/' . $event->id . '/comments', [
            'nick_name' => 'JohnDoe',
        ])->assertJson([
            'errors' => [
                'content' => [__('validation.required', ['attribute' => 'content'])],
            ],
        ]);
    }

    /** @test */
    public function destroy_a_comment()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $event = Event::factory()->create();

        $comment = Comment::factory()
            ->for($event, 'commentable')
            ->for($user)
            ->create();

        $response = $this->json('delete', '/api/events/' . $event->id . '/comments/' . $comment->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    /** @test */
    public function guests_can_not_destroy_a_comment()
    {
        $event = Event::factory()->create();

        $comment = Comment::factory()->for($event, 'commentable')->create();

        $this->json('delete', '/api/events/' . $event->id . '/comments/' . $comment->id)
            ->assertUnauthorized();
    }

    /** @test */
    public function user_only_can_destroy_his_own_comment()
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        $event = Event::factory()->create();

        $comment = Comment::factory()->for($event, 'commentable')->create();

        $response = $this->json('delete', '/api/events/' . $event->id . '/comments/' . $comment->id);

        $response->assertForbidden();

        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }
}
