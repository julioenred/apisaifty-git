<?php

define("MAX_NUMBER_CHARACTERES", 4000);
define("EXCERPT_BREAK", 400);

class Controller_breaks extends Controller_base
{
    
    public function get_allBreaks($offset, $limit)
    {
        if ($this->verificarAuth()) 
        {
            $breaks = $this->breaks($offset, $limit, 'all');
            return Arr::reindex($breaks);
        }
        else
        {
            return $this->error(401, 'Authorization error', 'User error or Pass error or User not active');
        }
    }

    public function get_breaks($offset, $limit, $userId)
    {   
        if ($this->verificarAuth()) 
        {
            if ($userId == 'me') 
            {
                $myUser = $this->getMyUser();
                $userId = $myUser->id;
            }

            $breaks = $this->breaks($offset, $limit, $userId);

            if ( ! is_numeric($userId) ) 
            {
                 $breaks = $this->breaks($offset, $limit, $userId, true);
            }

            return Arr::reindex($breaks);
        }
        else
        {
            return $this->error(401, 'Authorization error', 'User error or Pass error or User not active');
        }
    }

    public function get_break($id)
    {
        if ($this->verificarAuth()) 
        {
            $break = $this->userBreak($id);
            return Arr::reindex($break);
        }
        else
        {
            return $this->error(401, 'Authorization error', 'User error or Pass error or User not active');
        }
    }

    public function get_followed($offset, $limit, $userId = 'me')
    {
        
        if ($this->verificarAuth()) 
        {
            if ($userId == 'me') 
            {
                $myUser = $this->getMyUser();
                $userId = $myUser->id;
            }

            $allBreaks = $this->breaks($offset, $limit, 'all');
            $followeds = $this->usersContacts($userId, 'followed');
            //$this->dd($followeds);
            $breaksFollowed = array();

            foreach ($allBreaks as $key1 => $value1) 
            {
                foreach ($followeds as $key2 => $value2) 
                {
                    if ($value1['Id_Usuario'] == $value2['id']) 
                    {
                        $breaksFollowed[$key1] = $value1;
                    }
                }
            }

            if (empty($breaksFollowed)) 
            {
                return $this->exito(200, 'No hay Breaks que mostrar. Prueba a seguir a alguien!');
            }

            return Arr::reindex($breaksFollowed);
        }
        else
        {
            return $this->error(401, 'Authorization error', 'User error or Pass error or User not active');
        }        
    }

    private function getUsersMentioned( $String , $Symbol )
    {
        
        $numberOfSymbols = substr_count($String, $Symbol);

        if ( $numberOfSymbols != 0 ) 
        {
            $textSplit = str_split( $String );
            // @return Array = [0, 1, 2 ...]

            // $this->pintar($textSplit);

            

            for ($i=0; $i < $numberOfSymbols ; $i++) 
            { 
                $usersMentioned[] = '';
            }

            $y = 0;

            foreach ($textSplit as $key => $value) 
            {
                if ( $textSplit[$key] == $Symbol ) 
                {

                   //$this->pintar($textSplit[$key]);
                   for ($i=$key+1; $i < count( $textSplit ) ; $i++) 
                   { 
                       
                       if ( preg_match( '/^[0-9a-zA-Z]+$/', $textSplit[$i] ) ) 
                       {
                           //$this->pintar($textSplit[$i]);
                           //$this->pintar($usersMentioned);
                           $usersMentioned[$y] = $usersMentioned[$y] . $textSplit[$i];
                           //$this->pintar($usersMentioned);
                          
                       }
                       else
                       {
                           
                            $y++;
                            break;
                       } 
                       
                   }  

                }
            }
        }
        else
        {
            $usersMentioned[0] = 0;
        }

        //$this->pintar($usersMentioned);

        return $usersMentioned;
    }

    private function getLinkUsersMentioned( $usersMentioned, $Symbol )
    {
        //$this->pintar($usersMentioned);

        if ( $usersMentioned[0] != '0' ) 
        {
            
            // for ($z=0; $z < count( $usersMentioned ) ; $z++) 
            // { 
            //     $linkUsersMentioned[$z]['User'] = '';
            //     $linkUsersMentioned[$z]['Link'] = '';
            // }

            //$this->pintar( $linkUsersMentioned );
            
            $i = 0;

            //$this->pintar( 'hola' );

            foreach ($usersMentioned as $key => $value) 
            {
                //$this->pintar( $usersMentioned );
                $idUser = DB::select('id')->from('Usuarios')->where('Usuario', '=', strtolower( $usersMentioned[$key] ) )->execute();

                //$this->dd($idUser);

                if ( ! empty($idUser[0]['id']) ) 
                {
                    //$this->pintar( $idUser );
                    $linkUsersMentioned[$i]['User'] = $usersMentioned[$key];
                    $linkUsersMentioned[$i]['Link'] = "<a href='/usuario/" . $idUser[0]['id'] . "'>" . $Symbol . $usersMentioned[$key] . "</a>";
                    $i++;
                }

            }
        }
        else
        {
            //$this->pintar( 'adios' );
            $linkUsersMentioned[0] = 0;
        }

        //$this->pintar( isset( $linkUsersMentioned ) );

        if ( ! isset( $linkUsersMentioned ) ) 
        {
            return 0;
        }

        //$this->dd( $linkUsersMentioned );
        return $linkUsersMentioned;
    }

