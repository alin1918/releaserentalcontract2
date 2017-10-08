<?php
/**
 * Copyright © 2015 CedCommerce. All rights reserved.
 */

namespace SalesIgniter\RentalContract\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class Files extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $filesystem;

    protected $coreRegistry;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\App\ConfigInterface $backendConfig,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->filesystem = $filesystem;
        parent::__construct($context);
    }

    /**
     * Returns full path to order contract like /media/pdfs/contract_23.pdf
     *
     * @param $order
     *
     * @return string
     */

    public function getOrderPdfPath($order)
    {
        return $this->getPdfFilePath() . $this->getContractFilename($order);
    }

    public function getPdfFilePath()
    {
        return $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath() . 'pdfs' . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns contract file name, like contract_23.pdf
     *
     * @param $order
     *
     * @return string
     */

    public function getContractFilename($order)
    {
        /** @var $order \Magento\Sales\Model\Order */
        return 'contract_' . $order->getId() . '.pdf';
    }

    public function getSignaturePath()
    {
        return $this->getPdfFilePath() . 'signatures' . DIRECTORY_SEPARATOR;
    }

    /**
     * Return full quote signature path like /media/pdfs/signatures/signature_quote32.png
     *
     * @param $quoteId
     *
     * @return string
     * @internal param $quote
     *
     */

    public function getQuoteSignaturePath($quoteId)
    {
        if (is_object($quoteId)) {
            $quoteId = $quoteId->getId();
        }
        return $this->getSignaturePath() . 'signature_' . 'quote' . $quoteId . '.png';
    }

    public function getOrderSignaturePath($order)
    {
        return $this->getSignaturePath() . $this->getOrderFilename($order);
    }

    /**
     * Returns the order signature filename, like signature_order22.png
     *
     * @param $order
     *
     * @return string
     */

    public function getOrderFilename($order)
    {
        return 'signature_' . 'order' . $order->getId() . '.png';
    }

    /**
     * Returns full order signature file path like /media/pdfs/signatures/signature_order22.png
     *
     * @param $order
     *
     * @return string
     */

    public function getContractSignatureImageFile($order)
    {
        return $this->getSignaturePath() . $order->getSignatureImagefile();
    }

    public function decodeImage($signatureimage)
    {
        $signatureArray = explode(',', $signatureimage);

        if (is_array($signatureArray) && count($signatureArray) === 2) {
            $encoded_image = $signatureArray[1];
            return base64_decode($encoded_image);
        }
        return '';
    }

    public function saveImage($path, $image)
    {
        file_put_contents($path, $image);
    }

    public function createDirIfNotExists($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
    }
}
