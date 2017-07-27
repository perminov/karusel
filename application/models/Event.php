<?php
class Event extends Indi_Db_Table_Schedule {

    /**
     * Turn changes logging to 'On' for this model
     *
     * @var array
     */
    protected $_changeLog = array(
        'toggle' => true,
        'ignore' => 'price,clientAgreementNumber,title,birthChildAge,finalPrice,requestBy,requestByManagerId,requestDate,calendarStart,calendarEnd,spaceSince,spaceUntil,spaceFrame'
    );

    /**
     * @var string
     */
    protected $_rowClass = 'Event_Row';
}