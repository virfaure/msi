<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\MultiDimensionalIndexer\Alias;
use Magento\Framework\MultiDimensionalIndexer\IndexNameBuilder;
use Magento\Framework\MultiDimensionalIndexer\IndexNameResolver;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use Magento\InventoryIndexer\Indexer\InventoryIndexer;

/**
 * @inheritdoc
 */
class StockIndexTableNameResolver implements StockIndexTableNameResolverInterface
{
    const LEGACY_VIEW_NAME = 'inventory_stock_status';

    /**
     * @var IndexNameBuilder
     */
    private $indexNameBuilder;

    /**
     * @var IndexNameResolver
     */
    private $indexNameResolver;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var string
     */
    private $dimensionName;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @param IndexNameBuilder $indexNameBuilder
     * @param IndexNameResolver $indexNameResolver
     * @param ResourceConnection $resourceConnection
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param string $dimensionName
     */
    public function __construct(
        IndexNameBuilder $indexNameBuilder,
        IndexNameResolver $indexNameResolver,
        ResourceConnection $resourceConnection,
        DefaultStockProviderInterface $defaultStockProvider,
        string $dimensionName
    ) {
        $this->indexNameBuilder = $indexNameBuilder;
        $this->indexNameResolver = $indexNameResolver;
        $this->resourceConnection = $resourceConnection;
        $this->dimensionName = $dimensionName;
        $this->defaultStockProvider = $defaultStockProvider;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $stockId): string
    {
        if ($this->defaultStockProvider->getId() === $stockId) {
            $tableName = self::LEGACY_VIEW_NAME;
        } else {
            $indexName = $this->indexNameBuilder
                ->setIndexId(InventoryIndexer::INDEXER_ID)
                ->addDimension($this->dimensionName, (string)$stockId)
                ->setAlias(Alias::ALIAS_MAIN)
                ->build();

            $tableName = $this->indexNameResolver->resolveName($indexName);
        }

        return $this->resourceConnection->getTableName($tableName);
    }
}
