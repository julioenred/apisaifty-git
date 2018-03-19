<?php

use \Firebase\JWT\JWT;

class Controller_base extends Controller_Rest
{
    protected $key = 'Akdor7465jdjdkrj-lfkf/kdjielskdfm7465.,slk99578.d';
    protected $algorithm = array('HS256');
    protected $rootDirectoryPictures = '/var/www/ares/public/img/';
    protected $rootUrlPictures = 'http://saifty.com/img/';

    protected function dd($variable)
    {
        var_dump($variable);
        
    }
    
    protected function error($code = 500, $mensaje = 'Internal Server Error', $descripcion = 'Unexpected Error')
    {
        return [
                    'code' => $code, 
                    'mensaje' => $mensaje,
                    'descripcion' => $descripcion,
                ];
    }

    protected function exito($code = 200, $mensaje = 'Success', $datos = null)
    {
        return [
                    'code' => $code, 
                    'mensaje' => $mensaje,
                    'datos' => $datos
                ];
    }

    private function decodeToken()
    {
        try 
        {
            $header = apache_request_headers();

            if (isset($header['Authorization'])) 
            {
                $token = $header['Authorization'];
                $dataUser = JWT::decode($token, $this->key, $this->algorithm);
            }

            return $dataUser;
        } 
        catch (Exception $e) 
        {
            $this->error(500, $e->getMessage());
        }
    }

    public function resizeImage($ruta, $nombre, $alto, $ancho, $nombreN, $extension)
    {

        # $ruta: Ruta de la imagen (Ej: vergfer\ferfe\fwef\)
        # $nombre: Nombre original de la imagen
        # $alto: Alto deseado
        # $alto: Ancho deseado
        # $nombreN: Nombre de la nueva imagen reducida
        # $extension: Extension de la imagen

        $rutaImagenOriginal = $ruta . $nombre;

        try 
        {
            $datosexif = exif_read_data($rutaImagenOriginal);
        } 
        catch (Exception $e) 
        {
            $datosexif = [];
        }
        
        if( $extension == 'GIF' || $extension == 'gif' )
        {
            $img_original = imagecreatefromgif( $rutaImagenOriginal );
        }

        if($extension == 'jpg' || $extension == 'JPG' || $extension == 'jpeg' || $extension == 'JPEG')
        {
            $img_original = imagecreatefromjpeg( $rutaImagenOriginal );
        }

        if($extension == 'png' || $extension == 'PNG')
        {
            $img_original = imagecreatefrompng( $rutaImagenOriginal );
        }

        $max_ancho = $ancho;
        $max_alto = $alto;
        list( $ancho, $alto ) = getimagesize( $rutaImagenOriginal );
        $x_ratio = $max_ancho / $ancho;
        $y_ratio = $max_alto / $alto;

        if( ( $ancho <= $max_ancho ) && ( $alto <= $max_alto ) )
        {//Si ancho 
            $ancho_final = $ancho;
            $alto_final = $alto;
        } 
        elseif ( ( $x_ratio * $alto ) < $max_alto )
        {
            $alto_final = ceil( $x_ratio * $alto );
            $ancho_final = $max_ancho;
        } 
        else
        {
            $ancho_final = ceil( $y_ratio * $ancho );
            $alto_final = $max_alto;
        }

        $tmp = imagecreatetruecolor( $ancho_final, $alto_final );
        imagecopyresampled( $tmp, $img_original, 0, 0, 0, 0, $ancho_final, $alto_final, $ancho, $alto );
        imagedestroy( $img_original );
        $calidad = 99;

        if( ! empty( $datosexif['Orientation'] ) ) 
        { 
            switch( $datosexif['Orientation'] ) 
            {
                case 8:
                    $tmp = imagerotate($tmp,90,0);
                    break;
                case 3:
                    $tmp = imagerotate($tmp,180,0);
                    break;
                case 6:
                    $tmp = imagerotate($tmp,-90,0);
                    break;
            }
        } // Analizamos la orientacion vieja y giramos la imagen nueva

        imagejpeg( $tmp, $ruta . $nombreN, $calidad ); #Guardar imagen nueva y bajar calidad
        unlink( $ruta . $nombre ); #Eliminar imagen grande 
    }

    protected function getMyUser()
    {
        try 
        {
            $myUserToken = $this->decodeToken();
            $myUser = Model_User::find('all', array('where' => array('id' => $myUserToken->id)));
            foreach ($myUser as $key => $value) 
            {
               return $value;
            }
            
        } 
        catch (Exception $e) 
        {
            $this->error(500, $e->getMessage());
        }
    }

