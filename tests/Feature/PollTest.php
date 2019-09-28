<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Poll;
use App\Option;

class PollTest extends TestCase
{
    use RefreshDatabase;

    public function testShow()
    {
        $poll = factory(Poll::class)->create();
        $response = $this->get('/api/poll/'.$poll->id);
        $response->assertStatus(200);
    }

    public function testShowotFounddRequest()
    {
        $response = $this->get('/api/poll/5555');
        $response->assertStatus(404);
    }

    public function testShowInvalidMethod()
    {
        $response = $this->post('/api/poll/1');
        $response->assertStatus(405);
    }

    public function testStoreBadRequest()
    {
        $response = $this->post(
            '/api/poll',
            ['poll_description' => 'Test Poll']
        );
        $response->assertStatus(400)->assertJson([
            'error' => true,
        ]);
    }

    public function testStoreInvalidMethod()
    {
        $response = $this->put(
            '/api/poll',
            [
                'poll_description' => 'Test Poll',
                'options' => ['Option 1?','Option 2?']
            ]
        );
        $response->assertStatus(405);
    }

    public function testVote()
    {
        $poll = factory(Poll::class)->create();
        $poll->options()->createMany(
            factory(Option::class, 3)->create([
                'poll_id' => $poll->id])->toArray()
        );
        $response = $this->post(
            '/api/poll/'.$poll->id.'/vote',
            ['option_id' => $poll->options[0]->id]
        );
        $response->assertStatus(200)->assertJson([
            'votes' => true,
        ]);
    }

    public function testVoteBadRequest()
    {
        $poll = factory(Poll::class)->create();
        $poll->options()->createMany(
            factory(Option::class, 3)->create(
                ['poll_id' => $poll->id])->toArray()
        );
        $response = $this->post(
            '/api/poll/'.$poll->id.'/vote',
            ['option__id' => $poll->options[0]->id]
        );
        $response->assertStatus(400)->assertJson([
            'error' => true,
        ]);
    }

    public function testVotePollNotFound()
    {
        $response = $this->post(
            '/api/poll/5555/vote',
            ['option_id' => 1]
        );
        $response->assertStatus(404)->assertExactJson([
            'error' => 'Poll not found',
        ]);
    }

    public function testVoteOptionNotFound()
    {
        $poll = factory(Poll::class)->create();
        $response = $this->post(
            '/api/poll/'.$poll->id.'/vote',
            ['option_id' => 200]
        );
        $response->assertStatus(404)->assertExactJson([
            'error' => 'Option not found',
        ]);
    }

    public function testVoteInvalidMethod()
    {
        $response = $this->put(
            '/api/poll/1/vote',
            ['option_id' => 200]
        );
        $response->assertStatus(405);
    }

    public function testStats()
    {
        $poll = factory(Poll::class)->create();
        $poll->options()->createMany(
            factory(Option::class, 3)->create(
                ['poll_id' => $poll->id])->toArray()
        );
        $response = $this->get(
            '/api/poll/'.$poll->id.'/stats'
        );
        $response->assertStatus(200);
    }

    public function testStatsPollNotFound()
    {
        $response = $this->get(
            '/api/poll/5555/stats'
        );
        $response->assertStatus(404)->assertExactJson([
            'error' => 'Poll not found',
        ]);
    }

    public function testStatsBadRequest()
    {
        $response = $this->get(
            '/api/poll/5555/stats'
        );
        $response->assertStatus(404)->assertJson([
            'error' => true
        ]);
    }
}
