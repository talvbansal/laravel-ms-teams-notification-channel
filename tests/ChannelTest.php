<?php

namespace NotificationChannels\MsTeams\Test;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use NotificationChannels\MsTeams\Exceptions\CouldNotSendNotification;
use NotificationChannels\MsTeams\MsTeamsChannel;
use NotificationChannels\MsTeams\MsTeamsMessage;
use Orchestra\Testbench\TestCase;

class ChannelTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_send_a_notification()
    {
        $mock = new MockHandler([
            new Response(200),
        ]);

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $channel = new MsTeamsChannel($client);
        $response = $channel->send(new TestNotifiable(), new TestNotification());
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_it_could_not_send_the_notification()
    {
        $this->expectException(CouldNotSendNotification::class);

        $channel = new MsTeamsChannel(new Client());
        $channel->send(new TestNotifiableNoRoute(), new TestNotification());
    }
}

class TestNotifiable
{
    use Notifiable;

    /**
     * @return int
     */
    public function routeNotificationForMsTeams()
    {
        return 'https://ms-teams-url.com';
    }
}

class TestNotifiableNoRoute
{
    use Notifiable;
}

class TestNotification extends Notification
{
    public function toMsTeams($notifiable)
    {
        return MsTeamsMessage::create()
            ->title('Hello world')
            ->content('Here is an example of some text');
    }
}
