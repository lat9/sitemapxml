<?xml version="1.0" encoding="UTF-8"?>
<!--
/**
 * gss.xsl
 *
 * @package SitemapXML
 * @copyright Copyright 2007-2012 Andrew Berezin eCommerce-Service.com
 * @copyright Copyright 2005-2012 Serge Baccou http://sourceforge.net/projects/gstoolbox
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: gss.xsl, v 1.2 17.06.2012 17:14:14 Andrew Berezin $
 */
-->

<xsl:stylesheet version="2.0"
                xmlns:html="http://www.w3.org/TR/REC-html40"
                xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
                xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>
  <!-- Root template -->
  <xsl:template match="/">
<html>
<head>
<title>Sitemap XML File</title>
<style>
<![CDATA[
h1 {
  font-weight:bold;
  font-size:1.5em;
  margin-bottom:0;
  margin-top:1px;
}
h2 {
  font-weight:bold;
  font-size:1.2em;
  margin-bottom:0;
  color:#707070;
  margin-top:1px;
}
p.sml {
  font-size:0.8em;
  margin-top:0;
}
.sortup {
  font-style:italic;
  white-space:pre;
}
.sortdown {
  font-style:italic;
  white-space:pre;
}
.url2 {
  text-align:right;
}
.lastmod {
  text-align:center;
}
.changefreq {
  text-align:center;
}
.priority {
  text-align:center;
}
#copyright {
  text-align:center;
}
]]>
</style>
<script>
<![CDATA[
var selectedColor = "blue";
var defaultColor = "black";
var hdrRows = 1;
var numeric = '..';
var desc = '..';
var html = '..';
var freq = '..';

function initXsl(tabName,fileType) {
  hdrRows = 1;

  if(fileType=="sitemap") {
    numeric = ".3.";
    desc = ".1.";
    html = ".0.";
    freq = ".2.";
    initTable(tabName);
    setSort(tabName, 3, 1);
  }
  else {
    desc = ".1.";
    html = ".0.";
    initTable(tabName);
    setSort(tabName, 1, 1);
  }

  var theURL = document.getElementById("head1");
  theURL.innerHTML += ' ' + location;
  document.title += ': ' + location;
}

function initTable(tabName) {
  var theTab = document.getElementById(tabName);
  for(r=0;r<hdrRows;r++)
   for(c=0;c<theTab.rows[r].cells.length;c++)
     if((r+theTab.rows[r].cells[c].rowSpan)>hdrRows)
       hdrRows=r+theTab.rows[r].cells[c].rowSpan;
  for(r=0;r<hdrRows; r++){
    colNum = 0;
    for(c=0;c<theTab.rows[r].cells.length;c++, colNum++){
      if(theTab.rows[r].cells[c].colSpan<2){
        theCell = theTab.rows[r].cells[c];
        rTitle = theCell.innerHTML.replace(/<[^>]+>|&nbsp;/g,'');
        if(rTitle>""){
          theCell.title = "Change sort order for " + rTitle;
          theCell.onmouseover = function(){setCursor(this, "selected")};
          theCell.onmouseout = function(){setCursor(this, "default")};
          var sortParams = 15; // bitmapped: numeric|desc|html|freq
          if(numeric.indexOf("."+colNum+".")>-1) sortParams -= 1;
          if(desc.indexOf("."+colNum+".")>-1) sortParams -= 2;
          if(html.indexOf("."+colNum+".")>-1) sortParams -= 4;
          if(freq.indexOf("."+colNum+".")>-1) sortParams -= 8;
          theCell.onclick = new Function("sortTable(this,"+(colNum+r)+","+hdrRows+","+sortParams+")");
        }
      } else {
        colNum = colNum+theTab.rows[r].cells[c].colSpan-1;
      }
    }
  }
}

function setSort(tabName, colNum, sortDir) {
  var theTab = document.getElementById(tabName);
  theTab.rows[0].sCol = colNum;
  theTab.rows[0].sDir = sortDir;
  if (sortDir)
    theTab.rows[0].cells[colNum].className='sortdown'
  else
    theTab.rows[0].cells[colNum].className='sortup';
}

function setCursor(theCell, mode){
  rTitle = theCell.innerHTML.replace(/<[^>]+>|&nbsp;|\W/g,'');
  if(mode=="selected"){
    if(theCell.style.color!=selectedColor)
      defaultColor = theCell.style.color;
    theCell.style.color = selectedColor;
    theCell.style.cursor = "hand";
    window.status = "Click to sort by '"+rTitle+"'";
  } else {
    theCell.style.color = defaultColor;
    theCell.style.cursor = "";
    window.status = "";
  }
}

function sortTable(theCell, colNum, hdrRows, sortParams){
  var typnum = !(sortParams & 1);
  sDir = !(sortParams & 2);
  var typhtml = !(sortParams & 4);
  var typfreq = !(sortParams & 8);
  var tBody = theCell.parentNode;
  while(tBody.nodeName!="TBODY"){
    tBody = tBody.parentNode;
  }
  var tabOrd = new Array();
  if(tBody.rows[0].sCol==colNum) sDir = !tBody.rows[0].sDir;
  if (tBody.rows[0].sCol>=0)
    tBody.rows[0].cells[tBody.rows[0].sCol].className='';
  tBody.rows[0].sCol = colNum;
  tBody.rows[0].sDir = sDir;
  if (sDir)
     tBody.rows[0].cells[colNum].className='sortdown'
  else
     tBody.rows[0].cells[colNum].className='sortup';
  for(i=0,r=hdrRows;r<tBody.rows.length;i++,r++){
    colCont = tBody.rows[r].cells[colNum].innerHTML;
    if(typhtml) colCont = colCont.replace(/<[^>]+>/g,'');
    if(typnum) {
      colCont*=1;
      if(isNaN(colCont)) colCont = 0;
    }
    if(typfreq) {
    switch(colCont.toLowerCase()) {
      case "always":  { 
        colCont=0; 
        break;
      }
      case "hourly":  { 
        colCont=1; 
        break;
      }
      case "daily":   { 
        colCont=2; 
        break;
      }
      case "weekly":  { 
        colCont=3; 
        break;
      }
      case "monthly": { 
        colCont=4; 
        break;
      }
      case "yearly":  { 
        colCont=5; 
        break;
      }
      case "never":   { 
        colCont=6; 
        break;
      }
    }
  }
    tabOrd[i] = [r, tBody.rows[r], colCont];
  }
  tabOrd.sort(compRows);
  for(i=0,r=hdrRows;r<tBody.rows.length;i++,r++){
    tBody.insertBefore(tabOrd[i][1],tBody.rows[r]);
  }
  window.status = "";
}

function compRows(a, b){
  if(sDir){
    if(a[2]>b[2]) return -1;
    if(a[2]<b[2]) return 1;
  } else {
    if(a[2]>b[2]) return 1;
    if(a[2]<b[2]) return -1;
  }
  return 0;
}
]]>
</script>

