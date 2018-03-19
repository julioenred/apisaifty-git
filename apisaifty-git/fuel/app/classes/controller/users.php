<?php

use \Firebase\JWT\JWT;

class Controller_users extends Controller_base
{
    private $rootActivateAcount = 'http://saifty.com/activarcuenta/';

    public function post_login()
    {
        $user = Model_User::find('all', array(
            'where' => array(
                array('Email', Input::post('email')),
            )
        ));

        if ( ! empty($user) ) 
        {
            foreach ($user as $key => $value) 
            {
                $id = $user[$key]->id;
                $username = $user[$key]->Usuario;
                $email = $user[$key]->Email;
                $passwordHash = $user[$key]->password;
                $passwordIsOk = password_verify(Input::post('password'), $user[$key]->password);
                $active = $user[$key]->Activo;
            }
        }
        else
        {
            return $this->error(401, 'Error de Autenticación', 'Usuario o contraseña incorrectos');
        }

        if ($active == 0) 
        {
            return $this->error(400, 'Activa tu usuario a través del email que te hemos enviado', 'Usuario inactivo');
        }

        if ($email == Input::post('email') and $passwordIsOk) 
        {
            $token = array(
                "id" => $id,
                "email" => $email,
                "username" => $username,
                "password" => $passwordHash,
                "Active" => $active
            );

            $jwt = JWT::encode($token, $this->key);

            return [
                'code' => 200, 
                'token' => $jwt
            ];  
        }
        else
        {
            return $this->error(401, 'Error de Autenticación', 'Usuario o contraseña incorrectos');
        }
    }

    private function crear($data, $model)
    {
        
        $object1 = $model::find('all', ['where' => ['Email' => $data['Email']]]);
        $object2 = $model::find('all', ['where' => ['Usuario' => $data['Usuario']]]);

        if (empty($object1) and empty($object2)) 
        {
            $object = $model::forge();

            foreach ($data as $key => $value) 
            {
                $object->$key = $value;
            }

            $object->save();
        }
        else
        {
            return false;
        }

        return true;  
    }

    public function post_create()
    {
        $input = Input::all();
        $input['Usuario'] = strtolower( $input['Usuario'] );
        $input['password'] = password_hash($input['password'], PASSWORD_DEFAULT);
        $input['Token'] = $this->createToken();
        $input['Url_Foto_Perfil'] = 'avatar.png';
        $input['Url_Foto_Portada'] = 'foto-portada.jpg';
        $input['Texto_Inicial'] = '1';
        $input['Sobre_Ti'] = '';
        $input['Activo'] = '0';
        $input['Sector'] = 'OT';
        $input['Tipo'] = 'Usuario';
        $input['IDF'] = '0';
        $input['Created_at'] = date("Y-m-d H:i:s", strtotime('-1 hour', strtotime(date("Y-m-d H:i:s"))));
        $input['Updated_at'] = date("Y-m-d H:i:s", strtotime('-1 hour', strtotime(date("Y-m-d H:i:s"))));

        //$this->dd('trace');

        if ( ! $this->crear($input, 'Model_User')) 
        {
            return $this->error(500, 'username or email alredy exist');
        }
       
        
        $link = $this->rootActivateAcount . $input['Email'] . '/' . $input['Token'];
        $this->sendEmail(
                          ['template' => 'activateAcount', 'data' => ['link' => $link]], 
                          ['email' => $input['Email'], 'name' => $input['Usuario']],
                          'Activa tu cuenta'
                        );

        return $this->exito(200, 'User Created', $input);
        
    }

    public function get_allUsers()
    {
        if ($this->verificarAuth()) 
        {
            $users = $this->allusers('allusers');
            return Arr::reindex($users);
        }
        else
        {
            return $this->error(401, 'Authorization error', 'User error or Pass error or User not active');
        }
    }

    public function get_followed($id = 'me')
    {
        if ($this->verificarAuth()) 
        {
            if ($id == 'me') 
            {
                $myUser = $this->getMyUser();
                $id = $myUser->id;
            }

            $usersFollowed = $this->usersContacts($id, 'followed');
            if (empty($usersFollowed)) 
            {
                return $this->exito(200, 'user not followed');
            }
            return Arr::reindex($usersFollowed);
        }
        else
        {
            return $this->error(401, 'Authorization error', 'User error or Pass error or User not active');
        }
    }

    public function get_followers($id = 'me')
    {
        if ($this->verificarAuth()) 
        {
            if ($id == 'me') 
            {
                $myUser = $this->getMyUser();
                $id = $myUser->id;
            }

            $usersFollowers = $this->usersContacts($id, 'followers');
            if (empty($usersFollowers)) 
            {
                return $this->exito(200, 'user not followers');
            }
            return Arr::reindex($usersFollowers);
        }
        else
        {
            return $this->error(401, 'Authorization error', 'User error or Pass error or User not active');
        }
    }

