<?php
/**
 * Sitemap XML
 *
 * @package Sitemap XML
 * @copyright Copyright 2005-2016 Andrew Berezin eCommerce-Service.com
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @link http://www.sitemaps.org/
 * @version $Id: sitemapxml.php, v 3.9.7 highburyeye 02/05/2023
 * 
 * updated for 1.5.8 by TC
 * 
 */
zen_define_default('TABLE_SITEMAPXML_TEMP', DB_PREFIX . 'sitemapxml_temp');
zen_define_default('SITEMAPXML_MAX_ENTRIES', 5000);
zen_define_default('SITEMAPXML_MAX_SIZE', 10000000); // 10 485 760

zen_define_default('SITEMAPXML_CHECK_URL', 'false');

class zen_SiteMapXML
{
    private string $savepath;
    private string $savepathIndex;
    private string $sitemap;
    private string $videomap;
    private string $sitemapindex;
    private bool $compress;
    private string $base_url;
    private bool $magicSeo = false;
    private array $duplicatedLinks;
    private string $checkDuplicates;
    private bool $checkurl;

    private array $languageSession = [];
    private array $languages = [];
    private string $languagesIDs;
    private int $languagesCount = 0;
    private int $default_language_id = 0;

    private array $sitemapItems = [];
    private bool $submitFlag = true;
    private bool $inline = false;
    private bool $rebuild = false;
    private bool $genxml = true;
    private string $stylesheet = '';
    private string $base_url_index;

    private int $sitemapFileItems = 0;
    private int $sitemapFileSize = 0;
    private int $sitemapFileItemsTotal = 0;
    private int $sitemapFileSizeTotal = 0;
    private int $sitemapFileItemsMax;
    private $sitemapFile;
    private string $sitemapFileName;
    private string $sitemapType;
    private int $sitemapFileNameNumber = 0;
    private string $sitemapFileFooter = '</urlset>';
    private string $sitemapFileBuffer = '';
    private int $sitemapxml_max_entries;
    private int $sitemapxml_max_size;
    private string $timezone;

    private int $fb_maxsize = 4096;
    private string $fb = '';
    private $fp = null;
    private string $fn = '';
    private string $dir_ws;

    private $time_ping;

    private $statisticModuleTime = 0;
    private $statisticModuleQueries = 0;
    private $statisticModuleQueriesTime = 0;

    public function __construct(bool $inline = false, bool $rebuild = false, bool $genxml = true)
    {
        global $db;

        $this->statisticModuleTime = microtime(true);
        $this->statisticModuleQueries = $db->queryCount();
        $this->statisticModuleQueriesTime = $db->queryTime();

        $this->sitemap = 'sitemap';
        $this->videomap = 'videomap';
        $this->sitemapindex = SITEMAPXML_SITEMAPINDEX . '.xml';
        $this->compress = (SITEMAPXML_COMPRESS === 'true');
        $this->duplicatedLinks = [];
        $this->sitemapItems = [];
        $this->dir_ws = trim(SITEMAPXML_DIR_WS);
        $this->dir_ws = rtrim($this->dir_ws, '/');
        if ($this->dir_ws !== '') {
            $this->dir_ws .= '/';
        }
        $this->savepath = DIR_FS_CATALOG . $this->dir_ws;
        $this->savepathIndex = DIR_FS_CATALOG;
        $this->base_url = HTTP_SERVER . DIR_WS_CATALOG . $this->dir_ws;
        $this->base_url_index = HTTP_SERVER . DIR_WS_CATALOG;
        $this->submitFlag = true;
        $this->inline = $inline;
        $this->rebuild = $rebuild;
        $this->checkDuplicates = SITEMAPXML_CHECK_DUPLICATES;
        $db->Execute("DROP TABLE IF EXISTS " . TABLE_SITEMAPXML_TEMP);
        if ($this->checkDuplicates === 'mysql') {
            $sql =
                "CREATE TABLE IF NOT EXISTS " . TABLE_SITEMAPXML_TEMP . " (
                    `url_hash` CHAR(32) NOT NULL ,
                    PRIMARY KEY (`url_hash`)
                 ) ENGINE = MEMORY;";
            $db->Execute($sql);
        }

