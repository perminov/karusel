<link rel="stylesheet" href="/library/jquery-ui-1.12.1/jquery-ui.min.css"/>
<link rel="stylesheet" href="/library/select2/select2.min.css"/>
<link rel="stylesheet" href="/css/iform.css"/>
<link rel="stylesheet" href="/css/form.css"/>
<script src="/js/jquery-1.10.2.min.js"></script>
<script src="/library/jquery-ui-1.12.1/jquery-ui.min.js"></script>
<script std="<?=STD?>" src="/js/indi.js"></script>
<script src="/library/select2/select2.min.js"></script>
<form action="/index/save/" name="event" data-row="event-<?=(int)$this->row->id?>" method="post" enctype="multipart/form-data">
<table celpadding="0" cellspacing="0" border="0" width="100%">
<col width="50%">
<tr>
    <td>Место проведения мероприятия:</td>
    <td><select name="districtId">
        <?foreach($this->row->getComboData('districtId') as $o){?><option value="<?=$o->id?>"<?=$o->id==$this->row->districtId?' selected="selected"':''?>><?=$o->title?></option><?}?>
    </select></td>
</tr>
<tr>
    <td></td>
    <td><select name="placeId" data-options="">
        <option value="0"></option>
        <?foreach($this->row->getComboData('placeId') as $o){?><option data-publictimeids="<?=$o->publicTimeIds?>" value="<?=$o->id?>"><?=$o->title?></option><?}?>
    </select></td>
</tr>
<tr>
    <td>Дата:</td>
    <td><input name="date" disabled="disabled"/></td>
</tr>
<tr>
    <td>Время:</td>
    <td><select name="timeId" disabled="disabled" data-options="<?=$this->row->view('timeId-options')?>"></select></td>
</tr>
<tr>
    <td>Ваше имя:</td>
    <td><input name="clientTitle"/></td>
</tr>
<tr>
    <td>Контактный телефон:</td>
    <td><input name="clientPhone"/></td>
</tr>
<tr>
    <td>Email:</td>
    <td><input name="clientEmail"/></td>
</tr>
</table>
<table class="buttons" cellpadding="0" id="iframe-form-buttons">
    <tr>
        <td><div class="button-container"><span class="button"><input type="reset" value="очистить"></span></div></td>
        <td><div class="button-container"><span class="button"><input type="submit" value="отправить"></span></div></td>
    </tr>
</table>
</form>
<script>
$(function(){
    var timeId_update = function(disabled){
        var timeId = $('form[name="event"] select[name="timeId"]'),
            placeId = $('form[name="event"] select[name="placeId"]'),
            optionA = JSON.parse(timeId.attr('data-options')),
            timeIdA = [], dataA = [{id: 0, text: ''}], me = $(this), v = placeId.val(),
            disabled = disabled || [], o = placeId.find('option[value="' + v + '"]'); if (!o.length) return;

        try {timeIdA = o.attr('data-publictimeids').split(',')} catch (e) {};

        dataA = [{id: 0, text: ''}];
        optionA.forEach(function(option){
            if (timeIdA.indexOf(option.id + '') != -1) dataA.push({
                id: option.id,
                text: option.title,
                disabled: disabled.indexOf(parseInt(option.id)) != -1
            });
        });

        // Destroy
        timeId.select2('destroy'); timeId.html('');

        // Create/recreate
        timeId.select2({
            width: '100%',
            placeholder: {
                id: '0', // the value of the option
                text: ''
            },
            data: dataA,
            allowClear: true,
            minimumResultsForSearch: Infinity
        });

        // Bind error destroy on click
        timeId.next('.select2').click(function(){
            $(this).ierror(false);
        });
    };

    $('form[name="event"] input[name="date"]').iwatch({
        on: [{name: 'districtId', required: true}, {name: 'placeId', required: true}],
        zeroValue: '',
        callback: function(c, d){
            $.post('/index/form/consider/date/', $.extend(d, {date: new Date().toISOString().slice(0, 10)}), function(json) {
                c.data('disabledDates', json.disabledDates);
            })
        }
    }).datepicker({
        dateFormat: 'dd.mm.yy',
        dayNames: ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
        monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
        dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
        firstDay: 1,
        buttonImage: "<?=STD?>/i/b_calendar.png",
        buttonImageOnly: true,
        minDate: 5,
        maxDate: 35,
        buttonText: 'day',
        showOn: 'both',
        beforeShowDay: function(date) {
            var date = jQuery.datepicker.formatDate('yy-mm-dd', date),
                show = $(this).data('disabledDates') && $(this).data('disabledDates').indexOf(date) == -1,
                tip = show ? '' : 'Эта дата полностью занята';
            return [show, '', tip];
        },
        onChangeMonthYear: function(y, m, dp) {
            var c = $(this), date = y + '-' + (m > 9 ? m : '0' + m) + '-15';
            $.post('/index/form/consider/date/', $.extend($(this).iwatchinfo().data, {date: date}), function(json) {
                c.data('disabledDates', json.disabledDates);
                c.datepicker('refresh');
            })
        }
    });

    $('form[name="event"] select[name="timeId"]').iwatch({
        on: [{name: 'districtId', required: true}, {name: 'placeId', required: true}, {name: 'date', required: true}],
        zeroValue: 0,
        callback: function(c, d){
            $.post('/index/form/consider/timeId/', d, function(json) {
                timeId_update(json.disabledTimeIds);
            })
        }
    }).select2({
        disabled: true
    });

    $('form[name=event] select[name="districtId"]').select2({
        width: '100%',
        minimumResultsForSearch: Infinity
    });

    $('form[name=event] select[name="placeId"]').select2({
        width: '100%',
        minimumResultsForSearch: Infinity,
        placeholder: {id: '0', text: ''},
        allowClear: true
    }).change(function(){
        timeId_update();
    });

    $('form[name="event"] input[type="reset"]').click(function(){
        $('select.select2-hidden-accessible').each(function(){
            if ($(this).attr('name') == 'districtId') return;
            $(this).data('select2').val(0);
            $(this).data('select2').trigger('change');
        });
    });

    $('form[name="event"]').iform();
});
</script>
