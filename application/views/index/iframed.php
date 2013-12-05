<link rel="stylesheet" type="text/css" href="/library/extjs4/resources/css/ext-all.css"/>
<link rel="stylesheet" type="text/css" href="/css/iframed.css"/>
<link rel="stylesheet" type="text/css" href="/css/admin/combo.css"/>
<script>var STD = '<?=$_SERVER['STD']?>';</script>
<script type="text/javascript" src="/library/extjs4/ext-all.js"></script>
<script type="text/javascript" src="/library/extjs4/ext-lang-ru.js"></script>
<script type="text/javascript" src="/js/admin/index.js"></script>
<script type="text/javascript" src="/js/admin/indi.js?<?=rand(0, 10000)?>"></script>
<script type="text/javascript" src="/js/admin/indi.combo.form.js?<?=rand(0, 10000)?>"></script>
<script src="/js/jquery-1.9.1.min.js"></script>
<script src="/js/jquery-migrate-1.1.1.min.js"></script>
<script src="/js/jquery.scrollTo-min.js"></script>

<? $this->row = Misc::loadModel('Event')->createRow(); ?>
<? if (!$_SESSION['admin']['id']) $_SESSION['admin'] = array('id' => '15', 'email' => 'visitor@gmail.com', 'password' => 'visitor', 'profileId' => '17'); ?>
<script>
    var email = /^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
    window.comboFetchRelativePath = '/admin/client/form';
    window.publicTimes = [];
    Ext.require(['*']);
</script>
<style>
    form.form table tr {
        border: none !important;
    }
    table.x-field tr{
        border: none !important;
    }
</style>
<div class="features-list" style="margin-top: 0px;">
    <h3 class="feature-header" style="font-size: 32px;">Заявка на проведение<br>праздника</h3>
</div>

<form class="form row-form" action="/admin/client/save/" name="event" method="post" enctype="multipart/form-data" row-id="" style="">
<table celpadding="2" cellspacing="1" border="0" width="100%">
<col width="50%"/><col width="50%"/>
<tr class="info" id="tr-districtId">
    <td width="50%" id="td-left-districtId">Место проведения мероприятия:</td>
    <td width="50%" id="td-right-districtId"><?=$this->formCombo('districtId', 'event')?></td>
</tr>
<tr class="info" id="tr-placeId" style="border-top: 0px;">
    <td width="50%" id="td-left-placeId"></td>
    <td width="50%" id="td-right-placeId"><?=$this->formCombo('placeId', 'event')?></td>
</tr>
<tr class="info" id="tr-date">
    <td width="50%" id="td-left-date">Дата:</td>
    <td width="50%" id="td-right-date">
        <div style="position: relative; z-index: 99" id="calendardateDiv" class="calendar-div"><input type="text"
                                                                                                      name="date"
                                                                                                      value=""
                                                                                                      style="width: 62px; margin-top: 1px;"
                                                                                                      id="date"
                                                                                                      class="calendar-input">
            <a href="javascript:void(0);" onclick="$('#dateCalendarRender').toggle();" id="dateCalendarIcon"
               class="calendar-trigger"><img src="/i/admin/b_calendar.png" alt="Show calendar" width="14" height="18"
                                             border="0"
                                             style="vertical-align: top; margin-top: 1px; margin-left: -2px;"></a>

            <div id="dateCalendarRender" style="position: absolute; display: none; margin-top: 1px;">
                <?$params['displayFormat'] = 'd.m.Y'?>
                <script>
                    $('#date').change(function () {
                        Indi.combo.form.store.timeId = deepObjCopy(Indi.combo.form.store.timeIdBackup);
                        if ($('#date').val() == '') {
                            Indi.combo.form.toggle('timeId', true);
                        } else {
                            $.post(STD+'/auxillary/disabledTimes',
                                {placeId:$('#placeId').val(), date: Ext.Date.format(Ext.Date.parse($('#date').val(), Ext.getCmp('dateCalendar').longDayFormat), 'Y-m-d')},
                                function (disabledTimeIds) {
                                    Indi.combo.form.setDisabledOptions('timeId', disabledTimeIds);
                                    $('#timeId-keyword').val('');
                                    $('#timeId').val(0).change();
                                    Indi.combo.form.toggle('timeId', false);
                                    publicTimes = $('#placeId').attr('publicTimeIds').split(',');
                                    if (publicTimes.length) {
                                        do {
                                            for (var i in Indi.combo.form.store.timeId.ids) {
                                                if(publicTimes.indexOf(Indi.combo.form.store.timeId.ids[i]+'') == -1) {
                                                    Indi.combo.form.store.timeId.ids.splice(i, 1);
                                                    Indi.combo.form.store.timeId.data.splice(i, 1);
                                                    break;
                                                }
                                            }
                                        } while (publicTimes.length < Indi.combo.form.store.timeId.ids.length);
                                        Indi.combo.form.store.timeId.found = Indi.combo.form.store.timeId.ids.length;
                                    }
                                }
                                , 'json');
                        }
                    });
                    Ext.onReady(function () {
                        //Ext.Date.monthNames = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
                        Ext.create('Ext.picker.Date', {
                            //dayNames: ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
                            //monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
                            renderTo:'dateCalendarRender',
                            id:'dateCalendar',
                            width:185,
                            disabledDatesText:'На данную дату все уже забронировано',
                            //todayText: 'Сегодня',
                            //ariaTitle: 'Выбрать месяц и год',
                            ariaTitleDateFormat: '<?=$params['displayFormat']?>',
                            longDayFormat: '<?=$params['displayFormat']?>',
                            format: '<?=$params['displayFormat']?>',
                            //nextText: 'Следующий месяц',
                            //prevText: 'Предыдущий месяц',
                            //todayTip: 'Выбрать сегодняшнюю дату',
                            //startDay: 1,
                            minDate: Ext.Date.add(new Date(), Ext.Date.DAY, 5),
                            maxDate: Ext.Date.add(new Date(), Ext.Date.DAY, 35),
                            handler: function(picker, date) {
                                var selectedDate = Ext.Date.format(date, '<?=$params['displayFormat']?>');
                                $('#date').val(selectedDate);
                                $('#dateCalendarRender').toggle();
                                $('#date').change();
                            },
                            listeners: {
                                render: function(cal) {
                                    $('body').bind('click', function(e) {
                                        if($(e.target).closest('#'+cal.id).length == 0 &&
                                            !($(e.srcElement).hasClass('calendar-trigger') || $(e.srcElement).parent().hasClass('calendar-trigger')) &&
                                            $('#'+cal.id+'Render').css('display') != 'none') {
                                            $('#'+cal.id+'Render').hide();
                                        }
                                    });
                                }
                            }
                        });
                    });
                </script>
            </div>
        </div>
    </td>
