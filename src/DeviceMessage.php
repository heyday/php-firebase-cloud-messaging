<?php

namespace sngrl\PhpFirebaseCloudMessaging;

/**
 * FCM message addressed to one or multiple device tokens
 */
class DeviceMessage extends Message
{
    /**
     * Add a recipient device
     *
     * @param string $deviceToken
     * @return $this
     */
    public function addRecipient($deviceToken)
    {
        if (!is_string($deviceToken)) {
            throw new \InvalidArgumentException('Device token must be a string. Got ' . gettype($deviceToken));
        }

        $this->recipients[] = $deviceToken;
        return $this;
    }

    public function addRecipients(array $topicNames)
    {
        foreach ($topicNames as $topicName) {
            $this->addRecipient($topicName);
        }
        return $this;
    }

    /**
     * Divide this message into multiple messages with <= Message::MAX_DEVICES recipients
     *
     * This allows working around the FCM multi-cast recipient limit of 1,000 devices.
     *
     * @return DeviceMessage[]
     */
    public function chunk()
    {
        $messages = [];

        foreach (array_chunk($this->recipients, self::MAX_DEVICES) as $recipientsChunk) {
            $chunk = clone $this;
            $chunk->recipients = $recipientsChunk;

            $messages[] = $chunk;
        };

        return $messages;
    }

    public function jsonSerialize()
    {
        if (count($this->recipients) > self::MAX_DEVICES) {
            throw new \OutOfRangeException(sprintf(
                'Message device limit exceeded. Firebase supports a maximum of %d devices. Use multiple messages.', self::MAX_DEVICES
            ));
        }

        $data = parent::jsonSerialize();

        if (count($this->recipients) > 1) {
            $data['registration_ids'] = $this->recipients;
        } else {
            $data['to'] = reset($this->recipients);
        }

        return $data;
    }
}