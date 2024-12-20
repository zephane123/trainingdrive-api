<?php
class Authentication{

    protected $pdo;
    

    public function __construct(\PDO $pdo){
        $this->pdo = $pdo;
    }

    public function isAuthorized(){
        //compare request token to db token
        $headers = array_change_key_case(getallheaders(),CASE_LOWER);
        return $this->getToken() === $headers['authorization'];
    }

    private function getToken(){
        $headers = array_change_key_case(getallheaders(),CASE_LOWER);

        $sqlString = "SELECT token FROM accounts_tbl WHERE username=?";
        try{
            $stmt = $this->pdo->prepare($sqlString);
            $stmt->execute([$headers['x-auth-user']]);
            $result = $stmt->fetchAll()[0];
            return $result['token'];
        }
        catch(Exception $e){
            echo $e->getMessage();
        }
        return "";
    }

    private function generateHeader(){
        $header = [
            "typ" => "JWT",
            "alg" => "HS256",
            "app" => "DrivigShool",
            "dev" => "JonZeph and Zoie"
        ];
        return base64_encode(json_encode($header));
    }

    private function generatePayload($id, $username){
        $payload = [
            "uid" => $id,
            "uc" => $username,
            "email" => "carcar@gmail.com",
            "date" => date_create(),
            "exp" => date("Y-m-d H:i:s")
        ];
        return base64_encode(json_encode($payload));
    }

    private function generateToken($id, $username){
        $header = $this->generateHeader();
        $payload = $this->generatePayload($id, $username);
        $signature = hash_hmac("sha256", "$header.$payload", TOKEN_KEY);
        return "$header.$payload." . base64_encode($signature);
    }

    private function isSamePassword($inputPassword, $existingHash){
        $hash = crypt($inputPassword, $existingHash);
        return $hash === $existingHash;
    }

    private function encryptPassword($password){
        $hashFormat = "$2y$10$"; //blowfish
        $saltLength = 22;
        $salt = $this->generateSalt($saltLength);
        return crypt($password, $hashFormat . $salt);
    }

    private function generateSalt($length){
        $urs = md5(uniqid(mt_rand(), true));
        $b64String = base64_encode($urs);
        $mb64String = str_replace("+", ".", $b64String);
        return substr($mb64String, 0, $length);
    }

    public function saveToken($token, $username){
        
        $errmsg = "";
        $code = 0;
        
        try{
            $sqlString = "UPDATE accounts_tbl SET token=? WHERE username = ?";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute( [$token, $username] );

            $code = 200;
            $data = null;

            return array("data"=>$data, "code"=>$code);
        }
        catch(\PDOException $e){
            $errmsg = $e->getMessage();
            $code = 400;
        }

        
        return array("errmsg"=>$errmsg, "code"=>$code);

    }


    public function login($body){
        #   ->   =   - >
        $username = $body->username;
        $password = $body->password;

        $code = 0;
        $payload = "";
        $remarks = "";
        $message = "";

        try{
            $sqlString = "SELECT adminid, username, password, token FROM accounts_tbl WHERE username = ? AND isdeleted = 0";
           $stmt = $this->pdo->prepare($sqlString);
           $stmt->execute([$username]);

           if($stmt->rowCount() > 0){
            $result = $stmt->fetchAll()[0];
                if($this->isSamePassword($password, $result['password'])){
                    $code = 200;
                    $remarks = "success";
                    $message = "Logged in successfully";

                    $token = $this->generateToken($result['adminid'], $result['username']);
                    $token_arr = explode('.', $token);
                    $this->saveToken($token_arr[2], $result['username']);
                    $payload = array("adminid"=>$result['adminid'], "username"=>$result['username'], "token"=>$token_arr[2]);
                }
                else{
                    $code = 401;
                    $payload = null;
                    $remarks = "failed";
                    $message = "Incorrect Password.";
                }
           }
           else{
                $code = 401;
                $payload = null;
                $remarks = "failed";
                $message = "Username does not exist.";
           }
        }
        catch(\PDOException $e){
            $message = $e->getMessage();
            $remarks = "failed";
            $code = 400;
        }
        return array("payload"=>$payload, "remarks"=>$remarks, "message"=>$message, "code"=>$code);
    }


    public function addAccount($body){
        $values = [];
        $errmsg = "";
        $code = 0;



        $body->password = $this->encryptPassword($body->password);

        foreach($body as $value){
            array_push($values, $value);
        }
        
        try{
            $sqlString = "INSERT INTO accounts_tbl (adminid, username, password, email, isdeleted) VALUES (?, ?, ?, ?, 0)";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute($values);

            $code = 200;
            $data = null;

            return array("data"=>$data, "code"=>$code);
        }
        catch(\PDOException $e){
            $errmsg = $e->getMessage();
            $code = 400;
        }

        
        return array("errmsg"=>$errmsg, "code"=>$code);

    }

}




?>