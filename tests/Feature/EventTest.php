<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function list_all_upcoming_and_today_events()
    {
        $this->withoutExceptionHandling();

        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        Carbon::setTestNow('2021-04-09 12:00:00');

        $event1 = Event::factory()->create([
            'title'      => 'Event 1 Title',
            'content'    => 'Event 1 Content',
            'valid_from' => '2021-04-06 10:00:00',
            'valid_to'   => '2021-04-12 10:00:00',
            'gps_lat'    => '-26.650969718825014',
            'gps_lng'    => '-48.6844083429979',
        ]);

        $event1comment1 = Comment::factory()
            ->for($event1, 'commentable')
            ->create(['nick_name' => 'JohnDoe', 'content' => 'Event 1 Comment 1']);

        $event1comment2 = Comment::factory()
            ->for($event1, 'commentable')
            ->create(['nick_name' => 'JohnWick', 'content' => 'Event 1 Comment 2']);

        $event2 = Event::factory()->create([
            'title'      => 'Event 2 Title',
            'content'    => 'Event 2 Content',
            'valid_from' => '2021-04-08 10:00:00',
            'valid_to'   => '2021-04-20 10:00:00',
            'gps_lat'    => '-26.650969718825014',
            'gps_lng'    => '-48.6844083429979',
        ]);

        $event3 = Event::factory()->create([
            'title'      => 'Event 3 Title',
            'content'    => 'Event 3 Content',
            'valid_from' => '2021-04-09 09:00:00',
            'valid_to'   => '2021-04-09 10:00:00',
            'gps_lat'    => '-26.650969718825014',
            'gps_lng'    => '-48.6844083429979',
        ]);

        Event::factory()->create([
            'valid_from' => '2021-04-01 10:00:00',
            'valid_to'   => '2021-04-08 10:00:00',
        ]);

        Event::factory()->create([
            'valid_from' => '2021-04-10 10:00:00',
            'valid_to'   => '2021-04-25 10:00:00',
        ]);

        $response = $this->json('get', '/api/events');

        $response->assertOk();

        $response->assertExactJson([
            [
                'id'         => $event1->id,
                'title'      => 'Event 1 Title',
                'content'    => 'Event 1 Content',
                'valid_from' => '2021-04-06 10:00:00',
                'valid_to'   => '2021-04-12 10:00:00',
                'gps_lat'    => -26.650969718825014,
                'gps_lng'    => -48.6844083429979,
                'comments'   => [
                    [
                        'id'        => $event1comment1->id,
                        'nick_name' => 'JohnDoe',
                        'content'   => 'Event 1 Comment 1',
                    ],
                    [
                        'id'        => $event1comment2->id,
                        'nick_name' => 'JohnWick',
                        'content'   => 'Event 1 Comment 2',
                    ],
                ],
            ],
            [
                'id'         => $event2->id,
                'title'      => 'Event 2 Title',
                'content'    => 'Event 2 Content',
                'valid_from' => '2021-04-08 10:00:00',
                'valid_to'   => '2021-04-20 10:00:00',
                'gps_lat'    => -26.650969718825014,
                'gps_lng'    => -48.6844083429979,
                'comments'   => [],
            ],
            [
                'id'         => $event3->id,
                'title'      => 'Event 3 Title',
                'content'    => 'Event 3 Content',
                'valid_from' => '2021-04-09 09:00:00',
                'valid_to'   => '2021-04-09 10:00:00',
                'gps_lat'    => -26.650969718825014,
                'gps_lng'    => -48.6844083429979,
                'comments'   => [],
            ],
        ]);
    }

    /** @test */
    public function list_all_events_that_match_with_the_range_date()
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        Carbon::setTestNow('2021-04-09 00:39:00');

        $event1 = Event::factory()->create([
            'title'      => 'Event 1 Title',
            'content'    => 'Event 1 Content',
            'valid_from' => '2021-04-10 10:00:00',
            'valid_to'   => '2021-04-13 10:00:00',
            'gps_lat'    => '-26.650969718825014',
            'gps_lng'    => '-48.6844083429979',
        ]);

        $event2 = Event::factory()->create([
            'title'      => 'Event 2 Title',
            'content'    => 'Event 2 Content',
            'valid_from' => '2021-04-14 10:00:00',
            'valid_to'   => '2021-04-17 10:00:00',
            'gps_lat'    => '-26.650969718825014',
            'gps_lng'    => '-48.6844083429979',
        ]);

        $event3 = Event::factory()->create([
            'title'      => 'Event 3 Title',
            'content'    => 'Event 3 Content',
            'valid_from' => '2021-04-10 10:00:00',
            'valid_to'   => '2021-04-20 10:00:00',
            'gps_lat'    => '-26.650969718825014',
            'gps_lng'    => '-48.6844083429979',
        ]);

        Event::factory()->create([
            'valid_from' => '2021-04-01 10:00:00',
            'valid_to'   => '2021-04-10 10:00:00',
        ]);

        Event::factory()->create([
            'valid_from' => '2021-04-22 10:00:00',
            'valid_to'   => '2021-04-25 10:00:00',
        ]);

        $response = $this->json('get', '/api/events?from=2021-04-12&to=2021-04-18');

        $response->assertOk();


        $this->assertEquals(3, count($response->json()));

        $this->assertEquals($event1->id, $response->json()[0]['id']);

        $this->assertEquals($event2->id, $response->json()[1]['id']);

        $this->assertEquals($event3->id, $response->json()[2]['id']);
    }

    /** @test */
    public function guests_can_not_list_events()
    {
        $this->json('get', '/api/events')->assertUnauthorized();
    }

    /** @test */
    public function create_a_new_event()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $response = $this->json('post', '/api/events', [
            'title'      => 'Some Title',
            'content'    => 'Some Content',
            'valid_from' => '2021-04-01 10:00:00',
            'valid_to'   => '2021-04-20 10:00:00',
            'gps_lat'    => '1',
            'gps_lng'    => '2',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('events', [
            'user_id'    => $user->id,
            'title'      => 'Some Title',
            'content'    => 'Some Content',
            'valid_from' => '2021-04-01 10:00:00',
            'valid_to'   => '2021-04-20 10:00:00',
            'gps_lat'    => '1',
            'gps_lng'    => '2',
        ]);
    }

    /** @test */
    public function guests_can_not_create_a_new_event()
    {
        $this->json('post', '/api/events', [
            'title'      => 'Some Title',
            'content'    => 'Some Content',
            'valid_from' => '2021-04-01 10:00:00',
            'valid_to'   => '2021-04-20 10:00:00',
            'gps_lat'    => '1',
            'gps_lng'    => '2',
        ])->assertUnauthorized();
    }

    /** @test */
    public function title_is_required_to_create()
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        $this->json('post', '/api/events', [
            'content'    => 'Some Content',
            'valid_from' => '2021-04-01 10:00:00',
            'valid_to'   => '2021-04-20 10:00:00',
            'gps_lat'    => '1',
            'gps_lng'    => '2',
        ])->assertJson([
            'errors' => [
                'title' => [__('validation.required', ['attribute' => 'title'])],
            ],
        ]);
    }

    /** @test */
    public function content_is_required_to_create()
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        $this->json('post', '/api/events', [
            'title'      => 'Some Title',
            'valid_from' => '2021-04-01 10:00:00',
            'valid_to'   => '2021-04-20 10:00:00',
            'gps_lat'    => '1',
            'gps_lng'    => '2',
        ])->assertJson([
            'errors' => [
                'content' => [__('validation.required', ['attribute' => 'content'])],
            ],
        ]);
    }

    /** @test */
    public function valid_from_is_required_to_create()
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        $this->json('post', '/api/events', [
            'title'    => 'Some Title',
            'content'  => 'Some Content',
            'valid_to' => '2021-04-20 10:00:00',
            'gps_lat'  => '1',
            'gps_lng'  => '2',
        ])->assertJson([
            'errors' => [
                'valid_from' => [__('validation.required', ['attribute' => 'valid from'])],
            ],
        ]);
    }

    /** @test */
    public function valid_from_should_be_a_valid_date_to_create()
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        $this->json('post', '/api/events', [
            'title'      => 'Some Title',
            'content'    => 'Some Content',
            'valid_from' => 'invalid date',
            'valid_to'   => '2021-04-20 10:00:00',
            'gps_lat'    => '1',
            'gps_lng'    => '2',
        ])->assertJson([
            'errors' => [
                'valid_from' => [__('validation.date', ['attribute' => 'valid from'])],
            ],
        ]);
    }

    /** @test */
    public function valid_from_should_match_with_pattern_to_create()
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        $this->json('post', '/api/events', [
            'title'      => 'Some Title',
            'content'    => 'Some Content',
            'valid_from' => '04/30/2021 10:00:00',
            'valid_to'   => '2021-04-20 10:00:00',
            'gps_lat'    => '1',
            'gps_lng'    => '2',
        ])->assertJson([
            'errors' => [
                'valid_from' => [__('validation.date_format', ['attribute' => 'valid from', 'format' => 'Y-m-d H:i:s'])],
            ],
        ]);
    }

    /** @test */
    public function valid_to_is_required_to_create()
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        $this->json('post', '/api/events', [
            'title'      => 'Some Title',
            'content'    => 'Some Content',
            'valid_from' => '2021-04-20 10:00:00',
            'gps_lat'    => '1',
            'gps_lng'    => '2',
        ])->assertJson([
            'errors' => [
                'valid_to' => [__('validation.required', ['attribute' => 'valid to'])],
            ],
        ]);
    }

    /** @test */
    public function valid_to_should_be_a_valid_to_create()
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        $this->json('post', '/api/events', [
            'title'      => 'Some Title',
            'content'    => 'Some Content',
            'valid_from' => '2021-04-20 10:00:00',
            'valid_to'   => 'invalid date',
            'gps_lat'    => '1',
            'gps_lng'    => '2',
        ])->assertJson([
            'errors' => [
                'valid_to' => [__('validation.date', ['attribute' => 'valid to'])],
            ],
        ]);
    }

    /** @test */
    public function valid_to_should_match_with_pattern_to_create()
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        $this->json('post', '/api/events', [
            'title'      => 'Some Title',
            'content'    => 'Some Content',
            'valid_from' => '2021-04-20 10:00:00',
            'valid_to'   => '04/20/2021 10:00:00',
            'gps_lat'    => '1',
            'gps_lng'    => '2',
        ])->assertJson([
            'errors' => [
                'valid_to' => [__('validation.date_format', ['attribute' => 'valid to', 'format' => 'Y-m-d H:i:s'])],
            ],
        ]);
    }

    /** @test */
    public function gps_lat_is_required_to_create()
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        $this->json('post', '/api/events', [
            'title'      => 'Some Title',
            'content'    => 'Some Content',
            'valid_from' => '2021-04-20 10:00:00',
            'valid_to'   => '2021-04-24 10:00:00',
            'gps_lng'    => '2',
        ])->assertJson([
            'errors' => [
                'gps_lat' => [__('validation.required', ['attribute' => 'gps lat'])],
            ],
        ]);
    }

    /** @test */
    public function gps_lng_is_required_to_create()
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);

        $this->json('post', '/api/events', [
            'title'      => 'Some Title',
            'content'    => 'Some Content',
            'valid_from' => '2021-04-20 10:00:00',
            'valid_to'   => '2021-04-24 10:00:00',
            'gps_lat'    => '2',
        ])->assertJson([
            'errors' => [
                'gps_lng' => [__('validation.required', ['attribute' => 'gps lng'])],
            ],
        ]);
    }

    /** @test */
    public function update_an_event()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $event = Event::factory()->create([
            'title'      => 'Title',
            'content'    => 'Content',
            'valid_from' => '2021-04-06 10:00:00',
            'valid_to'   => '2021-04-12 10:00:00',
            'gps_lat'    => '1',
            'gps_lng'    => '2',
            'user_id'    => $user->id,
        ]);

        $response = $this->json('put', '/api/events/' . $event->id, [
            'title'      => 'Edit Title',
            'content'    => 'Edit Content',
            'valid_from' => '2021-05-01 10:00:00',
            'valid_to'   => '2021-05-05 10:00:00',
            'gps_lat'    => '2',
            'gps_lng'    => '3',
        ]);

        $response->assertStatus(204);

        $this->assertDatabaseHas('events', [
            'id'         => $event->id,
            'user_id'    => $user->id,
            'title'      => 'Edit Title',
            'content'    => 'Edit Content',
            'valid_from' => '2021-05-01 10:00:00',
            'valid_to'   => '2021-05-05 10:00:00',
            'gps_lat'    => '2',
            'gps_lng'    => '3',
        ]);
    }

    /** @test */
    public function guests_can_not_update_an_event()
    {
        $event = Event::factory()->create([]);

        $this->json('put', '/api/events/' . $event->id, [
            'title'      => 'Edit Title',
            'content'    => 'Edit Content',
            'valid_from' => '2021-05-01 10:00:00',
            'valid_to'   => '2021-05-05 10:00:00',
            'gps_lat'    => '2',
            'gps_lng'    => '3',
        ])->assertUnauthorized();
    }

    /** @test */
    public function user_only_can_change_his_own_event()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $event = Event::factory()->create([
            'title'      => 'Title',
            'content'    => 'Content',
            'valid_from' => '2021-04-06 10:00:00',
            'valid_to'   => '2021-04-12 10:00:00',
            'gps_lat'    => '1',
            'gps_lng'    => '2',
        ]);

        $response = $this->json('put', '/api/events/' . $event->id, [
            'title'      => 'Edit Title',
            'content'    => 'Edit Content',
            'valid_from' => '2021-05-01 10:00:00',
            'valid_to'   => '2021-05-05 10:00:00',
            'gps_lat'    => '2',
            'gps_lng'    => '3',
        ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('events', [
            'title'      => 'Title',
            'content'    => 'Content',
            'valid_from' => '2021-04-06 10:00:00',
            'valid_to'   => '2021-04-12 10:00:00',
            'gps_lat'    => '1',
            'gps_lng'    => '2',
            'user_id'    => $event->user_id,
        ]);
    }

    /** @test */
    public function title_is_required_to_update()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $event = Event::factory()->create(['user_id' => $user->id]);

        $this->json('put', '/api/events/' . $event->id, [
            'content'    => 'Edit Content',
            'valid_from' => '2021-05-01 10:00:00',
            'valid_to'   => '2021-05-05 10:00:00',
            'gps_lat'    => '2',
            'gps_lng'    => '3',
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

        $event = Event::factory()->create(['user_id' => $user->id]);

        $this->json('put', '/api/events/' . $event->id, [
            'title'      => 'title',
            'valid_from' => '2021-05-01 10:00:00',
            'valid_to'   => '2021-05-05 10:00:00',
            'gps_lat'    => '2',
            'gps_lng'    => '3',
        ])->assertJson([
            'errors' => [
                'content' => [__('validation.required', ['attribute' => 'content'])],
            ],
        ]);
    }

    /** @test */
    public function valid_from_is_required_to_update()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $event = Event::factory()->create(['user_id' => $user->id]);

        $this->json('put', '/api/events/' . $event->id, [
            'title'    => 'title',
            'content'  => 'content',
            'valid_to' => '2021-05-05 10:00:00',
            'gps_lat'  => '2',
            'gps_lng'  => '3',
        ])->assertJson([
            'errors' => [
                'valid_from' => [__('validation.required', ['attribute' => 'valid from'])],
            ],
        ]);
    }

    /** @test */
    public function valid_from_should_be_a_valid_date_to_update()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $event = Event::factory()->create(['user_id' => $user->id]);

        $this->json('put', '/api/events/' . $event->id, [
            'title'      => 'title',
            'content'    => 'content',
            'valid_from' => 'invalid date',
            'valid_to'   => '2021-05-05 10:00:00',
            'gps_lat'    => '2',
            'gps_lng'    => '3',
        ])->assertJson([
            'errors' => [
                'valid_from' => [__('validation.date', ['attribute' => 'valid from'])],
            ],
        ]);
    }

    /** @test */
    public function valid_from_should_match_with_pattern_to_update()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $event = Event::factory()->create(['user_id' => $user->id]);

        $this->json('put', '/api/events/' . $event->id, [
            'title'      => 'title',
            'content'    => 'content',
            'valid_from' => '01/05/2021 08:00:00',
            'valid_to'   => '2021-05-05 10:00:00',
            'gps_lat'    => '2',
            'gps_lng'    => '3',
        ])->assertJson([
            'errors' => [
                'valid_from' => [__('validation.date_format', ['attribute' => 'valid from', 'format' => 'Y-m-d H:i:s'])],
            ],
        ]);
    }

    /** @test */
    public function valid_to_is_required_to_update()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $event = Event::factory()->create(['user_id' => $user->id]);

        $this->json('put', '/api/events/' . $event->id, [
            'title'      => 'title',
            'content'    => 'content',
            'valid_from' => '2021-05-05 10:00:00',
            'gps_lat'    => '2',
            'gps_lng'    => '3',
        ])->assertJson([
            'errors' => [
                'valid_to' => [__('validation.required', ['attribute' => 'valid to'])],
            ],
        ]);
    }

    /** @test */
    public function valid_to_should_be_a_valid_date_to_update()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $event = Event::factory()->create(['user_id' => $user->id]);

        $this->json('put', '/api/events/' . $event->id, [
            'title'      => 'title',
            'content'    => 'content',
            'valid_from' => '2021-05-05 10:00:00',
            'valid_to'   => 'invalid date',
            'gps_lat'    => '2',
            'gps_lng'    => '3',
        ])->assertJson([
            'errors' => [
                'valid_to' => [__('validation.date', ['attribute' => 'valid to'])],
            ],
        ]);
    }

    /** @test */
    public function valid_to_should_match_with_pattern_to_update()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $event = Event::factory()->create(['user_id' => $user->id]);

        $this->json('put', '/api/events/' . $event->id, [
            'title'      => 'title',
            'content'    => 'content',
            'valid_from' => '2021-05-05 10:00:00',
            'valid_to'   => '05/10/2021 10:00:00',
            'gps_lat'    => '2',
            'gps_lng'    => '3',
        ])->assertJson([
            'errors' => [
                'valid_to' => [__('validation.date_format', ['attribute' => 'valid to', 'format' => 'Y-m-d H:i:s'])],
            ],
        ]);
    }

    /** @test */
    public function gps_lat_is_required_to_update()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $event = Event::factory()->create(['user_id' => $user->id]);

        $this->json('put', '/api/events/' . $event->id, [
            'title'      => 'title',
            'content'    => 'content',
            'valid_from' => '2021-05-05 10:00:00',
            'valid_to'   => '2021-05-10 10:00:00',
            'gps_lng'    => '3',
        ])->assertJson([
            'errors' => [
                'gps_lat' => [__('validation.required', ['attribute' => 'gps lat'])],
            ],
        ]);
    }

    /** @test */
    public function gps_lng_is_required_to_update()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $event = Event::factory()->create(['user_id' => $user->id]);

        $this->json('put', '/api/events/' . $event->id, [
            'title'      => 'title',
            'content'    => 'content',
            'valid_from' => '2021-05-05 10:00:00',
            'valid_to'   => '2021-05-10 10:00:00',
            'gps_lat'    => '3',
        ])->assertJson([
            'errors' => [
                'gps_lng' => [__('validation.required', ['attribute' => 'gps lng'])],
            ],
        ]);
    }

    /** @test */
    public function destroy_an_event()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $event = Event::factory()->create([
            'title'      => 'Title',
            'content'    => 'Content',
            'valid_from' => '2021-04-06 10:00:00',
            'valid_to'   => '2021-04-12 10:00:00',
            'gps_lat'    => '1',
            'gps_lng'    => '2',
            'user_id'    => $user->id,
        ]);

        $response = $this->json('delete', '/api/events/' . $event->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    /** @test */
    public function guests_can_not_destroy_an_event()
    {
        $event = Event::factory()->create([
            'title'      => 'Title',
            'content'    => 'Content',
            'valid_from' => '2021-04-06 10:00:00',
            'valid_to'   => '2021-04-12 10:00:00',
            'gps_lat'    => '1',
            'gps_lng'    => '2',
        ]);

        $this->json('delete', '/api/events/' . $event->id)->assertUnauthorized();
    }

    /** @test */
    public function user_only_can_destroy_his_own_events()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $event = Event::factory()->create([
            'title'      => 'Title',
            'content'    => 'Content',
            'valid_from' => '2021-04-06 10:00:00',
            'valid_to'   => '2021-04-12 10:00:00',
            'gps_lat'    => '1',
            'gps_lng'    => '2',
        ]);

        $response = $this->json('delete', '/api/events/' . $event->id);

        $response->assertForbidden();

        $this->assertDatabaseHas('events', ['id' => $event->id]);
    }

    /** @test */
    public function it_should_not_destroy_an_event_when_it_has_comments()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $event = Event::factory()->create([
            'title'      => 'Title',
            'content'    => 'Content',
            'valid_from' => '2021-04-06 10:00:00',
            'valid_to'   => '2021-04-12 10:00:00',
            'gps_lat'    => '1',
            'gps_lng'    => '2',
            'user_id'    => $user->id,
        ]);

        Comment::factory()->for($event, 'commentable')->create();

        $this->json('delete', '/api/events/' . $event->id)
            ->assertExactJson([
                'error'   => true,
                'message' => 'Can not delete event when it has comments.',
            ]);

        $this->assertDatabaseHas('events', ['id' => $event->id]);
    }
}