        $this->checkurl = (SITEMAPXML_CHECK_URL === 'true');
        $this->genxml = $genxml;
        $this->sitemapFileFooter = '</urlset>';
        $this->sitemapFileBuffer = '';
        $this->sitemapxml_max_entries = (int)SITEMAPXML_MAX_ENTRIES;
        $this->sitemapxml_max_size = ((int)SITEMAPXML_MAX_SIZE) - strlen($this->sitemapFileFooter);

        global $lng;
        if (empty($lng) || !is_object($lng)) {
            $lng = new language();
        }
        $this->languageSession = [
            'language' => $_SESSION['language'],
            'languages_id' => $_SESSION['languages_id'],
            'languages_code' => $_SESSION['languages_code'],
        ];
        $languagesIDsArray  = [];
        foreach ($lng->catalog_languages as $language) {
            $this->languages[$language['id']] = [
                'directory' => $language['directory'],
                'id' => $language['id'],
                'code' => $language['code'],
            ];
            $languagesIDsArray[] = $language['id'];
            if ($language['code'] === DEFAULT_LANGUAGE) {
                $this->default_language_id = $language['id'];
            }
        }
        if (SITEMAPXML_USE_ONLY_DEFAULT_LANGUAGE === 'true') {
            $languagesIDsArray  = [$this->default_language_id];
        }
        $this->languagesIDs = implode(',', $languagesIDsArray);
        $this->languagesCount = count($languagesIDsArray);

        $this->sitemapItems = [];

        $timezone = date('O');
        $this->timezone = substr($timezone, 0, 3) . ':' . substr($timezone, 3, 2);

        $this->magicSeo = (function_exists('unMagicSeoDoSeo'));

        if ($this->inline) {
            ob_start();
        }

