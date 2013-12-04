<?php
class Project_View_Helper_Admin_RenderGrid extends Indi_View_Helper_Admin_RenderGrid
{
    public function getFirstColumnWidthFraction(){
        if ($this->view->trail->getItem()->model->info('name') == 'event')
            return 0.23;
        else 
            return parent::getFirstColumnWidthFraction();
    }
}