<? $this->row = Misc::loadModel('Event')->createRow(); ?>
<? if (!$_SESSION['admin']['id']) $_SESSION['admin'] = array('id' => '15', 'email' => 'visitor@gmail.com', 'password' => 'visitor'); ?>
<script>
    window.comboFetchRelativePath = '/admin/client/form';
    Ext.require(['*']);
</script>
<style>
form.form table tr {
    border-top: 1px solid #C7D4FF;
    border-bottom: none;
}
table.x-field tr{
    border-top: none !important;
}
</style>
<form class="form row-form" action="/admin/client/save/" name="event" method="post" enctype="multipart/form-data" row-id="" style="border: 1px solid #99BCE8; visibility: hidden;">
<table celpadding="2" cellspacing="1" border="0" width="100%">
<tr class="table_topics"><td colspan="2" align="center" class="table_topics">Информация о мероприятии</td></tr>
<col width="50%"/><col width="50%"/>
<tr class="info" id="tr-districtId">
    <td width="50%" id="td-left-districtId">Место проведения мероприятия:</td>
    <td width="50%" id="td-right-districtId"><?=$this->formCombo('districtId', 'event')?></td>
</tr>
<tr class="info" id="tr-placeId" style="border-top: 0px;">
    <td width="50%" id="td-left-placeId"></td>
    <td width="50%" id="td-right-placeId" style="border-top: 1px solid #C7D4FF;"><?=$this->formCombo('placeId', 'event')?></td>
