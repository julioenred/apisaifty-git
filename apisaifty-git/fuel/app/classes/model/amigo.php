<?php

class Model_Amigo extends Orm\Model
{
    protected static $_table_name = 'Amigos';
    protected static $_primary_key = array('Id_Usuario_Solicita', 'Id_Usuario_Recibe');
    protected static $_properties = array(
        'Id_Usuario_Solicita', 
        'Id_Usuario_Recibe',
        'Created_at',
        'Updated_at'
    );
}