        $this->time_ping = time();
    }

    public function SitemapOpen(string $file, $last_date = 0, string $type = 'sitemap'): bool
    {
        if (strlen($this->sitemapFileBuffer) > 0) {
            $this->SitemapClose();
        }
        if (!$this->genxml) {
            return false;
        }

        $this->sitemapFile = $file;
        $this->sitemapType = $type;
        $this->sitemapFileName = $this->_getNameFileXML($file);
        if ($this->_checkFTimeSitemap($this->sitemapFileName, $last_date) === false) {
            return false;
        }
        if ($file === 'index') {
            $rc = $this->_fileOpen($this->sitemapFileName, $this->savepathIndex);
        } else {
            $rc = $this->_fileOpen($this->sitemapFileName);
        }
        if (!$rc) {
            return false;
        }
        $this->_SitemapReSet();
        $this->sitemapFileBuffer .= $this->_SitemapXMLHeader();
        if ($file !== 'index') {
            $i = strpos($this->sitemapFileName, '.');
            $name = substr($this->sitemapFileName, 0, $i);
            $ext = substr($this->sitemapFileName, $i);
            if ($sitemapFiles = glob($this->savepath . $name . '*' . $ext)) {
                foreach ($sitemapFiles as $fn) {
                    if ($fn === $this->savepath . $this->sitemapFileName) {
                        continue;
                    }
                    if (preg_match('@^' . preg_quote($this->savepath . $name) . '([\d]{3})' . preg_quote($ext) . '$@', $fn, $m) && $this->_fileSize($fn) > 0) {
                        if ($this->dir_ws !== '') {
                            unlink($fn);
                        } else {
                            $fp = fopen($fn, 'w');
                            fclose($fp);
                        }
                    }
                }
            }
        }
        return true;
    }

    public function SitemapSetMaxItems(int $maxItems): bool
    {
        $this->sitemapFileItemsMax = $maxItems;
        return true;
    }

    public function writeItem($link, $parms = '', $language_id = 0, $lastmod = '', $changefreq = '', $xtra = '')
    {
        if ($lastmod !== '') {
            $lastmod = strtotime($lastmod);
        }

        if (!isset($this->languages[$language_id])) {
            $language_id = $this->languageSession['languages_id'];
        }
        $langParm = $this->getLanguageParameter($language_id);
        if ($langParm !== false) {
            $_SESSION['language'] = $this->languages[$language_id]['directory'];
            $_SESSION['languages_id'] = $this->languages[$language_id]['id'];
            $_SESSION['languages_code'] = $this->languages[$language_id]['code'];
            if (strpos($link, 'http://') !== 0 && strpos($link, 'https://') !== 0) {
                if ($parms != '' && $langParm != '') {
                    $langParm = '&' . $langParm;
                }
                $link = zen_href_link($link, $parms . $langParm, 'NONSSL', false);
            } else {
                if ($langParm !== '') {
                    $langParm = (strpos($link, '?') === false ? '?' . $langParm : '&' . $langParm);
                }
                $link = $link . $langParm;
            }
            $_SESSION['language'] = $this->languageSession['language'];
            $_SESSION['languages_id'] = $this->languageSession['languages_id'];
            $_SESSION['languages_code'] = $this->languageSession['languages_code'];
            $this->SitemapWriteItem($link, (int)$lastmod, $changefreq, $xtra);
        }
    }

    protected function SitemapWriteItem($loc, int $lastmod = 0, $changefreq = '', $xtra = ''): bool
    {
        $time_now = time();
        if ($this->time_ping >= $time_now + 30) {
            $this->time_ping = $time_now;
            header('X-Ping: Pong');
        }

        if (!$this->genxml) {
            return false;
        }
        if ($this->magicSeo) {
            $href = '<html><body><a href="' . $loc . '">loc</a></body></html>';
            $out = unMagicSeoDoSeo($href);
            if (preg_match('@<a[^>]+href=(["\'])(.*)\1@isU', $out, $m)) {
                $loc = $m[2];
            }
        }
        $loc = $this->_url_encode($loc);

        if (!$this->_checkDuplicateLoc($loc)) {
            return false;
        }

        if ($this->checkurl) {
            if (!($info = $this->_curlExecute($loc, 'header')) || $info['http_code'] != 200) {
                return false;
            }
        }
        $itemRecord  = '';
        $itemRecord .= ' <url>' . "\n";
        $itemRecord .= '  <loc>' . $loc . '</loc>' . "\n";
        if ($lastmod > 0) {
            $itemRecord .= '  <lastmod>' . $this->_LastModFormat($lastmod) . '</lastmod>' . "\n";
        }
        if ($changefreq !== '' && $changefreq !== 'no') {
            $itemRecord .= '  <changefreq>' . $changefreq . '</changefreq>' . "\n";
        }
        if ($this->sitemapFileItemsMax > 0) {
            $itemRecord .= '  <priority>' . number_format(max((($this->sitemapFileItemsMax - $this->sitemapFileItemsTotal) / $this->sitemapFileItemsMax), 0.10), 2, '.', '') . '</priority>' . "\n";
        }
        if ($xtra !== '') {
            $itemRecord .= $xtra;
        }
        $itemRecord .= ' </url>' . "\n";

        if ($this->sitemapFileItems >= $this->sitemapxml_max_entries || ($this->sitemapFileSize + strlen($itemRecord)) >= $this->sitemapxml_max_size) {
            $this->_SitemapCloseFile();
            $this->sitemapFileName = $this->_getNameFileXML($this->sitemapFile . str_pad($this->sitemapFileNameNumber, 3, '0', STR_PAD_LEFT));
            if (!$this->_fileOpen($this->sitemapFileName)) {
                return false;
            }
            $this->_SitemapReSetFile();
            $this->sitemapFileBuffer .= $this->_SitemapXMLHeader();
        }
        $this->sitemapFileBuffer .= $itemRecord;
        $this->_fileWrite($this->sitemapFileBuffer);
        $this->sitemapFileSize += strlen($this->sitemapFileBuffer);
        $this->sitemapFileSizeTotal += strlen($this->sitemapFileBuffer);
        $this->sitemapFileItems++;
        $this->sitemapFileItemsTotal++;
        $this->sitemapFileBuffer = '';

        return true;
    }

    public function SitemapClose()
    {
        global $db;
        $this->_SitemapCloseFile();
        if ($this->sitemapFileItemsTotal > 0) {
            $total_time = microtime(true) - $this->statisticModuleTime;
            $total_queries = $db->queryCount() - $this->statisticModuleQueries;
            $total_queries_time = $db->queryTime() - $this->statisticModuleQueriesTime;
            echo sprintf(TEXT_TOTAL_SITEMAP,
                $this->sitemapFileNameNumber + 1,
                $this->sitemapFileItemsTotal,
                $this->sitemapFileSizeTotal,
                $this->timefmt($total_time),
                $total_queries,
                $this->timefmt($total_queries_time)
            ) . '<br>';
        }
        $this->_SitemapReSet();
    }

