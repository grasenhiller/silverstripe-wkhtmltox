<?php

namespace Grasenhiller\WkHtmlToX;

use mikehaertl\wkhtmlto\Command;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\View\Requirements;

class WkController extends Controller {

	private static $url_segment = 'gh-wkhtmltox';

	private static $allowed_actions = [
		'index' => 'ADMIN',
		'pdf' => 'ADMIN',
		'image' => 'ADMIN',
		'demoPdf' => 'ADMIN',
		'demoImage' => 'ADMIN',
		'header',
		'footer',
	];

	public function AbsoluteLink($action = null) {
		return Director::absoluteURL($this->Link($action));
	}

	/**
	 * display manpage for pdf creation
	 */
	public function pdf() {
		$pdf = new WkPdf();

		$command = new Command($pdf->getPdf()->getCommand() . ' --htmldoc');

		if ($command->execute()) {
			echo $command->getOutput();
		} else {
			echo $command->getError();
		}
	}

	/**
	 * display manpage for image creation
	 */
	public function image() {
		$image = new WkImage();

		$command = new Command($image->getImage()->getCommand() . ' --htmldoc');

		if ($command->execute()) {
			echo $command->getOutput();
		} else {
			echo $command->getError();
		}
	}

	/**
	 * Make the wkhtmltox variables available in templates and fix section variable
	 *
	 * @return array
	 */
	public function getHeaderFooterVariables() {
		$vars = $this->request->getVars();

		unset(
			$vars['url'],
			$vars['flushtoken'],
			$vars['flush']
		);

		if (isset($vars['page']) && strpos($vars['page'], '§ion=') !== false) {
			$parts = explode('§', $vars['page']);
			$vars['page'] = $parts[0];
			$vars['section'] = str_replace('ion=', '', $parts[1]);
		}

		$string = '';

		foreach ($vars as $key => $value) {
			$string .= '<strong style="color: red;">$' . $key . '</strong> => ' . $value . '<br>';
		}

		$htmlString = DBHTMLText::create();
		$htmlString->setValue($string);

		$vars['all_variables'] = $htmlString;

		return $vars;
	}

	/**
	 * Default pdf header function
	 *
	 * @return DBHTMLText
	 */
	public function header() {
		Requirements::clear();

		$templates = ['Grasenhiller\WkHtmlToX\PdfHeader'];
		$data = $this->getHeaderFooterVariables();

		if (isset($data['template']) && $data['template']) {
			$templates[] = $data['template'];
		}

		$this->extend('updateHeader', $templates, $data);

		return $this
			->customise($data)
			->renderWith(array_reverse($templates));
	}

	/**
	 * Default pdf footer function
	 *
	 * @return DBHTMLText
	 */
	public function footer() {
		Requirements::clear();

		$templates = ['Grasenhiller\WkHtmlToX\PdfFooter'];
		$data = $this->getHeaderFooterVariables();

		if (isset($data['template']) && $data['template']) {
			$templates[] = $data['template'];
		}

		$this->extend('updateFooter', $templates, $data);

		return $this
			->customise($data)
			->renderWith(array_reverse($templates));
	}

	/**
	 * demo pdf
	 */
	public function demoPdf() {
		$pdf = new WkPdf();

		$pdf->setOption('viewport-size', '1920x1080');
		$pdf->setOption('header-html', $this->AbsoluteLink('header'));
		$pdf->setOption('header-spacing', 10);
		$pdf->setOption('footer-html', $this->AbsoluteLink('footer'));
		$pdf->setOption('footer-spacing', 10);

		$variables = [
			'Foo' => 'Hello',
			'Bar' => 'World',
		];

		$html = $pdf::get_html($this, $variables,'Grasenhiller\WkHtmlToX\Image');
		$html = $pdf::replace_img_paths($html);

		$pdf->add($html);
		$pdf->add('https://google.com');

		$r = $this->request;
		$type = $r->getVar('type');

		if (!$type || $type == 'preview') {
			$pdf->preview();
		} else if ($type == 'download') {
			$pdf->download('demo.pdf');
		} else if ($type == 'save') {
			$file = $pdf->save('demo.pdf');
			echo $file->AbsoluteLink();
		}
	}

	/**
	 * demo image
	 */
	public function demoImage() {
		$image = new WkImage();

		$image->setOption('width', 800);
		$image->setOption('height', 600);

		$variables = [
			'Foo' => 'Hello',
			'Bar' => 'World',
		];

		$html = $image::get_html($this, $variables,'Grasenhiller\WkHtmlToX\Image');
		$html = $image::replace_img_paths($html);

		$image->add($html);

		$r = $this->request;
		$type = $r->getVar('type');

		if (!$type || $type == 'preview') {
			$image->preview();
		} else if ($type == 'download') {
			$image->download('demo.png');
		} else if ($type == 'save') {
			$img = $image->save('demo.png');
			echo $img->AbsoluteLink();
		}
	}
}