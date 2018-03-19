<?php

class Controller_messages extends Controller_base
{
    public function get_conversations($userId)
    {
        if ($this->verificarAuth()) 
        {
            if ($userId != 'me') 
            {
                return $this->error(403, 'permission denied');
            }

            $myUser = $this->getMyUser();
            $userId = $myUser->id;
            $conversations = $this->conversations($userId);
            return Arr::reindex($conversations);
        }
        else
        {
            return $this->error(401, 'Authorization error', 'User error or Pass error or User not active');
        }
    }

    public function get_usersWrote($userId)
    {
        if ($this->verificarAuth()) 
        {
            if ($userId != 'me') 
            {
                return $this->error(403, 'permission denied');
            }

            $myUser = $this->getMyUser();
            $userId = $myUser->id;
            $usersWrote = $this->usersWrote($userId);
            return Arr::reindex($usersWrote);
        }
        else
        {
            return $this->error(401, 'Authorization error', 'User error or Pass error or User not active');
        }
    }

    public function get_conversation($userId)
    {
        if ($this->verificarAuth()) 
        {

            $myUser = $this->getMyUser();
            $myUserId = $myUser->id;
            $conversation = $this->conversation($myUserId, $userId);
            return Arr::reindex($conversation);
        }
        else
        {
            return $this->error(401, 'Authorization error', 'User error or Pass error or User not active');
        }
    }

    public function post_create($idEmisor, $idReceptor)
    {
        if ($this->verificarAuth()) 
        {
            $input = Input::all();

            if ($idEmisor == 'me') 
            {
                $myUser = $this->getMyUser();
                $idEmisor = $myUser->id;
            }

            if ( ! isset($idEmisor) or ! isset($idReceptor) ) 
            {
                return $this->error(500, 'id user can`t be null');
            }

            if ( ! isset($input['message']) ) 
            {
                return $this->error(500, 'message must be written');
            }

            if ( ! $this->exist($idEmisor, 'Model_User') or ! $this->exist($idReceptor, 'Model_User')) 
            {
                return $this->error(500, 'Users must be exist');
            }

            if ( empty($input['message'])) 
            {
                return $this->error(500, 'message can`t be empty');
            }
            
            $message = new Model_Message();
            $message->Id_Usuario_Emisor = $idEmisor;
            $message->Id_Usuario_Receptor = $idReceptor;
            $message->Mensaje = $input['message'];
            $message->Url_Foto_Mensaje = 'empty';
            $message->Leido = '0';
            $message->Created_at = date("Y-m-d H:i:s", strtotime('-1 hour', strtotime(date("Y-m-d H:i:s"))));
            $message->Updated_at = date("Y-m-d H:i:s", strtotime('-1 hour', strtotime(date("Y-m-d H:i:s"))));
            $message->save();

            return $this->exito(200, 'message sent', ['message' => $input['message']]);
        }
        else
        {
            return $this->error(401, 'Authorization error', 'User error or Pass error or User not active');
        }
    }
}
