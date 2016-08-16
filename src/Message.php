<?php

namespace sngrl\PhpFirebaseCloudMessaging;

/**
 * @author sngrl
 */
abstract class Message implements \JsonSerializable
{
    /**
     * Maximum topics and devices
     * @see https://firebase.google.com/docs/cloud-messaging/http-server-ref#send-downstream
     */
    const MAX_TOPICS = 3;
    const MAX_DEVICES = 1000;

    protected $notification;
    protected $collapseKey;
    protected $priority;
    protected $data;
    protected $recipients = [];
    protected $jsonData = [];

    public function setNotification(Notification $notification)
    {
        $this->notification = $notification;
        return $this;
    }

    public function setCollapseKey($collapseKey)
    {
        $this->collapseKey = $collapseKey;
        return $this;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Set root message data via key
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setJsonKey($key, $value)
    {
        $this->jsonData[$key] = $value;
        return $this;
    }

    /**
     * Unset root message data via key
     *
     * @param string $key
     * @return $this
     */
    public function unsetJsonKey($key)
    {
        unset($this->jsonData[$key]);
        return $this;
    }

    /**
     * Get root message data via key
     *
     * @param string $key
     * @return mixed
     */
    public function getJsonKey($key)
    {
        return $this->jsonData[$key];
    }

    /**
     * Get root message data
     *
     * @return array
     */
    public function getJsonData()
    {
        return $this->jsonData;
    }

    /**
     * Set root message data
     *
     * @param array $array
     * @return $this
     */
    public function setJsonData($array)
    {
        $this->jsonData = $array;
        return $this;
    }

    public function setDelayWhileIdle($value)
    {
        $this->setJsonKey('delay_while_idle', (bool)$value);
        return $this;
    }

    public function setTimeToLive($value)
    {
        $this->setJsonKey('time_to_live', (int)$value);
        return $this;
    }

    public function jsonSerialize()
    {
        $jsonData = $this->jsonData;

        if (empty($this->recipients)) {
            throw new \UnexpectedValueException('Message must have at least one recipient');
        }

        if ($this->collapseKey) {
            $jsonData['collapse_key'] = $this->collapseKey;
        }
        if ($this->data) {
            $jsonData['data'] = $this->data;
        }
        if ($this->priority) {
            $jsonData['priority'] = $this->priority;
        }
        if ($this->notification) {
            $jsonData['notification'] = $this->notification;
        }

        return $jsonData;
    }
}