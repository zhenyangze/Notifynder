<?php namespace Fenos\Notifynder\Handler;

use Fenos\Notifynder\Notifynder;
use Illuminate\Config\Repository;
use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Application;

/**
 * Class NotifynderHandler
 *
 * @package Fenos\Notifynder\Handler
 */
class NotifynderHandler
{
    /**
     * @var \Illuminate\Events\Dispatcher
     */
    private $event;

    /**
     * @var \Illuminate\Config\Repository
     */
    private $config;

    /**
     * @param Dispatcher                    $event
     * @param \Illuminate\Config\Repository $config
     */
    function __construct(Dispatcher $event, Repository $config)
    {
        $this->event = $event;
        $this->config = $config;
    }

    /**
     * It fire the event associated to the passed key,
     * trigging the listener method bound with
     *
     * @param Notifynder $notifynder
     * @param string     $key
     * @param            $category_name
     * @param array      $values
     * @return mixed|null
     */
    public function fire(Notifynder $notifynder, $key, $category_name, array $values = [])
    {
        $values['eventName'] = $key;
        $notificationsResult = $this->event->fire($key,[$values,$category_name,$notifynder]);

        if ($notificationsResult[0] !== false)
        {
            return $notifynder->send($notificationsResult[0]);
        }

        return null;
    }

    /**
     * Deletegate events to categories
     *
     * @param Notifynder $notifynder
     * @param            $data
     * @param array      $events
     * @return mixed
     */
    public function delegate(Notifynder $notifynder, $data,array $events)
    {
        foreach($events as $category => $event)
        {
            $data['eventName'] = $event;
            $infoNotification = $this->event->fire($event,[$data,$category,$notifynder]);
            $notifynder->send($infoNotification[0]);
        }
    }

    /**
     * Boot The listeners
     */
    public function boot()
    {
        $listeners = $this->config->get('notifynder::listeners.listeners');

        if (count($listeners) > 0)
        {
            foreach($listeners as $key => $listener)
            {
                $this->event->listen($key,$listener);
            }
        }
    }
}
