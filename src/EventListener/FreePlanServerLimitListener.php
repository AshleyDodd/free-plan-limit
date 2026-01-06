<?php

namespace Plugins\FreePlanServerLimit\EventListener;

use App\Core\Entity\User;
use App\Core\Entity\Server;
use App\Core\Event\Server\ServerAboutToBeCreatedEvent;
use App\Core\Service\Plugin\PluginSettingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FreePlanServerLimitListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PluginSettingService $pluginSettingService
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ServerAboutToBeCreatedEvent::class => 'onServerAboutToBeCreated',
        ];
    }

    public function onServerAboutToBeCreated(ServerAboutToBeCreatedEvent $event): void
    {
        // Get settings from database
        $freeProductIdsString = (string) $this->pluginSettingService->get('free-plan-server-limit', 'free_product_ids', '1');
        $maxFreeServers = (int) $this->pluginSettingService->get('free-plan-server-limit', 'max_free_servers', 1);
        $errorMessage = (string) $this->pluginSettingService->get('free-plan-server-limit', 'error_message', 'You have reached the maximum number of servers allowed on the free plan.');

        // Parse comma-separated product IDs into array of integers
        $freeProductIds = array_map('intval', array_filter(explode(',', $freeProductIdsString)));

        // If no product IDs configured or product not in free list, allow creation
        if (empty($freeProductIds) || !in_array($event->getProductId(), $freeProductIds, true)) {
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
            ->setParameter('freeIds', $freeProductIds)
            ->getQuery()
            ->getSingleScalarResult();

        if ($serverCount >= $maxFreeServers) {
            throw new \Exception($errorMessage);
        }
    }
}
