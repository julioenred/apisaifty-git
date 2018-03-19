<?php

class Model_Block extends Orm\Model
{
    protected static $_table_name = 'Bloqueados';
    protected static $_primary_key = array('id');
    protected static $_properties = array(
        'id', 
        'Id_Bloqueador' => array(
            'data_type' => 'varchar',
           
        ),
        'Id_Bloqueado' => array(
            'data_type' => 'varchar',
           
        ),
        'Created_at' => array(
            'data_type' => 'varchar',
           
        ),
        'Updated_at' => array(
            'data_type' => 'varchar',
           
        ),
    );
}
