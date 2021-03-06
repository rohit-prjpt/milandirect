<?php
/**

 * Sales Model

 *

 * @category    Exceedz

 * @package     Exceedz_Sales

 */
class Exceedz_Sales_Model_Quote extends  Balance_ConfigurableSimplePriceOverride_Model_Sales_Quote
{


   public function addProductAdvanced(Mage_Catalog_Model_Product $product, $request = null, $processMode = null)
    {

        if ($request === null) {
            $request = 1;
        }
        if (is_numeric($request)) {
            $request = new Varien_Object(array('qty'=>$request));
        }
        if (!($request instanceof Varien_Object)) {
            Mage::throwException(Mage::helper('sales')->__('Invalid request for adding product to quote.'));
        }

        $cartCandidates = $product->getTypeInstance(true)
            ->prepareForCartAdvanced($request, $product, $processMode);

        /**
         * Error message
         */
        if (is_string($cartCandidates)) {
            return $cartCandidates;
        }

        /**
         * If prepare process return one object
         */
        if (!is_array($cartCandidates)) {
            $cartCandidates = array($cartCandidates);
        }

        $parentItem = null;
        $errors = array();
        $items = array();
        foreach ($cartCandidates as $candidate) {
            // Child items can be sticked together only within their parent
            $stickWithinParent = $candidate->getParentProductId() ? $parentItem : null;
            $candidate->setStickWithinParent($stickWithinParent);
            $item = $this->_addCatalogProduct($candidate, $candidate->getCartQty());
            if($request->getResetCount() && !$stickWithinParent && $item->getId() === $request->getId()) {
                $item->setData('qty', 0);
            }
            
            // Code to add custom option price
            $actual_price = $product->getPrice();
            if(count($candidate->getOptions()) > 0){
                foreach ($candidate->getOptions() as $_option) {
                    if(array_key_exists($_option->getId(), $request->getOptions())) {
                        foreach($_option->getValues() as $_value) {
                            if(in_array($_value->getOptionTypeId(), $request->getOptions())) {
                                if($_value->getPriceType() == 'fixed')
                                    $actual_price += $_value->getPrice();
                            }
                        }
                    }
                }
            }
            
            $item->setData('actual_price', $actual_price);
             
            $items[] = $item;

            /**
             * As parent item we should always use the item of first added product
             */
            if (!$parentItem) {
                $parentItem = $item;
            }
            if ($parentItem && $candidate->getParentProductId() && !$item->getId()) {
                $item->setParentItem($parentItem);
            }

            /**
             * We specify qty after we know about parent (for stock)
             */
            $item->addQty($candidate->getCartQty());

            // collect errors instead of throwing first one
            if ($item->getHasError()) {
                $errors[] = $item->getMessage();
            }
        }
        if (!empty($errors)) {
            Mage::throwException(implode("\n", $errors));
        }

        Mage::dispatchEvent('sales_quote_product_add_after', array('items' => $items));

        return $item;
    }
}