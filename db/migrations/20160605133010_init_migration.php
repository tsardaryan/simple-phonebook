<?php

use Phinx\Migration\AbstractMigration;

class InitMigration extends AbstractMigration
{
    public function change()
    {
		$users_table = $this->table('users');
        $users_table->addColumn('full_name', 'string')
            ->addColumn('phone_number', 'string')
            ->addColumn('birthday', 'date', array("null" => true))
            ->addColumn('address', 'string', array("null" => true))
			->addIndex(array('full_name'))
			->addIndex(array('phone_number'), array('unique' => true, 'name' => 'indx_phone_number'))
            ->create();
    }
}
