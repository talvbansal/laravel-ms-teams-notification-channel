# MS Teams Notifications Channel for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/talvbansal/laravel-ms-teams-notification-channel.svg?style=flat-square)](https://packagist.org/packages/talvbansal/laravel-ms-teams-notification-channel)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/talvbansal/laravel-ms-teams-notification-channel/master.svg?style=flat-square)](https://travis-ci.org/talvbansal/laravel-ms-teams-notification-channel)
[![StyleCI](https://styleci.io/repos/221256039/shield)](https://styleci.io/repos/221256039)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/:sensio_labs_id.svg?style=flat-square)](https://insight.sensiolabs.com/projects/:sensio_labs_id)
[![Quality Score](https://img.shields.io/scrutinizer/g/talvbansal/laravel-ms-teams-notification-channel.svg?style=flat-square)](https://scrutinizer-ci.com/g/talvbansal/laravel-ms-teams-notification-channel)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/talvbansal/laravel-ms-teams-notification-channel/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/talvbansal/laravel-ms-teams-notification-channel/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/talvbansal/laravel-ms-teams-notification-channel.svg?style=flat-square)](https://packagist.org/packages/talvbansal/laravel-ms-teams-notification-channel)

This package makes it easy to send notifications using [MS Teams](https://docs.microsoft.com/en-gb/microsoftteams/platform/task-modules-and-cards/cards/cards-reference#office-365-connector-card) with Laravel 5.5+ - 8.0.

## Contents

- [Installation](#installation)
	- [Setting up the Connector](#setting-up-the-connector)
- [Usage](#usage)
	- [Available Message methods](#available-message-methods)
- [Changelog](#changelog)
- [Testing](#testing)
- [Security](#security)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)


## Installation

You can install the package via composer:
```bash
composer require talvbansal/laravel-ms-teams-notification-channel
```

### Setting up the Connector

Please refer to [this article](https://docs.microsoft.com/en-gb/microsoftteams/platform/webhooks-and-connectors/how-to/add-incoming-webhook#add-an-incoming-webhook-to-a-teams-channel)  for setting up and adding a webhook connector to your MS Team's channel.

Then, configure your webhook url:

```php
// config/services.php
...
'ms-teams' => [
    'webhook_url' => env('MS_TEAMS_WEBHOOK_URL', 'WEBHOOK URL HERE')
],
...
```

You can change this to be whatever you like so if you have multiple teams you want to send notifications to you could do the following:

```php
// config/services.php
...
'ms-teams' => [
    'developers_webhook_url' => env('MS_TEAMS_DEVELOPERS_WEBHOOK_URL'),
    'helpdesk_webhook_url' => env('MS_TEAMS_HELPDESK_WEBHOOK_URL'),
],
...
```

As long as you remember to route the notifications to the correct team.

## Usage

You can now use the channel in your via() method inside the Notification class.


### Notifications
```php

use NotificationChannels\MsTeams\MsTeamsChannel;
use NotificationChannels\MsTeams\MsTeamsMessage;
use Illuminate\Notifications\Notification;

class InvoicePaid extends Notification
{
    public function via($notifiable)
    {
        return [MsTeamsChannel::class];
    }

    public function toMsTeams($notifiable)
    {
        $url = url('/invoice/' . $this->invoice->id);

        return MsTeamsMessage::create()
            // Optional recipient user id.
            ->to(config('services.ms-teams.webhook_url'))
            // Markdown supported.
            ->content("Hello there!\nYour invoice has been *PAID*")
            // (Optional) Inline Buttons
            ->button('View Invoice', $url)
            ->button('Download Invoice', $url)
            // (Optional) Supporting images
            ->image('https://source.unsplash.com/random/800x800?animals,nature&q='.now())
            ->image('https://source.unsplash.com/random/900x600?building,car&q='.now());
    }
}

```

### Routing the message
You can either send the notification by providing with the webhook url to the recipient to the to($url) method like shown in the above example or add a routeNotificationForMsTeams() method in your notifiable model:

```php
...
/**
 * Route notifications for the MS Teams channel.
 *
 * @return int
 */
public function routeNotificationForMsTeams()
{
    return config('services.ms-teams.webhook_url');
}
...
```

### Available Message methods

- `to($webhookUrl): (string)` Recipient's chat id.
- `title(''): (string)` Notification title, does not support markdown.
- `content(''): (string)` Notification message, supports markdown..
- `button($text, $url): (string)` Adds an inline "Call to Action" button. You can add as many as you want.
- `image($url): (string)` Adds an inline image from the given url. You can add as many as you want.
- `code($code): (string)` Adds a code fragment to the message. You can add as many as you want.
- `type($type): (string)` Change the accent of the card sent. You can choose between 'info', 'warning', 'error', 'success'

More examples and information about this package can be found [here.](https://www.talvbansal.me/blog/send-notifications-to-ms-teams-with-laravel/)

# Throttling notifications
If you find you're receiving too many of a given notification you can use [this package](https://github.com/talvbansal/laravel-throttled-failed-jobs) to help limit the number of notifications you get during a period where something has gone wrong.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Security

If you discover any security related issues, please email :author_email instead of using the issue tracker.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Talv Bansal](https://github.com/talvbansal)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

Please see [this repo](https://github.com/laravel-notification-channels/channels) for instructions on how to submit a channel proposal.
