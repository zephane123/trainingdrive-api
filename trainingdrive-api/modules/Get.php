<?php
include_once "Common.php";

class Get extends Common{

    protected $pdo;

    public function __construct(\PDO $pdo){
        $this->pdo = $pdo;
    }
    
    public function getLogs($date){
        $filename = "./logs/" . $date . ".log";
        
        // $file = file_get_contents("./logs/$filename");
        // $logs = explode(PHP_EOL, $file);

        
        $logs = array();
        try{
            $file = new SplFileObject($filename);
            while(!$file->eof()){
                array_push($logs, $file->fgets());
            }
            $remarks = "success";
            $message = "Successfully retrieved logs.";
        }
        catch(Exception $e){
            $remarks = "failed";
            $message = $e->getMessage();
        }
        

        return $this->generateResponse(array("logs"=>$logs), $remarks, $message, 200);
    }


   // Method to fetch all users
public function getAllUsers() {
    try {
        // Query to fetch all users from the user_tbl
        $query = "SELECT * FROM user_tbl WHERE isdeleted = 0";  // Assuming 'isdeleted' is used to filter active users
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            "status" => "success",
            "message" => "All users data retrieved successfully",
            "data" => $data
        ];
    } catch (PDOException $e) {
        return [
            "status" => "error",
            "message" => $e->getMessage(),
            "data" => null
        ];
    }
}
public function getUser($userId) {
    try {
        // Query to fetch a specific user by user ID
        $query = "SELECT * FROM user_tbl WHERE id = ? AND isdeleted = 0";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$userId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return [
                "status" => "success",
                "message" => "User data retrieved successfully",
                "data" => $data
            ];
        } else {
            return [
                "status" => "error",
                "message" => "User not found",
                "data" => null
            ];
        }
    } catch (PDOException $e) {
        return [
            "status" => "error",
            "message" => $e->getMessage(),
            "data" => null
        ];
    }
}

    
    
    public function getAccount($id){
        $condition = "isdeleted = 0";
        if($id != null){
            $condition .= " AND id=" . $id; 
        }

        $result = $this->getDataByTable('accounts_tbl', $condition, $this->pdo);

        if($result['code'] == 200){
            return $this->generateResponse($result['data'], "success", "Successfully retrieved records.", $result['code']);
        }
        return $this->generateResponse(null, "failed", $result['errmsg'], $result['code']);
    }

    public function getPayments($userId = null){
        $condition = "";
        if ($userId) {
            $condition = "WHERE p.user_id = " . intval($userId);
        }
    
        $sql = "SELECT p.id, u.fname, u.lname, pk.package_name, p.payment_amount, p.payment_date
                FROM payment_tbl p
                JOIN user_tbl u ON p.user_id = u.id
                JOIN package_tbl pk ON p.package_id = pk.id
                $condition";
    
        $result = $this->getDataBySQL($sql, $this->pdo);
    
        if ($result['code'] == 200) {
            return $this->generateResponse($result['data'], "success", "Successfully retrieved payment history.", $result['code']);
        }
        return $this->generateResponse(null, "failed", $result['errmsg'], $result['code']);
    }
    

    
    

    public function getUnpaidUsers() {
        try {
            $sql = "
                SELECT 
                    u.id AS user_id, 
                    u.fname, 
                    u.lname, 
                    u.email
                FROM 
                    user_tbl u
                LEFT JOIN 
                    payment_tbl p ON u.id = p.user_id
                WHERE 
                    p.id IS NULL AND u.isdeleted = 0
            ";
    
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Debugging: Log the results
            error_log(print_r($data, true));
    
            if ($data) {
                return $this->generateResponse(
                    $data,
                    "success",
                    "Users who have not paid retrieved successfully.",
                    200
                );
            }
    
            return $this->generateResponse(null, "failed", "No unpaid users found.", 404);
        } catch (Exception $e) {
            return $this->generateResponse(null, "failed", $e->getMessage(), 500);
        }
    }
    
    
    public function getComments() {
        try {
            // Query to fetch all comments from the comments_tbl
            $query = "SELECT id, fname, lname, email, package_name, comment, rating, created_at FROM comments_tbl";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            return [
                "status" => "success",
                "message" => "Comments retrieved successfully.",
                "data" => $data
            ];
        } catch (PDOException $e) {
            return [
                "status" => "error",
                "message" => $e->getMessage(),
                "data" => null
            ];
        }
    }

    
}
?>