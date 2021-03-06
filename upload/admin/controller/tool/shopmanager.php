<?php

class ControllerToolShopmanager extends Controller {

	private $error = array();

	public function index() {
		$this->load->language('tool/shopmanager');
		$this->load->model('tool/shopmanager');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateImport()) {
			$file = $this->request->files['upload']['tmp_name'];
			$group = $this->request->post['group'];
			if ($this->model_tool_shopmanager->upload($file, $group)) {
				$this->session->data['success'] = $this->language->get('text_success_' . $group);
				$this->redirect(HTTPS_SERVER . 'index.php?route=tool/shopmanager&token=' . $this->session->data['token']);
			} else {
				$this->error['warning'] = $this->language->get('error_upload_' . $group);
			}
		}

		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['entry_description'] = $this->language->get('entry_description');
		$this->data['entry_product_task'] = $this->language->get('entry_product_task');
		$this->data['entry_category_task'] = $this->language->get('entry_category_task');
		$this->data['entry_manufacturer_task'] = $this->language->get('entry_manufacturer_task');
		$this->data['button_import'] = $this->language->get('button_import');
		$this->data['button_export'] = $this->language->get('button_export');
		$this->data['tab_general'] = $this->language->get('tab_general');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		$this->document->breadcrumbs = array();

		$this->document->breadcrumbs[] = array(
			'href' => HTTPS_SERVER . 'index.php?route=common/home&token=' . $this->session->data['token'],
			'text' => $this->language->get('text_home'),
			'separator' => FALSE
		);

		$this->document->breadcrumbs[] = array(
			'href' => HTTPS_SERVER . 'index.php?route=tool/shopmanager&token=' . $this->session->data['token'],
			'text' => $this->language->get('heading_title'),
			'separator' => ' :: '
		);

		$this->data['action'] = HTTPS_SERVER . 'index.php?route=tool/shopmanager&token=' . $this->session->data['token'];

		$this->data['export'] = HTTPS_SERVER . 'index.php?route=tool/shopmanager/export&token=' . $this->session->data['token'];

		$this->template = 'tool/shopmanager.tpl';
		$this->children = array(
			'common/header',
			'common/footer',
		);
		$this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));
	}

	/**
	 * Экспорт в XLS
	 *
	 * @return good mood 
	 */
	public function export() {
		
		if ($this->validateExport()) {
			// set appropriate memory and timeout limits
			ini_set("memory_limit", "128M");
			set_time_limit(1800);
			$group = $this->request->get['group'];
			// send the categories, products and options as a spreadsheet file
			$this->load->model('tool/shopmanager');
			$this->model_tool_shopmanager->export($group);
		} else {

			// return a permission error page
			return $this->forward('error/permission');
		}
	}

	/**
	 * Проверки для импорта
	 * 
	 * @return type 
	 */
	private function validateImport() {

		if (!$this->user->hasPermission('modify', 'tool/shopmanager')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error)
			$this->validateUpload();


		if (!$this->error)
			$this->validateImportGroup($this->request->post);


		return (!$this->error) ? true : false;
	}

	/**
	 * Проверки для экспорта
	 * 
	 * @return type 
	 */
	private function validateExport() {

		if (!$this->user->hasPermission('modify', 'tool/shopmanager')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error)
			$this->validateImportGroup($this->request->get);


		return (!$this->error) ? true : false;
	}

	/**
	 * Проверка загружаемого файла для импорта
	 *
	 * @return type 
	 */
	private function validateUpload() {

		if (!isset($this->request->files['upload']) || !is_uploaded_file($this->request->files['upload']['tmp_name'])) {
			$this->error['warning'] = $this->language->get('error_import');
			return false;
		}
		return true;
	}

	/**
	 * Проверка на допустимые группы импорта / экспорта
	 *
	 * @param type $request
	 * @return type 
	 */
	private function validateImportGroup($request) {

		// Допустимые группы импорта / экспорта
		$import_groups = array('category', 'product');

		if (!isset($request['group']) || !in_array($request['group'], $import_groups)) {
			$this->error['warning'] = $this->language->get('error_import_groups');
			return false;
		}
		return true;
	}

}

?>