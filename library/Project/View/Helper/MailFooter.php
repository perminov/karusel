<?php
class Project_View_Helper_MailFooter {
	public function mailFooter($user, $additional = null){
		ob_start();?>
          <tr><td height="10"></td></tr>
          <tr>
            <td>
			  <?if (!is_array($additional)) $additional = array($additional)?>
			  <?for ($i = 0; $i < count($additional);$i++){?>
			  <p style="line-height: 28px; margin-top: 0; margin-right:0; margin-bottom: 32px; margin-left: 0; padding: 0;"><font face="arial,sans-serif" color="#505050" style="font-size:12px"><?=$additional[$i]?></font></p>
			  <?}?>

              <p style="margin-top: 0; margin-right:0; margin-bottom: 3px; margin-left: 0; padding: 0;"><font face="arial,sans-serif" color="#505050" style="font-size:12px">C уважением,</font></p>
              <p style="margin-top: 0; margin-right:0; margin-bottom: 0; margin-left: 0; padding: 0;"><font face="arial,sans-serif" color="#505050" style="font-size:12px">команда проекта <a href="http://<?=$_SERVER['HTTP_HOST']?>"><font face="arial,sans-serif" color="#0084c9"  style="font-size:12px"><?=ucfirst($_SERVER['HTTP_HOST'])?></font></a></font></p>
            </td>
          </tr>
          <tr><td height="133"></td></tr>
        </table>
      </td>
      <td width="60"></td>
    </tr>
</div>
		<?$xhtml = ob_get_clean();
		return $xhtml;
	}
}