# SitemapXML

## Version
#### v3.9.9 21.May.2023

### Authors

Andrew Berezin http://eCommerce-Service.com, @mc12345678 https://github.com/mc12345678, @lat9 https://github.com/lat9

Thanks
Special thanks to DivaVocals for the quality of the readme.

## Description
This Script generates a Sitemap as described here:

- https://www.sitemaps.org/
- https://support.google.com/webmasters/bin/answer.py?hl=en&answer=156184&topic=8476&ctx=topic

It can be downloaded from the Zen Cart Plugins repository here: https://www.zen-cart.com/downloads.php?do=file&id=367

**Note**:  Version 3.9.9 and later require that the store have a `CHARSET` that specifies `utf-8` encoding and a database using a `DB_CHARSET` of either `utf8` or `utf8mb4`.

Zen-Cart Versions
--------------
1.5.7[a-d], 1.5.8[a]

Support thread
--------------
https://www.zen-cart.com/showthread.php?126810-SitemapXML-v-2

https://zen-cart.su/plugins/sitemap-xml/msg7/

https://github.com/lat9/sitemapxml

Affected files
--------------
None

Affects DB
--------------
Yes (creates new records into configuration_group, configuration and admin_pages tables).

DISCLAIMER
--------------
Installation of this contribution is done at your own risk.
Backup your ZenCart database and any and all applicable files before proceeding.

