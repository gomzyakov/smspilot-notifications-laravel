<?php

namespace AvtoDev\SmsPilotNotifications;

use InvalidArgumentException;
use Illuminate\Notifications\Notification;
use AvtoDev\SmsPilotNotifications\Messages\SmsPilotMessage;
use AvtoDev\SmsPilotNotifications\ApiClient\Responses\MessageSentResponse;
use AvtoDev\SmsPilotNotifications\Exceptions\MissingNotificationRouteException;
use AvtoDev\SmsPilotNotifications\ApiClient\ApiClientInterface as SmsPilotApiClient;

/**
 * Class SmsPilotChannel.
 *
 * Channel fo a working with SMS Pilot service.
 */
class SmsPilotChannel
{
    /**
     * @var SmsPilotApiClient
     */
    protected $api_client;

    /**
     * Create a new SMS Pilot channel instance.
     *
     * @param SmsPilotApiClient $api_client
     *
     * @return void
     */
    public function __construct(SmsPilotApiClient $api_client)
    {
        $this->api_client = $api_client;
    }

    /**
     * Send the given notification.
     *
     * @param mixed        $notifiable
     * @param Notification $notification
     *
     * @throws MissingNotificationRouteException
     * @throws InvalidArgumentException
     *
     * @return MessageSentResponse|null
     */
    public function send($notifiable, Notification $notification)
    {
        if (! method_exists($notification, $route = 'toSmsPilot')) {
            throw new MissingNotificationRouteException(sprintf('Missing notification route "%s"', $route));
        }

        /** @var $message SmsPilotMessage */
        if (! ($message = $notification->{$route}($notifiable) instanceof SmsPilotMessage)) {
            throw new InvalidArgumentException(sprintf(
                'Route "%s" must returns object with instance of "%s"',
                $route,
                SmsPilotMessage::class
            ));
        }

        return $this->api_client->send($message);
    }
}
