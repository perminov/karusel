<?php
class Project_View_Helper_Admin_RenderCalendar {
    public function renderCalendar(){
        $gridFields = Indi::trail()->gridFields->toArray();
        $actions    = Indi::trail()->actions->toArray();
        $canadd = false; foreach ($actions as $action) if ($action['alias'] == 'save') {$canadd = true; break;}
        $currentPage = $this->view->getScope('page') ? $this->view->getScope('page') : 1;
        $filterFieldAliases = array();
        $icons = array('form', 'delete', 'save', 'toggle', 'up', 'down');
        $comboFilters = array();
        foreach (Indi::trail()->filters as $filter) {
            if (in_array($filter->foreign['fieldId']->foreign['elementId']['alias'], array('number','calendar','datetime'))) {
                $filterFieldAliases[] = $filter->foreign['fieldId']->alias . '-gte';
                $filterFieldAliases[] = $filter->foreign['fieldId']->alias . '-lte';
            } else {
                if ($filter->foreign['fieldId']->relation) $comboFilters[] = $this->view->filterCombo($filter);
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

            $a[] =  ($actions[$i]['alias'] == 'form' && $canadd && ! Indi::trail()->section->disableAdd ? '{
                text: "' . ACTION_CREATE . '",
                iconCls: "add",
                actionAlias: "' . $actions[$i]['alias'] . '",
                handler: function(){
                    loadContent(grid.indi.href + this.actionAlias + "/ph/" + Indi.trail.item().section.primaryHash + "/");
                }

                },' : '') . '{
                text: "' . $actions[$i]['title'] . '",
                actionAlias: "' . $actions[$i]['alias'] . '",
                id: "action-button-' . $actions[$i]['alias'] . '",
                '.(in_array($actions[$i]['alias'], $icons) ? 'iconCls: "' . $actions[$i]['alias'] . '",' : '').'
                handler: function(){
                    var selection = grid.getSelectionModel().getSelection();
                    if (selection.length) {
                        var row = selection[0].data;
                        var aix = selection[0].index + 1;
                    }
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
        $sections = Indi::trail()->sections->toArray();
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
                value: '" . urldecode($this->view->getScope('keyword')) . "',
                height: 19,
                cls: 'i-form-text',
                margin: '0 4 0 0',
                placeholder: 'Искать',
                id: 'fast-search-keyword',
                listeners: {
                    change: function(obj, newValue, oldValue, eOpts){
                        clearTimeout(timeout);
                        timeout = setTimeout(function(keyword){
                            grid.store.proxy.url = '" . STD . (COM?'':'/admin') . "/' + json.params.section + '/index/' + (json.params.id ? 'id/' + json.params.id + '/' : '') + 'json/1/' + (keyword ? 'keyword/' + keyword + '/' : '');
                            filterChange({});
                        }, 500, newValue);
                    }
                }
            }
        ";
        if ($sectionsDropdown) $tbarItems[] = $sectionsDropdown;
        if ($savedOrder = json_decode($this->view->getScope('order'))) {
            Indi::trail()->section->defaultSortFieldAlias = $savedOrder[0]->property;
            Indi::trail()->section->defaultSortDirection = $savedOrder[0]->direction;
        } else if ($defaultSortField = Indi::trail()->section->foreign('defaultSortField')){
            Indi::trail()->section->defaultSortFieldAlias = $defaultSortField->alias;
        }
        $meta = array(
            'columns' => $columns,
            'tbar' => $tbarItems,
            'fields' => $fields,
            'params' => Indi::trail(true)->requestParams,
            'section' => Indi::trail()->section->toArray(),
            'trail' => Indi::trail(true)(),
            'entity' => Indi::trail()->section->foreign('entityId')->title
        );
        if (STD) $meta = json_decode(str_replace('\/admin\/', str_replace('/', '\/', STD) . '\/admin\/', json_encode($meta)));
        if (COM) $meta = json_decode(str_replace('\/admin\/', '\/', json_encode($meta)));
        ob_start();?>
    <link rel="stylesheet" type="text/css" href="/library/extjs4/examples/calendar/resources/css/calendar.css?3" />
    <link rel="stylesheet" type="text/css" href="/library/extjs4/examples/calendar/resources/css/examples.css" />
    <style>
        .x-window-body-default{background: white !important;}
        .ext-cal-dayview .ext-cal-body-ct .ext-cal-bg-tbl{height: 420px !important;}
        .ext-cal-day-col-gutter {height: 420px !important; margin-top: -420px; margin-right: 0px;}
        .ext-cal-day-col .ext-cal-evt.ext-color-1 {border: 1px solid #306da6;}
        .ext-cal-day-col .ext-cal-evt.ext-color-2 {border: 1px solid #86a723;}
        .ext-cal-day-col .ext-cal-evt.ext-color-5 {border: 1px solid #cc7a00;}
		.ext-color-5, .x-ie .ext-color-5-ad, .x-opera .ext-color-5-ad{color: #ff9900;}
		.ext-cal-day-col .ext-color-5, .ext-dd-drag-proxy .ext-color-5, .ext-color-5-ad, .ext-color-5-ad .ext-cal-evm, .ext-color-5 .ext-cal-picker-icon, .ext-color-5-x dl, .ext-color-5-x .ext-cal-evb {
		background: #ff9900;
		}		
        .ext-cal-day-col .ext-evt-bd {line-height: 14px; font-size: 12px; word-break: break-all;}
    </style>
    <script type="text/javascript">
    Indi.section = '<?=Indi::trail()->section->alias?>';
    var json = <?=json_encode($meta)?>;
    var timeout, timeout2;
    var eventStore, calendarStore, eventStore1, showEditWindow, calendar;
    var formMask;
    var filterChange;
    Ext.Loader.setConfig({enabled: true, paths: {'Ext.calendar': Indi.std+'/library/extjs4/examples/calendar/src'}});
    Ext.require([
        'Ext.calendar.util.Date',
        'Ext.calendar.CalendarPanel',
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
        filterChange = function(obj, newv, oldv){
            var params = [];
            var usedFilterAliasesThatHasGridColumnRepresentedBy = [];
            for (var i in filterAliases) {
                var filterValue = Ext.getCmp('filter-'+filterAliases[i]).getValue();
                if (filterValue != '%' && filterValue != '' && filterValue !== null) {
                    var param = {};
                    if (Ext.getCmp('filter-'+filterAliases[i]).xtype == 'datefield') {
                        if(Ext.getCmp('filter-'+filterAliases[i]).format != 'Y-m-d') {
                            param[filterAliases[i]] = Ext.Date.format(Ext.Date.parse(Ext.getCmp('filter-'+filterAliases[i]).getRawValue(), Ext.getCmp('filter-'+filterAliases[i]).format), 'Y-m-d');
                        } else {
                            param[filterAliases[i]] = Ext.getCmp('filter-'+filterAliases[i]).getRawValue();
                        }
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
            if (!obj.noReload) {
                eventStore.currentPage = 1;
                eventStore.lastOptions.page = 1;
                eventStore.lastOptions.start = 0;
                if (obj.xtype == 'combobox') {
                    eventStore.reload();
                } else if (obj.xtype == 'datefield' && (/^([0-9]{4}-[0-9]{2}-[0-9]{2}|[0-9]{2}\.[0-9]{2}\.[0-9]{4})$/.test(obj.getRawValue()) || !obj.getRawValue().length)) {
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
        }
        var myMask;
        eventStore = Ext.create('Ext.data.Store', {
            model: 'Ext.calendar.data.EventModel',
            pageSize: <?=Indi::trail()->section->rowsOnPage?>,
            proxy:  new Ext.data.proxy.Ajax({
                url: '<?=PRE?>/<?=Indi::trail()->section->alias?>/index/json/1/',
                method: 'POST',
                reader: {
                    type: 'json',
                    root: 'blocks',
                    totalProperty: 'totalCount',
                    idProperty: 'id'
                }
            }),
            currentPage: <?=$currentPage?>,

            // private - override the default logic for memory storage
            listeners: {
                beforeload: function(store, operation, eOpts){
                    if (Ext.getCmp('i-center-content')) {
                        var requested;
                        if (operation.params.start == operation.params.end) {
                            requested = 'dayview';
                        } else {
                            var start = Ext.Date.parse(operation.params.start, 'm-d-Y');
                            var end = Ext.Date.parse(operation.params.end, 'm-d-Y');
                            if (Ext.Date.format(Ext.Date.add(start, Ext.Date.DAY, 6), 'm-d-Y') == operation.params.end) {
                                requested = 'weekview';
                            } else {
                                requested = 'monthview';
                            }
                        }
                        if (Ext.getCmp('i-center-content').activeView && Ext.getCmp('i-center-content').activeView.xtype == requested) {
                            if (!Ext.getCmp('i-center-content').lastFetch || Ext.Date.add(Ext.getCmp('i-center-content').lastFetch, Ext.Date.MILLI, 500) <= new Date()) {
                                Ext.getCmp('i-center-content').lastFetch = new Date();
                                myMask.show();
                                return true;
                            } else {
                                return false;
                            }
                        } else {
                            return false;
                        }
                    } else {
                        return false;
                    }
                },
                load: function(){
					var autoForm = $.cookie('last-row-id');
					if (autoForm && eventStore.getById(parseInt(autoForm))) 
						showEditWindow(eventStore.getById(parseInt(autoForm)), $('.i-center-content-month-evt-'+autoForm)[0] || $('#i-center-content-month')[0]);
					$.removeCookie('last-row-id', {path: '/'});
                    myMask.hide();
                }
            }
        });

        calendarStore = Ext.create('Ext.calendar.data.MemoryCalendarStore', {
            data: {
                "calendars":[{
                    "id":    1,
                    "title": "Предварительные"
                },{
                    "id":    2,
                    "title": "Потвержденные"
                },{
                    "id":    3,
                    "title": "Клиентские"
                },{
                    "id":    4,
                    "title": "Проведенные"
                }, {
					"id":    5,
					"title": "Отмененные"
				}]
            }
        });
        // The edit popup window is not part of the CalendarPanel itself -- it is a separate component.
        // This makes it very easy to swap it out with a different type of window or custom view, or omit
        // it altogether. Because of this, it's up to the application code to tie the pieces together.
        // Note that this function is called from various event handlers in the CalendarPanel above.
        showEditWindow = function(rec, animateTarget){
            var aix = rec.index + 1;
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
                    html: '<iframe src="<?=PRE?>/<?=Indi::trail()->section->alias?>/form/'+(rec.internalId ? 'id/'+rec.internalId+'/' : '')+'ph/'+Indi.trail.item().section.primaryHash+'/aix/'+aix+'/?width=600" width="100%" height="100%" scrolling="auto" frameborder="0" id="form-frame" name="form-frame"></iframe>',
                    border: 0
                }]
            });
            form.show();
        }

        // This is the app UI layout code.  All of the calendar views are subcomponents of
        // CalendarPanel, but the app title bar and sidebar/navigation calendar are separate
        // pieces that are composed in app-specific layout code since they could be omitted
        // or placed elsewhere within the application.
        //calendar = null;
        Ext.create('Ext.calendar.CalendarPanel', {
            eventStore: eventStore,
            calendarStore: calendarStore,
            border: false,
            closable: true,
            id:'i-center-content',
            /*activeItem: 3, // month view*/
            title: '<?=Indi::trail()->section->title?>',
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
                    if (Ext.getCmp('i-center-content').getDockedComponent('search-toolbar').hidden) {
                        Ext.getCmp('i-center-content').getDockedComponent('search-toolbar').show();
                    } else {
                        Ext.getCmp('i-center-content').getDockedComponent('search-toolbar').hide();
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
                            Ext.getCmp(p.id+'-tb-month').setText(Ext.Date.format(dateInfo.activeDate, 'F'));
                            //console.log(Ext.getCmp(p.id+'-tb-month'));
                            // will be null when switching to the event edit form so ignore
                            //Ext.getCmp('app-nav-picker').setValue(dateInfo.activeDate);
                            //this.updateTitle(dateInfo.viewStart, dateInfo.viewEnd);
                        }
                    },
                    scope: this
                },
                afterrender: function(){
                    Indi.combo.filter = Indi.combo.filter || new Indi.proto.combo.filter(); Indi.combo.filter.run();
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
        myMask = new Ext.LoadMask(Ext.getCmp('i-center-content').getEl().first('.x-panel-body'), {});
        myMask.show();
        filterChange({noReload: true});
        Ext.getCmp('i-center-content').setActiveView(2);
        mainPanel = Ext.getCmp('i-center-content');
        currentPanelId = Ext.getCmp('i-center-content').id;
    });
    </script>
    <script>
        Indi.trail.apply(<?=json_encode(Indi::trail(true)->toArray())?>);
        Indi.scope = <?=json_encode($this->view->getScope())?>;
        top.Indi.scope = Indi.scope;
    </script>
    <?//if (count($comboFilters)){echo '<span style="display: none">'.implode('', $comboFilters) . '</span>'; }?>
    <?if (count($comboFilters)){
            echo '<span style="display: none">'.implode('', $comboFilters) . '</span>';
            ?><script>Indi.combo.filter = Indi.combo.filter || new Indi.proto.combo.filter(); /*Indi.combo.filter.run();*/</script><?
        }?>

    </head>
    <div style="display:none;">
        <div id="app-header-content">
            <span id="app-msg" class="x-hidden"></span>
        </div>
    </div>
    <? return ob_get_clean();
    }
}