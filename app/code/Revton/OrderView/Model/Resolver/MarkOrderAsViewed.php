<?php
/**
 * @category  Revton
 * @package   Revton_OrderView
 * @author    Youssef Osama <youssef.osama.fareed@gmail.com>
 * @license   http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */
declare(strict_types=1);

namespace Revton\OrderView\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

class MarkOrderAsViewed implements ResolverInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param GetCustomer $getCustomer
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        GetCustomer              $getCustomer
    )
    {
        $this->orderRepository = $orderRepository;
        $this->getCustomer = $getCustomer;
    }

    /**
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     * @throws LocalizedException
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    )
    {
        if (!$context->getUserId()) {
            throw new GraphQlInputException(__('Customer not logged in.'));
        }

        $customer = $this->getCustomer->execute($context);
        $orderId = $args['order_id'];

        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $this->orderRepository->get($orderId);

        if ((int)$order->getCustomerId() !== (int)$customer->getId()) {
            throw new LocalizedException(__('Order does not belong to the current customer.'));
        }

        $order->setData('is_viewed', 1);
        $this->orderRepository->save($order);

        return [
            'success' => true,
            'message' => 'Order marked as viewed.',
            'order' => [
                'order_id' => $order->getIncrementId(),
                'entity_id' => $order->getEntityId(),
                'status' => $order->getStatus(),
                'state' => $order->getState(),
                'created_at' => $order->getCreatedAt(),
                'updated_at' => $order->getUpdatedAt(),
                'grand_total' => $order->getGrandTotal(),
                'subtotal' => $order->getSubtotal(),
                'tax_amount' => $order->getTaxAmount(),
                'shipping_amount' => $order->getShippingAmount(),
                'discount_amount' => $order->getDiscountAmount(),
                'currency_code' => $order->getOrderCurrencyCode(),
                'is_viewed' => $order->getData('is_viewed'),
                'customer_email' => $order->getCustomerEmail(),
                'customer_firstname' => $order->getCustomerFirstname(),
                'customer_lastname' => $order->getCustomerLastname(),
            ]
        ];
    }
}
