<?php

namespace NotificationChannels\MsTeams;

use NotificationChannels\MsTeams\Exceptions\CouldNotSendNotification;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;

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
            if (!$to = $notifiable->routeNotificationFor('msteams')) {
                throw CouldNotSendNotification::connectorWebHookUrlMissing();
            }

            $message->to($to);
        }

        $data = $message->toArray();

        if (! $url = Arr::get($data, 'url')) {
            return;
        }

        $payload = [];
        $payload['title'] = Arr::get($data, 'title');
        $payload['text'] = Arr::get($data, 'text');

        $payload['potentialAction'] = [];
        $payload['sections'] = [];

        $payload['potentialAction'] = collect(Arr::get($data, 'buttons', []))
            ->map(function($button){
                return (object)[
                    "@context" => "http://schema.org",
                    "@type" => "ViewAction",
                    "name" => $button['text'],
                    "target" => [
                        $button['url']
                    ]
                ];
            });

        $payload['sections'][]['images'] = collect(Arr::get($data, 'images', []))
            ->map(function($image){
                return (object)[
                    'image' => $image,
                ];
            });

        try {
            $response = $this->client->post($data['url'], [
                'json' => $payload,
            ]);
        }  catch (ClientException $exception) {
            throw CouldNotSendNotification::msTeamsRespondedWithAnError($exception);
        } catch (\Exception $exception) {
            throw CouldNotSendNotification::couldNotCommunicateWithMsTeams($exception);
        }

        return $response;
    }
}

