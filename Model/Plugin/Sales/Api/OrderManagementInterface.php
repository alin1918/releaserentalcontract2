<?php
/**
 * Copyright © 2018 SalesIgniter. All rights reserved.
 * See https://rentalbookingsoftware.com/license.html for license details.
 *
 */

namespace SalesIgniter\RentalContract\Model\Plugin\Sales\Api;

use SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface;
use SalesIgniter\Rental\Model\Product\Stock;
use SalesIgniter\Rental\Model\ReservationOrdersRepository;

class OrderManagementInterface {

	/**
	 * @var \SalesIgniter\Rental\Helper\Data $helperRental
	 */
	protected $helperRental;

	/**
	 * @var \SalesIgniter\Rental\Helper\Calendar
	 */
	private $helperCalendar;

	/**
	 * @var \Magento\Catalog\Model\Session
	 */
	private $catalogSession;
	/**
	 * @var \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface
	 */
	private $reservationOrdersRepository;
	/**
	 * @var \SalesIgniter\Rental\Model\Product\Stock
	 */
	private $stock;
	/**
	 * @var \SalesIgniter\Rental\Api\StockManagementInterface
	 */
	private $stockManagement;
	/**
	 * @var \Magento\Framework\Stdlib\DateTime\DateTime
	 */
	private $datetime;
	/**
	 * @var \Magento\Checkout\Model\Session
	 */
	private $checkoutSession;

	/**
	 * @param \SalesIgniter\Rental\Helper\Data                              $helperRental
	 * @param \SalesIgniter\Rental\Helper\Calendar                          $helperCalendar
	 * @param \SalesIgniter\Rental\Model\Product\Stock                      $stock
	 * @param \Magento\Checkout\Model\Session                               $checkoutSession
	 * @param \SalesIgniter\Rental\Api\ReservationOrdersRepositoryInterface $reservationOrdersRepository
	 * @param \SalesIgniter\Rental\Api\StockManagementInterface             $stockManagement
	 * @param \Magento\Framework\Stdlib\DateTime\DateTime                   $datetime
	 * @param \Magento\Catalog\Model\Session                                $catalogSession
	 *
	 * @internal param \SalesIgniter\Rental\Model\ResourceModel\ReservationOrders $reservationOrders
	 */
	public function __construct(
		\SalesIgniter\Rental\Helper\Data $helperRental,
		\SalesIgniter\Rental\Helper\Calendar $helperCalendar,
		Stock $stock,
		\Magento\Checkout\Model\Session $checkoutSession,
		ReservationOrdersRepositoryInterface $reservationOrdersRepository,
		\SalesIgniter\Rental\Api\StockManagementInterface $stockManagement,
		\Magento\Framework\Stdlib\DateTime\DateTime $datetime,
		\Magento\Catalog\Model\Session $catalogSession
	) {
		$this->helperRental                = $helperRental;
		$this->helperCalendar              = $helperCalendar;
		$this->catalogSession              = $catalogSession;
		$this->stock                       = $stock;
		$this->reservationOrdersRepository = $reservationOrdersRepository;
		$this->stockManagement             = $stockManagement;
		$this->datetime                    = $datetime;
		$this->checkoutSession             = $checkoutSession;
	}

	/**
	 * @param \Magento\Sales\Api\OrderManagementInterface $subject
	 * @param \Closure                                    $proceed
	 * @param \Magento\Sales\Api\Data\OrderInterface      $order
	 *
	 * @return \Magento\Sales\Api\Data\OrderInterface
	 */
	public function aroundPlace(
		\Magento\Sales\Api\OrderManagementInterface $subject,
		\Closure $proceed,
		\Magento\Sales\Api\Data\OrderInterface $order
	) {

		if ( $this->checkoutSession->getSignatureImage() ) {
			$order->setSignatureDate( $this->datetime->date() );
			$order->setSignatureImagefile( $this->checkoutSession->getSignatureImage() );
			$order->setSignatureText( $this->checkoutSession->getSignatureText() );
		}
		$returnOrder = $proceed( $order );

		return $returnOrder;
	}
}