    public function post_follow($idFollower, $idFollowed)
    {
        if ($this->verificarAuth()) 
        {
            if ($idFollower == 'me') 
            {
                $myUser = $this->getMyUser();
                $idFollower = $myUser->id;
            }

            if ( ! isset($idFollower) or ! isset($idFollowed) ) 
            {
                return $this->error(500, 'id user can`t be null');
            }

            if ( ! $this->exist($idFollower, 'Model_User') or ! $this->exist($idFollowed, 'Model_User')) 
            {
                return $this->error(500, 'User must be exist');
            }

            if ($idFollower == $idFollowed) 
            {
                return $this->error(500, 'You can not follow yourself');
            }

            $amigo = Model_Amigo::find('all', ['where' => ['Id_Usuario_Solicita' => $idFollower, 'Id_Usuario_Recibe' => $idFollowed]]);

            if (empty($amigo)) 
            {
                $amigo = new Model_Amigo;
                $amigo->Id_Usuario_Solicita = $idFollower;
                $amigo->Id_Usuario_Recibe = $idFollowed;
                $amigo->Created_at = date("Y-m-d H:i:s", strtotime('-1 hour', strtotime(date("Y-m-d H:i:s"))));
                $amigo->Updated_at = date("Y-m-d H:i:s", strtotime('-1 hour', strtotime(date("Y-m-d H:i:s"))));
                $amigo->save(); 
            }

            if ( ! $this->createNotification($idFollowed, $idFollower, 'Seguidor')) 
            {
                return $this->error(500, 'User not notificated');
            }

            return $this->exito(200, 'Users conected', ['idFollower' => $idFollower, 'idFollowed' => $idFollowed]);
        }
        else
        {
            return $this->error(401, 'Authorization error', 'User error or Pass error or User not active');
        }
    }

    public function post_unfollow($idFollower, $idUnfollowed)
    {
        if ($this->verificarAuth()) 
        {
            if ($idFollower == 'me') 
            {
                $myUser = $this->getMyUser();
                $idFollower = $myUser->id;
            }

            if ( ! isset($idFollower) or ! isset($idUnfollowed) ) 
            {
                return $this->error(500, 'id user can`t be null');
            }

            if ( ! $this->exist($idFollower, 'Model_User') or ! $this->exist($idUnfollowed, 'Model_User')) 
            {
                return $this->error(500, 'Users must be exist');
            }

            $amigo = Model_Amigo::find('all', ['where' => ['Id_Usuario_Solicita' => $idFollower, 'Id_Usuario_Recibe' => $idUnfollowed]]);

            if ( ! empty($amigo)) 
            {
                Model_Amigo::find([$idFollower, $idUnfollowed])->delete();
            }

            return $this->exito(200, 'Users disconected', ['idFollower' => $idFollower, 'idUnfollowed' => $idUnfollowed]);
        }
        else
        {
            return $this->error(401, 'Authorization error', 'User error or Pass error or User not active');
        }
    }

    public function get_usersTopSas()
    {   
        if ($this->verificarAuth()) 
        {
            $users = $this->allusers('topsas');
            return Arr::reindex($users);
        }
        else
        {
            return $this->error(401, 'Authorization error', 'User error or Pass error or User not active');
        }
    }

    public function get_users($username)
    {   
        if ($this->verificarAuth()) 
        {
            
            $users = $this->userByUsername($username);

            if (empty($users)) 
            {
                return $this->exito(200, 'users not found');
            }

            return Arr::reindex($users);
        }
        else
        {
            return $this->error(401, 'Authorization error', 'User error or Pass error or User not active');
        }
    }

    public function get_user($id = null)
    {
        
        if ($this->verificarAuth()) 
        {
            if ($id == null) 
            {
                return $this->error(500, 'Error interno del servidor', 'Parametro id null en la petición, pasa un parametro válido');
            }

            if ($id == 'me') 
            {
                $myUser = $this->getMyUser();
                $id = $myUser->id;
            }

            $user = $this->user($id);

            if ($user['isBlocked']) 
            {
                return $this->error(403, 'User is blocked', 'El usuario no existe');
            }

            if (empty($user)) 
            {
                return $this->error(500, 'Error interno del servidor', 'El usuario no existe');
            }

            return Arr::reindex($user);
        }
        else
        {
            return $this->error(401, 'Error de Autenticación', 'Usuario o contraseña incorrectos');
        }
    }

    public function get_pictures($userId)
    {
        if ($this->verificarAuth()) 
        {

            if ($userId == 'me') 
            {
                $myUser = $this->getMyUser();
                $userId = $myUser->id;
            }

            $userPictures = $this->pictures($userId);

            if ($userPictures == 0) 
            {
                return $this->exito(200, 'not pictures');
            }

            return Arr::reindex($userPictures);
        }
        else
        {
            return $this->error(401, 'Error de Autenticación', 'Usuario o contraseña incorrectos');
        }
    }

