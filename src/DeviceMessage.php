<?php

namespace sngrl\PhpFirebaseCloudMessaging;

/**
 * FCM message addressed to one or multiple device tokens
 */
class DeviceMessage extends Message
{
    /**
     * Switch between sending as multicast and sending to an individual device
     *
     * Always using multicast is the default as it makes the response format from
     * Firebase independent of the number of recipients
     *
     * @var bool
     */
    protected $forceMulticast = true;

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

    /**
     * Return the tokens
     * @return array
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * Add multiple recipients
     * @param array $deviceTokens
     * @return $this
     */
    public function addRecipients(array $deviceTokens)
    {
        foreach ($deviceTokens as $token) {
            $this->addRecipient($token);
        }

        return $this;
    }

    /**
     * Send this notification as multi-cast, even if it only has one recipient.
     */
    public function sendAsMulticast($multicast = true)
    {
        $this->forceMulticast = $multicast;
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

        if ($this->forceMulticast) {
            $data['registration_ids'] = $this->recipients;

        } else if (count($this->recipients) == 1) {
            $data['to'] = reset($this->recipients);
        } else {
            throw new \RuntimeException("Can't use single-recipient messaging with more or less than one recipient. Use multi-cast.");
        }

        return $data;
    }
}