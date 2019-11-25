<?php

namespace NotificationChannels\MsTeams;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;
use NotificationChannels\MsTeams\Exceptions\CouldNotSendNotification;

class MsTeamsChannel
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param $notifiable
     * @param Notification $notification
     * @return \Psr\Http\Message\ResponseInterface
     * @throws CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        /** @var MsTeamsMessage $message */
        $message = $notification->toMsTeams($notifiable);

        if ($message->toUnknown()) {
            if (! $to = $notifiable->routeNotificationFor('msteams')) {
                throw CouldNotSendNotification::connectorWebHookUrlMissing();
            }

            $message->to($to);
        }

        $data = $message->toArray();

        if (! $url = Arr::get($data, 'url')) {
            return;
        }

        $code = collect(Arr::get($data, 'code', []))
            ->map(function ($code) {
                return [
                    'name'  => 'Code',
                    'value' => "<pre>$code</pre>",
                ];
            });

        $potentialActions = collect(Arr::get($data, 'buttons', []))
            ->map(function ($button) {
                return (object) [
                    '@context' => 'http://schema.org',
                    '@type' => 'ViewAction',
                    'name' => $button['text'],
                    'target' => [
                        $button['url'],
                    ],
                ];
            });

        $images = collect(Arr::get($data, 'images', []))
            ->map(function ($image) {
                return (object) [
                    'image' => $image,
                ];
            });

        $payload = [
            '@type'      => 'MessageCard',
            '@context'   => 'http://schema.org/extensions',
            'summary' =>  Arr::get($data, 'title', 'Incoming notification'),
            'themeColor' =>  $this->getNotificationType(Arr::get($data, 'type', 'success')),
            'title'      => Arr::get($data, 'title'),

            'sections'   => [
                [
                    'activitySubtitle'  => sprintf('%s : (%s)', config('app.url'), config('app.env')),
                    'text' => Arr::get($data, 'text'),
                    'facts' => $code,
                    'images' => $images,
                ],
            ],

            'potentialAction' => $potentialActions,
        ];

        try {
            $response = $this->client->post($data['url'], [
                'json' => $payload,
            ]);
        } catch (ClientException $exception) {
            throw CouldNotSendNotification::msTeamsRespondedWithAnError($exception);
        } catch (\Exception $exception) {
            throw CouldNotSendNotification::couldNotCommunicateWithMsTeams($exception);
        }

        return $response;
    }

    /**
     * Generate a colour code use for the card accent colour...
     *
     * @param string $type
     * @return string
     */
    private function getNotificationType($type = 'info') : string
    {
        switch ($type) {
            case 'error':
                return '#D8000C';
                break;
            default:
            case 'info':
                return '#31708f';
                break;
            case 'success':
                return '#4F8A10';
                break;
            case 'warning':
                return '#FEEFB3';
                break;
        }
    }
}
