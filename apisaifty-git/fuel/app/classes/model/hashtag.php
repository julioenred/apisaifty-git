<?php

class Model_Hashtag extends Orm\Model
{
    protected static $_table_name = 'ActividadesRecientes';
    protected static $_primary_key = array('id');
    protected static $_properties = array(
        'id', // both validation & typing observers will ignore the PK o no
        'Titulo' => array(
            'data_type' => 'varchar',
            
        ),
        'Busqueda' => array(
            'data_type' => 'varchar',
           
        ),
        'Img1' => array(
            'data_type' => 'varchar',
           
        ),
        'Img2' => array(
            'data_type' => 'varchar',
           
        ),
        'Img3' => array(
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
