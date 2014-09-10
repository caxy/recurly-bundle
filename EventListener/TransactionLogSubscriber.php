<?php

namespace Caxy\Bundle\RecurlyBundle\EventListener;

use Caxy\Bundle\RecurlyBundle\Entity\Transaction;
use Caxy\Bundle\WebhookBundle\Event\WebhookEvent;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Class TransactionLogSubscriber
 *
 * @package Caxy\Bundle\RecurlyBundle\EventListener
 */
class TransactionLogSubscriber implements EventSubscriberInterface
{
    /**
     * @var ObjectManager
     */
    private $entityManager;

    /**
     * @var ObjectRepository
     */
    private $or;

    public function __construct(ManagerRegistry $managerRegistry, $className)
    {
        $this->entityManager = $managerRegistry->getManagerForClass($className);
        $this->or = $this->entityManager->getRepository($className);
    }

    /**
     * High priority event listener logs the transaction or stops
     * event propagation if it has already been or is being handled.
     *
     * @param WebhookEvent $event
     */
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

    /**
     * Low priority event listener marks the transaction as completed.
     *
     * @param WebhookEvent $event
     */
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