</head>

      <!-- Store in $fileType if we are in a sitemap or in a siteindex -->
      <xsl:variable name="fileType">
        <xsl:choose>
          <xsl:when test="//sitemap:url">sitemap</xsl:when>
          <xsl:otherwise>siteindex</xsl:otherwise>
        </xsl:choose>
      </xsl:variable>

      <!-- Body -->
      <body onLoad="initXsl('table0','{$fileType}');">

        <!-- Text and table -->
        <h1 id="head1">Sitemap XML</h1>
        <xsl:choose>
          <xsl:when test="$fileType='sitemap'"><xsl:call-template name="sitemapTable"/></xsl:when>
          <xsl:otherwise><xsl:call-template name="siteindexTable"/></xsl:otherwise>
        </xsl:choose>

        <!-- Copyright notice &#x0020; means significant space character -->
<div id="copyright">
                <a href="https://www.sitemaps.org/" target="_blank">Sitemaps.org - information about XML-based sitemaps</a>
<br />
                SitemapXML for zen-cart: © 2005-2012 <a href="https://www.zen-cart.com/member.php?4116-a_berezin" target="_blank">Andrew Berezin</a> -
                <a href="https://www.zen-cart.com/downloads.php?do=file&amp;id=367" target="_blank">Download</a> -
                <a href="https://www.zen-cart.com/showthread.php?126810-SitemapXML-v-2" target="_blank">Support thread</a>
