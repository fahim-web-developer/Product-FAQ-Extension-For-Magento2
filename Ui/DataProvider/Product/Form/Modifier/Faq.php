<?php
namespace Fahim\ProductFaq\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\StockDataFilter;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Textarea;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Modal;


class Faq extends AbstractModifier
{
    const PRODUCT_FAQ_FIELD = 'product_faq'; //attribute code

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var array
     */
    private $meta = [];

    /**
     * @var string
     */
    protected $scopeName;   

    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        $scopeName = ''
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->scopeName = $scopeName;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $fieldCode = self::PRODUCT_FAQ_FIELD;

        $model = $this->locator->getProduct();
        $modelId = $model->getId();

        $faqData = $model->getProductFaq();

        if ($faqData) {
            $faqData = json_decode($faqData, true);
            $path = $modelId . '/' . self::DATA_SOURCE_DEFAULT . '/'. self::PRODUCT_FAQ_FIELD;
            $data = $this->arrayManager->set($path, $data, $faqData);
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;
        $this->initProductFaqFields();
        return $this->meta;
    }


    protected function initProductFaqFields()
    {
        $faqPath = $this->arrayManager->findPath(
            self::PRODUCT_FAQ_FIELD,
            $this->meta,
            null,
            'children'
        );

        if ($faqPath) {
            $this->meta = $this->arrayManager->merge(
                $faqPath,
                $this->meta,
                $this->initFaqFieldStructure($faqPath)
            );
            $this->meta = $this->arrayManager->set(
                $this->arrayManager->slicePath($faqPath, 0, -3)
                . '/' . self::PRODUCT_FAQ_FIELD,
                $this->meta,
                $this->arrayManager->get($faqPath, $this->meta)
            );
            $this->meta = $this->arrayManager->remove(
                $this->arrayManager->slicePath($faqPath, 0, -2),
                $this->meta
            );
        }

        return $this;
    }   


    protected function initFaqFieldStructure($faqPath)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'dynamicRows',
                        'label' => __('Related product'),
                        'renderDefaultRecord' => false,
                        'recordTemplate' => 'record',
                        'dataScope' => '',
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'disabled' => false,
                        'sortOrder' =>
                            $this->arrayManager->get($faqPath . '/arguments/data/config/sortOrder', $this->meta),
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => '',
                            ],
                        ],
                    ],
                    'children' => [
                        'title' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Input::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Title'),
                                        'dataScope' => 'title',
                                        'require' => '1',
                                    ],
                                ],
                            ],
                        ],
                        'faq' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Textarea::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Url'),
                                        'dataScope' => 'faq',
                                        'require' => '1',
                                    ],
                                ],
                            ],
                        ],                        
                        'actionDelete' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'actionDelete',
                                        'dataType' => Text::NAME,
                                        'label' => '',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

}
?>