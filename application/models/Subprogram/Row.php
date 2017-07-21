<?php
class Subprogram_Row extends Indi_Db_Table_Row {

    /**
     * If `programId` was changed - decrease previous program's subprograms
     * counter and increase current program's subprograms counter
     */
    public function onUpdate() {

        // If `programId` was not affected - return
        if (!$this->affected('programId')) return;

        // Call onInsert() method
        $this->onInsert();

        // Temporarily restore previous value and call onDelete() method
        $this->programId = $this->affected('programId', true);
        $this->onDelete();
        $this->reset();
    }

    /**
     * Increase subprograms counter
     */
    public function onInsert() {
        $programR = $this->foreign('programId');
        $programR->subprogramsCount++;
        $programR->save();
    }

    /**
     * Decrease subprograms counter
     */
    public function onDelete() {
        $programR = $this->foreign('programId');
        $programR->subprogramsCount--;
        $programR->save();
    }
}