<?php
class Indi_View_Helper_SiteFooter extends Indi_View_Helper_Abstract{
	public function siteFooter(){
		ob_start();?>
	  </div>
	</td>
	<td width="200" style="background-image: url(./i/balls-right.png); background-repeat: no-repeat;">&nbsp;</td>
  </tr>
  
  <tr height="88">
	<td colspan="4" style="background-image: url(./i/footer.png); background-repeat: no-repeat;">
   
    </td>
  </tr>
  </table>
</body>
</html>
		<?return ob_get_clean();
	}
}