    private function replaceMessageForLink( $String = '' , $Symbol = '@')
    {
       
       $messageReplaced = $String;

       $usersMentioned =  $this->getUsersMentioned( $messageReplaced, $Symbol ); 

       //$this->dd( $usersMentioned );

       $linkUsersMentioned = $this->getLinkUsersMentioned( $usersMentioned, $Symbol );

       //$this->dd( $linkUsersMentioned );

       if ( $linkUsersMentioned != 0) 
       {
           foreach ($linkUsersMentioned as $key => $value) 
           {
                $messageReplaced = str_replace( $Symbol . $linkUsersMentioned[$key]['User'], $linkUsersMentioned[$key]['Link'], $messageReplaced);
           }
       }
       
           

       $messageReplaced = $this->transformLinksInMessage( $messageReplaced );

       $messageReplaced = $this->trasnformHashtagsInMessage( $messageReplaced );

       // $this->pintar( $messageReplaced );

       return $messageReplaced;
    }

    protected function excerptBreak( $String )
    {
        

        // $this->pintar($String);

        $excerptBreak = $this->lenString( $String , EXCERPT_BREAK );

        //echo $excerptBreak;
        //echo $String;

        //return;

        if ( $this->haveBreakLink( $excerptBreak ) ) 
        {
            $excerptBreak = $this->recalculateExcerptBreak( $excerptBreak );
        }

        // $this->pintar( $excerptBreak );

        return $excerptBreak;
    }

    public function trasnformHashtagsInMessage( $String )
    {
        //$this->pintar( $String );

        $textSplit = $this->splitString( $String );

        //$this->pintar( $textSplit );

        if ( ! empty( $textSplit ) and $textSplit[0] != '' ) 
        {
            $i = 0;

            foreach ($textSplit as $key => $value) 
            {
                if ( ! empty( $textSplit[$key] ) and $textSplit[$key][0] == '#' ) 
                {
                   $link[$i]['Anchor'] = $textSplit[$key];
                   $link[$i]['Link'] = "<a href=''>" . $textSplit[$key] . "</a>";
                   $i++; 
                }
            }
        }

        if ( isset($link) ) 
        {
            $messageReplaced = $String;

            foreach ($link as $key => $value) 
            {
    
                $messageReplaced = str_replace( $link[$key]['Anchor'], $link[$key]['Link'], $messageReplaced);
               
            }
        }
        else
        {
            return $String;
        }



        // $this->pintar($messageReplaced);

        return $messageReplaced;
    }

    public function lenString( $String, $len = 150 )
    {
        return substr( $String, 0, $len);
    }

    public function splitString( $string )
    {
        $array = preg_split('/ /', $string);
        return $array;   
    }

    private function numberCharacteres( $String )
    {
        return strlen( $String );
    }

    private function transformLinksInMessage( $String  )
    {
        
        $textSplit = $this->splitString( $String );
        // @return $textSplit['el', 'mensaje', 'por', 'palabras']
        //$this->pintar($textSplit);

        if ( ! empty( $textSplit ) ) 
        {
            $compare = ['http://', 'https://', 'www.'];
            $lenString = 3;
            $www = 2;
            $i = 0;
            


            foreach ($textSplit as $key => $value) 
            {
                if ( strlen( $textSplit[$key] ) > $lenString ) 
                {
                   
                    foreach ($compare as $key2 => $value2) 
                    {
                       // $this->pintar( strpos( $textSplit[$key], $compare[$key2] ) );

                        if ( strpos( $textSplit[$key], $compare[$key2] ) !== false ) 
                        {

                            if ( $key2 == $www) 
                            {
                                $link[$i]['Anchor'] = $textSplit[$key];
                                $link[$i]['Link'] = "<a href='http://" . $textSplit[$key] . "' target='_BLANK'>" . $this->lenString( $textSplit[$key], 30 ) . "</a>";
                            }
                            else
                            {
                                $link[$i]['Anchor'] = $textSplit[$key];
                                $link[$i]['Link'] = "<a href='" . $textSplit[$key] . "' target='_BLANK'>" .  $this->lenString( $textSplit[$key], 30 ) . "</a>";
                            }
                            
                            $i++;
                            break;
                        }
                        
                        
                    }
                }
            }
        }

        //$this->pintar($link);
        
        if ( isset($link) ) 
        {
            $messageReplaced = $String;

            foreach ($link as $key => $value) 
            {
    
                $messageReplaced = str_replace( $link[$key]['Anchor'], $link[$key]['Link'], $messageReplaced);
               
            }
        }
        else
        {
            return $String;
        }



        // $this->pintar($messageReplaced);

        return $messageReplaced;
    }

