<?php

use Illuminate\Support\Facades\Http;
use LaravelFCM\Facades\FCM;

class DownstreamTest extends FCMTestCase
{
    /**
     * @test
     */
    public function it_sends_a_notification_to_a_single_token()
    {
        Http::fake([
            '*' => Http::response(['name' => 'projects/test/messages/123'], 200),
        ]);

        $response = FCM::sendTo('uniqueToken');

        $this->assertEquals(1, $response->numberSuccess());
        $this->assertEquals(0, $response->numberFailure());

        Http::assertSent(function ($request) {
            return $request['message']['token'] === 'uniqueToken'
                && !array_key_exists('registration_ids', $request['message']);
        });
    }

    /**
     * @test
     */
    public function it_throws_404_for_an_unregistered_token()
    {
        Http::fake([
            '*' => Http::response(['error' => ['code' => 404, 'status' => 'UNREGISTERED']], 404),
        ]);

        try {
            FCM::sendTo('deadToken');
            $this->fail('expected an exception');
        } catch (\Exception $e) {
            $this->assertEquals(404, $e->getCode());
        }
    }

    /**
     * @test
     */
    public function it_collects_failed_tokens_for_array_sends()
    {
        Http::fakeSequence()
            ->push(['name' => 'projects/test/messages/1'], 200)
            ->push(['error' => ['code' => 404, 'status' => 'UNREGISTERED']], 404);

        $response = FCM::sendTo(['goodToken', 'deadToken']);

        $this->assertEquals(1, $response->numberSuccess());
        $this->assertEquals(1, $response->numberFailure());
        $this->assertEquals(['deadToken'], $response->tokensToDelete());
    }
}
