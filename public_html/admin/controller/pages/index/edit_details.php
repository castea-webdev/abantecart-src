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
class ControllerPagesIndexEditDetails extends AController {

	public $data = array();
	private $error = array();

	public function main() {

        //init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);

        $this->loadModel('user/user');
    	$this->loadLanguage('user/user');

		$this->document->setTitle( $this->language->get('text_edit_details') );

		$this->view->assign('success', $this->session->data['success']);
		if (isset($this->session->data['success'])) {
			unset($this->session->data['success']);
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->_validate()) {
			$this->model_user_user->editUser($this->user->getId(), $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success_details');
			$this->redirect( $this->html->getSecureURL('index/edit_details') );
    	}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->_validate()) {
			$this->redirect($this->html->getSecureURL('index/edit_details'));
		}

		$this->data['login'] =  $this->html->getSecureURL('index/login');

		$this->data['error'] = $this->error;


		$user_info = $this->model_user_user->getUser( $this->user->getId() );

		$fields = array('firstname', 'lastname', 'email');
		foreach ( $fields as $f ) {
			if (isset ( $this->request->post [$f] )) {
				$this->data [$f] = $this->request->post [$f];
			} else {
				$this->data[$f] = $user_info[$f];
			}
		}

		$this->data['action'] = $this->html->getSecureURL('index/edit_details');
		$this->data['update'] = $this->html->getSecureURL('listing_grid/user/update_field','&id='.$this->user->getId() );
		$form = new AForm('HS');

		$form->setForm(
			array(
				'form_name' => 'editFrm',
				'update' => $this->data['update'],
			)
		);

		$this->data['form']['id'] = 'editFrm';
		$this->data['form']['form_open'] = $form->getFieldHtml(
			array(
				'type' => 'form',
				'name' => 'editFrm',
				'attr' => 'confirm-exit="true"',
				'action' => $this->data['action'],
			)
		);
		$this->data['form']['submit'] = $form->getFieldHtml(
			array(
				'type' => 'button',
				'name' => 'submit',
				'text' => $this->language->get('button_save'),
				'style' => 'button3',
			)
		);

		foreach ( $fields as $f ) {
			$this->data['form']['fields'][$f] = $form->getFieldHtml(
				array(
					'type' => 'input',
					'name' => $f,
					'value' => $this->data[$f],
					'required' => true,
				)
			);
		}

		$this->data['form']['fields']['password'] = $form->getFieldHtml(
			array(
				'type' => 'passwordset',
				'name' => 'password',
				'value' => $this->data['password'],
			)
		);

		$this->view->batchAssign( $this->data );

		$this->processTemplate('pages/index/edit_details.tpl' );

        //update controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);
  	}

	private function _validate() {
    	if ((strlen(utf8_decode($this->request->post['firstname'])) < 2) || (strlen(utf8_decode($this->request->post['firstname'])) > 32)) {
      		$this->error['firstname'] = $this->language->get('error_firstname');
    	}

    	if ((strlen(utf8_decode($this->request->post['lastname'])) < 2) || (strlen(utf8_decode($this->request->post['lastname'])) > 32)) {
      		$this->error['lastname'] = $this->language->get('error_lastname');
    	}

    	if ( !empty($this->request->post['password']) ) {
      		if ((strlen(utf8_decode($this->request->post['password'])) < 4)) {
        		$this->error['password'] = $this->language->get('error_password');
      		}

	  		if (!$this->error['password'] && $this->request->post['password'] != $this->request->post['password_confirm']) {
	    		$this->error['password'] = $this->language->get('error_confirm');
	  		}
    	}

		$pattern = '/^[A-Z0-9._%-]+@[A-Z0-9][A-Z0-9.-]{0,61}[A-Z0-9]\.[A-Z]{2,6}$/i';
    	if (!preg_match($pattern, $this->request->post['email'])) {
      		$this->error['email'] = $this->language->get('error_email');
    	}

	    if (!$this->error) {
	  		return TRUE;
		} else {
	  		return FALSE;
		}
  	}

}