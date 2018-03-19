<?php

class Model_Break extends Orm\Model
{
    protected static $_table_name = 'Consejos';
    protected static $_primary_key = array('id');
    protected static $_properties = array(
        'id', // both validation & typing observers will ignore the PK o no
        'Id_Usuario' => array(
            'data_type' => 'varchar',
            
        ),
        'Url_Foto_Consejo' => array(
            'data_type' => 'varchar',
           
        ),
        'Consejo_Padre' => array(
            'data_type' => 'varchar',
           
        ),
        'Titulo' => array(
            'data_type' => 'varchar',
           
        ),
        'Comentario' => array(
            'data_type' => 'varchar',
           
        ),
        'Excerpt' => array(
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
