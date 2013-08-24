<?php
class Project_View_Helper_Admin_RenderCalendar extends Indi_View_Helper_Abstract{
    public function renderCalendar(){
        $gridFields = $this->view->trail->getItem()->gridFields->toArray();
        $actions    = $this->view->trail->getItem()->actions->toArray();
        $canadd = false; foreach ($actions as $action) if ($action['alias'] == 'save') {$canadd = true; break;}
        $currentPage = $_SESSION['admin']['indexParams'][$this->view->trail->getItem()->section->alias]['page'] ? $_SESSION['admin']['indexParams'][$this->view->trail->getItem()->section->alias]['page'] : 1;
        $filterFieldAliases = array();
        $icons = array('form', 'delete', 'save', 'toggle', 'up', 'down');
        foreach ($this->view->trail->getItem()->filters as $filter) {
            if (in_array($filter->foreign['fieldId']->foreign['elementId']['alias'], array('number','calendar','datetime'))) {
                $filterFieldAliases[] = $filter->foreign['fieldId']->alias . '-gte';
                $filterFieldAliases[] = $filter->foreign['fieldId']->alias . '-lte';
            } else {
                $filterFieldAliases[] = $filter->foreign['fieldId']->alias;
            }
        }
        // set up grid columns
        for($i = 0; $i < count($gridFields); $i++) {
            $aliases[] = array('name' => $gridFields[$i]['alias'], 'type' => 'string');
            $column = array('header' => $gridFields[$i]['title'], 'dataIndex' => $gridFields[$i]['alias'], 'sortable' => true);
            if ($i == 0) $column['flex'] = 1;
            if ($gridFields[$i]['alias'] == 'move')  $column['hidden'] = true;
            $columns[] = $column;
        }
        $fields = array_merge(array(array('name' => 'id', 'type' => 'int')), $aliases);

        $columns = array_merge(array(array('header' => 'id', 'dataIndex' => 'id', 'width' => 30, 'sortable' => true, 'align' =>'right', 'hidden' => true)), $columns);
        $a = array();
        for($i = 0; $i < count($actions); $i++) if ($actions[$i]['display'] == 1){

            $a[] =  ($actions[$i]['alias'] == 'form' && $canadd && ! $this->view->trail->getItem()->section->disableAdd ? '{
                text: "' . ACTION_CREATE . '",
                iconCls: "add",
                actionAlias: "' . $actions[$i]['alias'] . '",
                handler: function(){
                    loadContent(grid.indi.href + this.actionAlias + "/");
                }

                },' : '') . '{
                text: "' . $actions[$i]['title'] . '",
                actionAlias: "' . $actions[$i]['alias'] . '",
                '.(in_array($actions[$i]['alias'], $icons) ? 'iconCls: "' . $actions[$i]['alias'] . '",' : '').'
                handler: function(){
                    var selection = grid.getSelectionModel().getSelection();
                    if (selection.length) var row = selection[0].data;
                    ' .
                (
                $actions[$i]['rowRequired'] == 'y' ?
                    'if (!selection.length) {
                        Ext.MessageBox.show({
                            title: "' . GRID_WARNING_SELECTROW_TITLE . '",
                            msg: "' . GRID_WARNING_SELECTROW_MSG . '",
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.WARNING
                        });
                        return false;
                    } else {
                        ' . $actions[$i]['javascript'] . '
                    }
                    ' : $actions[$i]['javascript']) . '
                }
            }';
        }
        $actions = $a;
//			$actions = implode(',', $a);

        // set up dropdown to navigate through related different types of related items
        $sections = $this->view->trail->getItem()->sections->toArray();
        if (count($sections)) {
            $sectionsDropdown = "'" . GRID_SUBSECTIONS_LABEL . ":  ', '";
            $sectionsDropdown .= '<span><select style="border: 0;" name="sectionId" id="subsectionSelect">';
            $sectionsDropdown .= '<option value="">' . GRID_SUBSECTIONS_EMPTY_OPTION . '</option>';
            $maxLength = 12;
            for ($i = 0; $i < count($sections); $i++){
                $sectionsDropdown .= '<option value="' . $sections[$i]['alias'] . '">' . $sections[$i]['title'] . '</option>';
                $str = preg_replace('/&[a-z]+;/', '&', $sections[$i]['title']);
                $len = mb_strlen($str, 'utf-8');
                if ($len > $maxLength) $maxLength = $len;
            }
            $sectionsDropdown .= '</select></span>';
            $sectionsDropdown .= "'";
        }
        $tbarItems = array();
        if ($actions) $tbarItems[] = $actions;
        $tbarItems[] = "
            '->',
            '" . GRID_SUBSECTIONS_SEARCH_LABEL . ": ',
            {
                xtype: 'textfield',
                name: 'fast-search-keyword',
                height: 19,
                cls: 'fast-search-keyword',
                margin: '0 4 0 0',
                placeholder: 'Искать',
                id: 'fast-search-keyword',
                listeners: {
                    change: function(obj, newValue, oldValue, eOpts){
                        clearTimeout(timeout);
                        timeout = setTimeout(function(keyword){
                            grid.store.proxy.url = '" . $_SERVER['STD'] . ($GLOBALS['cmsOnlyMode']?'':'/admin') . "/' + json.params.section + '/index/' + (json.params.id ? 'id/' + json.params.id + '/' : '') + 'json/1/' + (keyword ? 'keyword/' + keyword + '/' : '');
                            gridStore.load();
                        }, 500, newValue);
                    }
                }
            }
        ";
        if ($sectionsDropdown) $tbarItems[] = $sectionsDropdown;
        if ($defaultSortField = $this->view->trail->getItem()->section->getForeignRowByForeignKey('defaultSortField')){
            $this->view->trail->getItem()->section->defaultSortFieldAlias = $defaultSortField->alias;
        }
        $meta = array(
            'columns' => $columns,
            'tbar' => $tbarItems,
            'fields' => $fields,
            'params' => $this->view->trail->requestParams,
            'section' => $this->view->trail->getItem()->section->toArray(),
            'trail' => $this->view->trail(),
            'entity' => $this->view->trail->getItem()->section->getForeignRowByForeignKey('entityId')->title
        );
        if ($_SERVER['STD']) $meta = json_decode(str_replace('\/admin\/', str_replace('/', '\/', $_SERVER['STD']) . '\/admin\/', json_encode($meta)));
        if ($GLOBALS['cmsOnlyMode']) $meta = json_decode(str_replace('\/admin\/', '\/', json_encode($meta)));
        ob_start();?>
    <link rel="stylesheet" type="text/css" href="/library/extjs4/examples/calendar/resources/css/calendar.css" />
    <link rel="stylesheet" type="text/css" href="/library/extjs4/examples/calendar/resources/css/examples.css" />
    <style>.x-window-body-default{background: white !important;}</style>
    <script type="text/javascript">
    var json = <?=json_encode($meta)?>;
    var timeout, timeout2;
    var eventStore, calendarStore, eventStore1, showEditWindow, calendar;
    var myMask, formMask;
    Ext.Loader.setConfig({enabled: true, paths: {'Ext.calendar': '/library/extjs4/examples/calendar/src'}});
    Ext.require([
        'Ext.calendar.util.Date',
        'Ext.calendar.CalendarPanel',
       /* 'Ext.calendar.data.MemoryCalendarStore',
        'Ext.calendar.data.MemoryEventStore',
        'Ext.calendar.data.Events',
        'Ext.calendar.data.Calendars',*/
        'Ext.calendar.form.EventWindow'
    ]);
    Ext.onReady(function(){
        var filterAliases = <?=json_encode($filterFieldAliases)?>;
        var gridColumnsAliases = [];
        for (var i =0; i < json.columns.length; i++) {
            if (json.columns[i].dataIndex != 'id' && json.columns[i].dataIndex != 'move') {
                gridColumnsAliases.push(json.columns[i].dataIndex);
            }
        }
        var filterChange = function(obj, newv, oldv){
            var params = [];
            var usedFilterAliasesThatHasGridColumnRepresentedBy = [];
            for (var i in filterAliases) {
                var filterValue = Ext.getCmp('filter-'+filterAliases[i]).getValue();
                if (filterValue != '%' && filterValue != '' && filterValue !== null) {
                    var param = {};
                    if (Ext.getCmp('filter-'+filterAliases[i]).xtype == 'datefield') {
                        param[filterAliases[i]] = Ext.getCmp('filter-'+filterAliases[i]).getRawValue();
                    } else {
                        param[filterAliases[i]] = Ext.getCmp('filter-'+filterAliases[i]).getValue();
                    }
                    params.push(param);
                    for (var j =0; j < gridColumnsAliases.length; j++) {
                        if (gridColumnsAliases[j] == filterAliases[i]) {
                            usedFilterAliasesThatHasGridColumnRepresentedBy.push(filterAliases[i]);
                        }
                    }
                }
            }
            eventStore.lastOptions.params.search = JSON.stringify(params);
            eventStore.proxy.extraParams = {search : JSON.stringify(params)};
            //Ext.getCmp('fast-search-keyword').setDisabled(usedFilterAliasesThatHasGridColumnRepresentedBy.length == gridColumnsAliases.length);
            if (obj.xtype == 'combobox') {
                if (obj.multiSelect) {
                    clearTimeout(timeout);
                    timeout = setTimeout(function(){
                        eventStore.reload();
                    }, 1000);
                    clearTimeout(timeout2);
                    timeout2 = setTimeout(function(){
                        obj.collapse();
                    }, 2000);
                } else {
                    eventStore.reload();
                }
            } else if (obj.xtype == 'datefield' && (/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/.test(obj.getRawValue()) || !obj.getRawValue().length)) {
                clearTimeout(timeout);
                timeout = setTimeout(function(){
                    eventStore.reload();
                }, 500);
            } else if (obj.xtype != 'datefield') {
                clearTimeout(timeout);
                timeout = setTimeout(function(){
                    eventStore.reload();
                }, 500);
            }
        }
        eventStore = Ext.create('Ext.data.Store', {
            model: 'Ext.calendar.data.EventModel',
            proxy:  new Ext.data.proxy.Ajax({
                url: '<?=$_SERVER['STD']?><?=$GLOBALS['cmsOnlyMode']?'':'/admin'?>/<?=$this->view->section->alias?>/index/json/1/',
                method: 'POST',
                reader: {
                    type: 'json',
                    root: 'blocks',
                    totalProperty: 'totalCount',
                    idProperty: 'id'
                }
            }),

            // private - override the default logic for memory storage
            listeners: {
                beforeload: function(store, operation, eOpts){
                    if(calendar) {
                        myMask.show();
                        return true;
                    } else {
                        return false;
                    }
                },
                load: function(){
                    // Mark rows as disabled if such rows exist
                    myMask.hide();

                    // Set up full request string (but without paging params)
                    /*var url = calendar.eventStore.getProxy().url;
                    var get = [];
                    if (calendar.eventStore.getProxy().extraParams.search) get.push('search=' + encodeURIComponent(calendar.eventStore.getProxy().extraParams.search));
                    if (calendar.eventStore.getSorters().length) get.push('sort=' + encodeURIComponent(JSON.stringify(calendar.eventStore.getSorters())));
                    calendar.indi.request = url + (get.length ? '?' + get.join('&') : '');*/
                }
            }
        });

        calendarStore = Ext.create('Ext.calendar.data.MemoryCalendarStore', {
            data: {
                "calendars":[{
                    "id":    1,
                    "title": "Home"
                },{
                    "id":    2,
                    "title": "Work"
                }]
            }
        });
        // The edit popup window is not part of the CalendarPanel itself -- it is a separate component.
        // This makes it very easy to swap it out with a different type of window or custom view, or omit
        // it altogether. Because of this, it's up to the application code to tie the pieces together.
        // Note that this function is called from various event handlers in the CalendarPanel above.
        showEditWindow = function(rec, animateTarget){
            if (form) form.destroy();
            form = Ext.create('widget.window', {
                title: 'Мероприятие',
                closable: true,
                animateTarget: animateTarget,
                width: 600,
                height: 500,
                resizable: false,
                layout: 'fit',
                for: rec.internalId,
                items: [{
                    id: 'calendar-form-panel',
                    html: '<iframe src="<?=$_SERVER['STD']?><?=$GLOBALS['cmsOnlyMode']?'':'/admin'?>/<?=$this->view->section->alias?>/form/'+(rec.internalId ? 'id/'+rec.internalId+'/' : '')+'?width=600" width="100%" height="100%" scrolling="auto" frameborder="0" id="form-frame" name="form-frame"></iframe>',
                    border: 0
                }]
            });
            form.show();
        }

        // This is the app UI layout code.  All of the calendar views are subcomponents of
        // CalendarPanel, but the app title bar and sidebar/navigation calendar are separate
        // pieces that are composed in app-specific layout code since they could be omitted
        // or placed elsewhere within the application.
        calendar = Ext.create('Ext.calendar.CalendarPanel', {
            eventStore: eventStore,
            calendarStore: calendarStore,
            border: false,
            closable: true,
            id:'<?=$this->view->section->alias?>Calendar',
            /*activeItem: 3, // month view*/
            title: '<?=$this->view->section->title?>',
            height: '100%',
            startDay: 1,
            renderTo: 'center-content-body',
            todayText: 'Сегодня',
            dayText: 'День',
            weekText: 'Неделя',
            monthText: 'Месяц',
            showDayView: true,
            showWeekView: true,
            tools: [<?if(count($filterFieldAliases)){?>{
                type: 'search',
                handler: function(event, target, owner, tool){
                    if (grid.getDockedComponent('search-toolbar').hidden) {
                        grid.getDockedComponent('search-toolbar').show();
                    } else {
                        grid.getDockedComponent('search-toolbar').hide();
                    }
                }
            }<?}?>],
            //tbar: eval('['+json.tbar+']'),
            dockedItems: [<?=$this->view->gridFilters()?>/*{
                        xtype: 'toolbar',
                        dock: 'top',
                        items: eval('['+json.tbar+']')
                    }*/],
            monthViewCfg: {
                showHeader: true,
                showWeekLinks: true,
                showWeekNumbers: true
            },
            listeners: {
                'eventclick': {
                    fn: function(vw, rec, el){
                        showEditWindow(rec, el);
                        //this.clearMsg();
                    },
                    scope: this
                },
                'viewchange': {
                    fn: function(p, vw, dateInfo){
                        if(this.editWin){
                            this.editWin.hide();
                        }
                        if(dateInfo){
                            // will be null when switching to the event edit form so ignore
                            //Ext.getCmp('app-nav-picker').setValue(dateInfo.activeDate);
                            //this.updateTitle(dateInfo.viewStart, dateInfo.viewEnd);
                        }
                    },
                    scope: this
                }
                <?if($canadd){?>,'dayclick': {
                    fn: function(vw, dt, ad, el){

                        this.showEditWindow({
                            StartDate: dt,
                            IsAllDay: ad
                        }, el);
                        //this.clearMsg();
                    },
                    scope: this
                }<?}?>
            }
        });
        $('#trail').html(json.trail);
        $('.trail-item-section').hover(function(){
            $('.trail-siblings').hide();
            var itemIndex = $(this).attr('item-index');
            var width = (parseInt($(this).width()) + 27);
            if ($('#trail-item-' + itemIndex + '-sections ul li').length) {
                $('#trail-item-' + itemIndex + '-sections').css('min-width', width + 'px');
                $('#trail-item-' + itemIndex + '-sections').css('display', 'inline-block');
            }
        }, function(){
            if (parseInt(event.pageY) < parseInt($(this).offset().top) || parseInt(event.pageX) < parseInt($(this).offset().left)) $('.trail-siblings').hide();
        });
        $('.trail-siblings').mouseleave(function(){
            $(this).hide();
        });

        myMask = new Ext.LoadMask(calendar.getEl(), {});
        calendar.setActiveView(2);
    });
    </script>
    </head>
    <div style="display:none;">
        <div id="app-header-content">
            <span id="app-msg" class="x-hidden"></span>
        </div>
    </div>
    <? return ob_get_clean();
    }
}