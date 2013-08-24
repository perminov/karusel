<?php
class Indi_View_Helper_SiteHeader extends Indi_View_Helper_Abstract{
	public function siteHeader(){
		ob_start();?>
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <?=$this->view->siteFavicon()?>
        <title><?=$this->view->seoTDK('title')?></title>
        <meta name="description" content="<?=$this->view->seoTDK('description')?>">
        <meta name="keywords" content="<?=$this->view->seoTDK('keyword')?>">
        <link rel="stylesheet" type="text/css" href="/css/style.css">
        <link rel="stylesheet" type="text/css" href="/css/adjust.css">
        <link rel="stylesheet" type="text/css" href="/library/extjs4/resources/css/ext-all.css"/>
        <link rel="stylesheet" type="text/css" href="/css/admin/layout.css"/>
        <link rel="stylesheet" type="text/css" href="/css/admin/index.css"/>
        <link rel="stylesheet" type="text/css" href="/css/admin/form.css"/>
        <link rel="stylesheet" type="text/css" href="/css/admin/combo.css"/>
        <script>var STD = '<?=$_SERVER['STD']?>';</script>
        <script type="text/javascript" src="/library/extjs4/ext-all.js"></script>
        <script type="text/javascript" src="/library/extjs4/ext-lang-ru.js"></script>
        <script type="text/javascript" src="/js/admin/index.js"></script>
        <script type="text/javascript" src="/js/admin/combo.js?6898"></script>
        <script src="/js/jquery-1.9.1.min.js"></script>
        <script src="/js/jquery-migrate-1.1.1.min.js"></script>
        <script src="/js/jquery.scrollTo-min.js"></script>
    </head>
    <body>
  <table class="main" width="1000" height="100%" align="center" border="0" style="background-color: white;">
  <tr><td colspan="4" height="100"><!--header will be here--></td></tr>
  <tr>
	<td width="200" valign="top"></td>
	<td valign="top">
	  <div>
		
		<?return ob_get_clean();
	}
}