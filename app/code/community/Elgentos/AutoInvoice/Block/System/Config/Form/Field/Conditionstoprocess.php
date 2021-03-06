<?php

class Elgentos_AutoInvoice_Block_System_Config_Form_Field_Conditionstoprocess extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $_stateRenderer;
    protected $_statusRenderer;
    protected $_paymentRenderer;
    protected $_eventRenderer;

    protected function getStateRenderer()
    {
        if (!$this->_stateRenderer) {
            $this->_stateRenderer = $this->getLayout()->createBlock('autoinvoice/system_config_form_field_orderstate', '',
                array('is_render_to_js_template' => true));
            $this->_stateRenderer->setClass('order_state_select');
        }
        return $this->_stateRenderer;
    }

    protected function getStatusRenderer()
    {
        if (!$this->_statusRenderer) {
            $this->_statusRenderer = $this->getLayout()->createBlock('autoinvoice/system_config_form_field_orderstatus', '',
                array('is_render_to_js_template' => true));
            $this->_stateRenderer->setClass('order_status_select');
        }
        return $this->_statusRenderer;
    }

    protected function getPaymentRenderer()
    {
        if (!$this->_paymentRenderer) {
            $this->_paymentRenderer = $this->getLayout()->createBlock('autoinvoice/system_config_form_field_paymentmethod', '',
                array('is_render_to_js_template' => true));
            $this->_stateRenderer->setClass('payment_method_select');
        }
        return $this->_paymentRenderer;
    }

    protected function getEventRenderer()
    {
        if (!$this->_eventRenderer) {
            $this->_eventRenderer = $this->getLayout()->createBlock('autoinvoice/system_config_form_field_event', '',
                array('is_render_to_js_template' => true));
            $this->_eventRenderer->setClass('event_select');
        }
        return $this->_eventRenderer;
    }

    protected function _prepareToRender()
    {
        $this->addColumn('order_state', array(
            'label' => Mage::helper('autoinvoice')->__('Order state'),
            'renderer' => $this->getStateRenderer(),
        ));
        $this->addColumn('order_status', array(
            'label' => Mage::helper('autoinvoice')->__('Order status'),
            'renderer' => $this->getStatusRenderer(),
        ));
        $this->addColumn('payment_method', array(
            'label' => Mage::helper('autoinvoice')->__('Payment method'),
            'renderer' => $this->getPaymentRenderer(),
        ));
        $this->addColumn('event', array(
            'label' => Mage::helper('autoinvoice')->__('Trigger on event'),
            'renderer' => $this->getEventRenderer(),
        ));
        $this->_addAfter = false;
    }

    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->getStateRenderer()->calcOptionHash($row->getData('order_state')),
            'selected="selected"'
        );
        $row->setData(
            'option_extra_attr_' . $this->getStatusRenderer()->calcOptionHash($row->getData('order_status')),
            'selected="selected"'
        );
        $row->setData(
            'option_extra_attr_' . $this->getPaymentRenderer()->calcOptionHash($row->getData('payment_method')),
            'selected="selected"'
        );
        $row->setData(
            'option_extra_attr_' . $this->getPaymentRenderer()->calcOptionHash($row->getData('event')),
            'selected="selected"'
        );
    }
}