</tr>
<tr class="info" id="tr-timeId">
    <td width="50%" id="td-left-timeId">Время:</td>
    <td width="50%" id="td-right-timeId"><?=$this->formCombo('timeId', 'event')?></td>
</tr>

<tr class="info" id="tr-programId">
    <td width="50%" id="td-left-programId">Анимационная программа:</td>
    <td width="50%" id="td-right-programId"><?=$this->formCombo('programId', 'event')?></td>
</tr>

<tr class="info" id="tr-subprogramId" style="border-top: 0px;">
    <td width="50%" id="td-left-subprogramId"></td>
    <td width="50%" id="td-right-subprogramId"><?=$this->formCombo('subprogramId', 'event')?></td>
</tr>
<tr class="info" id="tr-birthChildName">
    <td width="50%" id="td-left-birthChildName">Имя именинника:</td>
    <td width="50%" id="td-right-birthChildName"><input type="text" name="birthChildName" id="birthChildName" value=""
                                                        oninput="" style="width: 100%;"/></td>
</tr>
<tr class="info" id="tr-birthChildBirthDate"><td width="50%" id="td-left-birthChildBirthDate">Дата рождения:</td><td width="50%" id="td-right-birthChildBirthDate"><div style="position: relative; z-index: 98" id="calendarbirthChildBirthDateDiv" class="calendar-div"><input type="text" name="birthChildBirthDate" value="" readonly="readonly" style="width: 62px; margin-top: 1px;" id="birthChildBirthDate" class="calendar-input"> <a href="javascript:void(0);" onclick="$('#birthChildBirthDateCalendarRender').toggle();" id="birthChildBirthDateCalendarIcon" class="calendar-trigger"><img src="/i/admin/b_calendar.png" alt="Show calendar" width="14" height="18" border="0" style="vertical-align: top; margin-top: 1px; margin-left: -2px;"></a>		<div id="birthChildBirthDateCalendarRender" style="position: absolute; display: none; margin-top: 1px;">
    <script>
        $('#birthChildBirthDate').change(function(){
        });
        Ext.onReady(function() {
            Ext.create('Ext.picker.Date', {
                renderTo: 'birthChildBirthDateCalendarRender',
                id: 'birthChildBirthDateCalendar',
                width: 185,
                disabledDatesText: 'На данную дату все уже забронировано',
                ariaTitleDateFormat: '<?=$params['displayFormat']?>',
                longDayFormat: '<?=$params['displayFormat']?>',
                format: '<?=$params['displayFormat']?>',
                handler: function(picker, date) {
                    var selectedDate = Ext.Date.format(date, '<?=$params['displayFormat']?>');
                    $('#birthChildBirthDate').val(selectedDate);
                    $('#birthChildBirthDateCalendarRender').toggle();
                    $('#birthChildBirthDate').change();
                },
                listeners: {
                    render: function(cal) {
                        $('body').bind('click', function(e) {
                            if($(e.target).closest('#'+cal.id).length == 0 &&
                                !($(e.srcElement).hasClass('calendar-trigger') || $(e.srcElement).parent().hasClass('calendar-trigger')) &&
                                $('#'+cal.id+'Render').css('display') != 'none') {
                                $('#'+cal.id+'Render').hide();
                            }
                        });
                    }
                }
            });
        });
    </script>
