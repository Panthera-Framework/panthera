<?php
/**
  * Twitter wrapper integrated with Panthera Framework
  *
  * @package Panthera\modules\social
  * @author Mateusz Warzyński
  * @license GNU Affero General Public License 3, see license.txt
  */

// include necessary libraries
include(PANTHERA_DIR. '/share/twitter/twitter.php');
include(PANTHERA_DIR. '/share/twitter/tmhOAuth.php');


/*class twitter extends TwitterApp
{
      protected $panthera;
      
      public function __construct()
      {
            global $panthera;
            $this->panthera = $panthera;           
      }
}*/

/**
  * Twitter wrapper for Panthera Framework
  *
  * @package Panthera\modules\social
  * @author Mateusz Warzyńśki
  */

class twitterWrapper
{
      protected $panthera;
      public $app;
      
      public function __construct()
      {
            global $panthera;
            $this -> panthera = $panthera;
              
            // login to Panthera-Framework application
            //$this -> app = new TwitterApp(new tmhOAuth(array('consumer_key' => $panthera->config->getKey('twitter_consumerKey'), 'consumer_secret' => $panthera->config->getKey('twitter_consumerSecret'))));
            
            // login to Panthera-Framework application 'by hand'
            $this -> app = new TwitterApp(new tmhOAuth(array('consumer_key' => 'eUkuhsivfWgR6Uy1uRIA', 'consumer_secret' => 'PNsgMmGOIfQa55FMVIXU5QTtAueOgpmz4S9gQfhmI')));
            
      }
      
      // authenticate user
      public function authenticate()
      {
            return $this -> app -> auth();
      }
      
      // check authentication
      public function checkAuthentication()
      {
            return $this -> app -> isAuthed();
      }
      
      // tweet post on wall
      public function tweetPost($message)
      {
            if ($this -> app -> isAuthed())
                  $this -> app -> sendTweet($message);
      }
}