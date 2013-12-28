<?php
class Project_View_Helper_Admin_Viewport extends Indi_View_Helper_Admin_Viewport{
    public function viewport(){
        return parent::viewport() . '<script>Ext.onReady(function(){loadContent("' . PRE . '/index/home/");});</script>';
    }
}