<?php

declare(strict_types=1);

namespace Plugins\FreePlanServerLimit\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Initialize default settings for Free Plan Server Limit plugin.
 *
 * This migration creates default settings from the plugin's config_schema.
 * Settings are stored with context = 'plugin:free-plan-server-limit' to namespace them.
 */
final class Version20250104000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initialize default settings for Free Plan Server Limit plugin';
    }

    public function up(Schema $schema): void
    {
        // First, remove any existing settings to avoid duplicate key errors
        $this->addSql("DELETE FROM setting WHERE context = 'plugin:free-plan-server-limit'");

        // Settings are namespaced with context 'plugin:free-plan-server-limit'
        // Hierarchy: 'general' => 100

        // General settings (hierarchy: 100)
        $this->addSql("INSERT INTO setting (name, value, type, context, hierarchy, nullable) VALUES ('free_product_ids', '1', 'string', 'plugin:free-plan-server-limit', 100, false)");
        $this->addSql("INSERT INTO setting (name, value, type, context, hierarchy, nullable) VALUES ('max_free_servers', '1', 'integer', 'plugin:free-plan-server-limit', 100, false)");
        $this->addSql("INSERT INTO setting (name, value, type, context, hierarchy, nullable) VALUES ('error_message', 'You have reached the maximum number of servers allowed on the free plan.', 'string', 'plugin:free-plan-server-limit', 100, false)");
    }

    public function down(Schema $schema): void
    {
        // Remove all Free Plan Server Limit plugin settings on rollback
        $this->addSql("DELETE FROM setting WHERE context = 'plugin:free-plan-server-limit'");
    }
}
