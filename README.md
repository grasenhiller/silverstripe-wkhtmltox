# SilverStripe WkHtmlToX

Create pdfs and images out of SilverStripe with WkHtmlToX. Based on [WkHtmlToPdf](http://wkhtmltopdf.org/) and [mikehaertl's php wrapper](https://github.com/mikehaertl/phpwkhtmltopdf).

## Installation

### SilverStripe Module

``` sh
$ composer require grasenhiller/silverstripe-wkhtmltox
```

### WkHtmlToX Binaries

[WKHtmlToPdf](http://wkhtmltopdf.org/) must be installed on your server to use this module.
If it's not, you can try to use the provided binaries (v. 0.12.5). To do so, at first login via SSH and go into your home directory.

```ssh
cd ~
mkdir ~/wkhtmltox
tar -xjvf vendor/grasenhiller/silverstripe-wkhtmltox/wkhtmltox_binaries.tar.bz2 -C ~/wkhtmltox
chmod 755 ~/wkhtmltox/wkhtmltopdf
chmod 755 ~/wkhtmltox/wkhtmltoimage
```

After this, if you're typing ``./wkhtmltox/wkhtmltopdf -V`` you should see the version number. Now it **should** work ;)

At last you need to provide the correct path to the binaries. Inside your ``.env`` file, define these environment variables

```text
SS_WKHTMLTOPDF_BINARY='/absolute/path/to/wkhtmltox/wkhtmltopdf'
SS_WKHTMLTOIMAGE_BINARY='/absolute/path/to/wkhtmltox/wkhtmltoimage'
```

To get the absolute path, go into our wkhtmltox directory and type ``pwd``;

```ssh
cd ~/wkhtmltox/
pwd
``` 

## Behind a proxy?

You could define global proxy settings inside your ``.env`` file

```text
SS_PROXY="http://user:password@192.168.1.2:8080"
```

or without the need for authentication

```text
SS_PROXY="http://192.168.1.2:8080"
```

## Change binary paths

You can define a different binary path for wkhtmltopdf and wkhtmtltoimage inside your ``.env`` file

```text
SS_WKHTMLTOPDF_BINARY='/path/to/wkhtmltopdf'
SS_WKHTMLTOIMAGE_BINARY='/path/to/wkhtmltoimage'
```

## BasicAuth protected environment?

If you're working on a protected environment (dev?) and want to create pdfs or images from "locale" content, you need to define your BasicAuth credentials inside your ``.env`` file

```text
SS_WKHTMLTOX_USERNAME
SS_WKHTMLTOX_PASSWORD
```

## WkHtmlToX help/docs and examples?

Navigate to ``http://your-website.tld/gh-wkhtmltox/``

## Configuration (YML)

TBD

## Methods for both, pdf and image creation

These methods work with ``new WkPdf()`` and ``new WkImage()``

### Set folder where files should be saved

```php
$pdf = new WkPdf();
$pdf->setFolder('folder/beneath/assets/to/save');
```

### Generate html from SilverStripe templates

- **$obj**: The dataobject or page you want to render
- **$variables**: An array with extra data (optional)
- **$template**: The desired template (optional)

```php
$pdf = new WkPdf();
$html = $pdf::get_html($obj, $variables = [], $template = '');
```

### Replace relative image links

HTMLEditorFields normally don't store image links with absolute links.
But WkHtmlToX needs absolute links to work. So just replace them with this method. 

```php
$pdf = new WkPdf();
$html = $pdf::get_html($obj);
$html = $pdf::replace_img_paths($html);
```

### Get options the pdf or image will be created with

Get all options

```php
$pdf = new WkPdf();
echo '<pre>';
print_r($pdf->getOptions());
echo '</pre>;
die();
```

Get a specific option

```php
$pdf = new WkPdf();
echo $pdf->getOption('name_of_option');
die();
```

### Set (new) or remove (existing) options

setOptions()
setOption()
removeOption()
removeOptions()


# Sry, no time to finish the readme until now ...

### Overwrite global options

## Simple Example

```php
	private static $allowed_actions = [
		'pdfExport',
	];

	public function pdfExport() {
		$stylesheet = 'resources/vendor/grasenhiller/silverstripe-intranet-wiki/client/css/pdf.css';

		$filter = URLSegmentFilter::create();
		$filename = $filter->filter($this->MenuTitle);
		$filename .= '__' . date('Y-m-d') . '.pdf';

		$baseUrl = Director::absoluteBaseURL();

		$pdf = new WkPdf();
		$pdf->setOption('margin-top', 20);
		$pdf->setOption('margin-bottom', 20);
		$pdf->setOption('margin-left', 15);
		$pdf->setOption('margin-right', 15);
		$pdf->setOption('user-style-sheet', $stylesheet);
		$pdf->setOption('header-html', $baseUrl . 'gh-wkhtmltox/header?template=Grasenhiller\Intranet\Wiki\Pages\Pdf\Header');
		$pdf->setOption('footer-html', $baseUrl . 'gh-wkhtmltox/footer?template=Grasenhiller\Intranet\Wiki\Pages\Pdf\Footer');
		$pdf->setOption('header-spacing', 5);
		$pdf->setOption('footer-spacing', 5);

		$html = $pdf::get_html($this);

		$pdf->add($html);
		$pdf->download($filename);
	}
```



## Header & footer
gh-wkhtmltox/header
gh-wkhtmltox/footer
$all_variables + variables from docs


### Specific basic auth

## Helpful

viewport-size
zoom 1.045
window-status
h1 {
  page-break-before: always;
}

px & dpi

## WkImage
add()
construct

## WkPdf
add()
construct

## WkImage + WkPdf
preview()
download()
save()
getAsString()

## WkController
- docs
- demo
- header + hook
- footer + hook

## Config
- pdf global
- pdf "pages" predefined + custom
- image global + custom
- bypass_proxy_for_own_site

## templates
html!

## Todo

- Set Options setz mehrere optionen + set / get global options
- Statische Links ersetzen bei get_html (als Option)
- Stylesheets (user-style-sheet)
- Kommentare durchgehen
- README!
- IDE Annotation

## Known Bugs

- Header / Footer can't be loaded

