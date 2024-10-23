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
use Magento\Catalog\Api\Data\ProductInterface;

class CreateSimpleProduct implements DataPatchInterface
{
    /**
     * @var ProductInterfaceFactory
     */
    protected ProductInterfaceFactory $productFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * @var CategoryLinkManagementInterface
     */
    protected CategoryLinkManagementInterface $categoryLinkManagement;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var EavSetup
     */
    protected EavSetup $eavSetup;

    /**
     * @var State
     */
    protected State $appState;

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
     * @return void
     */
    public function apply() : void
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
    }

    /**
     * Create a product with the given attributes
     *
     * @param array $attributes
     * @return void
     */
    public function createProduct(array $attributes) : void
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
    private function productExists($sku) : bool
    {
        return $this->productFactory->create()->getIdBySku($sku) !== null;
    }

    /**
     * Initialize a product with the given attributes
     *
     * @param array $attributes
     * @return ProductInterface
     */
    private function initializeProduct(array $attributes) : ProductInterface
    {
        $product = $this->productFactory->create();
    
        foreach ($attributes as $key => $value) {
            $product->setData($key, $value);
        }
            
        return $product;
    }

    /**
     * Assign a product to the given categories
     *
     * @param string $sku
     * @param array $categoryIds
     * @return void
     */
    private function assignProductToCategories($sku, array $categoryIds) : void
    {
        $this->categoryLinkManagement->assignProductToCategories($sku, $categoryIds);
    }

    /**
     * Get dependencies
     *
     * @return array
     */
    public static function getDependencies() : array
    {
        return [];
    }

    /**
     * Get aliases
     *
     * @return array
     */
    public function getAliases() : array
    {
        return [];
    }
}
