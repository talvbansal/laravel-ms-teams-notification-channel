<?php

namespace NotificationChannels\MsTeams\Exceptions;

use GuzzleHttp\Exception\ClientException;

class CouldNotSendNotification extends \Exception
{
    /**
     * Thrown when there's a bad request and an error is responded.
     *
     * @param ClientException $exception
     *
     * @return static
     */
    public static function msTeamsRespondedWithAnError(ClientException $exception)
    {
        $statusCode = $exception->getResponse()->getStatusCode();
        $description = $exception->getMessage();

        return new static("Ms Teams responded with an error `{$statusCode} - {$description}`");
    }

    /**
     * Thrown when we're unable to communicate with Ms Teams.
     *
     * @return static
     */
    public static function couldNotCommunicateWithMsTeams($message)
    {
        return new static("The communication with Ms Teams failed. `{$message}`");
    }

    /**
     * Thrown when there is no chat id provided.
     *
     * @return static
     */
    public static function connectorWebHookUrlMissing()
    {
        return new static('Ms Teams a webhook url is missing.');
    }
}
