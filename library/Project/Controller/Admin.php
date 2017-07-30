<?php
class Project_Controller_Admin extends Indi_Controller_Admin{

    /**
     *
     */
    public function preDispatch(){

        // Demo data sql
        $sql = <<<SQL
UPDATE `animator` SET  `email` = CONCAT('animator', IF(`id` = 1, '', `id`)), `password` = CONCAT('animator',  IF(`id` = 1, '', `id`));
UPDATE `manager` SET `email` = CONCAT('manager', IF(`id` = 1, '', `id`)), `password` = CONCAT('manager',  IF(`id` = 1, '', `id`));
UPDATE `admin` SET `email` = 'admin', `password` = PASSWORD('admin') WHERE `email` = 'vmikhalko';
UPDATE `event` SET `clientPhone` = CONCAT(SUBSTRING(`clientPhone`, 1, CHAR_LENGTH(`clientPhone`) - 4), '****') WHERE CHAR_LENGTH(`clientPhone`) > 0;
UPDATE `event` SET `clientPassportNumber` = CONCAT(SUBSTRING(`clientPassportNumber`, 1, LENGTH(`clientPassportNumber`) - 6), '******');
UPDATE `event` SET `clientPassportIssueInfo` = CONCAT(SUBSTRING(`clientPassportIssueInfo`, 1, 3), REPEAT('*', CHAR_LENGTH(`clientPassportIssueInfo`) - 3));
UPDATE `event` SET `clientAddress` = CONCAT(SUBSTRING(`clientAddress`, 1, CHAR_LENGTH(`clientAddress`) - 6), '******');
UPDATE `changeLog` SET `was` = CONCAT(SUBSTRING(`was`, 1, CHAR_LENGTH(`was`) - 4), '****') WHERE `fieldId` = "2196" AND CHAR_LENGTH(`was`) > 0;
UPDATE `changeLog` SET `now` = CONCAT(SUBSTRING(`now`, 1, CHAR_LENGTH(`now`) - 4), '****') WHERE `fieldId` = "2196" AND CHAR_LENGTH(`now`) > 0;
UPDATE `changeLog` SET `was` = CONCAT(SUBSTRING(`was`, 1, LENGTH(`was`) - 6), '******') WHERE `fieldId` = "2194";
UPDATE `changeLog` SET `now` = CONCAT(SUBSTRING(`now`, 1, LENGTH(`now`) - 6), '******') WHERE `fieldId` = "2194";
UPDATE `changeLog` SET `was` = CONCAT(SUBSTRING(`was`, 1, 3), REPEAT('*', CHAR_LENGTH(`was`) - 3)) WHERE `fieldId` = "2195";
UPDATE `changeLog` SET `now` = CONCAT(SUBSTRING(`now`, 1, 3), REPEAT('*', CHAR_LENGTH(`now`) - 3)) WHERE `fieldId` = "2195";
UPDATE `changeLog` SET `was` = CONCAT(SUBSTRING(`was`, 1, CHAR_LENGTH(`was`) - 6), '******') WHERE `fieldId` = "2223";
UPDATE `changeLog` SET `now` = CONCAT(SUBSTRING(`now`, 1, CHAR_LENGTH(`now`) - 6), '******') WHERE `fieldId` = "2223";
SQL;
        /* $sql = explode(";\n", $sql);
        foreach ($sql as $sqlI)
            if (trim($sqlI) && !preg_match('/^#/', $sqlI))
                Indi::db()->query($sqlI); */

        // Call parent
        parent::preDispatch();
    }
}