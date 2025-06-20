<?php declare(strict_types=1);
/**
 * @category  Revton
 * @package   Revton_OrderView
 * @author    Youssef Osama <youssef.osama.fareed@gmail.com>
 * @license   http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Revton\OrderView\Setup\Patch\Schema;


use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\DB\Ddl\Table;

class AddIsViewedToSalesOrder implements SchemaPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function apply()
    {
        $setup = $this->moduleDataSetup;
        $setup->startSetup();

        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'is_viewed',
            [
                'type' => Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Customer Viewed Order'
            ]
        );

        $setup->endSetup();
        return $this;
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }
}
