<?php

namespace Plugins\FreePlanLimit\EventListener;

use App\Core\Entity\User;
use App\Core\Entity\Server;
use App\Core\Event\Server\ServerAboutToBeCreatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FreePlanServerLimitListener implements EventSubscriberInterface
{
    private array $freeProductIds = [1];
    private int $maxFreeServers = 1;

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ServerAboutToBeCreatedEvent::class => 'onServerAboutToBeCreated',
        ];
    }

    public function onServerAboutToBeCreated(ServerAboutToBeCreatedEvent $event): void
    {
        if (!in_array($event->getProductId(), $this->freeProductIds, true)) {
            return;
        }

        $user = $this->entityManager->find(User::class, $event->getUserId());
        if (!$user) {
            return;
        }

        $qb = $this->entityManager->createQueryBuilder();

        $serverCount = $qb->select('COUNT(s.id)')
            ->from(Server::class, 's')
            ->join('s.serverProduct', 'sp')
            ->join('sp.originalProduct', 'p')
            ->where('s.user = :user')
            ->andWhere('s.deletedAt IS NULL')
            ->andWhere($qb->expr()->in('p.id', ':freeIds'))
            ->setParameter('user', $event->getUserId())
            ->setParameter('freeIds', $this->freeProductIds)
            ->getQuery()
            ->getSingleScalarResult();



        if ($serverCount >= $this->maxFreeServers) {
            throw new \Exception('You have reached the maximum number of servers allowed on the free plan.');
        }
    }

}
