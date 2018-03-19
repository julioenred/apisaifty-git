<?php

class Model_Messages extends Orm\Model
{
    protected static $_table_name = 'Mensajes';
    protected static $_primary_key = array('id');
    protected static $_properties = array(
        'id',
        'Id_Usuario_Emisor' => array(
            'data_type' => 'varchar',
            
        ),
        'Id_Usuario_Receptor' => array(
            'data_type' => 'varchar',
            
        ),
        'Mensaje' => array(
            'data_type' => 'varchar',
            
        ),
        'Url_Foto_Mensaje' => array(
            'data_type' => 'varchar',
            
        ),
        'Leido' => array(
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
