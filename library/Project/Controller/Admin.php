<?php
class Project_Controller_Admin extends Indi_Controller_Admin{

    /**
     *
     */
    public function preDispatch(){

        // Replace
        if (Indi::demo(false) && Indi::ini('general')->domain == 'indi-engine.com' && preg_match('!/demo/!', STD)) {
            if (!Indi::db()->query('
                SELECT * FROM `admin`
                WHERE `email` = "admin" AND `password` = PASSWORD("admin")
            ')->fetch()) {

                // Demo data sql
                $sql = <<<SQL
UPDATE `animator` SET  `email` = CONCAT('animator', IF(`id` = 1, '', `id`)), `password` = CONCAT('animator',  IF(`id` = 1, '', `id`));
UPDATE `manager` SET `email` = CONCAT('manager', IF(`id` = 1, '', `id`)), `password` = CONCAT('manager',  IF(`id` = 1, '', `id`));
UPDATE `manager` SET `email` = "manager", `password` = "manager" LIMIT 1;
UPDATE `admin` SET `email` = 'admin', `password` = PASSWORD('admin') WHERE `email` = 'vmikhalko';
SQL;
                // Run queries
                $sql = explode(";\n", $sql);
                foreach ($sql as $sqlI)
                    if (trim($sqlI) && !preg_match('/^#/', $sqlI))
                        Indi::db()->query($sqlI);
            }
        }

        // Call parent
        parent::preDispatch();
    }
}