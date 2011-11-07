<?php
class CakePdfException extends Exception {}
/**
 * CakePDF Component
 *
 * @author Vitor Pacheco <vitor.pacheco@ifbaiano.edu.br>
 * @version 1.0
 *
 * @property Controller $controller
 */
class CakePdfComponent extends Object {

	/**
	 * Component name.
	 *
	 * @var string
	 */
	public $name = 'cake_pdf';

	/**
	 * Default options.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_defaults = array(
		'orientation' => 'portrait',
		'papper' => 'A4',
		'basePath' => '',
		'debug' => false,
	);

	/**
	 * Called before the Controller::beforeFilter().
	 *
	 * @access public
	 * @param Controller $controller
	 * @param array $settings
	 * @return void
	 */
	public function initialize(&$controller, $settings = array()) {
		$this->controller = $controller;
		$this->_defaults['basePath'] = 'http://' . $_SERVER['HTTP_HOST'] . '/theme/' . $this->controller->theme;
		$this->_defaults = Set::merge($this->_defaults, $settings);
	}

	/**
	 * Called after the Controller::beforeRender(), after the view class is
	 * loaded, and before the Controller::render()
	 *
	 * @param Controller $controller
	 * @return boolean Return true if the action is not listed in the array
	 * $this->_default['actions'].
	 */
	public function beforeRender(&$controller) {
		if (true === $this->_defaults['debug'] || true === $this->checkPrefix($this->controller->action)) {
			return true;
		} else {
			$this->controller->layout = 'pdf';
		}
	}

	/**
	 * Called after Controller::render() and before the output is printed to the
	 * browser.
	 * 
	 * @param Controller $controller
	 * @return void
	 */
	public function shutdown(&$controller) {
		if (true === $this->_defaults['debug'] || false === $this->checkPrefix($this->controller->action)) {
			return true;
		} else {
			define("DOMPDF_ENABLE_REMOTE", true);
			define("DOMPDF_UNICODE_ENABLED", true);
			define("DOMPDF_DEFAULT_MEDIA_TYPE", "print");
			define("DOMPDF_ENABLE_PHP", false);
			if (!App::import('Vendor', 'CakePdf.dompdf', array('file' => 'dompdf' . DS . 'dompdf_config.inc.php'))) {
				throw new CakePdfException('dompdf not found.');
			}
			$domPdf = new DOMPDF();
			$domPdf->set_base_path('http://' . $_SERVER['HTTP_HOST']);
			$domPdf->load_html($this->controller->output);
			$this->controller->output = null;
			$domPdf->set_paper($this->_defaults['papper'], $this->_defaults['orientation']);
			$domPdf->render();
			$domPdf->stream($this->controller->action);
		}
	}

	/**
	 * Check the action prefix.
	 *
	 * @param string $action
	 * @return boolean Return true if the action has the 'pdf_' prefix.
	 */
	private function checkPrefix($action) {
		if (stripos($action, 'pdf_') === 0) {
			return true;
		}
		return false;
	}

}
