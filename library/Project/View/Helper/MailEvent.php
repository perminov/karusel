<?php
class Project_View_Helper_MailEvent {
	public function mailEvent($event){
        $districtR = $event->foreign('districtId');
        $placeR = $event->foreign('placeId');
        $timeR = $event->foreign('timeId');
        ob_start();?>
		<?=Indi::view()->mailHeader($client, 'Поступила новая заявка', 'Поступила новая заявка на проведение детского празника в вашем локейшене', '')?>
          <tr>
            <td>
              <table width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse: collapse; border-spacing: 0;" bgcolor="#ececec">
				  <tr><td colspan="3" height="10"></td></tr>
				  <tr>
					  <td width="10"></td>
					  <td><font face="arial,sans-serif" color="#505050" style="font-size:12px; font-weight: bold;">Информация о мероприятии:</font></td>
					  <td width="10"></td>
				  </tr>
				  <tr><td colspan="3" height="10"></td></tr>
				  <tr>
					  <td width="10"></td>
					  <td>
                        <font face="arial,sans-serif" color="#505050" style="font-size:12px">
                            Локейшен, место: 
                        </font>
                        <font face="arial,sans-serif" color="#000000" style="font-size:12px">
                            <?=$districtR->title?>, <?=$placeR->title?>
                        </font>
                      </td>
					  <td width="10"></td>
				  </tr>
				  <tr><td colspan="3" height="9"></td></tr>
				  <tr>
					  <td width="10"></td>
					  <td>
                        <font face="arial,sans-serif" color="#505050" style="font-size:12px">Дата и время: </font>
                        <font face="arial,sans-serif" color="#000000" style="font-size:12px">
                            <?=ldate('%d-%b-%Y', $event->date)?>, <?=$timeR->title?>
                        </font>
                      </td>
					  <td width="10"></td>
				  </tr>
				  <tr><td colspan="3" height="9"></td></tr>
				  <tr>
					  <td width="10"></td>
					  <td><font face="arial,sans-serif" color="#505050" style="font-size:12px; font-weight: bold;">Информация о заказчике:</font></td>
					  <td width="10"></td>
				  </tr>
				  <tr><td colspan="3" height="9"></td></tr>
				  <tr>
					  <td width="10"></td>
					  <td>
                        <font face="arial,sans-serif" color="#505050" style="font-size:12px">ФИО: </font>
                        <font face="arial,sans-serif" color="#000000" style="font-size:12px"><?=$event->clientTitle?></font>
                      </td>
					  <td width="10"></td>
				  </tr>
				  <tr><td colspan="3" height="9"></td></tr>
				  <tr>
					  <td width="10"></td>
					  <td>
                        <font face="arial,sans-serif" color="#505050" style="font-size:12px">Контактный телефон: </font>
                        <font face="arial,sans-serif" color="#000000" style="font-size:12px"><?=$event->clientPhone?></font>
                      </td>
					  <td width="10"></td>
				  </tr>
				  <tr><td colspan="3" height="9"></td></tr>
				  <tr>
					  <td width="10"></td>
					  <td>
                        <font face="arial,sans-serif" color="#505050" style="font-size:12px">Электронная почта: </font>
                        <font face="arial,sans-serif" color="#000000" style="font-size:12px"><?=$event->clientEmail ? $event->clientEmail : 'не указана'?></font>
                      </td>
					  <td width="10"></td>
				  </tr>
				  <tr><td colspan="3" height="10"></td></tr>
              </table>
            </td>
          </tr>
		  <?=Indi::view()->mailFooter($client, 'При создании заявки заказчику было также отправлено уведомление с указанием аналогичной информации')?>
		<?$xhtml = ob_get_clean();
		return $xhtml;
	}
}