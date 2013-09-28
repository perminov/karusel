<?php
class Project_View_Helper_MailHeader extends Indi_View_Helper_Abstract{
	public function mailHeader($user, $subject, $announce, $additional = array()){
		ob_start();?>
<div id="mailsub">
  <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#ffffff" style="border-collapse: collapse; border-spacing: 0;">
    <tr>
      <td width="27"></td>
      <td>
        <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#ffffff" style="border-collapse: collapse; border-spacing: 0;">
          <tr><td height="25"></td></tr>
          <tr style="border-bottom: 1px solid #b3b3b3;"><td height="10"><img src="http://vkenguru.ru/events/data/upload/fck/Image/kenguru-logo.png" width="229" height="77" alt="Логотип"/></td></tr>
          <tr><td height="25"></td></tr>
          <tr>
			<td>
			  <p style="margin-top: 0; margin-right:0; margin-bottom: 15px; margin-left: 0; padding: 0;"><font face="arial,sans-serif" style="font-size:18px;"><strong><?=$subject?></strong></font></p>
              <p style="margin-top: 0; margin-right:0; margin-bottom: 15px; margin-left: 0; padding: 0;"><font face="arial,sans-serif" color="#505050" style="font-size:12px">Здравствуйте<?=$user->title?' '.$user->title:''?>!</font></p>
              <p style="margin-top: 0; margin-right:0; margin-bottom: 0px; margin-left: 0; padding: 0;"><font face="arial,sans-serif" color="#505050" style="font-size:12px"><?=$announce?></font></p>
			  <?if (!is_array($additional)) $additional = array($additional)?>
			  <?for ($i = 0; $i < count($additional);$i++){?>
			  <p style="margin-top: 0; margin-right:0; margin-bottom: 0px; margin-left: 0; padding: 0;"><font face="arial,sans-serif" color="#505050" style="font-size:12px"><?=$additional[$i]?></font></p>
			  <?}?>
            </td>
          </tr>
          <tr><td height="16"></td></tr>
		<?$xhtml = ob_get_clean();
		return $xhtml;
	}
}