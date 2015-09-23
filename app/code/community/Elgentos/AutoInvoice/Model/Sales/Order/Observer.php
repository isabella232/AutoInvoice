<?php

class Elgentos_AutoInvoice_Model_Sales_Order_Observer {

    public function salesOrderPlaceAfter($observer) {
        $order = $observer->getOrder();
        if(Mage::getStoreConfig('autoinvoice/general/trigger_sales_order_place_after', $order->getStoreId())) {
            $this->autoInvoice($order);
        }
    }

    public function salesOrderPaymentPay($observer) {
        $order = $observer->getPayment()->getOrder();
        if(Mage::getStoreConfig('autoinvoice/general/trigger_sales_order_payment_pay', $order->getStoreId())) {
               $this->autoInvoice($order);
        }
    }

    public function autoInvoice($order) {
        $processConditions = unserialize(Mage::getStoreConfig('autoinvoice/general/conditions_to_process'));
        $statusAfterProcessing = unserialize(Mage::getStoreConfig('autoinvoice/general/status_after_processing'));

        $state = $order->getState();
        $status = $order->getStatus();
        $paymentMethod = $order->getPayment()->getMethod();

        $canProcessInvoice = false;
        if($processConditions && is_array($processConditions)) {
            foreach ($processConditions as $condition) {
                if ($condition['order_state'] == $state && $condition['order_status'] == $status && $condition['payment_method'] == $paymentMethod) {
                    $canProcessInvoice = true;
                    break;
                }
            }
        }

        $ultimateStatus = $ultimateState = false;
        if($statusAfterProcessing && is_array($statusAfterProcessing)) {
            foreach ($statusAfterProcessing as $status) {
                if ($status['payment_method'] == $paymentMethod) {
                    $ultimateState = $status['order_state'];
                    $ultimateStatus = $status['order_status'];
                }
            }
        }

        if ($canProcessInvoice) {
            try {
                if($order->canInvoice() && Mage::getStoreConfig('autoinvoice/general/auto_invoice', $order->getStoreId())) {
                    $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
                    $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
                    $invoice->register();

                    $transactionSave = Mage::getModel('core/resource_transaction')
                        ->addObject($invoice)
                        ->addObject($invoice->getOrder());
                    $transactionSave->save();

                    $order->addStatusHistoryComment('Order invoice has automatically been created.', false);
                    $order->save();

                    if (Mage::getStoreConfig('sales_email/invoice/enabled', $order->getStoreId())) {
                        $invoice->sendEmail();
                    }
                }
            } catch(Exception $e) {
                Mage::log('Could not create invoice for ' . $order->getIncrementId() . ': ' . $e->getMessage(), null, 'elgentos_autoinvoice.log');
            }

            try {
                if($order->canShip() && Mage::getStoreConfig('autoinvoice/general/auto_shipment', $order->getStoreId())) {
                    $shipment = $order->prepareShipment();
                    $shipment->register();

                    $order->addStatusHistoryComment('Order shipment has automatically been created.', false);
                    $order->save();

                    $transactionSave = Mage::getModel('core/resource_transaction')
                        ->addObject($shipment)
                        ->addObject($shipment->getOrder());
                    $transactionSave->save();

                    if (Mage::getStoreConfig('sales_email/shipment/enabled', $order->getStoreId())) {
                        $shipment->sendEmail();
                    }
                }
            } catch(Exception $e) {
                Mage::log('Could not create invoice for ' . $order->getIncrementId() . ': ' . $e->getMessage(), null, 'elgentos_autoinvoice.log');
            }

            if($ultimateState && $ultimateStatus) {
                try {
                    $order->setState($ultimateState, $ultimateStatus, 'Status set to ' . $ultimateState . '/' . $ultimateStatus .' by Elgentos_AutoInvoice');
                } catch(Exception $e) {
                    Mage::log('Could not set state for ' . $order->getIncrementId() . ': ' . $e->getMessage(), null, 'elgentos_autoinvoice.log');
                }
            }
        }
    }

}
