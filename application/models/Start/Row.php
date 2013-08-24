<?php
class Start_Row extends Indi_Db_Table_Row{
    public function getTitle() {
        return preg_replace('/:00$/', '', $this->title);
    }
}