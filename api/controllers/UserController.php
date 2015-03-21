<?php

class UserController
{
   
    /**
     * Login user
     *
     * @url POST /v1/user/login
     * @noAuth
     */   
   public function login( $data ){
        global $userService;    
        global $wordService;
        if(!isset($data) || !isset($data->username)){
            throw new RestException(401, 'Credentials are required.');
        }
        
        $user = $userService->getUserByLogin( $data->username );
        if($user == null){
            throw new RestException(401, 'User [username='.$data->username.'] was not found');
        }
        if(hash_hmac('sha256', $data->password , $user['salt']) == $user['pass']){
            try {
                $key = "example_key";
                $token = array(
                   // "iss" => "http://www.drilapp.com",
                   // "aud" => "http://web.drilapp.com",
                    "iat" => time(),
                    "exp" => time() + 3600,
                    "uid" => $user['id_user']
                );
                unset($user['pass']);
                unset($user['salt']);
                $result['token'] = JWT::encode($token, $key);
                $result['user'] = $user;
                $result['actiavtedWords'] = $wordService->getAllUserActivatedWords($user['id_user']);
                $logger = Logger::getLogger('api');
                $logger->info("User [id=" .$user['id_user']."] was successfully logged in. [ip=" .$_SERVER['SERVER_ADDR']."]");
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
    public function create( $data )
    {
        //print_r($data);exit;
        global $userService;
        return $userService->create($data);
    }
   
}