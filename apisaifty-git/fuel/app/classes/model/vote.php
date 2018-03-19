<?php

class Model_Vote extends Orm\Model
{
    protected static $_table_name = 'Votaciones';
    protected static $_primary_key = array('Id_Usuario', 'Id_Consejo');
    protected static $_properties = array(
        'Id_Usuario', 
        'Id_Consejo',
        'Voto' => array(
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