</tr>
<tr class="info" id="tr-date">
    <td width="50%" id="td-left-date">Дата:</td>
    <td width="50%" id="td-right-date">
        <div style="position: relative; z-index: 99" id="calendardateDiv" class="calendar-div"><input type="text"
                                                                                                      name="date"
                                                                                                      value="2013-08-20"
                                                                                                      style="width: 62px; margin-top: 1px;"
                                                                                                      id="date"
                                                                                                      class="calendar-input">
            <a href="javascript:void(0);" onclick="$('#dateCalendarRender').toggle();" id="dateCalendarIcon"
               class="calendar-trigger"><img src="/i/admin/b_calendar.png" alt="Show calendar" width="14" height="18"
                                             border="0"
                                             style="vertical-align: top; margin-top: 1px; margin-left: -2px;"></a>

            <div id="dateCalendarRender" style="position: absolute; display: none; margin-top: 1px;">
                <script>
                    $('#date').change(function () {
                        if ($('#date').val() == '') {
                            COMBO.toggle('timeId', true);
                        } else {
                            $.post(STD+'/auxillary/disabledTimes',
                                {placeId:$('#placeId').val(), date:$('#date').val()},
                                function (disabledTimeIds) {
                                    COMBO.setDisabledOptions('timeId', disabledTimeIds);
                                    $('#timeId-keyword').val('');
                                    $('#timeId').val(0).change();
                                    COMBO.toggle('timeId', false);
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
                            ariaTitleDateFormat:'Y-m-d',
                            longDayFormat:'Y-m-d',
                            //nextText: 'Следующий месяц',
                            //prevText: 'Предыдущий месяц',
                            //todayTip: 'Выбрать сегодняшнюю дату',
                            //startDay: 1,
                            minDate: new Date(),
                            maxDate: Ext.Date.add(new Date(), Ext.Date.MONTH, 1),
                            handler:function (picker, date) {
                                var y = date.getFullYear();
                                var m = date.getMonth() + 1;
                                if (m.toString().length < 2) m = '0' + m;
                                var d = date.getDate();
                                if (d.toString().length < 2) d = '0' + d;
                                var selectedDate = y + '-' + m + '-' + d;
                                $('#date').val(selectedDate);
                                $('#dateCalendarRender').toggle();
                                $('#date').change();
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
    <td width="50%" id="td-right-subprogramId" style="border-top: 1px solid #C7D4FF;"><?=$this->formCombo('subprogramId', 'event')?></td>
</tr>
<tr class="info" id="tr-birthChildName">
    <td width="50%" id="td-left-birthChildName">Имя именинника:</td>
    <td width="50%" id="td-right-birthChildName"><input type="text" name="birthChildName" id="birthChildName" value=""
                                                        oninput="" style="width: 100%;"/></td>
</tr>
<tr class="info" id="tr-birthChildAge">
    <td width="50%" id="td-left-birthChildAge">Сколько лет исполняется:</td>
    <td width="50%" id="td-right-birthChildAge"><input type="text" name="birthChildAge" id="birthChildAge" value="0"
                                                       style="width: 50px; text-align: right;" maxlength="5"
                                                       oninput="this.value=number(this.value);"
                                                       onkeydown="if(event.keyCode==38||event.keyCode==40){if(event.keyCode==38)this.value=parseInt(this.value)+1;else if(event.keyCode==40)this.value=parseInt(this.value)-1;}"
                                                       autocomplete="off"/></td>
</tr>
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
                <td style="padding-right: 5px; padding-left: 5px;">1</td>
                <td>
                    <div id="childrenCount-slider" style="float: right; height: 20px; overflow: hidden;"></div>
                    <script>
                    Ext.onReady(function(){
                        Ext.create('Ext.slider.Single', {
                            renderTo: 'childrenCount-slider',
                            id: 'ext-childrenCount-slider',
                            hideLabel: true,
                            useTips: false,
                            width: 205,
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
                <td id="maxChildrenCount">8</td>
            </tr>
        </table>
</td>
</tr>
<tr class="info" id="tr-childrenAge">
    <td width="50%" id="td-left-childrenAge">Возраст детей:</td>
    <td width="50%" id="td-right-childrenAge"><input type="text" name="childrenAge" id="childrenAge" value="0"
                                                     style="width: 50px; text-align: right;" maxlength="5"
                                                     oninput="this.value=number(this.value);"
                                                     onkeydown="if(event.keyCode==38||event.keyCode==40){if(event.keyCode==38)this.value=parseInt(this.value)+1;else if(event.keyCode==40)this.value=parseInt(this.value)-1;}"
                                                     autocomplete="off"/></td>
</tr>
<tr class="info" id="tr-details">
    <td width="50%" id="td-left-details">Примечания к заказу:</td>
    <td width="50%" id="td-right-details"><textarea name="details" id="details" rows="0" cols="0"
                                                    style="width: 100%; height: 60px;"></textarea></td>
</tr>
<tr class="info" id="tr-client">
    <td width="50%" id="td-left-client">Информация о заказчике:</td>
    <td width="50%" id="td-right-client">
        <script>$("#tr-client").attr("class", "info")</script>
        <script>$("#td-left-client").attr({"colspan":"2", "align":"center", "class":"table_topics"});</script>
    </td>
</tr>
<tr class="info" id="tr-clientTitle">
    <td width="50%" id="td-left-clientTitle">ФИО:</td>
    <td width="50%" id="td-right-clientTitle"><input type="text" name="clientTitle" id="clientTitle" value="" oninput=""
                                                     style="width: 100%;"/></td>
</tr>
<tr class="info" id="tr-clientBirthDate">
    <td width="50%" id="td-left-clientBirthDate">Дата рождения:</td>
    <td width="50%" id="td-right-clientBirthDate">
        <div style="position: relative; z-index: 97" id="calendarclientBirthDateDiv" class="calendar-div"><input
            type="text" name="clientBirthDate" value="" style="width: 62px; margin-top: 1px; border: 1px solid #99BCE8;"
            id="clientBirthDate" class="calendar-input" readonly="readonly"> <a href="javascript:void(0);"
                                                            onclick="$('#clientBirthDateCalendarRender').toggle();"
                                                            id="clientBirthDateCalendarIcon"
                                                            class="calendar-trigger"><img src="/i/admin/b_calendar.png"
                                                                                          alt="Show calendar" width="14"
                                                                                          height="18" border="0"
                                                                                          style="vertical-align: top; margin-top: 1px; margin-left: -2px;"></a>

            <div id="clientBirthDateCalendarRender" style="position: absolute; display: none; margin-top: 1px;">
                <script>
                    $('#clientBirthDate').change(function () {
                    });
                    Ext.onReady(function () {
                        //Ext.Date.monthNames = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
                        Ext.create('Ext.picker.Date', {
                            //dayNames: ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
                            //monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
                            renderTo:'clientBirthDateCalendarRender',
                            id:'clientBirthDateCalendar',
                            width:185,
                            disabledDatesText:'На данную дату все уже забронировано',
                            //todayText: 'Сегодня',
                            //ariaTitle: 'Выбрать месяц и год',
                            ariaTitleDateFormat:'Y-m-d',
                            longDayFormat:'Y-m-d',
                            //nextText: 'Следующий месяц',
                            //prevText: 'Предыдущий месяц',
                            //todayTip: 'Выбрать сегодняшнюю дату',
                            //startDay: 1,
                            handler:function (picker, date) {
                                var y = date.getFullYear();
                                var m = date.getMonth() + 1;
                                if (m.toString().length < 2) m = '0' + m;
                                var d = date.getDate();
                                if (d.toString().length < 2) d = '0' + d;
                                var selectedDate = y + '-' + m + '-' + d;
                                $('#clientBirthDate').val(selectedDate);
                                $('#clientBirthDateCalendarRender').toggle();
                                $('#clientBirthDate').change();
                            }
                        });
                    });
                </script>
            </div>
        </div>
    </td>
</tr>
<tr class="info" id="tr-clientAddress">
    <td width="50%" id="td-left-clientAddress">Адрес:</td>
    <td width="50%" id="td-right-clientAddress"><input type="text" name="clientAddress" id="clientAddress" value=""
                                                       oninput="" style="width: 100%;"/></td>
</tr>
<tr class="info" id="tr-clientPassportNumber">
    <td width="50%" id="td-left-clientPassportNumber">Серия и номер паспорта:</td>
    <td width="50%" id="td-right-clientPassportNumber"><input type="text" name="clientPassportNumber"
                                                              id="clientPassportNumber" value="" oninput=""
                                                              style="width: 100px;" maxlength="10"
                                                              style="width: 100%;"/></td>
</tr>
<tr class="info" id="tr-clientPassportIssueInfo">
    <td width="50%" id="td-left-clientPassportIssueInfo">Кем и когда выдан:</td>
    <td width="50%" id="td-right-clientPassportIssueInfo"><input type="text" name="clientPassportIssueInfo"
                                                                 id="clientPassportIssueInfo" value="" oninput=""
                                                                 style="width: 100%;"/></td>
</tr>
<tr class="info" id="tr-clientPhone" style="border-bottom: none;">
    <td width="50%" id="td-left-clientPhone">Контактный телефон:</td>
    <td width="50%" id="td-right-clientPhone"><input type="text" name="clientPhone" id="clientPhone" value="" oninput=""
                                                     style="width: 100%;"/></td>
</tr>
</table>
</form>
<table class="buttons" style="border: 0;margin-top: 6px; width: 100%;" cellpadding="6">
    <tr style="border: 0;">
        <td id="" align="center" type="save" width="50%" align="left">
            Стоимость мероприятия:
            <input type="text" name="price" id="price" value="0" style="width: 50px; text-align: right;" maxlength="5"
                   readonly autocomplete="off"/>
            рублей
        </td>
        <td id="td-button-Оформить" align="center" type="save" width="50%" align="left">
            <script>
                Ext.onReady(function () {
                    Ext.create('Ext.Button', {
                        renderTo:'td-button-Оформить',
                        text:'Оформить заказ',
                        padding:'3 10 3 10',
                        margin:6,
                        handler:function () {
                            var data = {};
                            var fields = ['districtId', 'placeId', 'date', 'timeId', 'programId', 'subprogramId',
                                'birthChildName', 'birthChildAge', 'childrenCount', 'childrenAge', 'details', 'clientTitle',
                                'clientBirthDate', 'clientAddress', 'clientPassportNumber', 'clientPassportIssueInfo', 'clientPhone',
                                'price'];
                            var error = false;
                            var inp;
                            for (var i = 0; i < fields.length; i++) {
                                var value = $('#' + fields[i]).val();
                                if (['details', 'price'].indexOf(fields[i]) == -1 && (value == "0" || value.length == 0)) {
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
                                    if (response == 'ok') {
                                        Ext.MessageBox.show({
                                            title:"Сообщение",
                                            msg:'<?=$this->blocks['request-saved']?>',
                                            buttons:Ext.MessageBox.OK,
                                            icon:Ext.MessageBox.INFO,
                                            fn: function(){
                                                window.location.reload();
                                            }
                                        });
                                    } else {
                                        Ext.MessageBox.show({
                                            title:"Сообщение",
                                            msg:'<?=$this->blocks['request-expired']?>',
                                            buttons:Ext.MessageBox.OK,
                                            icon:Ext.MessageBox.WARNING
                                        });
                                    }
                                })
                            }
                        }
                    });
                });
            </script>
        </td>
    </tr>
</table>

<script>
COMBO.ready = function () {
    if ($('#placeId').val() != "0") {
        $('#date').removeAttr('disabled');
        $('#date').parents('.calendar-div').removeClass('disabled');
    } else {
        $('#date').attr('disabled', 'disabled');
        $('#date').parents('.calendar-div').addClass('disabled');
        $('#date').val('')
    }
    if ($('#date').val()) {
        COMBO.toggle('timeId', false);
    } else {
        COMBO.toggle('timeId', true);
    }
    if ($('#timeId').val() != "0") {
        COMBO.toggle('programId', false);
    } else {
        COMBO.toggle('programId', true);
    }
    if (!isNaN($('#programId').attr('subprogramsCount'))) {
        if ($('#programId').attr('subprogramsCount') == '0') {
            hide('tr-subprogramId');
            $('#programId').attr('animatorsCount', 1);
            $('#animatorIds-table').removeClass('disabled');
            if ($('#animatorIds-table').find('span.checkbox.checked').length < parseInt($('#programId').attr('animatorsCount'))) {
                $('#animatorIds-table').find('span.checkbox').parents('tr').not('.disabled').show();
            } else {
                $('#animatorIds-table').find('span.checkbox').not('.checked').parent().parent().hide();
            }
        } else {
            show('tr-subprogramId');
            if ($('#subprogramId').val() == '0') {
                $('#animatorIds-table').addClass('disabled');
            } else {
                var index = comboOptions['subprogramId'].ids.indexOf(parseInt($('#subprogramId').val()));
                $('#programId').attr('animatorsCount', comboOptions['subprogramId'].data[index].attrs.animatorsCount);
                $('#animatorIds-table').removeClass('disabled');
                if ($('#animatorIds-table').find('span.checkbox.checked').length < parseInt($('#programId').attr('animatorsCount'))) {
                    $('#animatorIds-table').find('span.checkbox').parents('tr').not('.disabled').show();
                } else {
                    $('#animatorIds-table').find('span.checkbox').not('.checked').parent().parent().hide();
                }
            }
        }
    } else {
        hide('tr-subprogramId');
    }
    $('form[name=event]').css('visibility', 'visible');
}
</script>