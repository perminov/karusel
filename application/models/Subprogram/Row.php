<?php
class Subprogram_Row extends Indi_Db_Table_Row{
    public function save() {
        if (!$this->_original['id']) {
            $programR = $this->foreign('programId');
            $programR->subprogramsCount++;
            $programR->save();
        } else if ($this->_modified['programId']) {
            $programR = $this->foreign('programId');
            $programR->subprogramsCount++;
            $programR->save();
            $newProgramId = $this->_modified['programId'];
            $this->_modified['programId'] = $this->_original['programId'];
            $programR = $this->foreign('programId');
            $programR->subprogramsCount--;
            $programR->save();
            $this->_modified['programId'] = $newProgramId;
        }
        parent::save();
    }

    public function delete(){
        $programR = $this->foreign('programId');
        $programR->subprogramsCount--;
        $programR->save();
        parent::delete();
    }
}