<?php

namespace Tests\Feature;

use App\Mail\NotifyNewsOwnerAboutNewComment;
use App\Models\Comment;
use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NewsCommentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function create_a_new_comment_on_news()
    {
        Mail::fake();

        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $news = News::factory()
            ->for(User::factory()->create(['email' => 'john@doe.com']))
            ->create();

        $response = $this->json('post', '/api/news/' . $news->id . '/comments', [
            'nick_name' => 'JohnDoe',
            'content'   => 'Some Content',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('comments', [
            'nick_name' => 'JohnDoe',
            'content'   => 'Some Content',
            'user_id'   => $user->id,
        ]);

        Mail::assertSent(NotifyNewsOwnerAboutNewComment::class, function ($mail) {
            return $mail->comment->commentable->user->email == 'john@doe.com';
        });
    }

    /** @test */
    public function guests_can_not_create_a_new_comment_on_news()
    {
        $news = News::factory()->create();

        $this->json('post', '/api/news/' . $news->id . '/comments', [
            'nick_name' => 'JohnDoe',
            'content'   => 'Some Content',
        ])->assertUnauthorized();
    }

    /** @test */
    public function nick_name_is_required_to_create_a_new_comment()
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        $news = News::factory()->create();

        $this->json('post', '/api/news/' . $news->id . '/comments', [
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

        $news = News::factory()->create();

        $this->json('post', '/api/news/' . $news->id . '/comments', [
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

        $news = News::factory()->create();

        $comment = Comment::factory()
            ->for($news, 'commentable')
            ->for($user)
            ->create();

        $response = $this->json('delete', '/api/news/' . $news->id . '/comments/' . $comment->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    /** @test */
    public function guests_can_not_destroy_a_comment()
    {
        $news = News::factory()->create();

        $comment = Comment::factory()->for($news, 'commentable')->create();

        $this->json('delete', '/api/news/' . $news->id . '/comments/' . $comment->id)
            ->assertUnauthorized();
    }

    /** @test */
    public function user_only_can_destroy_his_own_comment()
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        $news = News::factory()->create();

        $comment = Comment::factory()->for($news, 'commentable')->create();

        $response = $this->json('delete', '/api/news/' . $news->id . '/comments/' . $comment->id);

        $response->assertForbidden();

        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }
}
