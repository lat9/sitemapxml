<?php
/**
 * Common Template
 *
 * outputs the html header. i,e, everything that comes before the \</head\> tag <br />
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zen4All 2020 May 12 Modified in v1.5.7 $
 *
 * Last updated: v4.1.0
 */

// Prevent clickjacking risks by setting X-Frame-Options:SAMEORIGIN
header('X-Frame-Options:SAMEORIGIN');

?>
<!DOCTYPE html>
<html <?= HTML_PARAMS ?>>
<head>
<title><?= HEADING_TITLE ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?= CHARSET ?>">
<?php if (defined('FAVICON')) { ?>
<link rel="icon" href="<?= FAVICON ?>" type="image/x-icon">
<link rel="shortcut icon" href="<?= FAVICON ?>" type="image/x-icon">
<?php } //endif FAVICON ?>

<base href="<?= HTTP_SERVER . DIR_WS_CATALOG ?>">
<style>
body {
  font-family: Verdana, Geneva, sans-serif;
  font-size: small;
  }
</style>
</head>
<?php // NOTE: Blank line following is intended: ?>

