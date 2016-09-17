<?php
echo $this->formHeader('Договор на проведение мероприятия');
echo '<tr><td colspan="2" align="center" style="padding-top: 10px;">';
$this->months = array(
    '01' => 'Января', '02' => 'Февраля', '03' => 'Марта', '04' => 'Апреля', '05' => 'Мая', '06' => 'Июня',
    '07' => 'Июля', '08' => 'Августа', '09' => 'Сентября', '10' => 'Октября', '11' => 'Ноября', '12' => 'Декабря'
);
$this->row->foreign('districtId,placeId,animatorId,programId,subprogramId,timeId');
$name = 'agreement';
$value = $this->render('managerCalendarMydistrict/agreementTemplate.php');
$CKconfig['language'] = Indi::ini('view')->lang;
$customParams = array('width','height','bodyClass','style','script','sourceStripper');
foreach($customParams as $customParam) {
    if ($this->view->row->{$name . ucfirst($customParam)}) {
        $params[$customParam] = $this->view->row->{$name . ucfirst($customParam)};
    }
}
$params['width'] = 692;
$params['height'] = 380;

$params['style'] = '
body {
background-image: url('.STD.'/i/admin/bg-dogovor.jpg);
background-repeat:no-repeat; 
background-position: 50% 95%;
margin-right: 0px; 
margin-left: 25px;
} 

*{font-family:Verdana, Arial, Helvetica, sans-serif; font-size:10.4px; line-height: 12px !important;}';

// Set up styles configuration for editor contents
if ($params['style']) $CKconfig['style'] = $params['style'];
$CKconfig['style'] .= 'body{max-width: auto;min-width: auto;width: auto;}';
if ($params['contentsCss']) $CKconfig['contentsCss'] = preg_match('/^\[/', $params['contentsCss']) ? json_decode($params['contentsCss']) : $params['contentsCss'];
if (is_array($CKconfig['contentsCss'])) {
    $CKconfig['contentsCss'] = array_merge($CKconfig['contentsCss'], array($CKconfig['style'] . ' body{max-width: auto;min-width: auto;width: auto;}'));
} else {
    $CKconfig['contentsCss'] = array($CKconfig['contentsCss'], $CKconfig['style'] . ' body{max-width: auto;min-width: auto;width: auto;}');
}
if ($params['bodyClass']) $CKconfig['bodyClass'] = $params['bodyClass'];
$CKconfig['uiColor'] = '#B8D1F7';

// Set up editor size
if ($params['width']) $CKconfig['width'] = $params['width'] + 52;
if ($params['height']) $CKconfig['height'] = $params['height'];

// Set up editor javascript
if ($params['script']) $CKconfig['script'] = $params['script'];
if ($params['contentsJs']) $CKconfig['contentsJs'] = preg_match('/^\[/', $params['contentsJs']) ? json_decode($params['contentsJs']) : $params['contentsJs'];
if (is_array($CKconfig['contentsJs'])) {
    $CKconfig['contentsJs'] = array_merge($CKconfig['contentsJs'], array($CKconfig['script']));
} else {
    $CKconfig['contentsJs'] = array($CKconfig['contentsJs'], $CKconfig['script']);
}

// Set up stripping some elements from html-code if Source button is toggled
if ($params['sourceStripper']) $CKconfig['sourceStripper'] = $params['sourceStripper'];

// take in attention of STD
if (is_array($CKconfig['contentsCss'])) {
    for ($i = 0; $i < count($CKconfig['contentsCss']); $i++) {
        if (preg_match('/^\/.*\.css$/', $CKconfig['contentsCss'][$i])) {
            $CKconfig['contentsCss'][$i] = STD . $CKconfig['contentsCss'][$i];
        }
    }
}
if (is_array($CKconfig['contentsJs'])) {
    for ($i = 0; $i < count($CKconfig['contentsJs']); $i++) {
        if (preg_match('/^\/.*\.js$/', $CKconfig['contentsJs'][$i])) {
            $CKconfig['contentsJs'][$i] = STD . $CKconfig['contentsJs'][$i];
        }
    }
}
$CKconfig['readOnly'] = true;
?>
<textarea id="<?=$name?>" name="<?=$name?>"><?=str_replace(array('<','>'), array('&lt;','&gt;'), $value)?></textarea>
<script>
    CKFinder.setupCKEditor(null, '<?=STD?>/library/ckfinder/');
    var config = <?=json_encode($CKconfig)?>;

    config.toolbar = [
        {items: ['Source', 'Print'] },
        {items: [ 'Paste', 'PasteText', 'PasteFromWord', 'Table'] },
        {items: [ 'Image', 'Flash', 'oembed','Link', 'Unlink'] },
        {items: [ 'Bold', 'Italic', 'Underline'] },
        {items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent'] },
        {items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
        {items: ['Format'] },
        {items: ['Font'] },
        {items: ['FontSize' ] },
        {items: [ 'TextColor', 'BGColor', '-', 'Blockquote', 'CreateDiv' ] },
        {items: [ 'Maximize', 'ShowBlocks', 'Find', '-', 'RemoveFormat'  ] }
    ];
    config.enterMode = CKEDITOR.ENTER_BR;

    CKEDITOR.replace('<?=$name?>', config);$('#td-wide-<?=$name?>').css('padding-bottom', '1px');$('#tr-<?=$name?>').css('padding-bottom', '1px');
</script>
<?
echo '</td></tr>';
$xhtml  = '</table>';
$title[] = I_BACK;
$action[] = "top.window.Ext.getCmp('i-action-form-topbar-button-back').handler();";
$title[] = 'Распечатать';
$action[] = 'CKEDITOR.tools.callFunction(9, CKEDITOR.instances.agreement)';
echo $this->buttons($title, $action);
$xhtml .= '</form>';

ob_start();?>
<script>
    Ext.onReady(function(){
        Ext.util.Cookies.set(
            'last-row-id',
            <?=$this->row->id?>,

            // We set cookie expire date as 1 month
            Ext.Date.add(new Date(), Ext.Date.MONTH, 1),
            Indi.pre + '/'
        )
		//$.cookie('last-row-id', <?=$this->row->id?>, {path: '/'});
        top.window.Ext.getCmp('i-action-form-topbar-button-save').disable();
        top.window.Ext.getCmp('i-action-form-topbar-checkbox-autosave').disable();
        top.window.Ext.getCmp('i-action-form-topbar-button-add').disable();
    });
</script>
<?
$xhtml = ob_get_clean();
$parent = Indi::trail(1);
Indi::trail()->actions->exclude(3);
$actionA = Indi::trail()->actions->toArray();
foreach ($actionA as $actionI) if ($actionI['alias'] == 'save') {$save = true; break;}
echo $xhtml;