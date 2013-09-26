<?php
echo $this->formHeader('Договор на проведение мероприятия');
echo '<tr><td colspan="2" align="center" style="padding-top: 10px;">';
$this->months = array(
    '01' => 'Января', '02' => 'Февраля', '03' => 'Марта', '04' => 'Апреля', '05' => 'Мая', '06' => 'Июня',
    '07' => 'Июля', '08' => 'Августа', '09' => 'Сентября', '10' => 'Октября', '11' => 'Ноября', '12' => 'Декабря'
);
$this->row->setForeignRowsByForeignKeys('districtId,placeId,animatorIds,programId,timeId');

$name = 'agreement';
$value = $this->render('managerCalendarMydistrict/agreementTemplate.php');
$config = Indi_Registry::get('config');
$CKconfig['language'] = $config['view']->lang;
$customParams = array('width','height','bodyClass','style','script','sourceStripper');
foreach($customParams as $customParam) {
    if ($this->view->row->{$name . ucfirst($customParam)}) {
        $params[$customParam] = $this->view->row->{$name . ucfirst($customParam)};
    }
}
$params['width'] = 692;
$params['height'] = 380;
$params['style'] = 'body{margin-right: 20px;} *{line-height: 15px !important;}';
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

// take in attention of $_SERVER['STD']
if (is_array($CKconfig['contentsCss'])) {
    for ($i = 0; $i < count($CKconfig['contentsCss']); $i++) {
        if (preg_match('/^\/.*\.css$/', $CKconfig['contentsCss'][$i])) {
            $CKconfig['contentsCss'][$i] = $_SERVER['STD'] . $CKconfig['contentsCss'][$i];
        }
    }
}
if (is_array($CKconfig['contentsJs'])) {
    for ($i = 0; $i < count($CKconfig['contentsJs']); $i++) {
        if (preg_match('/^\/.*\.js$/', $CKconfig['contentsJs'][$i])) {
            $CKconfig['contentsJs'][$i] = $_SERVER['STD'] . $CKconfig['contentsJs'][$i];
        }
    }
}
$CKconfig['readOnly'] = true;
?>
<textarea id="<?=$name?>" name="<?=$name?>"><?=str_replace(array('<','>'), array('&lt;','&gt;'), $value)?></textarea>
<script>
    CKFinder.setupCKEditor(null, '<?=$_SERVER['STD']?>/library/ckfinder/');
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
$title[] = BUTTON_BACK;
$action[] = "top.window.loadContent('". $_SERVER['STD'] . ($GLOBALS['cmsOnlyMode'] ? '' : "/" . $this->module) . '/' . $this->section->alias . '/' . ($parent->row ? 'index/id/' . $parent->row->id . '/' : '') . '\')';
$title[] = 'Распечатать';
$action[] = 'CKEDITOR.tools.callFunction(9, CKEDITOR.instances.agreement)';
echo $this->buttons($title, $action);
$xhtml .= '</form>';

$sections = $this->trail->getItem()->sections->toArray();
if (count($sections)) {
    $sectionsDropdown = array();
    $maxLength = 12;
    // $sectionsDropdown[] = array('alias' => '', 'title' => '--Выберите--');
    for ($i = 0; $i < count($sections); $i++){
        $sectionsDropdown[] = array('alias' => $sections[$i]['alias'], 'title' => $sections[$i]['title']);
        $str = preg_replace('/&[a-z]+;/', '&', $sections[$i]['title']);
        $len = mb_strlen($str, 'utf-8');
        if ($len > $maxLength) $maxLength = $len;
    }
}
ob_start();?>
<script>
    $(document).ready(function(){
        var parent = top.window.$('iframe[name="form-frame"]').parent();
        while (parent.attr('id') != 'center-content-body') {
            parent.css('height', '100%');
            parent = parent.parent();
        }
    })
</script>
<?
$xhtml = ob_get_clean();
$parent = $this->trail->getItem(1);
$this->trail->items[1]->actions->exclude(3);
$actionA = $this->trail->getItem()->actions->toArray();
foreach ($actionA as $actionI) if ($actionI['alias'] == 'save') {$save = true; break;}
ob_start();?>
<script>
    var toolbar = {
        xtype: 'toolbar',
        dock: 'top',
        id: 'topbar',
        items: [
            {
                text: '<?=BUTTON_BACK?>',
                handler: function(){
                    top.window.loadContent('<?=$_SERVER['STD'] . ($GLOBALS['cmsOnlyMode'] ? '' : '/' . $this->module) . '/' . $this->section->alias . '/' . ($parent->row ? 'index/id/' . $parent->row->id . '/' : '')?>')
                },
                iconCls: 'back',
                id: 'button-back'
            }
            ,{
                text: 'Распечатать',
                handler: function(){
                    CKEDITOR.tools.callFunction(9, CKEDITOR.instances.agreement)
                },
                id: 'button-print'
            },
        <? if ($this->trail->getItem()->row->id && count($sections)){?>
            '->',
            '<?=GRID_SUBSECTIONS_LABEL?>: ',
            top.window.Ext.create('Ext.form.ComboBox', {
                store: top.window.Ext.create('Ext.data.Store',{
                    fields: ['alias', 'title'],
                    data: <?=json_encode($sectionsDropdown)?>
                }),
                valueField: 'alias',
                hiddenName: 'alias',
                displayField: 'title',
                typeAhead: false,
                width: <?=$maxLength*7+10?>,
                style: 'font-size: 10px',
                cls: 'subsection-select',
                id: 'subsection-select',
                editable: false,
                margin: '0 6 2 0',
                value: '<?=GRID_SUBSECTIONS_EMPTY_OPTION?>',
                listeners: {
                    change: function(cmb, newv, oldv){
                        if (this.getValue()) {
                            top.window.loadContent('<?=$_SERVER['STD']?><?=$GLOBALS['cmsOnlyMode']?'':'/admin'?>/' + cmb.getValue() + '/index/id/' + <?=$this->row->id?> + '/');
                        }
                    }
                }
            })
            <?}?>
        ]
    }
    var topbar = top.window.form.getDockedComponent('topbar');
    if (topbar) top.window.form.removeDocked(topbar);
    top.window.form.addDocked(toolbar);
    var topbar = top.window.form.getDockedComponent('topbar');
    var height = (top.window.$('#center-content-body').height() - topbar.getHeight() - 1);
    if (top.window.$('iframe[name="form-frame"]').height() > height) top.window.$('iframe[name="form-frame"]').css('height', height + 'px');
</script>
<? $xhtml .= ob_get_clean();
ob_start();?>
<script>
    $(document).ready(function(){
        var topbar = top.window.form.getDockedComponent('topbar');
        if (topbar != undefined) {
            var height = top.window.$('#center-content-body').height() - topbar.getHeight() - 1;
        } else {
            var height = top.window.$('#center-content-body').height() - 1;
        }
        top.window.$('iframe[name="form-frame"]').css('height', height + 'px');
    });
</script>
<? $xhtml .= ob_get_clean();
echo $xhtml;