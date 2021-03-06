<?php
namespace Scion\Views;

use Dwoo\Core;
use Dwoo\ITemplate;
use Dwoo\Template\File;
use Scion\File\Json;
use Scion\Mvc\Singleton;
use Scion\Scion;

class TemplateEngine extends Core {
	use Singleton;

	public function __construct() {
		//$this->debugMode = true;

		/**
		 * Add directories from json file
		 */
		$configuration = Scion::getJsonConfiguration();
		if (property_exists($configuration, 'configuration')
			&& property_exists($configuration->configuration, 'framework')
			&& property_exists($configuration->configuration->framework, 'template')) {

			$template = Json::decode(file_get_contents(Scion::getJsonConfiguration()->configuration->framework->template));

			if ($template) {
				if (property_exists($template->dwoo, 'cache')) {
					$this->setCacheDir($template->dwoo->cache);
				}

				if (property_exists($template->dwoo, 'compiled')) {
					$this->setCompileDir($template->dwoo->compiled);
				}

				if (property_exists($template->dwoo, 'view')) {
					$this->setTemplateDir($template->dwoo->view);
				}
			}
		}

		// Initialize globals
		$this->initGlobals();

		// Add directory to the scion framework plugins for Dwoo
		//$this->getLoader()->addDirectory(SCION_DIR . 'Views/plugins/');
		$this->addPlugin('url', '\Scion\Views\plugins\functionUrl');
		$this->addPlugin('javascript', '\Scion\Views\plugins\functionJavascriptGlobals');
	}
}