// generate sitemap index file
    public function GenerateSitemapIndex()
    {
        global $db;

        if ($this->genxml) {
            $sitemap_extension = ($this->compress === true) ? '.xml.gz' : '.xml';
            $sitemapFiles = [];
            if ($files = glob($this->savepath . $this->sitemap . '*' . $sitemap_extension)) {
                $sitemapFiles = $files;
            }

            if (count($sitemapFiles) > 0) {
                echo '<h2>' . TEXT_HEAD_SITEMAP_INDEX . '</h2>';
                $this->SitemapOpen('index', 0, 'index');
                clearstatcache();
                foreach ($sitemapFiles as $filename) {
                    $filenameBase = basename($filename);
                    if ($filenameBase !== $this->sitemapindex && $this->_checkFContentSitemap($filename)) {
                        $fileURL = $this->base_url . $filenameBase;
                        $fileURL = $this->_url_encode($fileURL);
                        echo TEXT_INCLUDE_FILE . $this->dir_ws . $filenameBase . ' (<a href="' . $fileURL . '" target="_blank">' . $fileURL . '</a>)' . '<br>';
                        $itemRecord = '';
                        $itemRecord .= ' <sitemap>' . "\n";
                        $itemRecord .= '  <loc>' . $fileURL . '</loc>' . "\n";
                        $itemRecord .= '  <lastmod>' . $this->_LastModFormat(filemtime($filename)) . '</lastmod>' . "\n";
                        $itemRecord .= ' </sitemap>' . "\n";
                        $this->sitemapFileBuffer .= $itemRecord;
                        $this->_fileWrite($this->sitemapFileBuffer);
                        $this->sitemapFileSize += strlen($this->sitemapFileBuffer);
                        $this->sitemapFileSizeTotal += strlen($this->sitemapFileBuffer);
                        $this->sitemapFileItems++;
                        $this->sitemapFileItemsTotal++;
                        $this->sitemapFileBuffer = '';
                    }
                }

                $data = '</sitemapindex>';
                $this->sitemapFileSizeTotal += strlen($data);
                $this->_fileWrite($data);

                $this->_fileClose();

                echo TEXT_URL_FILE . '<a href="' . $this->base_url_index . $this->sitemapFileName . '" target="_blank">' . $this->base_url_index . $this->sitemapFileName . '</a>' . '<br>';
                echo sprintf(TEXT_WRITTEN,
                    $this->sitemapFileItems++,
                    $this->sitemapFileSizeTotal,
                    $this->_fileSize($this->savepathIndex . $this->sitemapFileName)
                ) . '<br><br>';
            } else {
                echo '<h2>' . TEXT_HEAD_SITEMAP_INDEX_NONE . '</h2>';//steve prevously created an invalid xml file
            }
        }

        $db->Execute("DROP TABLE IF EXISTS " . TABLE_SITEMAPXML_TEMP);

        if ($this->inline) {
            if ($this->submitFlag) {
                ob_end_clean();
                $this->_outputSitemapIndex();
            } else {
                ob_end_flush();
            }
        }

        if ($this->inline) {
            die();
        }
    }

    // Replace associated function with ZC equivalent code/call including code that calls this function.
    // retrieve full cPath from category ID
    public function GetFullcPath($cID)
    {
        // Incorporate ZC function(s) to collect this information.
        return zen_get_generated_category_path_rev($cID);
    }

    public function setCheckURL(bool $checkurl)
    {
        $this->checkurl = $checkurl;
    }

    public function setStylesheet(string $stylesheet)
    {
        $this->stylesheet = $stylesheet;
    }

    public function getLanguageParameter($language_id = 0, $lang_parm = 'language')
    {
        $code = '';
        if ($language_id === 0) {
            $language_id = $this->default_language_id;
        }
        if (!isset($this->languages[$language_id]['code'])) {
            return false;
        }
        if (SITEMAPXML_USE_LANGUAGE_PARM !== 'false' && (($this->languages[$language_id]['code'] !== DEFAULT_LANGUAGE && $this->languagesCount > 1) || SITEMAPXML_USE_LANGUAGE_PARM === 'all')) {
            $code = $lang_parm . '=' . $this->languages[$language_id]['code'];
        }
        return $code;
    }

    // ZC code should exist to obtain this.
    public function getLanguageDirectory($language_id)
    {
        if (isset($this->languages[$language_id])) {
            $directory = $this->languages[$language_id]['directory'];
        } else {
            $directory = false;
        }
        return $directory;
    }

    public function getLanguagesIDs(): string
    {
        return $this->languagesIDs;
    }

    // ZC Sniffer class already offers this feature.
    public function dbTableExist(string $table): bool
    {
        return $GLOBALS['sniffer']->table_exists($table);
    }

    // ZC Sniffer class already offers this feature.
    public function dbColumnExist(string $table, string $column): bool
    {
        return $GLOBALS['sniffer']->field_exists($table, $column);
    }

    public function imagesTags($images, string $caption = 'true', string $license_url = ''): string
    {
        if (!is_array($images)) {
            // Provided image is not in format to support processing.
            return '';
        }

        $tags = '';
        foreach ($images as $image) {
            $image['title'] = htmlspecialchars($image['title']);
            $loc = HTTP_SERVER . DIR_WS_CATALOG . $image['file'];
            $tags .= '  <image:image>' . "\n";
            $tags .= '    <image:loc>' . $this->_url_encode($loc) . '</image:loc>' . "\n";
            if ($caption === 'true') {
                $tags .= '    <image:caption>' . $image['title'] . '</image:caption>' . "\n";
                $tags .= '    <image:title>' . $image['title'] . '</image:title>' . "\n";
            }
            if ($license_url !== '') {
                if (strpos($license_url, 'http://') !== 0 && strpos($license_url, 'https://') !== 0) {
                    $license_url = HTTP_SERVER . DIR_WS_CATALOG . $license_url;
                }
                $tags .= '    <image:license>' . $this->_url_encode($license_url) . '</image:license>' . "\n";
            }
            $tags .= '  </image:image>' . "\n";
        }
        return $tags;
    }

