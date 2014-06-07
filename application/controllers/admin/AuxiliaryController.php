<?php
class Admin_AuxiliaryController extends Indi_Controller_Auxiliary {
    public $eventId;
    public function preDispatch(){
        if (preg_match('/\/id\/([0-9]+)\//', $_SERVER['HTTP_REFERER'], $matches)) $this->eventId = $matches[1];
    }
    public function disabledDatesAction(){
        $disabledDates = array();
        if (Indi::post('placeId')) {
            $disabledDates = Indi::model('Event')->disabledDates(Indi::post('placeId'), $this->eventId);
        }
        die(json_encode($disabledDates));
    }
    public function disabledTimesAction(){
        if (Indi::post('placeId') && Indi::post('date')) {
            $disabled = Indi::model('Event')->disabledTimes(Indi::post('placeId'), Indi::post('date'), $this->eventId);
        }
        die(json_encode($disabled));
    }

    public function disabledAnimatorsAction() {
        $info = Indi::model('Event')->disabledAnimators(
            Indi::post('placeId'),
            Indi::post('date'),
            Indi::post('timeId'),
            Indi::post('animatorsNeededCount'),
            $this->eventId);
        die(json_encode($info));
    }
}