<?php

namespace Caxy\Bundle\RecurlyBundle\EventListener;

use Caxy\Bundle\RecurlyBundle\Entity\Transaction;
use Caxy\Bundle\WebhookBundle\Event\WebhookEvent;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TransactionLogSubscriber implements EventSubscriberInterface
{
    private $entityManager;

    private $or;

    public function __construct(ManagerRegistry $managerRegistry, $className)
    {
        $this->entityManager = $managerRegistry->getManagerForClass($className);
        $this->or = $this->entityManager->getRepository($className);
    }

    public function onWebhookPre(WebhookEvent $event)
    {
        $payload = $event->getPayload();

        $notification = new \Recurly_PushNotification($payload);

        if ($notification->transaction) {
            $id = (string) $notification->transaction->id;
            if ($transaction = $this->or->find($id)) {
                // The same transaction has already been processed or is being processed.
                $event->stopPropagation();
            } else {
                $date = \DateTime::createFromFormat(\DateTime::ISO8601, $notification->transaction->date);
                $transaction = new Transaction($id, $date);
                $this->entityManager->persist($transaction);
                $this->entityManager->flush($transaction);
            }
        }
    }

    public function onWebhookPost(WebhookEvent $event)
    {
        $payload = $event->getPayload();

        $notification = new \Recurly_PushNotification($payload);

        if ($notification->transaction) {
            $id = (string) $notification->transaction->id;
            if ($transaction = $this->or->find($id)) {
                $transaction->setSemaphore(false);
                $this->entityManager->flush($transaction);
            }
        }
    }

    /**
     * @return array|void
     */
    public static function getSubscribedEvents()
    {
        return array(
          'webhook.recurly' => array(
            array('onWebhookPre', 255),
            array('onWebhookPost', -255)
          )
        );
    }
}