</div>
</div></td></tr>
<tr class="info" id="tr-childrenCount">
    <td width="50%" id="td-left-childrenCount">Количество детей:</td>
    <td width="50%" id="td-right-childrenCount">
        <table cellpadding="0" cellspacing="0">
            <tr style="border-top: none; height: 20px;">
                <td style="padding-left: 0px;">        <input type="text" name="childrenCount" id="childrenCount" value="5"
                                                              style="width: 50px; text-align: right; margin-top: 1px;" maxlength="5"
                                                              oninput="this.value=number(this.value);" readonly="readonly"
                                                              onkeydown="if(event.keyCode==38||event.keyCode==40){if(event.keyCode==38)this.value=parseInt(this.value)+1;else if(event.keyCode==40)this.value=parseInt(this.value)-1;}"
                                                              autocomplete="off"/></td>
                <td style="padding-right: 5px; padding-left: 5px; padding-top: 3px;">1</td>
                <td>
                    <div id="childrenCount-slider" style="float: right; height: 21px; overflow: hidden;"></div>
                    <script>
                        Ext.onReady(function(){
                            Ext.create('Ext.slider.Single', {
                                renderTo: 'childrenCount-slider',
                                id: 'ext-childrenCount-slider',
                                hideLabel: true,
                                useTips: false,
                                width: 160,
                                value: 5,
                                increment: 1,
                                minValue: 1,
                                maxValue: 8,
                                margin: '0 5 0 0',
                                listeners: {
                                    change: function(obj, newv, oldv){
                                        $('#childrenCount').val(newv);
                                    }
                                }
                            });
                        });
                    </script>
                </td>
                <td id="maxChildrenCount" style="padding-top: 3px;">8</td>
            </tr>
        </table>
    </td>
</tr>
<tr class="info" id="tr-childrenAge">
    <td width="50%" id="td-left-childrenAge">Возраст детей:</td>
    <td width="50%" id="td-right-childrenAge"><input type="text" name="childrenAge" id="childrenAge" value="" oninput=""style="width: 50px;" maxlength="5"/></td>
</tr>
<tr class="info" id="tr-clientTitle">
    <td width="50%" id="td-left-clientTitle">Ваше имя:</td>
    <td width="50%" id="td-right-clientTitle"><input type="text" name="clientTitle" id="clientTitle" value="" oninput=""
                                                     style="width: 100%;"/></td>
</tr>
<tr class="info" id="tr-clientPhone" style="border-bottom: none;">
    <td width="50%" id="td-left-clientPhone">Контактный телефон:</td>
    <td width="50%" id="td-right-clientPhone"><input type="text" name="clientPhone" id="clientPhone" value="" oninput=""
                                                     style="width: 100%;"/></td>
</tr>
<tr class="info" id="tr-clientEmail" style="border-bottom: none;">
    <td width="50%" id="td-left-clientEmail">Email:</td>
    <td width="50%" id="td-right-clientEmail"><input type="text" name="clientEmail" id="clientEmail" value="" oninput=""
                                                     style="width: 100%;"/></td>
</tr>
</table>
<table class="buttons" style="border: 0; width: 100%;" cellpadding="6" id="iframe-form-buttons">
    <tr style="border: 0;">
        <td align="center" type="save" width="50%" align="left" style="vertical-align: top; font-size: 12pt; font-family: 'Cuprum', sans-serif">
            Стоимость:
            <input type="text" name="price" id="price" value="0" style="width: 50px; text-align: right;" maxlength="5"
                   readonly autocomplete="off"/>
            рублей
        </td>
        <td class="contacts-section">
            <div class="button-container" style="margin-top: 0;">
                <span class="button" style="display: initial; padding-left: 0;"><input type="reset" value="очистить" style="width: auto;"></span>
                <span class="button" style="display: initial;"><input type="submit" value="отправить" style="width: auto;"></span>
            </div>
        </td>
    </tr>
</table>
</form>

