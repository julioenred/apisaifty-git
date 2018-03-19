<?php

class Model_Notification extends Orm\Model
{
    protected static $_table_name = 'Notificaciones';
    protected static $_primary_key = array('id');
    protected static $_properties = array(
        'id',
        'Id_Usuario' => array(
            'data_type' => 'varchar',
            
        ),
        'Id_Tipo' => array(
            'data_type' => 'varchar',
            
        ),
        'Leida' => array(
            'data_type' => 'varchar',
            
        ),
        'Tipo' => array(
            'data_type' => 'varchar',
            
        ),
        'Created_at' => array(
            'data_type' => 'varchar',
            
        ),
        'Updated_at' => array(
            'data_type' => 'varchar',
            
        )
    );
}
