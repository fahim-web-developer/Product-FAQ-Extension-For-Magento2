<?php
namespace Fahim\ProductFaq\Observer;

use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;

class SerializedProductFaq implements ObserverInterface
{
    const ATTR_PRODUCT_FAQ_CODE = 'product_faq'; //attribute code

    /**
     * @var  \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Constructor
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->request = $request;
    }

    public function execute(Observer $observer)
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $observer->getEvent()->getDataObject();
        $post = $this->request->getPost();
        $post = $post['product'];
        $frequencyData = isset($post[self::ATTR_PRODUCT_FAQ_CODE]) ? $post[self::ATTR_PRODUCT_FAQ_CODE] : '';
        $product->setProductFaq($frequencyData);
        $requiredParams = ['title','faq']; // PARAMS you defined in Frequency.php file
        if (is_array($frequencyData)) {
            $frequencyData = $this->removeEmptyArray($frequencyData, $requiredParams);
            $product->setProductFaq(json_encode($frequencyData));
        }
    }

    private function removeEmptyArray($discountData, $requiredParams) {
        $requiredParams = array_combine($requiredParams, $requiredParams);
        $reqCount = count($requiredParams);

        foreach ($discountData as $key => $values) {
            $values = array_filter($values);
            $inersectCount = count(array_intersect_key($values, $requiredParams));
            if ($reqCount != $inersectCount) {
                unset($discountData[$key]);
            }  
        }
        return $discountData;
    }
}