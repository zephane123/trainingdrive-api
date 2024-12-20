<?php
class Archive{

    protected $pdo;

    public function __construct(\PDO $pdo){
        $this->pdo = $pdo;
    }




    public function deleteUser($id) {
        $errmsg = "";
        $code = 0;
    
        try {
            $sqlString = "UPDATE user_tbl SET isdeleted = 1 WHERE id = ?";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute([$id]);
    
            if ($sql->rowCount() > 0) {
                $code = 200; 
                $data = ["message" => "marked as deleted successfully"];
            } else {
                $code = 404; 
                $data = ["error" => "not found or already marked as deleted"];
            }
    
            return array("data" => $data, "code" => $code);
        } catch (\PDOException $e) {
            $errmsg = $e->getMessage();
            $code = 400; 
    
            return array("errmsg" => $errmsg, "code" => $code);
        }
    }
    public function destroyUser($id) {
        $errmsg = "";
        $code = 0;
    
        try {
            $sqlString = "DELETE FROM user_tbl WHERE id = ?";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute([$id]);
    
            if ($sql->rowCount() > 0) {
                $code = 200; 
                $data = ["message" => "deleted successfully"];
            } else {
                $code = 404; 
                $data = ["error" => "not found"];
            }
    
            return array("data" => $data, "code" => $code);
        } catch (\PDOException $e) {
            $errmsg = $e->getMessage();
            $code = 400; 
    
            return array("errmsg" => $errmsg, "code" => $code);
        }
    }
    public function deleteAccount($id) {
        $errmsg = "";
        $code = 0;
    
        try {
            $sqlString = "UPDATE accounts_tbl SET isdeleted = 1 WHERE id = ?";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute([$id]);
    
            if ($sql->rowCount() > 0) {
                $code = 200; 
                $data = ["message" => "marked as deleted successfully"];
            } else {
                $code = 404; 
                $data = ["error" => "not found or already marked as deleted"];
            }
    
            return array("data" => $data, "code" => $code);
        } catch (\PDOException $e) {
            $errmsg = $e->getMessage();
            $code = 400; 
    
            return array("errmsg" => $errmsg, "code" => $code);
        }
    }
    public function destroyAccount($id) {
        $errmsg = "";
        $code = 0;
    
        try {
            $sqlString = "DELETE FROM accounts_tbl WHERE id = ?";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute([$id]);
    
            if ($sql->rowCount() > 0) {
                $code = 200; 
                $data = ["message" => "deleted successfully"];
            } else {
                $code = 404; 
                $data = ["error" => "not found"];
            }
    
            return array("data" => $data, "code" => $code);
        } catch (\PDOException $e) {
            $errmsg = $e->getMessage();
            $code = 400; 
    
            return array("errmsg" => $errmsg, "code" => $code);
        }
    }
}

?>
