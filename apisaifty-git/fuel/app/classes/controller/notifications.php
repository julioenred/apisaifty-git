<?php

class Controller_notifications extends Controller_base
{
   public function get_notifications($userId)
   {
        if ($this->verificarAuth()) 
        {
            if ($userId != 'me') 
            {
                return $this->error(403, 'permission denied');
            }

            $myUser = $this->getMyUser();
            $userId = $myUser->id;
            
            $notifications = $this->notifications($userId);
            return Arr::reindex($notifications);
        }
        else
        {
            return $this->error(401, 'Authorization error', 'User error or Pass error or User not active');
        }
   }

   private function update($notificationId)
   {
        $user = $this->getMyUser();
        $notification = Model_Notification::find($notificationId);
        if ($notification->Id_Usuario == $user->id) 
        {
            $notification->Leida = '1';
            $notification->save();
            return true;
        }
        
        return false;  
   }

   public function get_notification($id)
   {
        if ($this->verificarAuth()) 
        {

            if ( $this->update($id) ) 
            {
                $notification = $this->notification($id);
                return Arr::reindex($notification);
            }

            return $this->error(500, 'the user logged not owner of notification');
        }
        else
        {
            return $this->error(401, 'Authorization error', 'User error or Pass error or User not active');
        }
   }
}