    protected function verificarAuth()
    {
        try 
        {
            $header = apache_request_headers();

            if (isset($header['Authorization'])) 
            {
                $token = $header['Authorization'];
                $dataJwtUser = JWT::decode($token, $this->key, $this->algorithm);

                if ( isset($dataJwtUser->email) and isset($dataJwtUser->password) ) 
                {
                    $user = Model_User::find('all', array(
                        'where' => array(
                            array('Email', $dataJwtUser->email),
                        )
                    ));

                    if ( ! empty($user) ) 
                    {
                        foreach ($user as $key => $value) 
                        {
                            $id = $user[$key]->id;
                            $email = $user[$key]->Email;
                            $password = $user[$key]->password;
                            $active = $user[$key]->Activo;
                        }
                    }
                    else
                    {
                        return false;
                    }
                }
                else
                {
                    return false;
                }

                if ($email == $dataJwtUser->email and $password == $dataJwtUser->password and $active == '1') 
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }   
        } 
        catch (Exception $e) 
        {
            return $this->error(500, 'Internal Server Error', $e->getMessage());
        }  
    }

    protected function createToken()
    {
        $an = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-&$";
        $su = strlen($an) - 1;
        $string =   substr($an, rand(0, $su), 1) .
                    substr($an, rand(0, $su), 1) .
                    substr($an, rand(0, $su), 1) .
                    substr($an, rand(0, $su), 1) .
                    substr($an, rand(0, $su), 1) .
                    substr($an, rand(0, $su), 1) .
                    substr($an, rand(0, $su), 1) .
                    substr($an, rand(0, $su), 1) .
                    substr($an, rand(0, $su), 1) .
                    substr($an, rand(0, $su), 1) .
                    substr($an, rand(0, $su), 1) .
                    substr($an, rand(0, $su), 1) .
                    substr($an, rand(0, $su), 1) .
                    substr($an, rand(0, $su), 1) .
                    substr($an, rand(0, $su), 1) .
                    substr($an, rand(0, $su), 1) .
                    substr($an, rand(0, $su), 1) .
                    substr($an, rand(0, $su), 1) .
                    substr($an, rand(0, $su), 1) .
                    substr($an, rand(0, $su), 1);
        return $string;
    }

    protected function sendEmail( 
                                $htmlBody = array(),
                                $to = array('email' => 'infosaifty@gmail.com', 'name' => 'Saifty'),
                                $subject = 'test email subject',
                                $body = 'test email body',
                                $from = array('email' => 'info@saifty.com', 'name' => 'Saifty')
                               )
    {
        
        $email = Email::forge();
        $email->from($from['email'], $from['name']);
        $email->to($to['email'], $to['name']);
        $email->subject($subject);

        if ( ! empty($htmlBody) ) 
        {
            $email->html_body( \View::forge( $htmlBody['template'], $htmlBody['data'] ) );
            $body = null;
        }

        if ($body != null) 
        {
            $email->body($body);
        }
        
        try
        {
            $email->send();
            return $this->exito(200, 'Email enviado');
        }
        catch(\EmailValidationFailedException $e)
        {
            return $this->error(500, $e->getMessage());
        }
        catch(\EmailSendingFailedException $e)
        {
            return $this->error(500, $e->getMessage());
        }  
    }

    protected function updateIdfUsers()
    {
        $users = Model_User::find('all');

        foreach ($users as $key => $value) 
        {
            $value->IDF = $this->getIdf($value->id);
        }
    }

    protected function updateIdf($userId)
    {
        
        try 
        {
            $user = Model_User::find($userId);
            $user->IDF = $this->getIdf($userId);
            $user->save();
        } 
        catch (Exception $e) 
        {
            return $this->error(500, 'error updating IDF');
        }    

    }

    protected function getIdf($id)
    {
        try 
        {
            $idf = file_get_contents('http://saifty.julioperezdecastro.info/getidf/' . $id);
        } 
        catch (Exception $e) 
        {
            return $this->error(500, $e->getMessage());
        }

        return $idf;
    }

    protected function exist($elementId, $model)
    {
        $object = $model::find('all', array(
                    'where' => array(
                        array('id', $elementId),
                     ),
                 )
        );

        if (empty($object)) 
        {
            return false;
        }

        return true;
    }

