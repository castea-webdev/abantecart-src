<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2014 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>

 UPGRADE NOTE:
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.
------------------------------------------------------------------------------*/
if (! defined ( 'DIR_CORE' ) || !IS_ADMIN) {
	header ( 'Location: static_pages/' );
}

class ControllerPagesToolUpdater extends AController {

	public $data;

	public function main() {

        //init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);

		$this->document->setTitle( $this->language->get('heading_title') );
		$this->document->resetBreadcrumbs();
		$this->document->addBreadcrumb( array ( 'href'=>$this->html->getSecureURL('index/home'),
		                                        'text'=>$this->language->get('text_home'),
		                                        'separator'=>FALSE  ));

		$this->document->addBreadcrumb( array ( 'href'=>$this->html->getSecureURL('tool/updater'),
		                                        'text'=>$this->language->get('heading_title'),
		                                        'separator'=>' :: '  ));

		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_type'] = $this->language->get('column_type');
		$this->data['column_category'] = $this->language->get('column_category');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_action'] = $this->language->get('column_action');
		$this->data['column_version'] = $this->language->get('column_version');
		$this->data['column_new_version'] = $this->language->get('column_new_version');
		$this->data['error'] = null;
		$this->data['text_nothing_todo'] = $this->data['success'] = '';
		
		if ( isset($this->session->data['success']) ) {
			$this->data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}
		
		if ( isset($this->session->data['error']) ) {
			$this->data['error'] = is_array($this->session->data['error']) ? implode("</br>",$this->session->data['error']) : $this->session->data['error'];
			unset($this->session->data['error']);
		}
		
		$this->data['extensions'] = array();
		//$this->model_tool_updater->check4updates();
		// updates.xml downloaded earlier in common/ant controller

		$this->data['extensions'] = $this->session->data['extension_updates'];
		
		if ( ! $this->data['extensions'] ) {
			$this->data['text_nothing_todo'] = $this->language->get('text_nothing_todo');
		}
		$this->view->assign('help_url', $this->gen_help_url() );
		$this->view->batchAssign($this->data);
		$this->processTemplate('pages/tool/updater.tpl');

        //update controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);
	}
}
?>