<?php

namespace Grasenhiller\WkHtmlToX;

use SilverStripe\AssetAdmin\Controller\AssetAdmin;
use SilverStripe\Assets\File;
use SilverStripe\Assets\FileNameFilter;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Image;
use SilverStripe\Control\Director;
use SilverStripe\Core\Environment;
use SilverStripe\Security\Security;
use SilverStripe\View\Requirements;
use SilverStripe\View\SSViewer;

class WkFile {

	protected $folder;
	protected $options;

	function __construct($type) {
		$this->autoSetProxy();
		$this->autoSetBinary($type);
		$this->autoSetBasicAuth();
	}

	/**
	 * Set the proxy if defined in the environment
	 */
	public function autoSetProxy() {
		$proxy = Environment::getEnv('SS_PROXY');

		if ($proxy) {
			$this->setOption('proxy', $proxy);
		}
	}

	/**
	 * Set the binary if defined in the environment
	 *
	 * @param string $type 'pdf' or 'image'
	 */
	public function autoSetBinary(string $type) {
		$binary = false;

		if ($type == 'pdf') {
			$binary = Environment::getEnv('SS_WKHTMLTOPDF_BINARY');
		} else if ($type == 'image') {
			$binary = Environment::getEnv('SS_WKHTMLTOIMAGE_BINARY');
		}

		if ($binary) {
			$this->setOption('binary', $binary);
		}
	}

	/**
	 * Set the basic auth credentials if defined in the environment
	 */
	public function autoSetBasicAuth() {
		$username = Environment::getEnv('SS_WKHTMLTOX_USERNAME');
		$password = Environment::getEnv('SS_WKHTMLTOX_PASSWORD');

		if ($username && $password) {
			$this->setOption('username', $username);
			$this->setOption('password', $password);
		}
	}

	/**
	 * @param string $fileName
	 * @param string $extension
	 *
	 * @return string
	 */
	protected function generateValidFileName(string $fileName, string $extension = '') {
		$filter = FileNameFilter::create();
		$parts = array_filter(preg_split("#[/\\\\]+#", $fileName));

		$fileName = implode('/', array_map(function ($part) use ($filter) {
			return $filter->filter($part);
		}, $parts));

		if ($extension) {
			$parts = explode('.', $fileName);

			if (
				count($parts) <= 1
				|| (count($parts) > 1 && $parts[count($parts) - 1] != $extension)
			) {
				$parts[] = $extension;
			}

			$fileName = implode('.', $parts);
		}

		return $fileName;
	}

	/**
	 * @param string $fileName
	 * @param string $fileClass
	 * @param array  $extraData
	 *
	 * @return mixed
	 */
	protected function createFile(string $fileName, string $fileClass, array $extraData = []) {
		$folder = $this->getFolder();

		$file = new $fileClass();
		$file->setFromLocalFile('assets/' . $folder->Filename . $fileName);
		$file->ParentID = $folder->ID;

		if (count($extraData)) {
			$file->update($extraData);
		}

		$file->write();

		AssetAdmin::singleton()->generateThumbnails($file);

		return $file;
	}

	/**
	 * @param string $folderName
	 */
	public function setFolder(string $folderName = 'wkhtmltox') {
		$this->folder = Folder::find_or_make($folderName);

		if (!file_exists($this->getServerPath())) {
			mkdir($this->getServerPath(), 0777, true);
		}
	}

	/**
	 * @return mixed
	 */
	public function getFolder() {
		if (!$this->folder) {
			$this->setFolder();
		}

		return $this->folder;
	}

	/**
	 * @return string
	 */
	public function getServerPath() {
		return getcwd() . DIRECTORY_SEPARATOR . ASSETS_DIR . DIRECTORY_SEPARATOR . $this->getFolder()->getFileName();
	}

	/**
	 * @param        $obj
	 * @param array  $variables
	 * @param string $template
	 * @param string $type 'Pdf' or 'Image'
	 *
	 * @return \SilverStripe\ORM\FieldType\DBHTMLText
	 */
	public static function get_html($obj, array $variables = [], string $template = '', string $type) {
		Requirements::clear();

		if (!$template) {
			$parts = explode('\\', $obj->ClassName);

			if (count($parts > 1)) {
				$last = $parts[count($parts) - 1];
				unset($parts[count($parts) - 1]);
				$parts[] = $type;
				$parts[] = $last;
				$template = implode('\\', $parts);
			} else {
				$template = $type . '\\' . $obj->ClassName;
			}
		}

		$viewer = new SSViewer($template);
		$html = $viewer->process($obj, $variables);

		return $html;
	}

	/**
	 * Replace all relative image paths with absolute ones
	 *
	 * @param $html
	 *
	 * @return mixed
	 */
	public static function replace_img_paths($html) {
		$baseUrl = Director::absoluteBaseURL();
		$html = str_ireplace('<img src="', '<img src="http://REPLACEHOLDER', $html);
		$html = str_ireplace('<img src="http://REPLACEHOLDERhtt', '<img src="htt', $html);
		$html = str_ireplace('<img src="http://REPLACEHOLDER', '<img src="' . $baseUrl, $html);
		return $html;
	}

	/**
	 * @return array
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * Overwrite all options with the given ones
	 *
	 * @param array $options
	 */
	public function setOptions(array $options) {
		$this->options = $options;
	}

	/**
	 * @param string $option
	 *
	 * @return mixed
	 */
	public function getOption(string $option) {
		$options = $this->getOptions();

		if (isset($options[$option])) {
			return $options[$option];
		}
	}

	/**
	 * Overwrite an existing or set a new option
	 *
	 * @param string $option
	 * @param string|int|bool   $value
	 */
	public function setOption(string $option, $value = false) {
		$options = $this->getOptions();

		if ($value) {
			$options[$option] = $value;
		} else {
			if (!in_array($option, $options, true)) {
				$options[] = $option;
			}
		}

		$this->setOptions($options);
	}

	/**
	 * @param string $option
	 */
	public function removeOption(string $option) {
		$options = $this->getOptions();

		if (isset($options[$option])) {
			unset($options[$option]);
		} else if ($key = array_search($option, $options)) {
			unset($options[$key]);
		}

		$this->setOptions($options);
	}

	/**
	 * @param array $options
	 */
	public function removeOptions(array $options) {
		foreach ($options as $option) {
			$this->removeOption($option);
		}
	}

	/**
	 * Output the error
	 *
	 * @param $wkObj
	 */
	protected function handleError($wkObj) {
		// todo: if dev or test output with print_r, else log
		$error = $wkObj->getError();
		echo '<pre>';
		print_r($error);
		die();
	}
}