    protected function username($id)
    {
        $user = DB::select('Usuario')->from('Usuarios')->where('id', $id)->execute();
        return $user[0]['Usuario'];
    }

    private function isFollowedMe($userId)
    {
        $myUser = $this->getMyUser();

        $isFollowedMe = Model_Amigo::find('all', ['where' => ['Id_Usuario_Solicita' => $myUser->id, 'Id_Usuario_Recibe' => $userId]]);

        if ( ! empty($isFollowedMe)) 
        {
           return true;
        }

        return false;
    }

    private function isBlocked($userId)
    {
        $myUser = $this->getMyUser();
        $isBlocked = Model_Block::find('all', ['where' => ['Id_Bloqueador' => $myUser->id, 'Id_Bloqueado' => $userId]]);
        // $this->dd($isBlocked);

        if ( ! empty($isBlocked) ) 
        {
            return true;
        }

        return false;
    }

    protected function allusers($type)
    {
        
        if ($type == 'allusers') 
        {
            $users = Model_User::find('all');
        }
        else
        {
            $users = Model_TopSas::find('all', array('order_by' => array('IDF' => 'desc')));
        }
        

        foreach ($users as $key => $value) 
        {
            $usersFinish[$key] = $value->to_array();

            // $this->dd($user);
             
            $usersFinish[$key]['numberBreaks'] = $this->numberBreaks($usersFinish[$key]['id']);
            $usersFinish[$key]['numberPictures'] = $this->numberPictures($usersFinish[$key]['id']);
            $usersFinish[$key]['numberContacts'] = $this->numberContacts($usersFinish[$key]['id']);
            $usersFinish[$key]['followingStatus'] = $this->isFollowedMe($usersFinish[$key]['id']);
            $usersFinish[$key]['isBlocked'] = $this->isBlocked($usersFinish[$key]['id']); 
            $usersFinish[$key]['longTimeUser'] = $this->longTime($usersFinish[$key]['Created_at']);
        }  

        return Arr::reindex($usersFinish); 
    }

    protected function user($id)
    {
        $user = Model_User::find('all', array(
                        'where' => array(
                            array('id', $id),
                         ),
                     )
            );

        foreach ($user as $key => $value) 
        {
            $user = $value->to_array();
            
            $user['numberBreaks'] = $this->numberBreaks($user['id']);
            $user['numberPictures'] = $this->numberPictures($user['id']);
            $user['numberContacts'] = $this->numberContacts($user['id']);
            $user['followingStatus'] = $this->isFollowedMe($user['id']);
            $user['isBlocked'] = $this->isBlocked($user['id']);
            $user['longTimeUser'] = $this->longTime($user['Created_at']);
        }  

        return Arr::reindex($user); 
    }

    protected function userByUsername($username)
    {
        $like = '%' . trim($username) . '%';
        $usersByUsername = Model_User::find('all', array(
                                'where' => array(
                                    array('Usuario', 'LIKE', $like),
                                 ),
                             )
        );

        foreach ($usersByUsername as $key => $value) 
        {
            $users[$key] = $value->to_array();
            
            $users[$key]['numberBreaks'] = $this->numberBreaks($users[$key]['id']);
            $users[$key]['numberPictures'] = $this->numberPictures($users[$key]['id']);
            $users[$key]['numberContacts'] = $this->numberContacts($users[$key]['id']);
            $users[$key]['followingStatus'] = $this->isFollowedMe($users[$key]['id']);
            $users[$key]['isBlocked'] = $this->isBlocked($users[$key]['id']);  
            $users[$key]['longTimeUser'] = $this->longTime($users[$key]['Created_at']);
        } 

        return Arr::reindex($users);  
    }

    protected function numberPositiveVotes($breakId)
    {
        $votes = Model_Vote::find('all', ['where' => array( ['Id_Consejo', $breakId], ['Voto', '1'] ) ]);
        return count($votes);
    }

    protected function numberNegativeVotes($breakId)
    {
        $votes = Model_Vote::find('all', ['where' => array( ['Id_Consejo', $breakId], ['Voto', '0'] ) ]);
        return count($votes);
    }

