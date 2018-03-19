<?php

class Controller_hashtags extends Controller_base
{
    private $rootUrlHashtags = 'http://saifty.com/img/hashtags/';

    public function get_hashtags()
    {
        if ($this->verificarAuth()) 
        {
            $hashtags = Model_Hashtag::find('all');
            //$this->dd($hashtags);
            foreach ($hashtags as $key => $value) 
            {
                $value['Img1'] = $this->rootUrlHashtags . $value['Img1'];
                $value['Img2'] = $this->rootUrlHashtags . $value['Img2'];
                $value['Img3'] = $this->rootUrlHashtags . $value['Img3'];
            }
            return Arr::reindex($hashtags);
        }
        else
        {
            return $this->error(401, 'Authorization error', 'User error or Pass error or User not active');
        }
    }

    public function get_hashtag($id)
    {
        if ($this->verificarAuth()) 
        {
            $hashtag = $this->hashtag($id);
            return Arr::reindex($hashtag);
        }
        else
        {
            return $this->error(401, 'Authorization error', 'User error or Pass error or User not active');
        }
    }
}
