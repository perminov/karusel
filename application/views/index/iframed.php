<link rel="stylesheet" type="text/css" href="/library/extjs4iframe/resources/css/ext-all<?=!$this->get['iframed']?'-mobile':''?>.css"/>
<link rel="stylesheet" type="text/css" href="/css/iframed<?=!$this->get['iframed']?'-mobile':''?>.css"/>
<script>var STD = '<?=$_SERVER['STD']?>';</script>
<script type="text/javascript" src="/library/extjs4/ext-all.js"></script>
<script type="text/javascript" src="/library/extjs4/ext-lang-ru.js"></script>
<script type="text/javascript" src="/js/admin/indi.js?<?=rand(0, 10000)?>"></script>
<script type="text/javascript" src="/js/admin/indi.combo.form.js?<?=rand(0, 10000)?>"></script>
<script src="/js/jquery-1.9.1.min.js"></script>
<script src="/js/jquery-migrate-1.1.1.min.js"></script>
<script src="/js/jquery.scrollTo-min.js"></script>

<? $this->row = Indi::model('Event')->createRow(); ?>
<? if (!$_SESSION['admin']['id']) $_SESSION['admin'] = array('id' => '15', 'email' => 'visitor@gmail.com', 'password' => 'visitor', 'profileId' => '17'); ?>
<script>
    var email = /^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
    window.comboFetchRelativePath = '/admin/client/form';
    window.publicTimes = [];
    Ext.require(['*']);
</script>
<?if (!$this->get['iframed']){?>
<center>
<div style="background: rgba(108, 200, 230, 0.65); width: 621px; padding: 20px;  margin-top: 20px; border-radius: 18px 18px 18px 18px;">
<h3 style="  font-family: 'Days', sans-serif;   margin: 0 0 -2px;
  font-size: 24px;
  color: #fff;
  font-weight: normal;
  line-height: 1;
  text-align: center;
  margin-bottom: 5px;
  text-transform: uppercase;" class="feature-header dr">Заявка на проведение праздника</h3>
<?}?>
<form action="/admin/client/save/" name="event" method="post" enctype="multipart/form-data" row-id="" style="">
<table celpadding=0 cellspacing=0 border="0" width="100%">

<tr id="tr-districtId">
    <td width="50%" id="td-left-districtId">Место проведения мероприятия:</td>
    <td width="50%" id="td-right-districtId"><?=$this->formCombo('districtId', 'event')?></td>
    
    
    </tr>
<tr id="tr-placeId" style="border-top: 0px;">
    <td width="50%" id="td-left-placeId">
    
    
   
    
    </td>
    <td width="50%" id="td-right-placeId"><?=$this->formCombo('placeId', 'event')?></td>
</tr>



