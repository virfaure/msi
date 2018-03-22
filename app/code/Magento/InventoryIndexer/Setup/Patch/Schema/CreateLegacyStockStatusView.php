<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolver;

/**
 * Creates MySQL View to use when Default Stock is used.
 */
class CreateLegacyStockStatusView implements SchemaPatchInterface
{
    private $schemaSetup;

    /**
     * @param SchemaSetupInterface $schemaSetup
     */
    public function __construct(
        SchemaSetupInterface $schemaSetup
    ) {
        $this->schemaSetup = $schemaSetup;
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->schemaSetup->startSetup();
        $legacyView = $this->schemaSetup->getTable(StockIndexTableNameResolver::LEGACY_VIEW_NAME);
        $cataloginventoryStockStatus = $this->schemaSetup->getTable('cataloginventory_stock_status');
        $catalogProductEntity = $this->schemaSetup->getTable('catalog_product_entity');
        $sql = "CREATE
                VIEW {$legacyView}
                  AS
                    SELECT
                      css.product_id,
                      css.website_id,
                      css.stock_id,
                      css.qty          AS quantity,
                      css.stock_status AS is_salable,
                      cpe.sku
                    FROM {$cataloginventoryStockStatus} AS css
                      INNER JOIN {$catalogProductEntity} AS cpe
                        ON css.product_id = cpe.entity_id;";
        $this->schemaSetup->getConnection()->query($sql);
        $this->schemaSetup->endSetup();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
