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

if (defined('IS_DEMO') && IS_DEMO ) {
	header ( 'Location: static_pages/demo_mode.php' );
}

class ControllerPagesSaleContact extends AController {
	public $data = array();
	private $error = array();
	 
	public function main() {

        //init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);

		$this->document->setTitle( $this->language->get('heading_title') );
		$this->loadModel('sale/customer');

		$this->data['token'] = $this->session->data['token'];
		
 		if (isset($this->error)) {
 			$this->data['error_warning'] = '';
 			foreach($this->error as $message) {
 				$this->data['error_warning'] .= $message . '<br/>';
 			}
		} else {
			$this->data['error_warning'] = '';
		}
		
 		if (isset($this->error['subject'])) {
			$this->data['error_subject'] = $this->error['subject'];
		} else {
			$this->data['error_subject'] = '';
		}
	 	
		if (isset($this->error['message'])) {
			$this->data['error_message'] = $this->error['message'];
		} else {
			$this->data['error_message'] = '';
		}	

		if (isset($this->error['recipient'])) {
			$this->data['error_recipient'] = $this->error['recipient'];
		} else {
			$this->data['error_recipient'] = '';
		}	

  		$this->document->initBreadcrumb( array (
       		'href'      => $this->html->getSecureURL('index/home'),
       		'text'      => $this->language->get('text_home'),
      		'separator' => FALSE
   		 ));
   		$this->document->addBreadcrumb( array ( 
       		'href'      => $this->html->getSecureURL('sale/contact'),
       		'text'      => $this->language->get('heading_title'),
      		'separator' => ' :: '
   		 ));
				
		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}
				
		$this->data['action'] = $this->html->getSecureURL('sale/contact');
    	$this->data['cancel'] = $this->html->getSecureURL('sale/contact');

		if (isset($this->request->post['store_id'])) {
			$this->data['store_id'] = $this->request->post['store_id'];
		} else {
			$this->data['store_id'] = 0;
		}

		
		$this->data['customers'] = array();
		if (isset($this->request->post['to']) && $this->request->post['to']) {					
			foreach ($this->request->post['to'] as $customer_id) {
				$customer_info = $this->model_sale_customer->getCustomer($customer_id);
					
				if ($customer_info) {
					$this->data['customers'][] = array(
						'customer_id' => $customer_info['customer_id'],
						'name'        => $customer_info['firstname'] . ' ' . $customer_info['lastname'] . ' (' . $customer_info['email'] . ')'
					);
				}
			}
		}

		$this->loadModel('catalog/product');
		
		$this->data['products'] = $this->model_catalog_product->getProducts();
		
		if (isset($this->request->post['product'])) {
			$this->data['product'] = $this->request->post['product'];
		} else {
			$this->data['product'] = '';
		}
		
		if (isset($this->request->post['recipient'])) {
			$this->data['recipient'] = $this->request->post['recipient'];
		} else {
			$this->data['recipient'] = '';
		}
		
		if (isset($this->request->post['subject'])) {
			$this->data['subject'] = $this->request->post['subject'];
		} else {
			$this->data['subject'] = '';
		}
		
		if (isset($this->request->post['message'])) {
			$this->data['message'] = $this->request->post['message'];
		} else {
			$this->data['message'] = '';
		}
		
		$this->loadModel('catalog/category');
		$categories = $this->model_catalog_category->getCategories(0);
		$this->data['categories'][0] = $this->language->get('text_select_category');
		foreach($categories as $category){
			$this->data['categories'][$category['category_id']] = $category['name'];
		}

		$form = new AForm('ST');
		$form->setForm(array(
		    'form_name' => 'mail_form',
			'update' => $this->data['update']
	    ));

		$this->data['form_open'] = $form->getFieldHtml(
			array(  'type' => 'form',
				    'name' => 'mail_form',
					'action' => $this->html->getSecureURL('sale/contact/sendNewsletter')
			));

		$this->loadModel('setting/store');
		
		$stores = array(0 => $this->language->get('text_default'));
		$allstores = $this->model_setting_store->getStores();
		if($allstores){
			foreach( $allstores as $item){
				$stores[$item['store_id']] = $item['name'];
			}
		}


        $this->data['form']['store'] = $form->getFieldHtml(array(
		    'type' => 'selectbox',
		    'name' => 'store_id',
	        'value'=> $this->data['store_id'],
	        'options' => $stores,
	        'style' => 'large-field'
	    ));

		$subscribers_count = $this->model_sale_customer->getCustomersByNewsletter();
		$subscribers_count = sizeof($subscribers_count);

		$customers_count = $this->model_sale_customer->getTotalCustomers();

        $this->data['form']['recipient'] = $form->getFieldHtml(array(
		    'type' => 'selectbox',
		    'name' => 'recipient',
	        'value'=> $this->data['recipient'],
	        'options' => array(''=> $this->language->get('text_custom_send'),
	                           'newsletter'=> $this->language->get('text_newsletter') .' '. sprintf($this->language->get('text_total_to_be_sent'),$subscribers_count) ,
	                           'customer'=> $this->language->get('text_customer') .' '. sprintf($this->language->get('text_total_to_be_sent'),$customers_count)),
			'style' => 'large-field'
	    ));

        $this->data['form']['search'] = $form->getFieldHtml(array(
		    'type' => 'input',
		    'name' => 'search',
	        'value'=> '',
			'placeholder' => $this->language->get('text_search_placeholder'),
			'style' => 'large-field'
	    ));

