<?php

namespace sngrl\PhpFirebaseCloudMessaging;

/**
 * FCM message addressed to one or more topics
 */
class TopicMessage extends Message
{
    /**
     * Condition format string for sending to multiple topics
     * @var string
     */
    protected $condition;

    /**
     * Add a destination topic
     *
     * @param string $topicName
     * @return $this
     */
    public function addTopic($topicName)
    {
        if (!is_string($topicName)) {
            throw new \InvalidArgumentException('Topic name must be a string. Got ' . gettype($topicName));
        }

        $this->recipients[] = $topicName;
        return $this;
    }

    /**
     * Specify a condition pattern when sending to combinations of topics
     * https://firebase.google.com/docs/cloud-messaging/topic-messaging#sending_topic_messages_from_the_server
     *
     * Examples:
     * "%s && %s" > Send to devices subscribed to topic 1 and topic 2
     * "%s && (%s || %s)" > Send to devices subscribed to topic 1 and topic 2 or 3
     *
     * @param string $condition
     * @return $this
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;
        return $this;
    }

    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();

        if (count($this->recipients) > 1) {
            $data['condition'] = $this->createConditionString();
        } else {
            $data['to'] = sprintf('/topics/%s', reset($this->recipients));
        }

        return $data;
    }

    protected function createConditionString()
    {
        $recipientCount = count($this->recipients);

        if ($recipientCount > self::MAX_TOPICS) {
            throw new \OutOfRangeException(sprintf('Firebase supports addressing a max of %d topics per message; %d given.', self::MAX_TOPICS, $recipientCount));

        } else if (!$this->condition) {
            throw new \InvalidArgumentException('Missing topic condition format string. You must specify a condition pattern when sending to combinations of topics.');

        } else if ($recipientCount != substr_count($this->condition, '%s')) {
            throw new \UnexpectedValueException('The number of message topics must match the number of occurrences of "%s" in the condition pattern.');
        }

        return vsprintf($this->condition, $this->recipients);
    }
}