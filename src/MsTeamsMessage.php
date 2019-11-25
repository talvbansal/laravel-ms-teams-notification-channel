<?php

namespace NotificationChannels\MsTeams;

class MsTeamsMessage
{
    private $payload = [];

    public function __construct($content = '')
    {
        $this->content($content);
    }

    public static function create($content = ''): self
    {
        return new static($content);
    }

    public function to($url): self
    {
        $this->payload['url'] = $url;

        return $this;
    }

    public function toUnknown() :bool
    {
        return empty($this->payload['url']);
    }

    public function title($title) : self
    {
        $this->payload['title'] = $title;

        return $this;
    }

    public function content($content) : self
    {
        $this->payload['text'] = $content;

        return $this;
    }

    public function code($content) : self
    {
        $this->payload['code'][] = $content;

        return $this;
    }

    public function type($type) : self
    {
        $this->payload['type'] = $type;

        return $this;
    }

    public function button($text, $url): self
    {
        $this->payload['buttons'][] = compact('text', 'url');

        return $this;
    }

    public function image($image): self
    {
        $this->payload['images'][] = $image;

        return $this;
    }

    public function toArray()
    {
        return $this->payload;
    }
}
