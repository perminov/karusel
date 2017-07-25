<?php
class Indi_View_Helper_SiteFooter {
	public function siteFooter(){
		ob_start();?>
</body>
</html>
		<?return ob_get_clean();
	}
}