    public function post_picture()
    {
        
        if ($this->verificarAuth()) 
        {

            //$this->dd($_FILES);
            $user = $this->getMyUser();

            $config = array(
                'path' => $this->rootDirectoryPictures . $user->id . '-' . $user->Usuario,
                'randomize' => true,
                'ext_whitelist' => array('jpg', 'jpeg', 'png'),
                'prefix' => $user->Usuario . '-',
                'create_path' => true
            );


            Upload::process($config);

            if (Upload::is_valid())
            {
                Upload::save();
            }

            foreach(Upload::get_files() as $file)
            {
                $newName = 'resized-' . strtotime(date("Y-m-d H:i:s")) . '-' .  $file['saved_as'];
                $this->resizeImage($file['saved_to'], $file['saved_as'], 200, 200,  $newName, $file['extension']);
                return $this->exito(200, 'image uploaded', ['name' => $newName]);
            }

            foreach (Upload::get_errors() as $file)
            {
                $this->dd($file);
            }
        }
        else
        {
            return $this->error(401, 'Error de Autenticación', 'Usuario o contraseña incorrectos');
        }

    }

    public function post_createAvatar()
    {
        
        if ($this->verificarAuth()) 
        {

            //$this->dd($_FILES);
            $user = $this->getMyUser();

            $config = array(
                'path' => $this->rootDirectoryPictures,
                'randomize' => false,
                'ext_whitelist' => array('jpg', 'jpeg', 'png'),
                'prefix' => $user->Usuario,
                'new_name' => '-avatar-temporal'
            );


            Upload::process($config);

            if (Upload::is_valid())
            {
                Upload::save();

            }

            foreach(Upload::get_files() as $file)
            {
                $newName = $user->Usuario . '-' . strtotime(date("Y-m-d H:i:s")) . '-avatar.' . strtolower($file['extension']);
                $this->resizeImage($file['saved_to'], $file['saved_as'], 200, 200,  $newName, $file['extension']);
                $this->modificar($user->id, ['Url_Foto_Perfil' => $newName], 'Model_User');
                return $this->exito(200, 'image uploaded', ['name' => $newName]);
                //$this->dd($file);
            }

            foreach (Upload::get_errors() as $file)
            {
                return $this->error(500, 'Hubo un problema', 'no se pudo actualizar el avatar');
            }
        }
        else
        {
            return $this->error(401, 'Error de Autenticación', 'Usuario o contraseña incorrectos');
        }

    }

    public function post_createCoverPage()
    {
        
        if ($this->verificarAuth()) 
        {

            //$this->dd($_FILES);
            $user = $this->getMyUser();

            $config = array(
                'path' => $this->rootDirectoryPictures,
                'randomize' => false,
                'ext_whitelist' => array('jpg', 'jpeg', 'png'),
                'prefix' => $user->Usuario,
                'new_name' => '-coverpage-temporal'
            );


            Upload::process($config);

            if (Upload::is_valid())
            {
                Upload::save();

            }

            foreach(Upload::get_files() as $file)
            {
                $newName = $user->Usuario .  '-' . strtotime(date("Y-m-d H:i:s")) . '-coverpage.' . strtolower($file['extension']);
                $this->resizeImage($file['saved_to'], $file['saved_as'], 800, 200,  $newName, $file['extension']);
                $this->modificar($user->id, ['Url_Foto_Portada' => $newName], 'Model_User');
                return $this->exito(200, 'image uploaded', ['name' => $newName]);
                $this->dd($file);
            }

            foreach (Upload::get_errors() as $file)
            {
                $this->dd($file);
            }
        }
        else
        {
            return $this->error(401, 'Error de Autenticación', 'Usuario o contraseña incorrectos');
        }

    }

    public function post_block($userId)
    {
        if ($this->verificarAuth()) 
        {
            $myUser = $this->getMyUser();
            $isBlocked = Model_Block::find('all', ['where' => ['Id_Bloqueador' => $myUser->id, 'Id_Bloqueado' => $userId]]);

            if ( empty($isBlocked) ) 
            {
                $block = new Model_Block();
                $block->Id_Bloqueador = $myUser->id;
                $block->Id_Bloqueado = $userId;
                $block->save();
            }

            return $this->exito(200, 'User bloqued', ['userId' => $userId]);
            
        }
        else
        {
            return $this->error(401, 'Error de autenticación', 'usuario o contraseña incorrectos');
        }
    }