<tr id="tr-date">
        <td width="50%" id="td-left-date">Дата:</td>
        <td width="25%" id="td-right-date">
    <!-- ext-all -->
        <div style="position: relative; z-index: 99" id="calendardateDiv" class="calendar-div">
        <input type="text"name="date" value="" id="date" class="calendar-input" readonly="readonly" onclick="$('#dateCalendarRender').toggle()">
            <a href="javascript:void(0);" onclick="$('#dateCalendarRender').toggle();" id="dateCalendarIcon"
               class="calendar-trigger"><img src="/i/admin/b_calendar1.png" alt="Show calendar" width="34" height="34"
                                             border="0"
                                             style="vertical-align: top; margin-top: 8px; margin-left: 4px;"></a>

            <div id="dateCalendarRender" style="position: absolute; display: none; margin-top: 1px;">
                <?$params['displayFormat'] = 'd.m.Y'?>
                <script>
                    $('#date').change(function () {
                        Indi.combo.form.store.timeId = Indi.copy(Indi.combo.form.store.timeIdBackup);
                        if ($('#date').val() == '') {
                            Indi.combo.form.toggle('timeId', true);
                        } else {
                            $.post(Indi.std+'/auxiliary/disabledTimes/',
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
                        Ext.create('Ext.picker.Date', {
                            renderTo:'dateCalendarRender',
                            id:'dateCalendar',
                            width:288,
                            disabledDatesText:'На данную дату все уже забронировано',
                            ariaTitleDateFormat: '<?=$params['displayFormat']?>',
                            longDayFormat: '<?=$params['displayFormat']?>',
                            format: '<?=$params['displayFormat']?>',
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
                                            !($(e.srcElement || e.target).hasClass('calendar-trigger') ||
                                             $(e.srcElement || e.target).parent().hasClass('calendar-trigger') ||
                                             $(e.srcElement || e.target).hasClass('calendar-input')
                                            ) &&
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
<tr class="info" id="tr-clientTitle">
    <td width="50%" id="td-left-clientTitle">Ваше имя:</td>
    <td width="50%" id="td-right-clientTitle"><input type="text" name="clientTitle" id="clientTitle" value="" oninput=""
                                                     style="width: 99%;"/></td>
</tr>
<tr class="info" id="tr-clientPhone" style="border-bottom: none;">
    <td width="50%" id="td-left-clientPhone">Контактный телефон:</td>
    <td width="50%" id="td-right-clientPhone"><input type="text" name="clientPhone" id="clientPhone" value="" oninput=""
                                                     style="width: 99%;"/></td>
</tr>
<tr class="info" id="tr-clientEmail" style="border-bottom: none;">
    <td width="50%" id="td-left-clientEmail">Email:</td>
    <td width="50%" id="td-right-clientEmail"><input type="text" name="clientEmail" id="clientEmail" value="" oninput=""
                                                     style="width: 99%;"/></td>
</tr>
</table>
<table class="buttons" style=" solid; width: 100%;" cellpadding="0" id="iframe-form-buttons">
    <tr style="display: none;">
        
        <td type="save" width="50%">
            Стоимость, руб:
                        
        </td>
        
         <td type="save" width="50%">
            
            <input class="price-input" type="text" name="price" id="price" value="0" maxlength="5"
                   readonly autocomplete="off"/>
            
        </td>
        
        </tr>
        <tr>
        
        <td>
            <div class="button-container">
                <span class="button">
                <input type="reset" value="очистить"></span>
            </div>   
        </td>        
        <td>        
            <div class="button-container">    
                <span class="button">
                <input type="submit" value="отправить"></span>
            </div>
        </td>
        
    </tr>
</table>
</form>
<div id="confirmation" style="margin-top: 20px; display: none;"></div>
<?if (!$this->get['iframed']){?>
</div></center>
<?}?>

<script>
$(document).ready(function(){
    $(window).blur(function(){
        $('div[id$="CalendarRender"]').hide();
    });
});
    Indi.ready(function(){
        $('input[type="reset"]').click(function(){
            $('#districtId').val(0).change();
            $('input[type="submit"]').removeAttr('disabled');
        });
        $('input[type="submit"]').click(function(){
            var data = {};
            var fields = ['districtId', 'placeId', 'date', 'timeId', 'clientTitle',
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
                <?$this->blocks['request-saved'] = str_replace(array('[', ']'), array('<', '>'), $this->blocks['request-saved']);?>
                $.post(Indi.std+'/admin/client/save/', data, function (response) {
                    top.window.$('iframe[name="form-frame"]').height(664);
                    if (response == 'ok') {
						$('form[name=event]').hide();
                        $('#confirmation').html('<?=$this->blocks['request-saved']?>'.replace('%clientTitle%', $('#clientTitle').val()).replace('%addr%', $('#districtId').attr('address')).replace('%date%', $('#date').val()).replace('%time%', $('#timeId-keyword').val()));
                        $('#confirmation').show();
                    } else {
                        Ext.MessageBox.show({
                            title:"Сообщение",
                            msg:'<?=$this->blocks['request-expired']?>',
                            maxWidth: 300,
                            buttons:Ext.MessageBox.OK,
                            icon:Ext.MessageBox.WARNING,
                            modal: true,
                            fn: function(){
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
        window.Indi.combo.form.store.timeIdBackup = Indi.copy(window.Indi.combo.form.store.timeId);
		if (top.window.location.search == '?test') {
			Ext.onReady(function(){
				Ext.MessageBox.show({
					title:"Сообщение",
					msg:'<?=$this->blocks['request-saved']?>',
					buttons:Ext.MessageBox.OK,
					icon:Ext.MessageBox.INFO,
					modal: true,
					fn: function(btn){
					}
				});
			
			});
		}
    }, 'combo.form');
</script>