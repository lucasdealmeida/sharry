<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NewsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function list_all_news_with_comments_created_today()
    {
        $this->travel(-10)->days();
        News::factory()->create();

        $this->travel(-1)->days();
        News::factory()->create();

        $this->travelBack();

        $news1 = News::factory()->create(['title' => 'Title News 1', 'content' => 'Content News 1']);
        $news2 = News::factory()->create(['title' => 'Title News 2', 'content' => 'Content News 2']);

        $news1comment1 = Comment::factory()
            ->for($news1, 'commentable')
            ->create(['nick_name' => 'JohnDoe', 'content' => 'News 1 Comment 1']);
        $news1comment2 = Comment::factory()
            ->for($news1, 'commentable')
            ->create(['nick_name' => 'JohnWick', 'content' => 'News 1 Comment 2']);

        $news2comment1 = Comment::factory()
            ->for($news2, 'commentable')
            ->create(['nick_name' => 'JohnSmith', 'content' => 'News 2 Comment 1']);
        $news2comment2 = Comment::factory()
            ->for($news2, 'commentable')
            ->create(['nick_name' => 'JohnDavis', 'content' => 'News 2 Comment 2']);

        $response = $this->json('get', '/api/news');
        $response->assertOk();
        $response->assertExactJson([
            [
                'id'       => $news1->id,
                'title'    => 'Title News 1',
                'content'  => 'Content News 1',
                'comments' => [
                    [
                        'id'        => $news1comment1->id,
                        'nick_name' => 'JohnDoe',
                        'content'   => 'News 1 Comment 1',
                    ],
                    [
                        'id'        => $news1comment2->id,
                        'nick_name' => 'JohnWick',
                        'content'   => 'News 1 Comment 2',
                    ],
                ],
            ],
            [
                'id'       => $news2->id,
                'title'    => 'Title News 2',
                'content'  => 'Content News 2',
                'comments' => [
                    [
                        'id'        => $news2comment1->id,
                        'nick_name' => 'JohnSmith',
                        'content'   => 'News 2 Comment 1',
                    ],
                    [
                        'id'        => $news2comment2->id,
                        'nick_name' => 'JohnDavis',
                        'content'   => 'News 2 Comment 2',
                    ],
                ],
            ],
        ]);
    }

    /** @test */
    public function create_a_new_news()
    {
        $user = User::factory()->create();

        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->json('post', '/api/news', [
            'title'   => 'Some Title',
            'content' => 'Some Content',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('news', [
            'title'   => 'Some Title',
            'content' => 'Some Content',
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function guests_can_not_access_creating_route()
    {
        $this->json('post', '/api/news', [
            'title'   => 'some title',
            'content' => 'some content',
        ])->assertUnauthorized();
    }

    /** @test */
    public function title_is_required_to_create()
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $this->json('post', '/api/news', [
            'content' => 'some content',
        ])->assertJson([
            'errors' => [
                'title' => [__('validation.required', ['attribute' => 'title'])],
            ],
        ]);
    }

    /** @test */
    public function content_is_required_to_create()
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $this->json('post', '/api/news', [
            'title' => 'some title',
        ])->assertJson([
            'errors' => [
                'content' => [__('validation.required', ['attribute' => 'content'])],
            ],
        ]);
    }

    /** @test */
    public function update_a_news()
    {
        $user = User::factory()->create();

        Sanctum::actingAs(
            $user,
            ['*']
        );

        $news = News::factory()->create([
            'title'   => 'Title',
            'content' => 'Content',
            'user_id' => $user->id,
        ]);

        $response = $this->json('put', '/api/news/' . $news->id, [
            'title'   => 'New Title',
            'content' => 'New Content',
        ]);

        $response->assertStatus(204);

        $this->assertDatabaseHas('news', [
            'id'      => $news->id,
            'user_id' => $user->id,
            'title'   => 'New Title',
            'content' => 'New Content',
        ]);
    }

    /** @test */
    public function guests_can_not_access_update_page()
    {
        $news = News::factory()->create([
            'title'   => 'Title',
            'content' => 'Content',
        ]);

        $this->json('put', '/api/news/' . $news->id, [
            'title'   => 'New Title',
            'content' => 'New Content',
        ])->assertUnauthorized();
    }

    /** @test */
    public function user_only_can_changes_his_own_news()
    {
        $user = User::factory()->create();

        Sanctum::actingAs(
            $user,
            ['*']
        );

        $news = News::factory()->create([
            'title'   => 'Title',
            'content' => 'Content',
        ]);

        $this->json('put', '/api/news/' . $news->id, [
            'title'   => 'New Title',
            'content' => 'New Content',
        ])->assertForbidden();

        $this->assertDatabaseHas('news', [
            'title'   => 'Title',
            'content' => 'Content',
        ]);
    }

    /** @test */
    public function title_is_required_to_update()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $news = News::factory()->create(['user_id' => $user->id]);

        $this->json('put', '/api/news/' . $news->id, [
            'content' => 'New Content',
        ])->assertJson([
            'errors' => [
                'title' => [__('validation.required', ['attribute' => 'title'])],
            ],
        ]);
    }

    /** @test */
    public function content_is_required_to_update()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $news = News::factory()->create(['user_id' => $user->id,]);

        $this->json('put', '/api/news/' . $news->id, [
            'title' => 'Title',
        ])->assertJson([
            'errors' => [
                'content' => [__('validation.required', ['attribute' => 'content'])],
            ],
        ]);
    }

    /** @test */
    public function destroy_a_news()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $news = News::factory()->create([
            'title'   => 'Title',
            'content' => 'Content',
            'user_id' => $user->id,
        ]);

        $this->json('delete', '/api/news/' . $news->id)->assertStatus(204);

        $this->assertDatabaseMissing('news', [
            'id'      => $news->id,
            'user_id' => $user->id,
            'title'   => 'Title',
            'content' => 'Content',
        ]);
    }

    /** @test */
    public function guests_can_not_destroy_a_news()
    {
        $news = News::factory()->create();

        $this->json('delete', '/api/news/' . $news->id)->assertUnauthorized();
    }

    /** @test */
    public function user_only_can_destroy_his_own_news()
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        $user = User::factory()->create();

        $news = News::factory()->create([
            'title'   => 'Title',
            'content' => 'Content',
            'user_id' => $user->id,
        ]);

        $this->json('delete', '/api/news/' . $news->id)->assertForbidden();

        $this->assertDatabaseHas('news', [
            'id'      => $news->id,
            'user_id' => $user->id,
            'title'   => 'Title',
            'content' => 'Content',
        ]);
    }

    /** @test */
    public function it_should_not_destroy_a_news_when_news_has_comments()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $news = News::factory()->create([
            'title'   => 'Title',
            'content' => 'Content',
            'user_id' => $user->id,
        ]);

        Comment::factory()->for($news, 'commentable')->create();

        $this->json('delete', '/api/news/' . $news->id)
            ->assertExactJson([
                'error' => true,
                'message' => 'Can not delete news when it has comments.'
            ]);

        $this->assertDatabaseHas('news', [
            'id'      => $news->id,
            'user_id' => $user->id,
            'title'   => 'Title',
            'content' => 'Content',
        ]);
    }
}
