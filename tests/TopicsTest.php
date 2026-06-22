<?php

use Illuminate\Support\Facades\Http;
use LaravelFCM\Message\Topics;
use LaravelFCM\Message\Exceptions\NoTopicProvidedException;

class TopicsTest extends FCMTestCase
{
    /**
     * @test
     */
    public function it_throw_an_exception_if_no_topic_is_provided()
    {
        $topics = new Topics();

        $this->expectException(NoTopicProvidedException::class);
        $topics->build();
    }

    /**
     * @test
     */
    public function it_builds_a_single_topic_body()
    {
        Http::fake(['*' => Http::response(['name' => 'projects/test/messages/1'], 200)]);

        $topics = new \LaravelFCM\Message\Topics();
        $topics->topic('myTopic');

        \LaravelFCM\Facades\FCM::sendToTopic($topics);

        Http::assertSent(function ($request) {
            return $request['message']['topic'] === 'myTopic'
                && !array_key_exists('condition', $request['message']);
        });
    }

    /**
     * @test
     */
    public function it_builds_a_condition_body()
    {
        Http::fake(['*' => Http::response(['name' => 'projects/test/messages/1'], 200)]);

        $topics = new \LaravelFCM\Message\Topics();
        $topics->topic('TopicA')->andTopic('TopicB');

        \LaravelFCM\Facades\FCM::sendToTopic($topics);

        Http::assertSent(function ($request) {
            return $request['message']['condition'] === "'TopicA' in topics && 'TopicB' in topics"
                && !array_key_exists('topic', $request['message']);
        });
    }

    /**
     * @test
     */
    public function it_has_two_topics_and()
    {
        $target = [
            'condition' => "'firstTopic' in topics && 'secondTopic' in topics",
        ];

        $topics = new Topics();

        $topics->topic('firstTopic')->andTopic('secondTopic');

        $this->assertEquals($target, $topics->build());
    }

    /**
     * @test
     */
    public function it_has_two_topics_or()
    {
        $target = [
            'condition' => "'firstTopic' in topics || 'secondTopic' in topics",
        ];

        $topics = new Topics();

        $topics->topic('firstTopic')->orTopic('secondTopic');

        $this->assertEquals($target, $topics->build());
    }

    /**
     * @test
     */
    public function it_has_two_topics_or_and_one_and()
    {
        $target = [
            'condition' => "'firstTopic' in topics || 'secondTopic' in topics && 'thirdTopic' in topics",
        ];

        $topics = new Topics();

        $topics->topic('firstTopic')->orTopic('secondTopic')->andTopic('thirdTopic');

        $this->assertEquals($target, $topics->build());
    }

    /**
     * @test
     */
    public function it_has_a_complex_topic_condition()
    {
        $target = [
            'condition' => "'TopicA' in topics && ('TopicB' in topics || 'TopicC' in topics) || ('TopicD' in topics && 'TopicE' in topics)",
        ];

        $topics = new Topics();

        $topics->topic('TopicA')
               ->andTopic(function ($condition) {
                   $condition->topic('TopicB')->orTopic('TopicC');
               })
               ->orTopic(function ($condition) {
                   $condition->topic('TopicD')->andTopic('TopicE');
               });

        $this->assertEquals($target, $topics->build());
    }

    /**
     * @test
     */
    public function it_reports_topic_send_success_from_name()
    {
        Http::fake(['*' => Http::response(['name' => 'projects/test/messages/9'], 200)]);

        $topics = new \LaravelFCM\Message\Topics();
        $topics->topic('myTopic');

        $response = \LaravelFCM\Facades\FCM::sendToTopic($topics);

        $this->assertTrue($response->isSuccess());
    }
}
