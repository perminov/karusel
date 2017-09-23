<?php
class Event extends Indi_Db_Table {

    /**
     * Turn changes logging to 'On' for this model
     *
     * @var array
     */
    protected $_changeLog = array(
        'toggle' => true,
        'ignore' => 'price,clientPhone2,clientAgreementNumber,title,birthChildAge,finalPrice,requestBy,requestByManagerId,requestDate,calendarStart,calendarEnd,spaceSince,spaceUntil,spaceFrame'
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
}