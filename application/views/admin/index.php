<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>КЕНГУРУ. Система управления мероприятиями</title>
	<link rel="stylesheet" type="text/css" href="/library/extjs4/resources/css/ext-all.css" />
	<link rel="stylesheet" type="text/css" href="/css/admin/layout.css?1" />
	<link rel="stylesheet" type="text/css" href="/css/admin/index.css" />
	<link rel="stylesheet" type="text/css" href="/css/admin/form.css" />
	<script type="text/javascript" src="/library/extjs4/ext-all.js"></script>
    <?$config = Indi_Registry::get('config');?>
	<script type="text/javascript" src="/library/extjs4/ext-lang-<?=$config['view']->lang?>.js"></script>
	<script type="text/javascript" src="/js/admin/index.js"></script>
	<script type="text/javascript" src="/js/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="/js/jquery-migrate-1.1.1.min.js"></script>
    <style>
        button span.add {
            background-image: url('<?=$_SERVER['STD']?>/library/extjs4/resources/themes/images/default/shared/add.gif') !important;
        }
        button span.form{
            background-image: url('<?=$_SERVER['STD']?>/library/extjs4/resources/themes/images/default/shared/form.gif') !important;
        }
        button span.delete{
            background-image: url('<?=$_SERVER['STD']?>/library/extjs4/resources/themes/images/default/shared/delete.gif') !important;
        }
        button span.back{
            background-image: url('<?=$_SERVER['STD']?>/i/admin/icon-action-back.gif') !important;
        }
        button span.save{
            background-image: url('<?=$_SERVER['STD']?>/i/admin/icon-action-save.png') !important;
        }
        button span.toggle{
            background-image: url('<?=$_SERVER['STD']?>/i/admin/icon-action-toggle.png') !important;
        }
        button span.up{
            background-image: url('<?=$_SERVER['STD']?>/i/admin/icon-action-upper.png') !important;
        }
        button span.down{
            background-image: url('<?=$_SERVER['STD']?>/i/admin/icon-action-lower.png') !important;
        }
    </style>
</head>
<body>
<script>Ext.require(['*']); var viewport, menu, mainPanel, grid, form, loadContent, currentPanelId, locationHistory = [];</script>
<?=$this->menu()?>
<?=$this->viewport()?>
</body>
</html>