    public function post_unBlock($userId)
    {
        if ($this->verificarAuth()) 
        {
            $myUser = $this->getMyUser();
            $isBlocked = Model_Block::find('all', ['where' => ['Id_Bloqueador' => $myUser->id, 'Id_Bloqueado' => $userId]]);

            if ( ! empty($isBlocked) ) 
            {
                foreach ($isBlocked as $key => $blocked) 
                {
                    $block = Model_Block::find($blocked->id);
                    $block->delete();
                }
                
            }

            return $this->exito(200, 'User unbloqued', ['userId' => $userId]);
            
        }
        else
        {
            return $this->error(401, 'Error de autenticación', 'usuario o contraseña incorrectos');
        }
    }

    private function modificar($elementId, $data, $model)
    {
        try 
        {
            $object = $model::find($elementId);

            foreach ($data as $key => $value) 
            {
                $object->$key = $value;
            }

            $object->save();
            
        } 
        catch (Exception $e) 
        {
            return $this->error(500, $e->getMessage());
        }
        
    }

    public function post_update($userId)
    {
        $input = Input::all();

        if ($this->verificarAuth()) 
        {
            
            $this->modificar($userId, $input, 'Model_User');
            return $this->exito(200, 'Usuario Modificado', Arr::reindex($input));
        }
        else
        {
            return $this->error(401, 'Error de autenticación', 'usuario o contraseña incorrectos');
        }
    }

    private function borrar($elementId, $model)
    {
        try 
        {
            $object = $model::find($elementId);
            $object->delete();
        } 
        catch (Exception $e) 
        {
            return $this->error(500, $e->getMessage());
        }     
    }

    public function post_down()
    {
        if ($this->verificarAuth()) 
        {
            $myUser = $this->getMyUser();

            $user = Model_User::find($myUser->id);
            $user->Baja = '1';
            $user->Baja_Motivo = Input::post('motivo');
            $user->Updated_at = date("Y-m-d H:i:s", strtotime('-1 hour', strtotime(date("Y-m-d H:i:s"))));
            $user->save();

            return $this->exito(200, 'user down');
        }
        else
        {
            return $this->error(401, 'Error de autenticación', 'usuario o contraseña incorrectos');
        }
    }

    public function post_up()
    {
        if ($this->verificarAuth()) 
        {
            $myUser = $this->getMyUser();

            $user = Model_User::find($myUser->id);
            $user->Baja = '0';
            $user->Updated_at = date("Y-m-d H:i:s", strtotime('-1 hour', strtotime(date("Y-m-d H:i:s"))));
            $user->save();

            return $this->exito(200, 'user up');
        }
        else
        {
            return $this->error(401, 'Error de autenticación', 'usuario o contraseña incorrectos');
        }
    }

    public function post_report($userId)
    {
        if ($this->verificarAuth()) 
        {
            if ( Input::post('motivo') == null or empty(Input::post('motivo'))) 
            {
                return $this->error(500, 'set post value. motivo not set or empty');
            }

            $myUser = $this->getMyUser();

            if ($myUser->id == $userId) 
            {
                return $this->error(500, 'you can`t report yourself');
            }

            $isReport = Model_Report::find('all', ['where' => ['Id_Denunciante' => $myUser->id, 'Id_Denunciado' => $userId]]);

            if (empty($isReport)) 
            {
                $report = new Model_Report();
                $report->Id_Denunciante = $myUser->id;
                $report->Id_Denunciado = $userId;
                $report->Motivo = Input::post('motivo');
                $report->Created_at = date("Y-m-d H:i:s", strtotime('-1 hour', strtotime(date("Y-m-d H:i:s"))));
                $report->Updated_at = date("Y-m-d H:i:s", strtotime('-1 hour', strtotime(date("Y-m-d H:i:s"))));
                $report->save();
            }
            else
            {
                foreach ($isReport as $key => $value) 
                {
                    $report = Model_Report::find($isReport[$key]->id);
                }
                
                $report->Motivo = Input::post('motivo');
                $report->Updated_at = date("Y-m-d H:i:s", strtotime('-1 hour', strtotime(date("Y-m-d H:i:s"))));
                $report->save();
            }

            return $this->exito(200, 'user reported');
        }
        else
        {
            return $this->error(401, 'Error de autenticación', 'usuario o contraseña incorrectos');
        }
    }

    public function post_delete($userId)
    {

        if ($this->verificarAuth()) 
        {
            try
            {
                if ( ! $this->exist($userId, 'Model_User') ) 
                {
                    return $this->error(500, 'Error interno del servidor', 'el usuario a borrar no existe');
                }

                $this->borrar($userId, 'Model_User');
                return $this->exito(200, 'Usuario Borrado');
            }
            catch(Exception $e)
            {
                return $this->error(500, $e->getMessage());
            }
        }
        else
        {
            return $this->error(401, 'Error de autenticación', 'usuario o contraseña incorrectos');
        }
    }
}
