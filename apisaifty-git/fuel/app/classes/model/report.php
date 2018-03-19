<?php

class Model_Report extends Orm\Model
{
    protected static $_table_name = 'Denunciados';
    protected static $_primary_key = array('id');
    protected static $_properties = array(
        'id', 
        'Id_Denunciante' => array(
            'data_type' => 'varchar',
           
        ),
        'Id_Denunciado' => array(
            'data_type' => 'varchar',
           
        ),
        'Motivo' => array(
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