    private function haveBreakLink( $excerptBreak )
    {
        //$this->pintar( $excerptBreak );

        $numberOpenLinks   = substr_count($excerptBreak, '<a');

        $numberCloseLinks  = substr_count($excerptBreak, '</a>');

        //$this->pintar( $numberOpenLinks != $numberCloseLinks );

        return $numberOpenLinks != $numberCloseLinks;
    }

    private function turnAroud( $breakLink )
    {
        return strrev( $breakLink );
    }

    private function searchBreakLink( $excerptBreak )
    {
        $cont = -1;
        $breakLinkInvested = '';

        //$this->pintar( $excerptBreak );


        do 
        {
            
            $breakLinkInvested = $breakLinkInvested . substr( $excerptBreak , $cont, 1); 
            $cont--;

            //$this->pintar( $breakLinkInvested );
            //$this->pintar( substr_count($breakLinkInvested, 'a<') );

        } while ( substr_count($breakLinkInvested, 'a<') == 0 );

        // $this->pintar( $breakLinkInvested );

        $breakLink = $this->turnAroud( $breakLinkInvested );

        // $this->pintar( $breakLink );

        return $breakLink;
    }

    private function deleteBreakLink( $breakLink , $excerptBreak )
    {
        return str_replace( $breakLink, '', $excerptBreak );
    }

    private function recalculateExcerptBreak( $excerptBreak )
    {
        $breakLink = $this->searchBreakLink( $excerptBreak );

        $excerptBreak = $this->deleteBreakLink( $breakLink , $excerptBreak );

        // $this->pintar( $excerptBreak );

        return $excerptBreak;
    }

