<?php

namespace Grasenhiller\WkHtmlToX;

use mikehaertl\wkhtmlto\Image as WkImageOriginal;
use SilverStripe\Assets\Image;
use SilverStripe\Core\Config\Config;

class WkImage extends WkFile {

	private $image;

	/**
	 * WkImage constructor.
	 *
	 * @param array $options
	 */
	function __construct(array $options = []) {
		$config = Config::inst()->get('Grasenhiller\WkHtmlToX', 'Image');

		if (!count($options)) {
			$options = $config['options']['global'];
		}

		$this->setImage(new WkImageOriginal());
		$this->setOptions($options);

		parent::__construct('image');
	}

	/**
	 * @return WkImageOriginal
	 */
	public function getImage() {
		return $this->image;
	}

	/**
	 * @param WkImageOriginal $image
	 */
	public function setImage(WkImageOriginal $image) {
		$this->image = $image;
	}

	/**
	 * @param array $options
	 */
	public function setOptions(array $options) {
		parent::setOptions($options);

		$this->getImage()->setOptions($options);
	}

	/**
	 * @param        $obj
	 * @param array  $variables
	 * @param string $template
	 * @param string $type 'Pdf' or 'Image'
	 *
	 * @return \SilverStripe\ORM\FieldType\DBHTMLText
	 */
	public static function get_html($obj, array $variables = [], string $template = '', string $type = 'Image') {
		return parent::get_html($obj, $variables, $template, 'Image');
	}

	/**
	 * Add content for the image
	 *
	 * @param string $content HTML code or an url
	 */
	public function add(string $content) {
		$image = $this->getImage();
		$image->setPage($content);
		$this->setImage($image);
	}

	/**
	 * display the image inside the browser
	 */
	public function preview() {
		$image = $this->getImage();

		if(!$image->send()) {
			$this->handleError($image);
		}
	}

	/**
	 * Force the image to download
	 *
	 * @param string $fileName
	 * @param string $extension
	 */
	public function download(string $fileName, string $extension = 'jpg') {
		$image = $this->getImage();

		if(!$image->send($this->generateValidFileName($fileName, $extension))) {
			$this->handleError($image);
		}
	}

	/**
	 * Save it to the filesystem and return the file
	 *
	 * @param string $fileName
	 * @param string $extension
	 * @param string $fileClass
	 * @param array  $extraData
	 *
	 * @return mixed
	 */
	public function save(string $fileName, string $extension = 'jpg', string $fileClass = Image::class, array $extraData = []) {
		$image = $this->getImage();
		$fileName = $this->generateValidFileName($fileName, $extension);
		$serverPath = $this->getServerPath();

		if (!$image->saveAs($serverPath . $fileName)) {
			$this->handleError($image);
		} else {
			return $this->createFile($fileName, $fileClass, $extraData);
		}
	}


	/**
	 * Get the raw image as string
	 *
	 * @return bool|string
	 */
	public function getAsString() {
		$image = $this->getImage();
		$string = $image->toString();

		if ($string === false) {
			$this->handleError($image);
		} else {
			return $string;
		}
	}
}