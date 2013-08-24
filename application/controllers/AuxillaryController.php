<?php
class AuxillaryController extends Indi_Controller{
    public function preDispatch(){
        if (preg_match('/\/id\/([0-9]+)\//', $_SERVER['HTTP_REFERER'], $matches)) $this->eventId = $matches[1];
    }
    public function disabledDatesAction(){
        $disabledDates = array();
        if ($this->post['placeId']) {
            $disabledDates = Misc::loadModel('Event')->disabledDates($this->post['placeId'], $this->eventId);
        }
        die(json_encode($disabledDates));
    }
    public function disabledTimesAction(){
        if ($this->post['placeId'] && $this->post['date']) {
            $disabled = Misc::loadModel('Event')->disabledTimes($this->post['placeId'], $this->post['date'], $this->eventId);
        }
        die(json_encode($disabled));
    }

    public function disabledAnimatorsAction() {
        $info = Misc::loadModel('Event')->disabledAnimators(
            $this->post['placeId'],
            $this->post['date'],
            $this->post['timeId'],
            $this->post['animatorsNeededCount'],
            $this->eventId);
        die(json_encode($info));
    }
}