    public function post_create($fatherBreak = '0')
    {
        if ($this->verificarAuth()) 
        {
            $input = Input::all();
            $user = $this->getMyUser();

            // $newBreak = new Model_Break();
            // $newBreak->Id_Usuario = $user->id;
            // $newBreak->Titulo = '';
            // $newBreak->Comentario = mb_detect_encoding($input['message']);
            // $newBreak->Excerpt = mb_detect_encoding($input['message']);
            // $newBreak->Url_Foto_Consejo = '';
            // $newBreak->Consejo_Padre = $fatherBreak;
            // $newBreak->Created_at = $created = date("Y-m-d H:i:s", strtotime('-1 hour', strtotime(date("Y-m-d H:i:s"))));
            // $newBreak->Updated_at = date("Y-m-d H:i:s", strtotime('-1 hour', strtotime(date("Y-m-d H:i:s"))));
            // $newBreak->save();

            // exit;

            if ( empty($_FILES) and (! isset($input['message']) or $input['message'] == '')) 
            {
                return $this->error(500, 'message empty');
            }

            if (empty($_FILES)) 
            {
                $replaceMessageForLink = $this->replaceMessageForLink($input['message']);
                if ( $this->numberCharacteres( $replaceMessageForLink ) > MAX_NUMBER_CHARACTERES ) 
                {
                    return $this->error(500, 'Message too long'); 
                }

                $excerptBreak = $this->excerptBreak( $replaceMessageForLink );
                
                $newBreak = new Model_Break();
                $newBreak->Id_Usuario = $user->id;
                $newBreak->Titulo = '';
                $newBreak->Comentario = $replaceMessageForLink;
                $newBreak->Excerpt = $excerptBreak;
                $newBreak->Url_Foto_Consejo = '';
                $newBreak->Consejo_Padre = $fatherBreak;
                $newBreak->Created_at = $created = date("Y-m-d H:i:s", strtotime('+1 hour', strtotime(date("Y-m-d H:i:s"))));
                $newBreak->Updated_at = date("Y-m-d H:i:s", strtotime('+1 hour', strtotime(date("Y-m-d H:i:s"))));
                $newBreak->save();

                if ($fatherBreak != '0') 
                {
                    $usersInConversation = $this->usersInConversation($fatherBreak);
                    //$this->dd($usersInConversation);
                    foreach ($usersInConversation as $key => $userId) 
                    {
                        if ($userId != $user->id) 
                        {
                            if ( ! $this->createNotification($userId, $fatherBreak, 'Conversacion')) 
                            {
                                return $this->error(500, 'break created but notifications not created');
                            }
                        }  
                    }
                }

                return $this->exito(200, 'break created');
            }

            $config = array(
                'path' => $this->rootDirectoryPictures,
                'randomize' => true,
                'ext_whitelist' => array('jpg', 'jpeg', 'png'),
                'prefix' => $user->Usuario,
                'new_name' => '-consejo-temporal-' 
            );

            Upload::process($config);

            if (Upload::is_valid())
            {
                Upload::save();
            }

            foreach(Upload::get_files() as $file)
            {
                $newNamePicture = 'consejo-user-' . $user->Usuario . strtotime(date("Y-m-d H:i:s")) . strtolower($file['extension']);
                $this->resizeImage($file['saved_to'], $file['saved_as'], 425, 425,  $newNamePicture, $file['extension']);
                $replaceMessageForLink = $this->replaceMessageForLink($input['message']);
                if ( $this->numberCharacteres( $replaceMessageForLink ) > MAX_NUMBER_CHARACTERES ) 
                {
                    return $this->error(500, 'Message too long'); 
                }

                $excerptBreak = $this->excerptBreak( $replaceMessageForLink );

                // var_dump($replaceMessageForLink);
                // exit;

                if ($replaceMessageForLink == '0') {
                    $replaceMessageForLink = '';
                }

                if ($excerptBreak == '0') {
                    $excerptBreak = '';
                }
                
                $newBreak = new Model_Break();
                $newBreak->Id_Usuario = $user->id;
                $newBreak->Titulo = '';
                $newBreak->Comentario = $replaceMessageForLink;
                $newBreak->Excerpt = $excerptBreak;
                $newBreak->Url_Foto_Consejo = $newNamePicture;
                $newBreak->Consejo_Padre = $fatherBreak;
                $newBreak->Created_at = date("Y-m-d H:i:s", strtotime('+1 hour', strtotime(date("Y-m-d H:i:s"))));
                $newBreak->Updated_at = date("Y-m-d H:i:s", strtotime('+1 hour', strtotime(date("Y-m-d H:i:s"))));
                $newBreak->save();

                if ($fatherBreak != '0') 
                {
                    $usersInConversation = $this->usersInConversation($fatherBreak);
                    //$this->dd($usersInConversation);
                    foreach ($usersInConversation as $key => $userId) 
                    {
                        if ($userId != $user->id) 
                        {
                            if ( ! $this->createNotification($userId, $fatherBreak, 'Conversacion')) 
                            {
                                return $this->error(500, 'break created but notifications not created');
                            }
                        }  
                    }
                }

                return $this->exito(200, 'break created');
            }

            foreach (Upload::get_errors() as $file)
            {
                return $this->error(500, "error uploading break's image", $file);
            }
        }
        else
        {
            return $this->error(401, 'Authorization error', 'User error or Pass error or User not active');
        }
    }

    public function post_vote($breakId, $vote)
    {
        if ($this->verificarAuth()) 
        {
            if ($vote != '0' and $vote != '1') 
            {
                return $this->error(500, 'invalid vote');
            }

            $myUser = $this->getMyUser();    

            $Votacion = Model_Vote::find('all', ['where' => ['Id_Usuario' => $myUser->id, 'Id_Consejo' => $breakId]]);

            //$this->dd($Votacion);

            if ( empty($Votacion) ) 
            {
                $Votacion                = new Model_Vote();
                $Votacion->Id_Usuario    = $myUser->id;
                $Votacion->Id_Consejo    = $breakId;
                $Votacion->Voto          = $vote;
                $Votacion->save();
            }
            else
            {
                DB::update('Votaciones')->where('Id_Usuario', $myUser->id)
                                          ->where('Id_Consejo', $breakId)
                                          ->value('Voto', $vote )->execute();
            }

            return $this->exito(200, 'vote succesful');
        }
        else
        {
            return $this->error(401, 'Authorization error', 'User error or Pass error or User not active');
        }


    }

    public function post_delete($breakId)
    {
        if ($this->verificarAuth()) 
        {
            
            if ( ! $this->isUserBreak($breakId)) 
            {
                return $this->error(403, 'permision denied');
            }

            $break = Model_Break::find($breakId);
            $break->delete();

            return $this->exito(200, 'break deleted');

        }
        else
        {
            return $this->error(401, 'Authorization error', 'User error or Pass error or User not active');
        }
    }
}
