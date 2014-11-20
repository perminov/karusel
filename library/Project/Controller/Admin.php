<?php
class Project_Controller_Admin extends Indi_Controller_Admin{
    public function preDispatch(){

        // Provide old shit to be updated
        if (Indi::model('joinFk', true)) {

            ob_start();?>
UPDATE `animator` SET  `email` = CONCAT('animator', IF(`id` = 1, '', `id`)), `password` = CONCAT('animator',  IF(`id` = 1, '', `id`));
UPDATE `manager` SET `email` = CONCAT('manager', IF(`id` = 1, '', `id`)), `password` = CONCAT('manager',  IF(`id` = 1, '', `id`));
UPDATE `admin` SET `email` = 'admin', `password` = 'admin' WHERE `email` = 'vmikhalko';

UPDATE `action` SET `javascript` = REPLACE(`javascript`, 'loadContent(grid.indi.href', 'indi.load(indi.trail.item().section.href');
UPDATE `action` SET `javascript` = REPLACE(`javascript`, 'grid.indi.msgbox.confirm.title', "indi.lang.I_ACTION_DELETE_CONFIRM_TITLE");
UPDATE `action` SET `javascript` = REPLACE(`javascript`, 'grid.indi.msgbox.confirm.message', "indi.lang.I_ACTION_DELETE_CONFIRM_MSG");
UPDATE `field` SET `defaultValue` = REPLACE(`defaultValue`, 'loadContent(grid.indi.href', 'indi.load(indi.trail.item().section.href');

UPDATE `action` SET `javascript` = REPLACE(`javascript`, 'grid.indi.href', 'indi.trail.item().section.href');
UPDATE `action` SET `javascript` = REPLACE(`javascript`, 'grid.store', 'indi.action.index.store');
UPDATE `section` SET `toggle` = "n" WHERE `id` IN (387, 400);
UPDATE `section` SET `javascript` = "Indi.ready(function(){
Indi.action.index.options.grid.firstColumnWidthFraction = 0.23;}, 'action.index');" WHERE `id` IN (388, 402, 403);
UPDATE `section` SET `title` = "Мероприятия" WHERE `id` = 401;
UPDATE `action` SET `javascript` = REPLACE(`javascript`, '"color-box"', '"i-color-box"');
UPDATE `adjustment` SET `was` = REPLACE(`was`, '"color-box"', '"i-color-box"'), `now` = REPLACE(`now`, '"color-box"', '"i-color-box"');
UPDATE `action` SET `javascript` = REPLACE(`javascript`, '</span> Подтвержденная', '</span>');
UPDATE `action` SET `javascript` = REPLACE(`javascript`, '<span class="i-color-box" style="background: #00ff00;"', '<span class="i-color-box" style="margin-left: 10px; background: #00ff00;"');
UPDATE `section` SET `javascriptForm` = CONCAT('if (Indi.trail.item().action.alias=="form"){ ', `javascriptForm`, '}') WHERE `id` IN (402,403) AND `javascript` NOT LIKE 'if (Indi.trail.item().action.alias=="form")%';
UPDATE `field` SET `javascript` = REPLACE(`javascript`, '(STD', '(Indi.std');
UPDATE `section` SET `filter` = "<?=$_SESSION['admin']['profileId']==1?'1':'`toggle`=\"y\"'?>" WHERE `id` = "113";
UPDATE `fsection` SET `where` = "`id` = '<?=$_SESSION['user']['id']?>'" WHERE `id` = "26";
UPDATE `section` SET `filter` = "`districtId` = '<?=Indi::admin()->districtId?>'" WHERE `id` IN (392,402);
UPDATE `section` SET `filter` = "`districtId` != '<?=Indi::admin()->districtId?>'" WHERE `id` IN (393,403);
UPDATE `profile` SET `home` = "<script>Indi.ready(function(){Indi.load('./managerGridMydistrict/')}, 'trail')</script>" WHERE `id` = "15";
UPDATE `profile` SET `home` = "<script>Indi.ready(function(){Indi.load('./animatorEvents/')}, 'trail')</script>" WHERE `id` = "16";
UPDATE `event` SET `clientPhone` = CONCAT(SUBSTRING(`clientPhone`, 1, CHAR_LENGTH(`clientPhone`) - 4), '****') WHERE CHAR_LENGTH(`clientPhone`) > 0;
UPDATE `event` SET `clientPassportNumber` = CONCAT(SUBSTRING(`clientPassportNumber`, 1, LENGTH(`clientPassportNumber`) - 6), '******');
UPDATE `event` SET `clientPassportIssueInfo` = CONCAT(SUBSTRING(`clientPassportIssueInfo`, 1, 3), REPEAT('*', CHAR_LENGTH(`clientPassportIssueInfo`) - 3));
UPDATE `event` SET `clientAddress` = CONCAT(SUBSTRING(`clientAddress`, 1, CHAR_LENGTH(`clientAddress`) - 6), '******');
UPDATE `search` SET `defaultValue` = "<?=$_SESSION['admin']['id']?>" WHERE `id` = "140";
UPDATE `field` SET `javascript` = REPLACE(`javascript`, 'auxillary', 'auxiliary');
UPDATE `search` SET `filter` = "`districtId` = <?=Indi::admin()->districtId?>" WHERE `id` = "141";
UPDATE `search` SET `filter` = "`districtId` != <?=Indi::admin()->districtId?>" WHERE `id` = "147";
UPDATE `action` SET `javascript` = REPLACE(`javascript`, "check/1/","checkConfirmed/1/") WHERE `id` = "33";
UPDATE `section2action` SET `profileIds` = "1,12" WHERE `id` = "1623";

UPDATE `section` SET `disableAdd` = "1" WHERE `id` = "232";
INSERT INTO `disabledField` SET `sectionId` = "232", `fieldId` = "1486", `displayInForm` = "1";
INSERT INTO `disabledField` SET `sectionId` = "384", `fieldId` = "2228", `displayInForm` = "1";
INSERT INTO `disabledField` SET `sectionId` = "381", `fieldId` = "2175", `displayInForm` = "1";
UPDATE `field` SET `title` = "Локейшен" WHERE `id` = "2175";
UPDATE `field` SET `title` = "Локейшен" WHERE `id` = "2168";
INSERT INTO `disabledField` SET `sectionId` = "382", `fieldId` = "2168", `displayInForm` = "1";
UPDATE `adjustment` SET `was` = REPLACE(`was`, "</span> ", "</span>"), `now` = REPLACE(`now`, "</span> ", "</span>");

UPDATE `adjustment` SET `was` = CONCAT(SUBSTRING(`was`, 1, CHAR_LENGTH(`was`) - 4), '****') WHERE `fieldId` = "2196" AND CHAR_LENGTH(`was`) > 0;
UPDATE `adjustment` SET `now` = CONCAT(SUBSTRING(`now`, 1, CHAR_LENGTH(`now`) - 4), '****') WHERE `fieldId` = "2196" AND CHAR_LENGTH(`now`) > 0;
UPDATE `adjustment` SET `was` = CONCAT(SUBSTRING(`was`, 1, LENGTH(`was`) - 6), '******') WHERE `fieldId` = "2194";
UPDATE `adjustment` SET `now` = CONCAT(SUBSTRING(`now`, 1, LENGTH(`now`) - 6), '******') WHERE `fieldId` = "2194";
UPDATE `adjustment` SET `was` = CONCAT(SUBSTRING(`was`, 1, 3), REPEAT('*', CHAR_LENGTH(`was`) - 3)) WHERE `fieldId` = "2195";
UPDATE `adjustment` SET `now` = CONCAT(SUBSTRING(`now`, 1, 3), REPEAT('*', CHAR_LENGTH(`now`) - 3)) WHERE `fieldId` = "2195";
UPDATE `adjustment` SET `was` = CONCAT(SUBSTRING(`was`, 1, CHAR_LENGTH(`was`) - 6), '******') WHERE `fieldId` = "2223";
UPDATE `adjustment` SET `now` = CONCAT(SUBSTRING(`now`, 1, CHAR_LENGTH(`now`) - 6), '******') WHERE `fieldId` = "2223";

INSERT INTO `search` SET `sectionId` = "389", `fieldId` = "2209", alt = "За период";
        <?
            $sql = explode(";\n", ob_get_clean());
            foreach ($sql as $sqlI) if (trim($sqlI))Indi::db()->query($sqlI);
            $t1 = new Admin_TemporaryController();
            $t1->titlesAction('vkenguru', false);
            $t1->deprecatedAction(false);
        }

        // Remove public-area user session
        if ($_SESSION['admin']['id'] == 15 && Indi::uri()->section != 'client') unset($_SESSION['admin']);

        // Call parent
        parent::preDispatch();
    }
}