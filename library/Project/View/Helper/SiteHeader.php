<?php
class Project_View_Helper_SiteHeader {
	public function siteHeader(){
		ob_start();?>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <?=Indi::view()->siteFavicon()?>
        <title><?=Indi::view()->siteMetatag('title')?></title>
        <meta name="description" content="<?=Indi::view()->siteMetatag('description')?>">
        <meta name="keywords" content="<?=Indi::view()->siteMetatag('keywords')?>">
        <link rel="stylesheet" href="/library/jquery-ui-1.12.1/jquery-ui.min.css"/>
        <link rel="stylesheet" href="/library/select2/select2.min.css"/>
        <link rel="stylesheet" href="/css/iform.css"/>
        <link rel="stylesheet" href="/css/form.css"/>
        <script src="/js/jquery-1.10.2.min.js"></script>
        <script src="/js/jquery.mask.js"></script>
        <script src="/library/jquery-ui-1.12.1/jquery-ui.min.js"></script>
        <script std="<?=STD?>" src="/js/indi.js"></script>
        <script src="/library/select2/select2.min.js"></script>
    </head>

		<?return ob_get_clean();
	}
}