<?php
class Patch {

    protected $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function patchUser($body, $id){
        $values = [];
        $errmsg = "";
        $code = 0;
    
        foreach($body as $value){
            array_push($values, $value);
        }
    
        array_push($values, $id); // Use $id for the WHERE clause
        
        try {
            $sqlString = "UPDATE user_tbl SET fname=?, lname=?, email=?, no=?, package=? ,isdeleted=0 WHERE id = ?";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute($values);
    
            if ($sql->rowCount() > 0) {
                $code = 200;
                $data = ["message" => "Row updated successfully"];
            } else {
                $code = 404; // No rows affected
                $data = ["error" => "No rows affected"];
            }
    
            return array("data" => $data, "code" => $code);
        } catch (\PDOException $e) {
            $errmsg = $e->getMessage();
            $code = 400;
        }
    
        return array("errmsg" => $errmsg, "code" => $code);
    }
    

    // Archive user by setting isdeleted = 1
    public function archiveUser($id) {
        return $this->executeArchive("user_tbl", $id, "no");
    }

    // Patch account to update fields or restore by setting isdeleted = 0
    public function patchAccount($body, $id) {
        return $this->executePatch(
            "accounts_tbl",
            ['username', 'password', 'isdeleted'],
            $body,
            $id,
            "id"
        );
    }

    // Archive account by setting isdeleted = 1
    public function archiveAccount($id) {
        return $this->executeArchive("accounts_tbl", $id, "id");
    }

    // Generic method to handle patch requests
    private function executePatch($table, $columns, $body, $id, $idColumn) {
        $fields = [];
        $values = [];
        foreach ($columns as $column) {
            if (isset($body[$column])) {
                $fields[] = "$column = ?";
                $values[] = $body[$column];
            }
        }

        if (empty($fields)) {
            return ["data" => ["error" => "No fields to update"], "code" => 400];
        }

        $values[] = $id;
        $sqlString = "UPDATE $table SET " . implode(', ', $fields) . " WHERE $idColumn = ?";

        try {
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute($values);

            if ($sql->rowCount() > 0) {
                return ["data" => ["message" => "Updated successfully"], "code" => 200];
            } else {
                return ["data" => ["error" => "No rows affected"], "code" => 404];
            }
        } catch (\PDOException $e) {
            return ["errmsg" => $e->getMessage(), "code" => 400];
        }
    }

    // Generic method to handle archive requests
    private function executeArchive($table, $id, $idColumn) {
        try {
            $sqlString = "UPDATE $table SET isdeleted = 1 WHERE $idColumn = ?";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute([$id]);

            if ($sql->rowCount() > 0) {
                return ["data" => ["message" => "Archived successfully"], "code" => 200];
            } else {
                return ["data" => ["error" => "No rows affected"], "code" => 404];
            }
        } catch (\PDOException $e) {
            return ["errmsg" => $e->getMessage(), "code" => 400];
        }
    }
}
?>
