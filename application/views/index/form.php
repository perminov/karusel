<form action="/index/save/" name="event" data-row="event-<?=(int)$this->row->id?>" method="post" enctype="multipart/form-data">
<table celpadding="0" cellspacing="0" border="0" width="100%">
<col width="50%">
<tr>
    <td>Место проведения мероприятия:</td><td><select name="districtId">
        <?foreach($this->row->getComboData('districtId') as $o){?>
            <option value="<?=$o->id?>"><?=$o->title?></option>
        <?}?>
    </select></td>
</tr>
<tr>
    <td></td><td><select name="placeId">
        <?foreach($this->row->getComboData('placeId') as $o){?>
            <option value="<?=$o->id?>"><?=$o->title?></option>
        <?}?>
    </select></td>
</tr>
<tr>
    <td>Дата:</td>
    <td><input name="date" autocomplete="off"/></td>
</tr>
<tr>
    <td>Время:</td><td><select name="timeId">
        <?foreach($this->row->getComboData('timeId') as $o){?>
            <option value="<?=$o->id?>"><?=$o->title?></option>
        <?}?>
    </select></td>
</tr>
<tr><td>Ваше имя:</td><td><input name="clientTitle"/></td></tr>
<tr><td>Контактный телефон:</td><td><input name="clientPhone"/></td></tr>
<tr><td>Email:</td><td><input name="clientEmail"/></td></tr>
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
    $('[name="clientPhone"]').mask('+7 (000) 000-00-00');
    $('[name="districtId"], [name="placeId"], [name="timeId"]').select22();
    $('[name="placeId"]').iwatch({
        on: [{name: 'districtId', required: true}],
        odata: true,
        callback: function(c, data) {
            $.post('/index/form/odata/placeId/', {consider: JSON.stringify(data)}, function(json) {
                c.odata(json);
            });
        }
    });
    $('[name="timeId"]').iwatch({
        on: [{name: 'placeId', required: true}],
        odata: true,
        callback: function(c, data) {
            $.post('/index/form/odata/timeId/', {consider: JSON.stringify(data)}, function(json) {
                c.odata(json);
            });
        }
    });
    $('[name="date"]').datepicker({
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
        showOn: 'both'
    });
    $('form[name="event"]').iform({
        spaceFieldsEvents: <?=json_encode(t()->model->toArray(true)->space['fields']['events'])?>
    });
});
</script>