<script>
    Indi.ready(function(){
        $('input[type="reset"]').click(function(){
            $('#districtId').val(0).change();
            $('input[type="submit"]').removeAttr('disabled');
            top.window.$('iframe[name="form-frame"]').height(490);
        });
        $('input[type="submit"]').click(function(){
            var data = {};
            var fields = ['districtId', 'placeId', 'date', 'timeId', 'programId', 'subprogramId',
                'birthChildName', 'birthChildBirthDate', 'childrenCount', 'childrenAge', 'clientTitle',
                'clientPhone', 'clientEmail', 'price'];
            var error = false;
            var inp;
            for (var i = 0; i < fields.length; i++) {
                var value = $('#' + fields[i]).val();
                if (['price'].indexOf(fields[i]) == -1 && (value == "0" || value.length == 0 || (fields[i] == 'clientEmail' && !email.test(value)))) {
                    if ($('#' + fields[i]).parent().hasClass('combo-div')) {
                        inp = $('#' + fields[i]).parent().find('.combo-keyword');
                    } else {
                        inp = $('#' + fields[i]);
                    }
                    if (!inp.attr('disabled')) {
                        error = true;
                        if (!inp.attr('border-backup')) inp.attr('border-backup', inp.css('border'));
                        inp.css('border', '1px solid #ff6666');
                        inp.focus(function () {
                            $(this).css('border', $(this).attr('border-backup'));
                        })
                        inp.click(function () {
                            $(this).css('border', $(this).attr('border-backup'));
                        })
                        inp.change(function () {
                            $(this).css('border', $(this).attr('border-backup'));
                        })
                    }
                }
                data[fields[i]] = value;
            }
            if (error == false) {
                data.animatorsNeededCount = parseInt($('#programId').attr('animatorsCount'));
                $.post(STD+'/admin/client/save', data, function (response) {
                    top.window.$('iframe[name="form-frame"]').height(400);
                    if (response == 'ok') {
                        Ext.MessageBox.show({
                            title:"Сообщение",
                            msg:'<?=$this->blocks['request-saved']?>',
                            buttons:Ext.MessageBox.OK,
                            icon:Ext.MessageBox.INFO,
                            modal: true,
                            fn: function(){
                                top.window.$('iframe[name="form-frame"]').height(490);
                                $('input[type="submit"]').attr('disabled', 'disabled');
                            }
                        });
                    } else {
                        Ext.MessageBox.show({
                            title:"Сообщение",
                            msg:'<?=$this->blocks['request-expired']?>',
                            maxWidth: 500,
                            buttons:Ext.MessageBox.OK,
                            icon:Ext.MessageBox.WARNING,
                            modal: true,
                            fn: function(){
                                top.window.$('iframe[name="form-frame"]').height(490);
                            }
                        });
                    }
                })
            }
            return false;
        });
        if ($('#placeId').val() != "0") {
            $('#date').removeAttr('disabled');
            $('#date').parents('.calendar-div').removeClass('disabled');
        } else {
            $('#date').attr('disabled','disabled');
            $('#date').parents('.calendar-div').addClass('disabled');
            $('#date').val('')
        }
        if ($('#date').val()) {
            Indi.combo.form.toggle('timeId', false);
        } else {
            Indi.combo.form.toggle('timeId', true);
        }
        if ($('#timeId').val() != "0") {
            Indi.combo.form.toggle('programId', false);
        } else {
            Indi.combo.form.toggle('programId', true);
        }
        if (!isNaN($('#programId').attr('subprogramsCount'))) {
            if ($('#programId').attr('subprogramsCount') == '0') {
                //hide('tr-subprogramId');
                $('#programId').attr('animatorsCount', 1);
                $('#animatorIds-table').removeClass('disabled');
                if($('#animatorIds-table').find('span.checkbox.checked').length < parseInt($('#programId').attr('animatorsCount'))) {
                    $('#animatorIds-table').find('span.checkbox').parents('tr').not('.disabled').show();
                } else {
                    $('#animatorIds-table').find('span.checkbox').not('.checked').parent().parent().hide();
                }
            } else {
                show('tr-subprogramId');
                if ($('#subprogramId').val() == '0') {
                    $('#animatorIds-table').addClass('disabled');
                } else {
                    var index = Indi.combo.form.store['subprogramId'].ids.indexOf(parseInt($('#subprogramId').val()));
                    $('#programId').attr('animatorsCount', Indi.combo.form.store['subprogramId'].data[index].attrs.animatorsCount);
                    $('#animatorIds-table').removeClass('disabled');
                    if($('#animatorIds-table').find('span.checkbox.checked').length < parseInt($('#programId').attr('animatorsCount'))) {
                        $('#animatorIds-table').find('span.checkbox').parents('tr').not('.disabled').show();
                    } else {
                        $('#animatorIds-table').find('span.checkbox').not('.checked').parent().parent().hide();
                    }
                }
            }
        } else {
            //hide('tr-subprogramId');
        }
        //$('form[name=event]').css('visibility', 'visible');
        setTimeout(function(){
            hide('tr-subprogramId');
        }, 100);
        window.Indi.combo.form.store.timeIdBackup = deepObjCopy(window.Indi.combo.form.store.timeId);
    }, 'combo.form');
</script>