		$this->data['form']['category'] = $form->getFieldHtml(array(
			'type' => 'selectbox',
			'name' => 'category_id',
			'value' => 0,
			'options' => $this->data['categories'],
			'style' => 'large-field'
		));

        $this->data['form']['subject'] = $form->getFieldHtml(array(
		    'type' => 'input',
		    'name' => 'subject',
	        'value'=> $this->request->post['subject']
	    ));
        $this->data['form']['submit'] = $form->getFieldHtml(array(
		    'type' => 'button',
		    'name' => 'submit',
		    'text' => $this->language->get('button_go'),
		    'style' => 'button1',
	    ));
		$this->data['form']['cancel'] = $form->getFieldHtml(array(
		    'type' => 'button',
		    'name' => 'cancel',
		    'text' => $this->language->get('button_cancel'),
		    'style' => 'button2',
	    ));

        $this->view->batchAssign( $this->data );


        $this->view->assign('category_products', $this->html->getSecureURL('product/product/category'));
        $this->view->assign('customers_list', $this->html->getSecureURL('user/customers'));
        $this->view->assign('rl', $this->html->getSecureURL('common/resource_library', '&object_name=&object_id&type=image&mode=url'));
		$this->view->assign('help_url', $this->gen_help_url('mail') );
		$this->view->assign('language_code', $this->session->data['language']);
		
		$this->processTemplate('pages/sale/contact.tpl' );

        //update controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);
	}

	public function sendNewsletter(){

        //init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);
		// this method can process only posting.
		if ($this->request->server['REQUEST_METHOD'] != 'POST'){
			$this->redirect($this->html->getSecureURL('sale/contact'));
		}

		if (!$this->_validate()) {
			$this->main();
			return null;
		}

		$this->loadModel('sale/customer');
		$this->loadModel('setting/store');
		$store_info = $this->model_setting_store->getStore($this->request->post['store_id']);
		if ($store_info) {
		    $store_name = $store_info['store_name'];
		} else {
		    $store_name = $this->config->get('store_name');
		}

		$emails = array();

		// All customers by group
		if (isset($this->request->post['recipient'])) {
		    $customers = $results = array();
		    if($this->request->post['recipient'] == 'newsletter'){
		    		$results = $this->model_sale_customer->getCustomersByNewsletter();
		    }else if($this->request->post['recipient'] == 'customer'){
		    		$results = $this->model_sale_customer->getCustomers();
		    }
		    foreach ($results as $result) {
		    	$customer_id = $result['customer_id'];
		    	$emails[$customer_id] = $customers[$customer_id] = trim($result['email']);
		    }
		}

		// All customers by name/email
		if (isset($this->request->post['to']) && $this->request->post['to']) {
		    foreach ($this->request->post['to'] as $customer_id) {
		    	$customer_info = $this->model_sale_customer->getCustomer($customer_id);
		    	if ($customer_info) {
		    		$emails[] = trim($customer_info['email']);
		    	}
		    }
		}

		// All customers by product
		if (isset($this->request->post['product'])) {
		    foreach ($this->request->post['product'] as $product_id) {
		    	$results = $this->model_sale_customer->getCustomersByProduct($product_id);
		    	if( $customers ){
		    		$emails = array();
		    	}
		    	foreach ($results as $result) {
		    		if($customers && in_array($result['email'],$customers)){
		    			$emails[] = trim($result['email']);
		    		}
		    	}
		    }
		}

		// Prevent Duplicates
		$emails = array_unique($emails);

		if ($emails) {
		    $message_html  = '<html dir="ltr" lang="en">' . "\n";
		    $message_html .= '<head>' . "\n";
		    $message_html .= '<title>' . $this->request->post['subject'] . '</title>' . "\n";
		    $message_html .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";
		    $message_html .= '</head>' . "\n";
		    $message_html .= '<body>%MESSAGEBODY%</body>' . "\n";
		    $message_html .= '</html>' . "\n";

		    foreach ($emails as $email) {
		    	$mail = new AMail( $this->config );
		    	$mail->setTo($email);
		    	$mail->setFrom($this->config->get('store_main_email'));
		    	$mail->setSender($store_name);
		    	$mail->setSubject($this->request->post['subject']);

		    	$message_body = $this->request->post['message'];
		    	if($this->request->post['recipient'] == 'newsletter'){
		    		if(($customer_id = array_search($email,$customers))){
		    			$message_body .= "\n\n<br><br>".sprintf($this->language->get('text_unsubscribe'),
		    									 $email,
		    									 $this->html->getCatalogURL('account/unsubscribe','&email='.$email.'&customer_id='.$customer_id));
		    		}
		    	}
		    	$message_body = html_entity_decode($message_body, ENT_QUOTES, 'UTF-8');
		    	$message_html = str_replace('%MESSAGEBODY%',$message_body,$message_html);

		    	$mail->setHtml($message_html);
		    	$mail->send();
		    	if($mail->error){
		    		$this->error['warning'] = 'Error: Emails does not sent! Please see error log for details.';
		    		break;
		    	}
		    }

		}
		if(!$mail->error){
		    $this->session->data['success'] = $this->language->get('text_success');
		    $this->redirect($this->html->getSecureURL('sale/contact'));
		}

		 //update controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);
	}


	private function _validate() {
		if (!$this->user->canModify('sale/contact')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['subject']) {
			$this->error['subject'] = $this->language->get('error_subject');
		}

		if (!$this->request->post['message']) {
			$this->error['message'] = $this->language->get('error_message');
		}

		if(!$this->request->post['recipient'] && !$this->request->post['to']){
			$this->error['recipient'] = $this->language->get('error_recipients');
		}

		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

}