/////////////////////////

    protected function _checkFTimeSitemap(string $filename, $last_date = 0): bool
    {
// TODO: Multifiles
        if ($this->rebuild === true || empty($last_date)) {
            return true;
        }

        clearstatcache();
        if (SITEMAPXML_USE_EXISTING_FILES === 'true'
            && file_exists($this->savepath . $filename)
            && (filemtime($this->savepath . $filename) >= strtotime($last_date))
            && $this->_checkFContentSitemap($this->savepath . $filename)) {
            echo '"' . $filename . '" ' . TEXT_FILE_NOT_CHANGED . '<br>';
            return false;
        }
        return true;
    }

    protected function _checkFContentSitemap(string $filename): bool
    {
        if (($fsize = $this->_fileSize($filename)) > 0) {
            // -----
            // PHP functions used to read the file depend on whether it was
            // gzipped or not.
            //
            if (pathinfo($filename, PATHINFO_EXTENSION) === 'gz') {
                $fopen = 'gzopen';
                $read_only = 'rb9';
                $fread = 'gzread';
                $fclose = 'gzclose';
            } else {
                $fopen = 'fopen';
                $read_only = 'r';
                $fread = 'fread';
                $fclose = 'fclose';
            }

            if ($fp = $fopen($filename, $read_only)) {
                $contents = (string)$fread($fp, 1000);
                $fclose($fp);
                if (strpos($contents, '<urlset ') !== false || strpos($contents, '<sitemapindex ') !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function _getNameFileXML(string $filename): string
    {
        switch ($this->sitemapType) {
            case 'index':
                $filename = $this->sitemapindex;
                break;
          case 'video':
                $filename = $this->videomap . $filename . '.xml' . ($this->compress ? '.gz' : '');
                break;
          case 'sitemap':
          default:
                $filename = $this->sitemap . $filename . '.xml' . ($this->compress ? '.gz' : '');
                break;
        }
        return $filename;
    }

    // format the LastMod field
    protected function _LastModFormat(int $date): string
    {
        if (SITEMAPXML_LASTMOD_FORMAT === 'full') {
            return gmdate('Y-m-d\TH:i:s', $date) . $this->timezone;
        } else {
            return gmdate('Y-m-d', $date);
        }
    }

    protected function _SitemapXMLHeader(): string
    {
        $header = '<?xml version="1.0" encoding="UTF-8"?'.'>' . "\n";
        $header .= ($this->stylesheet != '' ? '<?xml-stylesheet type="text/xsl" href="' . DIR_WS_CATALOG . $this->stylesheet . '"?'.'>' . "\n" : "");
        switch ($this->sitemapType) {
            case 'index':
                $header .= '<sitemapindex xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
                $header .= '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd"' . "\n";
                $header .= '        xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
                break;
            case 'video':
                $header .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
                $header .= '        xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">' . "\n";
                break;
            case 'sitemap':
            default:
                $header .= '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
                $header .= '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"' . "\n";
                $header .= '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"' . "\n";
                $header .= '        xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
                break;
        }
        $header .= '<!-- generator="Zen-Cart SitemapXML" ' . SITEMAPXML_VERSION . ' -->' . "\n";
        $header .= '<!-- ' . $this->sitemapFileName . ' created at ' . date('Y-m-d H:i:s') . ' -->' . "\n";
        return $header;
    }

    protected function _clearHTML(string $html): string
    {
        $html = str_replace('&nbsp;', ' ', $html);
        $html = preg_replace('@\s\s+@', ' ', $html);
        $html = preg_replace('@<head>(.*)</'.'head>@si', '', $html);
        $html = preg_replace('@<script(.*)</'.'script>@si', '', $html);
        $html = preg_replace('@<title>(.*)</'.'title>@si', '', $html);
        $html = preg_replace('@(<br\s*[/]*>|<p.*>|</p>|</div>|</h\d+>)@si', "$1\n", $html);
        $html = preg_replace("@\n\s+@", "\n", $html);
        $html = strip_tags($html);
        $html = trim($html);
        $html = nl2br($html, false);
        return $html;
    }

    protected function _outputSitemapIndex()
    {
        header('Last-Modified: ' . gmdate('r') . ' GMT');
        header('Content-Type: text/xml; charset=UTF-8');
        header('Content-Length: ' . $this->_fileSize($this->savepathIndex . $this->sitemapindex));
        echo file_get_contents($this->savepathIndex . $this->sitemapindex);
    }

    protected function _curlExecute(string $url, string $read = 'page')
    {
        if (!function_exists('curl_init')) {
            echo TEXT_ERROR_CURL_NOT_FOUND . '<br>';
            return false;
        }
        if (!$ch = curl_init()) {
            echo TEXT_ERROR_CURL_INIT . '<br>';
            return false;
        }

        $url = str_replace('&amp;', '&', $url);

        $curl_options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FRESH_CONNECT => 1,
        ];
        if ($read === 'page') {
            $curl_page_options = [
                CURLOPT_HEADER => 0,
                CURLOPT_NOBODY => 0,
                CURLOPT_FOLLOWLOCATION => 1,
            ];
        } else {
            $curl_page_options = [
                CURLOPT_HEADER => 1,
                CURLOPT_NOBODY => 1,
                CURLOPT_FOLLOWLOCATION => 0,
            ];
        }
        $curl_options = array_merge($curl_options, $curl_page_options);
        if (CURL_PROXY_REQUIRED === 'True') {
            $proxy_tunnel_flag = (defined('CURL_PROXY_TUNNEL_FLAG') && strtoupper(CURL_PROXY_TUNNEL_FLAG) === 'FALSE') ? false : true;
            $curl_options[CURLOPT_HTTPPROXYTUNNEL] = $proxy_tunnel_flag;
            $curl_options[CURLOPT_PROXYTYPE] = CURLPROXY_HTTP;
            $curl_options[CURLOPT_PROXY] = CURL_PROXY_SERVER_DETAILS;
        }
        curl_setopt_array($ch, $curl_options);

        if (!$result = curl_exec($ch)) {
            echo sprintf(TEXT_ERROR_CURL_EXEC, curl_error($ch), $url) . '<br>';
            return false;
        }

        $info = curl_getinfo($ch);
        curl_close($ch);
        if (empty($info['http_code'])) {
            echo sprintf(TEXT_ERROR_CURL_NO_HTTPCODE, $url) . '<br>';
        } elseif ($info['http_code'] != 200) {
            echo sprintf(TEXT_ERROR_CURL_ERR_HTTPCODE, $info['http_code'], $url) . '<br>';
        }
        if ($read === 'page') {
            if ($info['size_download'] == 0) {
                echo sprintf(TEXT_ERROR_CURL_0_DOWNLOAD, $url) . '<br>';
            }
            if (isset($info['download_content_length']) && $info['download_content_length'] > 0 && $info['download_content_length'] != $info['size_download']) {
                echo sprintf(TEXT_ERROR_CURL_ERR_DOWNLOAD, $url, $info['size_download'], $info['download_content_length']) . '<br>';
            }
            $info['html_page'] = $result;
        }
        return $info;
    }

///////////////////////
    protected function _SitemapReSet(): bool
    {
        $this->_SitemapReSetFile();
        $this->statisticModuleReset();
        $this->sitemapFileItemsTotal = 0;
        $this->sitemapFileSizeTotal = 0;
        $this->sitemapFileNameNumber = 0;
        $this->sitemapFileItemsMax = 0;
        $this->duplicatedLinks = [];
        return true;
    }

    protected function _SitemapReSetFile(): bool
    {
        $this->sitemapFileBuffer = '';
        $this->sitemapFileItems = 0;
        $this->sitemapFileSize = 0;
        $this->sitemapFileNameNumber++;
        return true;
    }

    protected function _SitemapCloseFile()
    {
        if (!$this->_fileIsOpen()) {
            return;
        }
        if ($this->sitemapFileItems > 0) {
            $this->sitemapFileBuffer .= $this->sitemapFileFooter;
            $this->sitemapFileSizeTotal += strlen($this->sitemapFileBuffer);
            $this->_fileWrite($this->sitemapFileBuffer);
        }
        $this->_fileClose();
        echo sprintf(TEXT_FILE_SITEMAP_INFO,
            $this->base_url . $this->sitemapFileName,
            $this->base_url . $this->sitemapFileName,
            $this->sitemapFileItems,
            $this->sitemapFileSize,
            $this->_fileSize($this->savepath . $this->sitemapFileName)
        ) . '<br>';
    }

    public function statisticModuleReset()
    {
        global $db;

        $this->statisticModuleTime = microtime(true);
        $this->statisticModuleQueries = $db->queryCount();
        $this->statisticModuleQueriesTime = $db->queryTime();
    }

    protected function _checkDuplicateLoc($loc)
    {
        global $db;
        if ($this->checkDuplicates === 'true') {
            if (isset($this->duplicatedLinks[$loc])) {
                return false;
            }
            $this->duplicatedLinks[$loc] = true;
        } elseif ($this->checkDuplicates === 'mysql') {
            $url_hash = md5($loc);
            $sql = "SELECT SQL_NO_CACHE COUNT(*) AS total FROM " . TABLE_SITEMAPXML_TEMP . " WHERE url_hash=:urlHash";
            $sql = $db->bindVars($sql, ':urlHash', $url_hash, 'string');
            $check = $db->ExecuteNoCache($sql);
            $total = $check->fields['total'];
            mysqli_free_result($check->resource);
            unset($check);
            if ($total > 0) {
                return false;
            }
            $sql = "INSERT INTO " . TABLE_SITEMAPXML_TEMP . " SET url_hash=:urlHash";
            $sql = $db->bindVars($sql, ':urlHash', $url_hash, 'string');
            $db->Execute($sql);
        }
        return true;
    }

///////////////////////
    protected function _fileOpen(string $filename, string $path = '')
    {
        if ($path === '') {
            $path = $this->savepath;
        }
        $this->fn = $filename;
        $this->fb = '';
        if (is_file($path . $filename) && !is_writable($path . $filename)) {
            @chmod($path . $filename, 0666);
        }
        if (substr($this->fn, -3) === '.gz') {
            $this->fp = gzopen($path . $filename, 'wb9');
        } else {
            $this->fp = fopen($path . $filename, 'w+');
        }
        if (!$this->fp) {
            if (!is_file($path . $filename)) {
                echo '<span style="font-weight:bold;color:red;">' . sprintf(TEXT_FAILED_TO_CREATE, $path . $filename) . '</span>' . '<br>';
            } else {
                echo '<span style="font-weight:bold;color:red;">' . sprintf(TEXT_FAILED_TO_CHMOD, $path . $filename) . '</span>' . '<br>';
            }
            $this->submitFlag = false;
        }
        return $this->fp;
    }

    protected function _fileIsOpen(): bool
    {
        return (isset($this->fp) && $this->fp !== false);
    }

    protected function _fileWrite(string $data = '')
    {
        $ret = true;
        if (strlen($this->fb) > $this->fb_maxsize || ($data === '' && strlen($this->fb) > 0)) {
            if (substr($this->fn, -3) === '.gz') {
                $ret = gzwrite($this->fp, $this->fb, strlen($this->fb));
            } else {
                $ret = fwrite($this->fp, $this->fb, strlen($this->fb));
            }
            $this->fb = '';
        }
        $this->fb .= $data;
        return $ret;
    }

    protected function _fileClose()
    {
        if (!isset($this->fp) || $this->fp == false) {
            return;
        }
        if (strlen($this->fb) > 0) {
            $this->_fileWrite();
        }
        if (substr($this->fn, -3) === '.gz') {
            gzclose($this->fp);
        } else {
            fclose($this->fp);
        }
        unset($this->fp);
    }

    protected function _fileSize(string $fn): int
    {
        clearstatcache();
        return (int)filesize($fn);
    }

    public function timefmt($s)
    {
        $m = floor($s / 60);
        $s = $s - $m * 60;
        return $m . ':' . number_format($s, 4);
    }

    protected function _url_encode($loc)
    {
        $parsed_url = parse_url($loc);
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host = $parsed_url['host'] ?? '';
        $port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user = isset($parsed_url['user']) ? rawurlencode($parsed_url['user']) : '';
        $pass = isset($parsed_url['pass']) ? ':' . rawurlencode($parsed_url['pass']) : '';
        $pass = ($user || $pass) ? $pass . '@' : '';
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        if (!empty($path)) {
            $parts = preg_split("/([\/;=])/", $path, -1, PREG_SPLIT_DELIM_CAPTURE);
            $path = '';
            foreach ($parts as $part) {
                switch ($part) {
                    case '/':
                    case ';':
                    case '=':
                        $path .= $part;
                        break;
                    default:
                        $path .= rawurlencode($part);
                        break;
                }
            }
            // legacy patch for servers that need a literal /~username
            $path = str_replace('/%7E', '/~', $path);
        }
        $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        if ($query !== '') {
            $query = str_replace('&amp;', '&', $query);
            $query = str_replace('&&', '&', $query);
            $parts = preg_split("/([&=\?])/", $query, -1, PREG_SPLIT_DELIM_CAPTURE);
            $query = '';
            foreach ($parts as $part) {
                switch ($part) {
                    case '&':
                    case '=':
                    case '?':
                        $query .= $part;
                        break;
                    default:
                        $query .= urlencode($part);
                        break;
                }
            }
            $query = str_replace('&', '&amp;', $query);
        }
        $fragment = isset($parsed_url['fragment']) ? '#' . urlencode($parsed_url['fragment']) : '';
        $loc = $scheme . $user . $pass . $host . $port . $path . $query . $fragment;
        return $loc;
    }
}
