<?php

namespace Grasenhiller\WkHtmlToX;

use mikehaertl\wkhtmlto\Pdf as WkPdfOriginal;
use SilverStripe\Core\Config\Config;

class WkPdf {

	private $options;
	private $pdf;

	function __construct($options = [], $pageSize = 'A4', $orientation = 'Portrait') {
		if (!count($options)) {
			$config = Config::inst()->get('Grasenhiller\WkHtmlToX', 'Pdf');
			$options = $config['options']['global'];
		}

		if ($pageSize && $orientation && isset($config['options'][$pageSize . $orientation])) {
			$specificOptions = $config['options'][$pageSize . $orientation];
			$options = array_merge($options, $specificOptions);
		} else {
			// todo: log that no options for those values are stored
		}

		$this->options = $options;
		$this->pdf = new WkPdfOriginal($options);
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
	 * @return array
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * @param array $options
	 */
	public function setOptions(array $options) {
		$this->options = $options;
		$this->pdf->setOptions($options);
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
	 * @param string $option
	 * @param string|int|bool   $value
	 */
	public function setOption(string $option, $value = false) {
		$options = $this->getOptions();

		if ($value) {
			$options[$option] = $value;
		} else {
			if (!in_array($option, $options)) {
				$options[] = $option;
			}
		}

		$this->options = $options;
		$this->pdf->setOptions($options);
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
}