<?php
namespace sngrl\PhpFirebaseCloudMessaging\Tests;

use GuzzleHttp;
use GuzzleHttp\Psr7\Response;
use sngrl\PhpFirebaseCloudMessaging\Client;
use sngrl\PhpFirebaseCloudMessaging\TopicMessage;

class ClientTest extends PhpFirebaseCloudMessagingTestCase
{
    private $fixture;

    protected function setUp()
    {
        parent::setUp();
        $this->fixture = new Client();
    }

    public function testSendConstruesValidJsonForNotificationWithTopic()
    {
        $apiKey = 'key';
        $headers = array(
            'Authorization' => sprintf('key=%s', $apiKey),
            'Content-Type' => 'application/json'
        );

        $guzzle = \Mockery::mock(\GuzzleHttp\Client::class);
        $guzzle->shouldReceive('post')
            ->once()
            ->with(Client::DEFAULT_API_URL, array('headers' => $headers, 'body' => '{"to":"\\/topics\\/test"}'))
            ->andReturn(\Mockery::mock(Response::class));

        $this->fixture->injectGuzzleHttpClient($guzzle);
        $this->fixture->setApiKey($apiKey);

        $message = new TopicMessage();
        $message->addTopic('test');

        $this->fixture->send($message);
    }
}