Features:
--------------
* supports Search-Engine Safe URLs (including MagicSeo)
* could be accessed by http or command line
* autogenerates multiple sitemaps for sites with over 50.000 URLs
* autogenerates multiple sitemaps if filesize exceeded 10MB
* writes files compressed or uncompressed (You can use the gzip feature or compress your Sitemap files using gzip)
* using index.php wrapper - http://domain.com/index.php?main_page=sitemapxml
* using languages file and etc.
* auto-notify Google and other SE.
* generation of a sitemap index file
* generation of xml-sitemaps for (separate files):
  1. Products with images (supports multilangual products, support hideCategories)
  2. Categories with images (supports multilangual categories, support hideCategories)
  3. Manufacturers with images
  4. Main (Home) page
  5. Reviews
  6. EZ-pages
    * multi-language support,
    * 'EZ pages rel=nofollow attribute' support (https://www.zen-cart.com/index.php?main_page=product_contrib_info&products_id=944),
    * 'date_added'/'last_modified' support,
    * check internal links ('alt_url') by "noindex" rule (main_page in ROBOTS_PAGES_TO_SKIP),
    * toc_chapter proccessing
  7. Testimonial Manager https://www.zen-cart.com/downloads.php?do=file&id=299
  8. News Box Manager https://www.zen-cart.com/downloads.php?do=file&id=147
  9. News and Article Manager & Optional Sideboxes https://www.zen-cart.com/downloads.php?do=file&id=791
  10. Product reviews page

If the products, categories, reviews have not changed since the last generation (time creation corresponding xml-sitemap file), a new xml-sitemap file not created (using existing xml-sitemap).

Priority is calculated on the basis of the positions in the selection from the database, ie the operator ORDER BY in the sql query. First item have priority=1.00, last=0.10. So can no longer be situations where all items in the file have the same priority.
* Products - ORDER BY p.products_sort_order ASC, last_date DESC
* Categories - ORDER BY c.sort_order ASC, last_date DESC
* Reviews - ORDER BY r.reviews_rating ASC, last_date DESC
* EZ-pages - ORDER BY p.sidebox_sort_order ASC, last_date DESC
* Testimonials - ORDER BY last_date DESC
* Box News - ORDER BY last_date DESC

$_GET parameters:
-------------------------
ping=yes - Pinging Search Engine Systems.

inline=yes - output file sitemapindex.xml. In Google Webmaster Tools you can define your "Sitemap URL":
  http://your_domain/index.php?main_page=sitemapxml&inline=yes
  And every time Google will get index.php?main_page=sitemapxml he will receive a fresh sitemapindex.xml.

genxml=no - don't generate xml-files.

rebuild=yes - force rebuild all sitemap*.xml files.

Comments and suggestions are welcome.
If you need any more sitemaps (faq, news, etc) you may ask me, but I will do only if it matches with my interests.

Install:
--------------
0. BACK UP your database & store.
1. Unzip the SitemapXML package to your local hard drive, retaining the folder structure.
2. Rename the "YOUR_Admin" folder in the "sitemapXML" folder to match the name of your admin folder.
     sitemapXML/YOUR_Admin/
3. Upload the files from "sitemapXML" to the root of your store. (DO NOT upload the "sitemapXML" folder, just the CONTENTS of this folder  (copy ALL of the add-on files to your store!! Most issues are caused by store owners who decide to NOT load ALL of the module files)
4. Set permissions on the directory /sitemap/ to 777.
5. Go to Admin -> Configuration -> Sitemap XML and setup all parameters.
6. Go to Admin -> Tools -> Sitemap XML (If error messages occur, change permissions on the XML files to 777).
7. To have this update and automatically notify Google, you will need to set up a Cron job via your host's control panel.

Upgrade:
--------------
0. BACK UP your database & store.
1. Unzip the SitemapXML package to your local hard drive, retaining the folder structure.
2. Rename the "YOUR_Admin" folder in the "sitemapXML" folder to match the name of your admin folder.
     sitemapXML/YOUR_Admin/
3. Upload the files from "sitemapXML" to the root of your store. (DO NOT upload the "sitemapXML" folder, just the CONTENTS of this folder (copy ALL of the add-on files to your store!! Most issues are caused by store owners who decide to NOT load ALL of the module files)

Un-Install:
--------------
1. Go to Admin -> Tools -> Sitemap XML and click "Un-Install SitemapXML SQL".
2. Delete all files that were copied from the installation package.

History
--------------
v 2.0.0 02.02.2009 19:21 - Initial version

v 2.1.0 30.04.2009 10:35 - Lot of changes and bug fixed

v 3.0.2 11.08.2011 16:14 - Lot of changes and bug fixed, Zen-Cart 1.5.0 Support, MagicSeo Support

v 3.0.3 27.08.2011 13:11 - Small bug fix, delete Zen-Cart 1.5.0 Autoinstall

v 3.0.4 30.09.2011 14:58 - Code cleaning, Readme corrected, Small bug fix, Zen-Cart 1.5.0 compliant - replace admin $_GET by $_POST

v 3.0.5 02.12.2011 02:11 - Support Box News module, cleaning

v 3.1.0 14.12.2011 13:32 - Code cleaning, Readme corrected, Small bug fix,
                           Replace Configuration parameter 'Generate language for default language' by 'Using parameter language in links',
                           Modified algorithm for processing multi-language links
                           Add Sitemap Files List to admin

v 3.2.2 07.05.2012 19:12 - Bug fixes
                           Traditional code cleaning
                           Correct MagicSeo Support
                           Truncate additional multi files
                           Add sitemapXML simple file manager
                           Add 'Start Security Token'
                           Rename sitemapxml_homepage.php to sitemapxml_mainpage.php
                           Add image sitemap support http://support.google.com/webmasters/bin/answer.py?answer=178636 for products, categories, manufacturers

v 3.2.4 28.05.2012 13:38 - Bug fixes
                           Add parameter "Check Dublicates"
                           Add parameter "Sitemap directory"

v 3.2.5 31.05.2012 14:52 - Add parameter "Use cPath parameter in products url". Coordinate this value with the value of variable $includeCPath in file init_canonical.php

v 3.2.6 17.06.2012 16:13 - Bug fixes
                           Rewrite gss.xls

v 3.2.7 24.09.2012 13:23 - ReadMe editing - thanks to Scott C Wilson aka swguy (http://www.zen-cart.com/member.php?22320-swguy)
                           Products additional images sitemap support
                           Bug fix 'inline=yes'

v 3.2.8 24.01.2013 18:10 - Add url encoded for RFC 3986 compatibility.

v 3.2.9 24.02.2013 13:48 - Bug fixes
                           Delete xml validations
                           Delete absolute path from information message

v 3.2.10 22.04.2013 8:15 - Add confirm() to delete/truncate

v 3.2.12 19.09.2013 8:06 - Replace absolute path to .xsl

v 3.3.1 31.01.2015 16:27 - Bug fixes
                           Add Product reviews pages
                           Add plugin control

v 3.6.0 26.04.2016 10:33   - Bug fixes

v 3.7.0 07.07.2016 11:25   - Add configuration parameter for categories paging

v 3.8.0 07.07.2016 12:39   - Code Review. Thanks to steve aka torvista

v 3.9.1 29.08.2016 18:56   - Add auto installer. Thanks to Frank Riegel aka frank18

v 3.9.2 09.11.2016 13:37   - Bug fixes (Select plugins)

v 3.9.3 16.04.2017 18:35   - Auto installer Bug fixes

v 3.9.4 17.04.2017 14:27   - Another Auto installer Bug fixes

v 3.9.5 17.04.2017 15:55   - Add configuration multilanguage support

v 3.9.6 06.07.2019 13:33   - Arrange for PHP 7.3, 
                             incorporate fixes posted to forum, 
                             incorporate fix for responsive_classic,
                             refactored various code.
                             Addressed strict php notifications

v 3.9.7 28/04/2023 (highburyeye)

- Add support for Zen Cart 1.5.8

v 3.9.8 03/05/2023 (highburyeye)

- Additional support for PHP 8.1

v3.9.9 21/05/2023 (lat9)

- Drops support for Zen Cart versions prior to 1.5.7; tested on PHP versions 7.3 through 8.2 and Zen Cart 1.5.7 through 1.5.8a.
- Provide interoperability with PHP 8.2, defining all class variables and removing usage of the now-deprecated `utf8_encode` function.
- Removes the automatic Zen Cart check for plugin updates as that can have adverse performance implications for a site's admin processing.
- Corrects MySQL fatal errors when run with more strict (e.g. MySQL 8) SQL servers.
- "Refreshed" the majority of the PHP files to use now-current PHP and Zen Cart programming styles and removing code that was required for Zen Cart versions prior to 1.5.7 and/or PHP versions less than 7.3.
- Removed configuration multi-language support.
