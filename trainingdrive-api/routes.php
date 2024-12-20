<?php

//import get and files
require_once "./modules/PostController.php";
require_once "./config/database.php";
require_once "./modules/Get.php";
require_once "./modules/Post.php";
require_once "./modules/Patch.php";
require_once "./modules/Archive.php"; 
require_once "./modules/Auth.php";
//require_once "./modules/Crypt.php";

$db = new Connection();
$pdo = $db->connect();

//Class instantiation
$post = new Post($pdo);
$get = new Get($pdo);
$patch = new Patch($pdo);
$archive = new Archive($pdo);
$auth = new Authentication($pdo);


//retrieved and endpoints and split
if(isset($_REQUEST['request'])){
    $request = explode("/", $_REQUEST['request']);
}
else{
    echo "URL does not exist.";
}

//get post put patch delete etc
//Request method - http request methods you will be using

switch($_SERVER['REQUEST_METHOD']){

    case "GET":    
        if ($auth->isAuthorized()) {
            switch ($request[0]) {
    
                case "user":
                    echo json_encode($get->getUser($request[1] ?? null));
                break; 
                case "users":
                    echo json_encode($get->getAllUsers($request[1] ?? null));
                break; 
                    case "log":
                        echo json_encode($get->getLogs($request[1] ?? date("Y-m-d")));
                    break;
// to see accounts
                    case "account":
                        echo json_encode($get->getAccount($request[1] ?? null));
                    break; 
                
                    case "paid":
                        echo json_encode($get->getPayments($request[1] ?? null));
                        break;
                        

                        case "notpaid":
                            echo json_encode($get->getUnpaidUsers($request[1] ?? null));
                        break; 

                        case "commentslist":
                            echo json_encode($get->getComments($request[1] ?? null));
                        break; 

                    default:
                        http_response_code(401);
                        echo "This is invalid endpoint";
                    break;
               
            }
        } else {
            echo json_encode(["status" => "unauthorized", "message" => "Unauthorized access."]);
        }
        break;
    

        case "POST":
            $body = json_decode(file_get_contents("php://input"));
        
            if ($request[0] === "login" || $request[0] === "signup") {
                if ($request[0] === "login") {
                    echo json_encode($auth->login($body));
                } elseif ($request[0] === "signup") {
                    echo json_encode($auth->addAccount($body));
                }
            } elseif ($request[0] === "enroll") {
                // Allow "enroll" without authorization
                echo json_encode($post->postUser($body));
            } elseif ($auth->isAuthorized()) {
                switch ($request[0]) {
                    case "user":
                        echo json_encode($post->postUser($body));
                        break;

                        case "pay":
                            $body = json_decode(file_get_contents("php://input"));
                            echo json_encode($post->makePayment($body));
                            break;
                        
                            case "comment":
                                echo json_encode($post->postComment($body));
                                break;
                
                            default:
                                echo json_encode([
                                    "payload" => null,
                                    "status" => [
                                        "remark" => "failed",
                                        "message" => "Invalid endpoint."
                                    ],
                                    "prepared_by" => "JonZoie",
                                    "date_generated" => date("Y-m-d H:i:s")
                                ]);
                                break;
                        }
                    
                    break;
            } else {
                echo "Unauthorized";
            }
            break;
        
    
        case "DELETE":
            if ($auth->isAuthorized()) {
                switch($request[0]){
                    case "deleteuser":
                        echo json_encode($archive->deleteUser($request[1]));
                        break;
                    case "destroyuser":
                        echo json_encode($archive->destroyUser($request[1]));
                        break;
                    case "deleteaccount":
                        echo json_encode($archive->deleteAccount($request[1]));
                        break;
                    case "destroyaccount":
                        echo json_encode($archive->destroyAccount($request[1]));
                        break;
                    default:
                        http_response_code(401);
                        echo "This is an invalid endpoint";
                        break;
                }
            } else {
                echo "Unauthorized";
            }
            break;
    
            case "PATCH":
                $body = json_decode(file_get_contents("php://input"), true);
                if ($auth->isAuthorized()) {
                    if (!isset($request[1]) || !is_numeric($request[1])) {
                        http_response_code(400);
                        echo json_encode(["error" => "Invalid or missing ID in the endpoint"]);
                        break;
                    }
            
                    $id = (int)$request[1];
            
                    if ($body === null) {
                        http_response_code(400);
                        echo json_encode(["error" => "Invalid JSON body"]);
                        break;
                    }
            
                    switch ($request[0]) {
                        case "user":
                            $result = $patch->patchUser($body, $id);
                            if (isset($result["errmsg"])) {
                                http_response_code(400);
                                echo json_encode(["error" => $result["errmsg"]]);
                            } else {
                                http_response_code(200);
                                echo json_encode($result);
                            }
                            break;
            
                        case "account":
                            $result = $patch->patchAccount($body, $id);
                            if (isset($result["errmsg"])) {
                                http_response_code(400);
                                echo json_encode(["error" => $result["errmsg"]]);
                            } else {
                                http_response_code(200);
                                echo json_encode($result);
                            }
                            break;
            
                        default:
                            http_response_code(404);
                            echo json_encode(["error" => "Invalid endpoint"]);
                            break;
                    }
                } else {
                    http_response_code(401);
                    echo json_encode(["error" => "Unauthorized"]);
                }
                break;
            
    }
    
    ?>
