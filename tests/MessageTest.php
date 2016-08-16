<?php

namespace sngrl\PhpFirebaseCloudMessaging\Tests;

use sngrl\PhpFirebaseCloudMessaging\DeviceMessage;
use sngrl\PhpFirebaseCloudMessaging\Notification;
use sngrl\PhpFirebaseCloudMessaging\TopicMessage;

class MessageTest extends PhpFirebaseCloudMessagingTestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testThrowsExceptionWhenNoRecepientWasAdded()
    {
        $message = new DeviceMessage();
        $message->jsonSerialize();
    }

    /**
     * The current API requires a format string to represent the logic for combining topics
     *
     * @expectedException \InvalidArgumentException
     */
    public function testThrowsExceptionWhenMultipleTopicsWereGiven()
    {
        $message = new TopicMessage();

        $message->addTopic('breaking-news');
        $message->addTopic('another topic');

        $message->jsonSerialize();
    }

    public function testJsonEncodeWorksOnTopicMessages()
    {
        $expected = [
            'to' => '/topics/breaking-news',
            'notification' => [
                'title' => 'test',
                'body' => 'a nice testing notification',
            ],
        ];

        $notification = new Notification('test', 'a nice testing notification');

        $message = new TopicMessage();
        $message->setNotification($notification);
        $message->addTopic('breaking-news');

        $this->assertEquals($expected, json_decode(json_encode($message), true));
    }

    public function testJsonEncodeWorksOnDeviceRecipients()
    {
        $expected = [
            'to' => 'deviceId',
            'notification' => [
                'title' => 'test',
                'body' => 'a nice testing notification',
            ],
        ];

        $notification = new Notification('test', 'a nice testing notification');
        $message = new DeviceMessage();
        $message->setNotification($notification);

        $message->addRecipient('deviceId');
        $this->assertEquals($expected, json_decode(json_encode($message), true));
    }
}