<br />
                Google Sitemaps Stylesheets v1.5a: © 2005 <a href="https://sourceforge.net/projects/gstoolbox" target="_blank">Project</a>
</div>
      </body>
    </html>
  </xsl:template>

  <!-- siteindexTable template -->
  <xsl:template name="siteindexTable">
    <h2>Number of sitemaps in this Google sitemap index: <xsl:value-of select="count(sitemap:sitemapindex/sitemap:sitemap)"></xsl:value-of></h2>
    <p class="sml">Click on the table headers to change sorting.</p>
    <table border="1px" width="100%" class="data" id="table0" cellspacing="0" cellpadding="2px">
      <tr class="header">
        <th>Sitemap URL</th>
        <th>Last modification date</th>
      </tr>
      <xsl:apply-templates select="sitemap:sitemapindex/sitemap:sitemap">
        <xsl:sort select="sitemap:lastmod" order="descending"/>
      </xsl:apply-templates>
    </table>
  </xsl:template>

  <!-- sitemapTable template -->
  <xsl:template name="sitemapTable">
    <h2>Number of URLs in this Sitemap: <xsl:value-of select="count(sitemap:urlset/sitemap:url)"></xsl:value-of></h2>
    <p class="sml">Click on the table headers to change sorting.</p>
    <table border="1px" width="100%" class="data" id="table0" cellspacing="0" cellpadding="2px">
    <tr class="header">
      <th>Sitemap URL</th>
      <th>Last modification date</th>
      <th>Change freq.</th>
      <th>Priority</th>
    </tr>
    <xsl:apply-templates select="sitemap:urlset/sitemap:url">
      <xsl:sort select="sitemap:priority" order="descending"/>
    </xsl:apply-templates>
  </table>
  </xsl:template>

  <!-- sitemap:url template -->
  <xsl:template match="sitemap:url">
    <tr>
      <td>
        <xsl:variable name="sitemapURL"><xsl:value-of select="sitemap:loc"/></xsl:variable>
        <a href="{$sitemapURL}" target="_blank" ref="nofollow"><xsl:value-of select="$sitemapURL"></xsl:value-of></a>
      </td>
      <td class="lastmod"><xsl:value-of select="sitemap:lastmod"/></td>
      <td class="changefreq"><xsl:value-of select="sitemap:changefreq"/></td>
      <td class="priority"><xsl:value-of select="sitemap:priority"/></td>
    </tr>
    <xsl:apply-templates select="image:image">
    </xsl:apply-templates>
  </xsl:template>

  <xsl:template match="image:image">
    <tr>
      <xsl:variable name="loc"><xsl:value-of select="image:loc"/></xsl:variable>
      <td class="url2"><a href="{$loc}" target="_blank"><xsl:value-of select="image:loc"/></a></td>
      <td colspan="3"><xsl:value-of select="image:caption"/></td>
    </tr>
  </xsl:template>

  <!-- sitemap:sitemap template -->
  <xsl:template match="sitemap:sitemap">
    <tr>
      <td>
        <xsl:variable name="sitemapURL"><xsl:value-of select="sitemap:loc"/></xsl:variable>
        <a href="{$sitemapURL}"><xsl:value-of select="$sitemapURL"></xsl:value-of></a>
      </td>
      <td><xsl:value-of select="sitemap:lastmod"/></td>
    </tr>
  </xsl:template>

</xsl:stylesheet>