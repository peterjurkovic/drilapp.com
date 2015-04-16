<?php

class UserController
{
   
   private $userService;    
   private $wordService;
   private $settingsService;

    /**
     * Login user
     *
     * @url POST /v1/user/login
     * @noAuth
     */   
   public function login( $data ){
        global $drilConf;
        if(!isset($data) || !isset($data->username)){
            throw new RestException(401, 'Credentials are required.');
        }
        
        $user = $this->userService->getUserByLogin( $data->username );
        if($user == null){
            throw new RestException(401, 'User [username='.$data->username.'] was not found');
        }
        if(hash_hmac('sha256', $data->password , $user['salt']) == $user['pass']){
            try {

                $token = array(
                   // "iss" => "http://www.drilapp.com",
                   // "aud" => "http://web.drilapp.com",
                    "iat" => time(),
                    "exp" => time() + 10200,
                    "uid" => $user['id']
                );
                unset($user['pass']);
                unset($user['salt']);
                $result['token'] = JWT::encode($token, $drilConf['dril_auth']);
                $result['user'] = $user;
                $result['actiavtedWords'] = $this->wordService->getAllUserActivatedWords($user['id']);
                $result['settings'] = $this->settingsService->getOrCreateUserSettings($user['id']);
                $logger = Logger::getLogger('api');
                $logger->info("User [id=" .$user['id']."] was successfully logged in. [ip=" .$_SERVER['SERVER_ADDR']."]");
               return $result;

            } catch(UnexpectedValueException $ex) { 
              throw new RestException(401, "Invalid security token " .$data->username);   
            }    
        }else{
            throw new RestException(401, "Bad username or password " .$data->username);   
        }

   }

    /**
     * Create new book
     *
     * @url POST /v1/users
     * @noAuth
     */
    public function create( $data ) {
        return $this->userService->create($data);
    }
   
    public function init(){
        global $conn;
        $this->userService = new UserService($conn);
        $this->wordService = new WordService($conn);
        $this->settingsService = new SettingsService($conn);
    }
}