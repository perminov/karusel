<?php
class Event extends Indi_Db_Table {

    /**
     * Turn changes logging to 'On' for this model
     *
     * @var array
     */
    protected $_changeLog = array(
        'toggle' => true,
        'ignore' => 'price,clientPhone2,clientAgreementNumber,title,birthChildAge,finalPrice,requestBy,requestByManagerId,requestDate,calendarStart,calendarEnd,spaceSince,spaceUntil,spaceFrame,problem'
    );

    /**
     * Daily hours, open for event scheduling
     *
     * @var array
     */
    protected $_daily = array(
        'since' => '10:00:00',
        'until' => '20:00:00'
    );

    /**
     * @var string
     */
    protected $_rowClass = 'Event_Row';

    /**
     * Setup fields, that are linked to space owners
     *
     * @return array
     */
    protected function _spaceOwners() {

        // Return
        return array(
            'placeId' => array(
                'rex' => 'int11',
                'hours' => [
                    'time' => function ($owner, $event, $date) {
                        if (Indi::uri('module') == 'admin' || !$owner->publicTimeIds) return false;
                        return array('time' => $owner->publicTimeIds, 'span' => $owner->duration * 60);
                    },
                    'only' => true
                ]
            ),
            'animatorId' => array(
                'rex' => 'int11list',
                'pre' => function($r){
                    $r->spaceFrame = _2sec('30m');
                }
            )
        );
    }
}