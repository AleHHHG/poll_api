<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Poll;
use App\Option;
use App\User;

class PollTest extends TestCase
{
    use RefreshDatabase;

    public function testShow()
    {
        $user = factory(User::class)->make();
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
        $poll = factory(Poll::class)->create();
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->get('/api/poll/'.$poll->id);
        $response->assertStatus(200);
    }

    public function testShowWithoutToken()
    {
        $poll = factory(Poll::class)->create();
        $response = $this->get('/api/poll/'.$poll->id);
        $response->assertStatus(401);
    }

    public function testShowInvalidToken()
    {
        $token = '123456';
        $poll = factory(Poll::class)->create();
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->get('/api/poll/'.$poll->id);
        $response->assertStatus(401);
    }

    public function testShowNotFounddRequest()
    {
        $user = factory(User::class)->make();
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->get('/api/poll/5555');
        $response->assertStatus(404);
    }

    public function testShowInvalidMethod()
    {
        $response = $this->post('/api/poll/1');
        $response->assertStatus(405);
    }

    public function testStore()
    {
        $user = factory(User::class)->make();
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->post(
            '/api/poll',
            ['poll_description' => 'Test Poll', 'options' => ['Ola?', 'Ok?']]
        );
        $response->assertStatus(201);
    }

    public function testStoreWithoutToken()
    {
        $response = $this->post(
            '/api/poll',
            ['poll_description' => 'Test Poll', 'options' => ['Ola?', 'Ok?']]
        );
        $response->assertStatus(401);
    }

    public function testStoreInvalidToken()
    {
        $token = '123456';
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->post(
            '/api/poll',
            ['poll_description' => 'Test Poll', 'options' => ['Ola?', 'Ok?']]
        );
        $response->assertStatus(401);
    }
    
    public function testStoreBadRequest()
    {
        $user = factory(User::class)->make();
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->post(
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
        $user = factory(User::class)->make();
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
        $poll = factory(Poll::class)->create();
        $poll->options()->createMany(
            factory(Option::class, 3)->create([
                'poll_id' => $poll->id])->toArray()
        );
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->post(
            '/api/poll/'.$poll->id.'/vote',
            ['option_id' => $poll->options[0]->id]
        );
        $response->assertStatus(200)->assertJson([
            'votes' => true,
        ]);
    }

    public function testVoteWithoutToken()
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
        $response->assertStatus(401)->assertJson([
            'error' => true,
        ]);
    }

    public function testVoteInvalidToken()
    {
        $token = '123456';
        $poll = factory(Poll::class)->create();
        $poll->options()->createMany(
            factory(Option::class, 3)->create([
                'poll_id' => $poll->id])->toArray()
        );
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->post(
            '/api/poll/'.$poll->id.'/vote',
            ['option_id' => $poll->options[0]->id]
        );
        $response->assertStatus(401)->assertJson([
            'error' => true,
        ]);
    }

    public function testVoteBadRequest()
    {
        $user = factory(User::class)->make();
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
        $poll = factory(Poll::class)->create();
        $poll->options()->createMany(
            factory(Option::class, 3)->create(
                ['poll_id' => $poll->id])->toArray()
        );
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->post(
            '/api/poll/'.$poll->id.'/vote',
            ['option__id' => $poll->options[0]->id]
        );
        $response->assertStatus(400)->assertJson([
            'error' => true,
        ]);
    }

    public function testVotePollNotFound()
    {
        $user = factory(User::class)->make();
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->post(
            '/api/poll/5555/vote',
            ['option_id' => 1]
        );
        $response->assertStatus(404)->assertExactJson([
            'error' => 'Poll not found',
        ]);
    }

    public function testVoteOptionNotFound()
    {
        $user = factory(User::class)->make();
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
        $poll = factory(Poll::class)->create();
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->post(
            '/api/poll/'.$poll->id.'/vote',
            ['option_id' => 200]
        );
        $response->assertStatus(404)->assertExactJson([
            'error' => 'Option not found',
        ]);
    }

    public function testVoteInvalidMethod()
    {
        $user = factory(User::class)->make();
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
        $response = $this->put(
            '/api/poll/1/vote',
            ['option_id' => 200]
        );
        $response->assertStatus(405);
    }

    public function testStats()
    {
        $user = factory(User::class)->make();
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
        $poll = factory(Poll::class)->create();
        $poll->options()->createMany(
            factory(Option::class, 3)->create(
                ['poll_id' => $poll->id])->toArray()
        );
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->get(
            '/api/poll/'.$poll->id.'/stats'
        );
        $response->assertStatus(200);
    }

    public function testStatsWithoutToken()
    {
        $poll = factory(Poll::class)->create();
        $poll->options()->createMany(
            factory(Option::class, 3)->create(
                ['poll_id' => $poll->id])->toArray()
        );
        $response = $this->get(
            '/api/poll/'.$poll->id.'/stats'
        );
        $response->assertStatus(401)->assertJson([
            'error' => true
        ]);
    }

    public function testStatsInvalidToken()
    {
        $token = '123456';
        $poll = factory(Poll::class)->create();
        $poll->options()->createMany(
            factory(Option::class, 3)->create(
                ['poll_id' => $poll->id])->toArray()
        );
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->get(
            '/api/poll/'.$poll->id.'/stats'
        );
        $response->assertStatus(401)->assertJson([
            'error' => true
        ]);
    }

    public function testStatsPollNotFound()
    {
        $user = factory(User::class)->make();
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->get(
            '/api/poll/5555/stats'
        );
        $response->assertStatus(404)->assertExactJson([
            'error' => 'Poll not found',
        ]);
    }

    public function testStatsInvalidMethod()
    {
        $response = $this->post(
            '/api/poll/5555/stats'
        );
        $response->assertStatus(405)->assertJson([
            'error' => true
        ]);
    }
}
