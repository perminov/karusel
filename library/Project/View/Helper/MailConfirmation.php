<?php
class Project_View_Helper_MailConfirmation {
	public function mailConfirmation($event){
        $districtR = $event->foreign('districtId');
        $placeR = $event->foreign('placeId');
        $timeR = $event->foreign('timeId');
        $user = (object) array('title' => $event->clientTitle);
        ob_start();?>
		<?=Indi::view()->mailHeader($user, 'Вы оставили заявку', 'Вы оставили заявку на проведение детского празника на сайте ' . ucfirst($_SERVER['HTTP_HOST']), '')?>
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
                            Где: 
                        </font>
                        <font face="arial,sans-serif" color="#000000" style="font-size:12px">
                            <?=$districtR->address?>, <?=$placeR->title?>
                        </font>
                      </td>
					  <td width="10"></td>
				  </tr>
				  <tr><td colspan="3" height="9"></td></tr>
				  <tr>
					  <td width="10"></td>
					  <td>
                        <font face="arial,sans-serif" color="#505050" style="font-size:12px">Когда: </font>
                        <font face="arial,sans-serif" color="#000000" style="font-size:12px">
                            <?=ldate('%d-%b-%Y', $event->date)?>, <?=$timeR->title?>
                        </font>
                      </td>
					  <td width="10"></td>
				  </tr>
				  <tr><td colspan="3" height="9"></td></tr>
				  <tr><td colspan="3" height="9"></td></tr>
				  <tr>
					  <td width="10"></td>
					  <td><font face="arial,sans-serif" color="#505050" style="font-size:12px; font-weight: bold;">Указанные вами личные сведения:</font></td>
					  <td width="10"></td>
				  </tr>
				  <tr><td colspan="3" height="9"></td></tr>
				  <tr>
					  <td width="10"></td>
					  <td>
                        <font face="arial,sans-serif" color="#505050" style="font-size:12px">Ваше имя: </font>
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
				  <tr><td colspan="3" height="10"></td></tr>
              </table>
            </td>
          </tr>
		  <?=Indi::view()->mailFooter($user, 'Вам нужно будет придти по указанному адресу для внесения предоплаты и подписания договора на проведение данного мероприятия.')?>
		<?$xhtml = ob_get_clean();
		return $xhtml;
	}
}