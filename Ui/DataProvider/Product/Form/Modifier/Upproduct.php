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


class Upproduct extends AbstractModifier
{
    const UP_PRODUCT_FIELD = 'up_product'; //attribute code

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
        $fieldCode = self::UP_PRODUCT_FIELD;

        $model = $this->locator->getProduct();
        $modelId = $model->getId();

        $upData = $model->getUpProduct();

        if ($upData) {
            $upData = json_decode($upData, true);
            $path = $modelId . '/' . self::DATA_SOURCE_DEFAULT . '/'. self::UP_PRODUCT_FIELD;
            $data = $this->arrayManager->set($path, $data, $upData);
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;
        $this->initUpProductFields();
        return $this->meta;
    }


    protected function initUpProductFields()
    {
        $upPath = $this->arrayManager->findPath(
            self::UP_PRODUCT_FIELD,
            $this->meta,
            null,
            'children'
        );

        if ($upPath) {
            $this->meta = $this->arrayManager->merge(
                $upPath,
                $this->meta,
                $this->initUpFieldStructure($upPath)
            );
            $this->meta = $this->arrayManager->set(
                $this->arrayManager->slicePath($upPath, 0, -3)
                . '/' . self::UP_PRODUCT_FIELD,
                $this->meta,
                $this->arrayManager->get($upPath, $this->meta)
            );
            $this->meta = $this->arrayManager->remove(
                $this->arrayManager->slicePath($upPath, 0, -2),
                $this->meta
            );
        }

        return $this;
    }   


    protected function initUpFieldStructure($upPath)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'dynamicRows',
                        'label' => __('Up Sell product'),
                        'renderDefaultRecord' => false,
                        'recordTemplate' => 'record',
                        'dataScope' => '',
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'disabled' => false,
                        'sortOrder' =>
                            $this->arrayManager->get($upPath . '/arguments/data/config/sortOrder', $this->meta),
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
                        'up' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Textarea::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Url'),
                                        'dataScope' => 'up',
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