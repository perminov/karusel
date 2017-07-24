<?php
class Project_View_Helper_SiteHeader {
	public function siteHeader(){
        return;
		ob_start();?>
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <?=Indi::view()->siteFavicon()?>
        <title><?=Indi::view()->siteMetatag('title')?></title>
        <meta name="description" content="<?=Indi::view()->siteMetatag('description')?>">
        <meta name="keywords" content="<?=Indi::view()->siteMetatag('keyword')?>">
        <link rel="stylesheet" type="text/css" href="/css/style.css">
        <link rel="stylesheet" type="text/css" href="/css/adjust.css">
        <link rel="stylesheet" type="text/css" href="/library/extjs4/resources/css/ext-all.css"/>
        <link rel="stylesheet" type="text/css" href="/css/admin/layout.css"/>
        <link rel="stylesheet" type="text/css" href="/css/admin/index.css"/>
        <link rel="stylesheet" type="text/css" href="/css/admin/form.css"/>
        <link rel="stylesheet" type="text/css" href="/css/admin/indi.combo.css"/>
        <script>var STD = '<?=STD?>';</script>
        <script type="text/javascript" src="/library/extjs4/ext-all.js"></script>
        <script type="text/javascript" src="/library/extjs4/ext-lang-ru.js"></script>
        <script type="text/javascript" src="/js/admin/indi.js?<?=rand(0, 10000)?>"></script>
        <script type="text/javascript" src="/js/admin/indi.combo.form.js?<?=rand(0, 10000)?>"></script>
        <script src="/js/jquery-1.9.1.min.js"></script>
        <script src="/js/jquery-migrate-1.1.1.min.js"></script>
        <script src="/js/jquery.scrollTo-min.js"></script>
    </head>
    <body>
  <table class="main" width="1000" height="100%" align="center" border="0" style="background-image: url(./i/dr.png); background-repeat: no-repeat;">
  <tr><td colspan="4" height="160"><!--header will be here--></td></tr>
  <tr>
	<td width="200" valign="top"  style="background-image: url(./i/balls-left.png); background-repeat: no-repeat;"></td>
	
    <td valign="top">
    
    Заполните форму ниже для регистрации заявки на проведение мероприятия.<br> 
    Вы получите подтверждение регистрации заявки по электронной почте.<br>
    Наш менеджер свяжется с Вами в течение одного дня для согласования деталей проведения мероприятия.<br><br>
    
	  <div>
		
		<?return ob_get_clean();
	}
}