    protected function longTime($created_at)
    {
        $alta = strtotime( $created_at ) - 3600;
        $now  = strtotime('now');

        // $this->dd($now);
        // $this->dd($alta);
        // exit;

        //$prueba = '2016-02-03 13:25:36';
        //$segundos = $now - strtotime($prueba);
        $segundos           = $now - $alta;
        $diferencia_anos    = intval($segundos/60/60/24/365);
        $diferencia_meses   = intval($segundos/60/60/24/30);
        $diferencia_semanas = intval($segundos/60/60/24/7);
        $diferencia_dias    = intval($segundos/60/60/24);
        $diferencia_horas   = intval($segundos/60/60);
        $diferencia_minutos = intval($segundos/60);

        // $prueba_semanas = $segundos/60/60/24/7;

        //$this->pintar($diferencia_dias);

        if ( $diferencia_minutos < 60 ) 
        {
            $breakTime = $diferencia_minutos .  ' MIN';
        }
        else
        {
            if ( $diferencia_horas < 24 ) 
            {
                $breakTime = $diferencia_horas . ' H';
            }
            else
            {
                if ( $diferencia_dias < 31) 
                {
                    $breakTime = $diferencia_dias . ' D';
                }
                else
                {
                   
                    // if ( $diferencia_meses < 12 ) 
                    // {
                        $breakTime = $diferencia_meses . ' M';
                    // }
                    
                }

                
            }
        }

        // $this->pintar( $diferencia_minutos );
        // $this->pintar( strtotime('now') );
        // $this->pintar( $segundos );
        // $this->pintar( $fechaAlta );

        return $breakTime;
    }



