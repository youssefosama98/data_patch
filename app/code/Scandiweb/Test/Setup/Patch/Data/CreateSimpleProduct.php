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
    private $productFactory;
    private $productRepository;
    private $categoryLinkManagement;
    private $storeManager;
    private $eavSetup;
    private $appState;

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

    public function apply()
    {
        $this->appState->emulateAreaCode('adminhtml', [$this, 'createProduct']);
        return $this;
    }

    public function createProduct()
    {
        $product = $this->productFactory->create();
        if ($product->getIdBySku('simple-product')) {
            return;
        }

        $product->setSku('simple-product')
            ->setName('Simple Product')
            ->setTypeId(Type::TYPE_SIMPLE)
            ->setPrice(10)
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED)
            ->setStockData(['is_in_stock' => 1, 'qty' => 100])
            ->setAttributeSetId($this->eavSetup->getAttributeSetId(Product::ENTITY, 'Default'))
            ->setWebsiteIds([$this->storeManager->getStore()->getWebsiteId()]);

        $product = $this->productRepository->save($product);

        $this->categoryLinkManagement->assignProductToCategories($product->getSku(),
            [2]);
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
