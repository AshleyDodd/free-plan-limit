<?php

namespace Plugins\FreePlanServerLimit;

use App\Core\Service\Plugin\PluginSettingService;
use Psr\Log\LoggerInterface;

/**
 * Bootstrap class for Free Plan Server Limit plugin initialization.
 */
class Bootstrap
{
    public function __construct(
        private readonly PluginSettingService $pluginSettingService,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * Initialize the plugin.
     *
     * This method is called once when the plugin is enabled.
     *
     * @return void
     */
    public function initialize(): void
    {
        $this->logger->info('Free Plan Server Limit plugin: Bootstrap initialization started');

        try {
            // Ensure default settings exist (fallback if migration didn't run)
            $this->ensureDefaultSettings();

            // Perform any other setup tasks
            $this->setupPluginDefaults();

            $this->logger->info('Free Plan Server Limit plugin: Bootstrap initialization completed successfully');
        } catch (\Exception $e) {
            $this->logger->error('Free Plan Server Limit plugin: Bootstrap initialization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Ensure default settings exist (fallback if migration didn't run).
     *
     * @return void
     */
    private function ensureDefaultSettings(): void
    {
        // Check if settings already exist
        $existingValue = $this->pluginSettingService->get('free-plan-server-limit', 'max_free_servers');
        
        if ($existingValue === null) {
            $this->logger->info('Free Plan Server Limit plugin: Creating default settings (migration may not have run)');
            
            // Create default settings if they don't exist
            $this->pluginSettingService->set('free-plan-server-limit', 'free_product_ids', '1');
            $this->pluginSettingService->set('free-plan-server-limit', 'max_free_servers', 1);
            $this->pluginSettingService->set('free-plan-server-limit', 'error_message', 'You have reached the maximum number of servers allowed on the free plan.');
            
            $this->logger->info('Free Plan Server Limit plugin: Default settings created successfully');
        }
    }

    /**
     * Setup plugin-specific defaults.
     *
     * @return void
     */
    private function setupPluginDefaults(): void
    {
        // Log current configuration
        $maxServers = (int) $this->pluginSettingService->get('free-plan-server-limit', 'max_free_servers', 1);
        $productIds = (string) $this->pluginSettingService->get('free-plan-server-limit', 'free_product_ids', '1');

        $this->logger->info('Free Plan Server Limit plugin: Configuration loaded', [
            'max_free_servers' => $maxServers,
            'free_product_ids' => $productIds,
        ]);
    }

    /**
     * Cleanup method called when plugin is disabled (optional).
     *
     * @return void
     */
    public function cleanup(): void
    {
        $this->logger->info('Free Plan Server Limit plugin: Bootstrap cleanup started');

        // Perform cleanup tasks if needed
        // Don't delete user data or settings - just cleanup temporary resources

        $this->logger->info('Free Plan Server Limit plugin: Bootstrap cleanup completed');
    }
}