    protected function breaks($offset, $limit, $userIdOrText = 'all', $byText = false)
    {
        try 
        {
            //$this->dd($userIdOrText);
            $where = array( array('Consejo_Padre', '0') );
            $fatherBreaks = Model_Break::find('all', array( 'where' => $where, 'rows_offset' => $offset, 'rows_limit' => $limit, 'order_by' => array('created_at' => 'desc') ) );

            if ($userIdOrText != 'all') 
            {
                $where = array( array('Id_Usuario', $userIdOrText), array('Consejo_Padre', '0') );
                $fatherBreaks = Model_Break::find('all', array( 'where' => $where, 'order_by' => array('created_at' => 'desc') ) );
            }

            if ($byText) 
            {
                $like = '\'% ' . trim($userIdOrText) . ' %\'';
                $likeHashtag = '\'%#' . trim($userIdOrText) . '%\'';
                $fatherBreaks = DB::query("SELECT * FROM `Consejos` 
                                           WHERE `Consejo_Padre` = '0' 
                                           and (`Comentario` LIKE $like or `Comentario` LIKE $likeHashtag)")
                                    ->execute();
                
                foreach ($fatherBreaks as $key => $value) 
                {
                    $object[$key] = Model_Break::find($value['id']);
                }

                $fatherBreaks = $object;

            }

            if (empty($fatherBreaks)) 
            {
                return $this->exito(200, 'not breaks');
            }

            //$this->dd($fatherBreaks);
            $allBreaks = Model_Break::find('all');

            foreach ($fatherBreaks as $key1 => $value1) 
            {
                $fatherUser = $this->user($value1->Id_Usuario);
                //$this->dd($fatherUser);
                $fatherBreak = $value1->to_array();
                $fatherPositiveVotes = ['numberPositiveVotes' => $this->numberPositiveVotes($value1->id)];
                $fatherNegativeVotes = ['numberNegativeVotes' => $this->numberNegativeVotes($value1->id)];
                $longTimeBreak = ['longTimeBreak' => $this->longTime($value1->Created_at)];
                $fatherUserBreak = array_merge($fatherUser, $fatherBreak, $fatherPositiveVotes, $fatherNegativeVotes, $longTimeBreak);
                // $this->dd($fatherUserBreak);
                $breaks[$key1] = $fatherUserBreak; 

                foreach ($allBreaks as $key2 => $value2) 
                {
                    if ($value1->id == $value2->Consejo_Padre) 
                    {
                        $childUser = $this->user($value2->Id_Usuario);
                        $childBreak = $value2->to_array();
                        $childPositiveVotes = ['numberPositiveVotes' => $this->numberPositiveVotes($value2->id)];
                        $childNegativeVotes = ['numberNegativeVotes' => $this->numberNegativeVotes($value2->id)];
                        $childLongTimeBreak = ['longTimeBreak' => $this->longTime($value2->Created_at)];
                        $childUserBreak = array_merge($childUser, $childBreak, $childPositiveVotes, $childNegativeVotes, $childLongTimeBreak);
                        $breaks[$key1]['childBreaks'][] = $childUserBreak;
                    }

                }

                if ( ! isset($breaks[$key1]['childBreaks']) ) 
                {
                    $breaks[$key1]['childBreaks'][] = array();
                }


            }

            //$this->dd($breaks);
            return Arr::reindex($breaks);
        } 
        catch (Exception $e) 
        {
            $this->error(500, $e->getMessage());
        }
    }

    protected function isUserBreak($id)
    {
        $user = $this->getMyUser();
        $break = Model_Break::find($id);

        if ($break->Id_Usuario != $user->id) 
        {
            return false;
        }

        return true;
    }

    protected function userBreak($idBreak)
    {
        try 
        {
            $break = Model_Break::find('all', array('where' => array('id' => $idBreak) ) );
            foreach ($break as $key => $value) 
            {
                $break = $value;
            }
            $user = $this->user($break->Id_Usuario);
            $breakPositiveVotes = ['numberPositiveVotes' => $this->numberPositiveVotes($break->id)];
            $breakNegativeVotes = ['numberNegativeVotes' => $this->numberNegativeVotes($break->id)];
            $longTimeBreak = ['longTimeBreak' => $this->longTime($break->Created_at)];
            $userBreak = array_merge($user, $break->to_array(), $breakPositiveVotes, $breakNegativeVotes, $longTimeBreak);

            if ($break->Consejo_Padre != '0') 
            {
                $break = Model_Break::find('all', array('where' => array('id' => $break->Consejo_Padre) ) );
                foreach ($break as $key => $value) 
                {
                    $break = $value;
                }
                $user = $this->user($break->Id_Usuario);
                $breakPositiveVotes = ['numberPositiveVotes' => $this->numberPositiveVotes($break->id)];
                $breakNegativeVotes = ['numberNegativeVotes' => $this->numberNegativeVotes($break->id)];
                $longTimeBreak = ['longTimeBreak' => $this->longTime($break->Created_at)];
                $userBreak = array_merge($user, $break->to_array(), $breakPositiveVotes, $breakNegativeVotes, $longTimeBreak);
                $allBreaks = Model_Break::find('all');

                foreach ($allBreaks as $key => $value) 
                {
                    if ($userBreak['id'] == $value->Consejo_Padre) 
                    {
                        $childUser = $this->user($value->Id_Usuario);
                        $childBreak = $value->to_array();
                        $childPositiveVotes = ['numberPositiveVotes' => $this->numberPositiveVotes($value->id)];
                        $childNegativeVotes = ['numberNegativeVotes' => $this->numberNegativeVotes($value->id)];
                        $childLongTimeBreak = ['longTimeBreak' => $this->longTime($value->Created_at)];
                        $childUserBreak = array_merge($childUser, $childBreak, $childPositiveVotes, $childNegativeVotes, $childLongTimeBreak);
                        $userBreak['childBreaks'][] = $childUserBreak;
                    }
                }
            }
            else
            {
                $allBreaks = Model_Break::find('all');

                foreach ($allBreaks as $key => $value) 
                {
                    if ($userBreak['id'] == $value->Consejo_Padre) 
                    {
                        $childUser = $this->user($value->Id_Usuario);
                        $childBreak = $value->to_array();
                        $childPositiveVotes = ['numberPositiveVotes' => $this->numberPositiveVotes($value->id)];
                        $childNegativeVotes = ['numberNegativeVotes' => $this->numberNegativeVotes($value->id)];
                        $childLongTimeBreak = ['longTimeBreak' => $this->longTime($value->Created_at)];
                        $childUserBreak = array_merge($childUser, $childBreak, $childPositiveVotes, $childNegativeVotes, $childLongTimeBreak);
                        $userBreak['childBreaks'][] = $childUserBreak;
                    }
                }
            }

            return Arr::reindex($userBreak);
        } 
        catch (Exception $e) 
        {
            return $this->error(500, $e->getMessage());
        }
    }

    public function numberContacts($userId)
    {

        $Contacto = [];


        $idRecibe = DB::select('Id_Usuario_Recibe')
                        ->from('Amigos')
                        ->where('Id_Usuario_Solicita', '=', $userId)
                        ->execute();

        for ($i=0; $i < count($idRecibe) ; $i++) 
        { 
            $Cont = DB::select('Id_Usuario_Solicita')
                        ->from('Amigos')
                        ->where('Id_Usuario_Recibe', '=', $userId )
                        ->where('Id_Usuario_Solicita', '=', $idRecibe[$i]['Id_Usuario_Recibe'])
                        ->execute();

            if ( ! empty ($Cont) ) 
            {
                $Contacto[$i] = DB::select('Id_Usuario_Solicita')
                        ->from('Amigos')
                        ->where('Id_Usuario_Recibe', '=', $userId )
                        ->where('Id_Usuario_Solicita', '=', $idRecibe[$i]['Id_Usuario_Recibe'])
                        ->execute();
            }

            
        }

        //$this->dd(count($Contacto));

        return count($Contacto);
    }

    protected function usersContacts($userId, $type)
    {
        $where = ['Id_Usuario_Recibe' => $userId];
        $message = 'No hay usuarios seguidores';
        $property = 'Id_Usuario_Solicita';
        
        if ( $type == 'followed') 
        {
            $where = ['Id_Usuario_Solicita' => $userId];
            $message = 'No hay usuarios seguidos';
            $property = 'Id_Usuario_Recibe';
        }

        $usersContacts = array();
        $contacts = Model_Amigo::find('all', ['where' => $where ] );

        if (empty($contacts)) 
        {
           return array();
        }

        foreach ($contacts as $key => $value) 
        {
            $usersContacts[] = $this->user($value->$property);
        }

        return Arr::reindex($usersContacts);
    }

    protected function numberBreaks($userId)
    {
        
        $breaks = Model_Break::find('all', ['where' => [ ['Id_Usuario' => $userId], ['Consejo_Padre' => '0'] ] ] );
        return count($breaks);
    }

    protected function usersWrote($myUserId)
    {
        // $this->dd($myUserId);

        $messagesWrote = DB::select('*')->from('Mensajes')
                                        ->where('Id_Usuario_Emisor', $myUserId)
                                        ->or_where('Id_Usuario_Receptor', $myUserId)->execute();
        $usersConversations = [];

        foreach ($messagesWrote as $key => $value) 
        {
            if ($value['Id_Usuario_Emisor'] != $myUserId) 
            {
                $usersConversations[$key] = $value['Id_Usuario_Emisor'];
            }
            else
            {
                $usersConversations[$key] = $value['Id_Usuario_Receptor'];
            }  
        }

        $usersConversations = array_unique($usersConversations);

        // $this->dd($usersConversations);

        $messages = DB::select('*')->from('Mensajes')->execute();

        // $this->dd($messages);

        foreach ($usersConversations as $key => $value) 
        {
            $usersConversations[$key] = $this->user($value);  
            
            unset($usersConversations[$key]['Created_at']);
            unset($usersConversations[$key]['Updated_at']);
            $messagesNotSendByMe = [];

            foreach ($messages as $keyMessages => $valueMessages) 
            {
                if (
                    ($messages[$keyMessages]['Id_Usuario_Emisor'] == $value 
                    or 
                    $messages[$keyMessages]['Id_Usuario_Receptor'] == $value)
                    and
                    ($messages[$keyMessages]['Id_Usuario_Emisor'] == $myUserId 
                    or 
                    $messages[$keyMessages]['Id_Usuario_Receptor'] == $myUserId)
                   ) 
                {
                    $valueMessages['longTimeMessage'] = $this->longTime($valueMessages['created_at']);
                    $conversations[] = $valueMessages;
                    // $this->dd($valueMessages);
                }  

                if (
                    $messages[$keyMessages]['Id_Usuario_Emisor'] == $value 
                    and 
                    $messages[$keyMessages]['Id_Usuario_Receptor'] == $myUserId
                   ) 
                {
                    $messagesNotSendByMe[] = $valueMessages;

                } 

            }

            // $this->dd($conversations);

            if (empty($messagesNotSendByMe)) 
            {
                $leido = ['Leido' => '1'];
            }
            else
            {
                $lastMessageNotSendByMe = array_pop($messagesNotSendByMe);
                $leido = ['Leido' => $lastMessageNotSendByMe['Leido']];
            }

            
            
            $lastMessages = array_pop($conversations);  

            $usersWrote[] = array_merge($usersConversations[$key], $lastMessages, $leido);
        }

        // $this->dd($conversations);

        // $this->dd($usersWrote);

        return $usersWrote;
    }

    protected function conversation($myUserId, $userId)
    {
        $messages = DB::select('*')->from('Mensajes')->execute();

        foreach ($messages as $keyMessages => $valueMessages) 
        {
            if (
                ($messages[$keyMessages]['Id_Usuario_Emisor'] == $myUserId 
                or 
                $messages[$keyMessages]['Id_Usuario_Receptor'] == $myUserId)
                and
                ($messages[$keyMessages]['Id_Usuario_Emisor'] == $userId 
                or 
                $messages[$keyMessages]['Id_Usuario_Receptor'] == $userId)
               ) 
            {
                if (
                    $messages[$keyMessages]['Id_Usuario_Emisor'] == $userId 
                    and 
                    $messages[$keyMessages]['Id_Usuario_Receptor'] == $myUserId
                   ) 
                {
                    $message = Model_Message::find($messages[$keyMessages]['id']);
                    $message->Leido = '1';
                    $message->save();
                }
                

                $userEmisor = $this->user($messages[$keyMessages]['Id_Usuario_Emisor']);
                $userReceptor = $this->user($messages[$keyMessages]['Id_Usuario_Receptor']);
                $userEmisorFinish['Emisor_Usuario'] = $userEmisor['Usuario'];
                $userEmisorFinish['Emisor_Url_Foto_Perfil'] = $userEmisor['Url_Foto_Perfil']; 
                $userEmisorFinish['Emisor_Sector'] = $userEmisor['Sector'];
                $userReceptorFinish['Receptor_Usuario'] = $userReceptor['Usuario'];
                $userReceptorFinish['Receptor_Url_Foto_Perfil'] = $userReceptor['Url_Foto_Perfil']; 
                $userReceptorFinish['Receptor_Sector'] = $userReceptor['Sector'];
                $longTimeMessage = ['longTimeMessage' => $this->longTime($messages[$keyMessages]['created_at'])];

                //$this->dd($userReceptor);
                unset($userEmisor['Created_at']);
                unset($userEmisor['Updated_at']);
                $message = $valueMessages;

                $conversations[] = array_merge($message, $userEmisorFinish, $userReceptorFinish, $longTimeMessage);

            }  
        }
        

        if (empty($conversations)) 
        {
            return $this->exito(200, 'user don´t have conversations');
        }

        //$this->dd($conversations);

        return Arr::reindex($conversations);
    }

    protected function conversations($userId)
    {
        $conversations = [];
        $usersWrote = $this->usersWrote($userId);
        // $this->dd($usersWrote);
        $messages = DB::select('*')->from('Mensajes')->execute();

        foreach ($usersWrote as $keyUsersWrote => $valueUsersWrote) 
        {
            foreach ($messages as $keyMessages => $valueMessages) 
            {
                if (
                    ($messages[$keyMessages]['Id_Usuario_Emisor'] == $usersWrote[$keyUsersWrote]['id'] 
                    or 
                    $messages[$keyMessages]['Id_Usuario_Receptor'] == $usersWrote[$keyUsersWrote]['id'])
                    and
                    ($messages[$keyMessages]['Id_Usuario_Emisor'] == $userId 
                    or 
                    $messages[$keyMessages]['Id_Usuario_Receptor'] == $userId)
                   ) 
                {
                    $userEmisor = $this->user($messages[$keyMessages]['Id_Usuario_Emisor']);
                    $userReceptor = $this->user($messages[$keyMessages]['Id_Usuario_Receptor']);
                    // $this->dd($userReceptor);
                    unset($userEmisor['Created_at']);
                    unset($userEmisor['Updated_at']);
                    $message = $valueMessages;
                    if ($userId != $userEmisor['id']) 
                    {
                        $userConversation['userConversation'] = $userEmisor['Usuario'];
                    }

                    if ($userId != $userReceptor['id']) 
                    {
                        $userConversation['userConversation'] = $userReceptor['Usuario'];
                    }

                    $conversations[$keyUsersWrote][] = array_merge($message, $userEmisor, $userConversation);

                }

                
            }
        }

        if (empty($conversations)) 
        {
            return $this->exito(200, 'user don´t have conversations');
        }

        //$this->dd($conversations);

        return Arr::reindex($conversations);
    }

    protected function notifications($userId)
    {
        $notifications = Model_Notification::find('all', ['where' => ['Id_Usuario' => $userId], 'order_by' => ['Updated_at' => 'desc'] ] );
        
        if (empty($notifications)) 
        {
            return $this->exito(200, 'user don´t have notifications');
        }

        return Arr::reindex($notifications);
    }

    protected function notification($id)
    {
        $notification = Model_Notification::find($id);

        if ($notification->Tipo == 'Seguidor') 
        {
            $follower = $this->user($notification->Id_Tipo);
            return Arr::reindex($follower);
        }

        if ($notification->Tipo == 'Conversacion') 
        {
            $break = $this->userBreak($notification->Id_Tipo);
            return Arr::reindex($break);
        }

        return $this->error();
    }

    protected function usersInConversation($idFatherBreak)
    {
        $idUsers = DB::select('Id_Usuario')->from('Consejos')
                                                ->group_by('Id_Usuario')
                                                ->where('Consejo_Padre', $idFatherBreak)
                                                ->or_where('id', $idFatherBreak)
                                                ->execute();

        foreach ($idUsers as $key => $value) 
        {
            $ids[] = $value['Id_Usuario'];
        }

        return $ids;
    }

    protected function createNotification($userId, $typeId, $type)
    {
        if ( ! $this->exist($userId, 'Model_User')) 
        {
            return false;
        }

        if ($type != 'Conversacion' and $type != 'Seguidor') 
        {
            return false;
        }

        if ($type == 'Conversacion') 
        {
            if ( ! $this->exist($typeId, 'Model_Break')) 
            {
                return false;
            }
        }

        if ($type == 'Seguidor') 
        {
            if ( ! $this->exist($typeId, 'Model_User')) 
            {
                return false;
            }
        }

        //$this->trace('true');

        $existNotification = Model_Notification::find('all', ['where' => ['Id_Usuario' => $userId, 'Id_Tipo' => $typeId, 'Tipo' => $type]]);

        if (empty($existNotification)) 
        {
            $notification = new Model_Notification();
            $notification->Id_Usuario = $userId;
            $notification->Id_Tipo = $typeId;
            $notification->Tipo = $type;
            $notification->Leida = '0';
            $notification->Created_at = date("Y-m-d H:i:s", strtotime('-1 hour', strtotime(date("Y-m-d H:i:s"))));
            $notification->Updated_at = date("Y-m-d H:i:s", strtotime('-1 hour', strtotime(date("Y-m-d H:i:s"))));
            $notification->save();
        }
        else
        {
            $notification = Model_Notification::find('all', ['where' => ['Id_Usuario' => $userId, 'Id_Tipo' => $typeId, 'Tipo' => $type]]);
            //$this->dd($notification);
            foreach ($notification as $key => $value) 
            {
                $notification = Model_Notification::find($value->id);
            }
            
            $notification->Leida = '0';
            $notification->Updated_at = date("Y-m-d H:i:s", strtotime('-1 hour', strtotime(date("Y-m-d H:i:s"))));
            $notification->save();
        }

        return true;
    }

    protected function numberPictures($userId)
    {
        $pictures = $this->pictures($userId);

        if ($pictures == 0) 
        {
            return 0;
        }

        return count($pictures);
    }

    protected function pictures($userId)
    {
        $user['id'] = $userId;
        $user['username'] = $this->username($userId);
        
        $pictures = array();
        $i = 0;
        
        $directory = $this->rootDirectoryPictures . $user['id'] . '-' . $user['username'] . '/';
        $url = $this->rootUrlPictures . $user['id'] . '-' . $user['username'] . '/';

        if ( ! file_exists($directory) ) 
        {
            return 0;
        }

        $directorio = opendir( $directory ); //ruta actual

        while ($archivo = readdir($directorio)) //obtenemos un archivo y luego otro sucesivamente
        {
            if ( ( $archivo != '.' ) && ( $archivo != '..' ) ) 
            {
                $pictures[$i] = $url . $archivo;
                $i++;
            }    
        }

        return Arr::reindex($pictures);         
    }

    protected function hashtag($id)
    {
        $hashtag = Model_Hashtag::find('all', array('where' => array('id' => $id)));
        $breaks = [];
        
        foreach ($hashtag as $key => $value) 
        {
            $busqueda = $value['Busqueda'];  
        }
        $busqueda = explode(',', $busqueda);
        $todosConsejos = Model_Break::find('all');
        $numeroTodosConsejos = count($todosConsejos);

        foreach ($busqueda as $key => $value) 
        {
            $breaks[$key] = $this->breaks(0, $numeroTodosConsejos, trim($value), true);
        }
       
        foreach ($breaks as $key => $moreBreaks) 
        {
            for ($i=0; $i < count($moreBreaks); $i++) 
            { 
                $allBreaks[] = $moreBreaks[$i]['id'];
            }
        }

        $allBreaks = array_unique($allBreaks);

        foreach ($allBreaks as $key => $value) 
        {
            $allBreaks[$key] = $this->userBreak($value);
        }
        
        return Arr::reindex($allBreaks);
    }   
}
