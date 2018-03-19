<?php

class Model_TopSas extends Orm\Model
{
    protected static $_table_name = 'TopSAS';
    protected static $_primary_key = array('id');
    protected static $_properties = array(
        'id', // both validation & typing observers will ignore the PK o no
        'Email' => array(
            'data_type' => 'varchar',
            
        ),
        'Usuario' => array(
            'data_type' => 'varchar',
            
        ),
        'Nombre' => array(
            'data_type' => 'varchar',
            
        ),
        'Mostrar_Nombre' => array(
            'data_type' => 'varchar',
            
        ),
        'Apellidos' => array(
            'data_type' => 'varchar',
            
        ),
        'Mostrar_Apellidos' => array(
            'data_type' => 'varchar',
            
        ),
        'Ubicacion' => array(
            'data_type' => 'varchar',
            
        ),
        'Mostrar_Ubicacion' => array(
            'data_type' => 'varchar',
            
        ),
        'password' => array(
            'data_type' => 'varchar',
           
        ),
        'Url_Foto_Perfil' => array(
            'data_type' => 'varchar',
           
        ),
        'Url_Foto_Portada' => array(
            'data_type' => 'varchar',
           
        ),
        'Sobre_Ti' => array(
            'data_type' => 'varchar',
           
        ),
        'Activo' => array(
            'data_type' => 'varchar',
           
        ),
        'Texto_Inicial' => array(
            'data_type' => 'varchar',
           
        ),
        'Token' => array(
            'data_type' => 'varchar',
           
        ),
        'Sector' => array(
            'data_type' => 'varchar',
           
        ),
        'Especificar' => array(
            'data_type' => 'varchar',
           
        ),
        'Tipo' => array(
            'data_type' => 'varchar',
           
        ),
        'Baja' => array(
            'data_type' => 'varchar',
           
        ),
        'Baja_Motivo' => array(
            'data_type' => 'varchar',
           
        ),
        'Created_at' => array(
            'data_type' => 'varchar',
           
        ),
        'Updated_at' => array(
            'data_type' => 'varchar',
           
        ),
        'IDF' => array(
            'data_type' => 'varchar',
           
        )
    );
}
