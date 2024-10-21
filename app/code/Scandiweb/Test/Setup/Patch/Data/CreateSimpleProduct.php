<?php

namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\State;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\CategoryLinkManagementInterface;

class CreateSimpleProduct implements DataPatchInterface
{
    /**
     * @var ProductInterfaceFactory
     */
    private $productFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CategoryLinkManagementInterface
     */
    private $categoryLinkManagement;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * @var State
     */
    private $appState;

    /**
     * Constructor
     *
     * @param ProductInterfaceFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param EavSetup $eavSetup
     * @param CategoryLinkManagementInterface $categoryLinkManagement
     * @param State $appState
     */
    public function __construct(
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        EavSetup $eavSetup,
        CategoryLinkManagementInterface $categoryLinkManagement,
        State $appState
    ) {
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->storeManager = $storeManager;
        $this->eavSetup = $eavSetup;
        $this->appState = $appState;
    }

    /**
     * Apply the data patch
     *
     * @return $this
     */
    public function apply()
    {
        $this->appState->emulateAreaCode('adminhtml', [$this, 'createProduct'], [
            [
                'sku' => 'Egyptian-Cotton-Sheet',
                'name' => 'Egyptian Cotton Sheet',
                'type_id' => Type::TYPE_SIMPLE,
                'price' => 10,
                'visibility' => Visibility::VISIBILITY_BOTH,
                'status' => Status::STATUS_ENABLED,
                'stock_data' => ['is_in_stock' => 1, 'qty' => 100],
                'attribute_set_id' => $this->eavSetup->getAttributeSetId(Product::ENTITY, 'Default'),
                'website_ids' => [$this->storeManager->getStore()->getWebsiteId()],
                'category_ids' => [2]
            ]
        ]);
        return $this;
    }

    /**
     * Create a product with the given attributes
     *
     * @param array $attributes
     * @return void
     */
    public function createProduct(array $attributes)
    {
        if ($this->productExists($attributes['sku'])) {
            return;
        }

        $product = $this->initializeProduct($attributes);
        $product = $this->productRepository->save($product);
        $this->assignProductToCategories($product->getSku(), $attributes['category_ids']);
    }

    /**
     * Check if a product with the given SKU exists
     *
     * @param string $sku
     * @return bool
     */
    private function productExists($sku)
    {
        return $this->productFactory->create()->getIdBySku($sku) !== null;
    }

    /**
     * Initialize a product with the given attributes
     *
     * @param array $attributes
     * @return Product
     */
    private function initializeProduct(array $attributes)
    {
        $product = $this->productFactory->create();
        $product->setSku($attributes['sku'])
            ->setName($attributes['name'])
            ->setTypeId($attributes['type_id'])
            ->setPrice($attributes['price'])
            ->setVisibility($attributes['visibility'])
            ->setStatus($attributes['status'])
            ->setStockData($attributes['stock_data'])
            ->setAttributeSetId($attributes['attribute_set_id'])
            ->setWebsiteIds($attributes['website_ids']);

        return $product;
    }

    /**
     * Assign a product to the given categories
     *
     * @param string $sku
     * @param array $categoryIds
     * @return void
     */
    private function assignProductToCategories($sku, array $categoryIds)
    {
        $this->categoryLinkManagement->assignProductToCategories($sku, $categoryIds);
    }

    /**
     * Get dependencies
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get aliases
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }
}
