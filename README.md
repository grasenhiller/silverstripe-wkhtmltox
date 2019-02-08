WARNING! WIP!

-------------------

# SilverStripe WkHtmlToX

Create PDFs and Images from HTML or URLs in SilverStripe. Based on [WkHtmlToPdf](http://wkhtmltopdf.org/) and [mikehaertl's php wrapper](https://github.com/mikehaertl/phpwkhtmltopdf).

## Installation

``` sh
$ composer require grasenhiller/silverstripe-wkhtmltox
```

## Getting started

## Usage

## Proxy

SS_PROXY="http://user:password@192.168.1.2:8080"

## Options

http://your-website.tld/gh-wkhtmltox/

## Binary

SS_WKHTMLTOPDF_BINARY
SS_WKHTMLTOIMAGE_BINARY

tar -xjvf vendor/grasenhiller/silverstripe-wkhtmltox/wkhtmltox_binaries.tar.bz2 -C vendor/silverstripe-wkhtmltox-binaries/
chmod 755 vendor/silverstripe-wkhtmltox-binaries/wkhtmltopdf
chmod 755 vendor/silverstripe-wkhtmltox-binaries/wkhtmltoimage
 
## Baisc Auth

## Header & footer
gh-wkhtmltox/header
gh-wkhtmltox/footer
$all_variables + variables from docs

#### Global

SS_WKHTMLTOX_USERNAME
SS_WKHTMLTOX_PASSWORD

#### Specific

## Helpful

viewport-size
zoom 1.045
window-status
h1 {
  page-break-before: always;
}

px & dpi

## Todo

- Statische Links ersetzen bei get_html (als Option)
- Stylesheets (user-style-sheet)
- Kommentare durchgehen
- README!
