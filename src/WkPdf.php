<?php

namespace Grasenhiller\WkHtmlToX;

use mikehaertl\wkhtmlto\Pdf as WkPdfOriginal;
use SilverStripe\Assets\File;
use SilverStripe\Core\Config\Config;

class WkPdf extends WkFile {

	private $pdf;

	/**
	 * WkPdf constructor.
	 *
	 * @param string $pageSize
	 * @param string $orientation
	 * @param array  $options
	 */
	function __construct(string $pageSize = null, string $orientation = null, array $options = []) {
		$config = Config::inst()->get('Grasenhiller\WkHtmlToX', 'Pdf');

		if (!count($options)) {
			$defaultOptionsInUse = true;
			$options = $config['options']['global'];
		} else {
			$defaultOptionsInUse = false;
		}

		if ($pageSize && $orientation && isset($config['options'][$pageSize . $orientation])) {
			$specificOptions = $config['options'][$pageSize . $orientation];

			if ($defaultOptionsInUse) {
				$options = array_merge($options, $specificOptions);
			} else {
				$options = array_merge($specificOptions, $options);
			}
		} else if ($pageSize && $orientation) {
			$this->handleMissingYmlConfig($pageSize, $orientation);
		}

		$this->setPdf(new WkPdfOriginal());
		$this->setOptions($options);

		parent::__construct('pdf');
	}

	/**
	 * @return WkPdfOriginal
	 */
	public function getPdf() {
		return $this->pdf;
	}

	/**
	 * @param WkPdfOriginal $pdf
	 */
	public function setPdf(WkPdfOriginal $pdf) {
		$this->pdf = $pdf;
	}

	/**
	 * @param array $options
	 */
	public function setOptions(array $options) {
		parent::setOptions($options);

		$this->getPdf()->setOptions($options);
	}

	/**
	 * @param        $obj
	 * @param array  $variables
	 * @param string $template
	 * @param string $type 'Pdf' or 'Image'
	 *
	 * @return \SilverStripe\ORM\FieldType\DBHTMLText
	 */
	public static function get_html($obj, array $variables = [], string $template = '', string $type = 'Pdf') {
		return parent::get_html($obj, $variables, $template, 'Pdf');
	}

	/**
	 * Add a page to the pdf
	 *
	 * @param string $content HTML code or an url
	 * @param string $type valid values are 'Page', 'Cover' and 'Toc'
	 * @param array  $options
	 */
	public function add(string $content, string $type = 'Page', array $options = []) {
		$pdf = $this->getPdf();

		if ($type == 'Page') {
			$pdf->addPage($content, $options);
		} else if ($type == 'Cover') {
			$pdf->addCover($content, $options);
		} else if ($type == 'Toc') {
			$pdf->addToc($options);
		}

		$this->setPdf($pdf);
	}

	/**
	 * display the pdf inside the browser
	 */
	public function preview() {
		$pdf = $this->getPdf();

		if(!$pdf->send()) {
			$this->handleError($pdf);
		}
	}

	/**
	 * Force the pdf to download
	 *
	 * @param string $fileName
	 */
	public function download(string $fileName) {
		$pdf = $this->getPdf();

		if(!$pdf->send($this->generateValidFileName($fileName, 'pdf'))) {
			$this->handleError($pdf);
		}
	}

	/**
	 * Save it to  the filesystem and return the file
	 *
	 * @param string $fileName
	 * @param string $fileClass
	 * @param array  $extraData
	 *
	 * @return mixed
	 */
	public function save(string $fileName, string $fileClass = File::class, array $extraData = []) {
		$pdf = $this->getPdf();
		$fileName = $this->generateValidFileName($fileName, 'pdf');
		$serverPath = $this->getServerPath();

		if (!$pdf->saveAs($serverPath . $fileName)) {
			$this->handleError($pdf);
		} else {
			return $this->createFile($fileName, $fileClass, $extraData);
		}
	}

	/**
	 * Get the raw pdf as string
	 *
	 * @return bool|string
	 */
	public function getAsString() {
		$pdf = $this->getPdf();
		$string = $pdf->toString();

		if ($string === false) {
			$this->handleError($pdf);
		} else {
			return $string;
		}
	}
}