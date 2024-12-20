<?php

include_once "Common.php";
include_once "PostController.php";

class Post extends Common {
    protected $pdo;
    protected $postController;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
        $this->postController = new PostController($pdo);
    }

    public function postStudents() {
        // Code for retrieving data on DB
        return "This is some student records added.";
    }

    public function postClasses() {
        // Code for retrieving data on DB
        return "This is some classes records added.";
    }

    public function postFaculty() {
        // Code for retrieving data on DB
        return "This is some faculty records added.";
    }

    public function postUser($body) {
        if (is_array($body)) {
            $body = (object) $body;
        }
        $result = $this->postData(
            "user_tbl",
            [
                "fname" => $body->fname,
                "lname" => $body->lname,
                "email" => $body->email,
                "no" => $body->no,
                "package" => $body->package
            ],
            $this->pdo
        );
        if ($result['code'] == 200) {
            $this->logger("Congrats", "POST", "Created a new record.");
            return $this->generateResponse(
                $result['data'],
                "success",
                "New record has been successfully created.",
                201
            );
        }
        $this->logger("JonZeph and Zoie", "POST", $result['errmsg']);
        return $this->generateResponse(null, "failed", $result['errmsg'], $result['code']);
    }

    public function makePayment($body) {
        $errmsg = "";
        $code = 0;

        $userId = $body->user_id;
        $packageId = $body->package_id;

        try {
            // Fetch package cost from package_tbl
            $packageQuery = "SELECT cost FROM package_tbl WHERE id = ?";
            $stmt = $this->pdo->prepare($packageQuery);
            $stmt->execute([$packageId]);
            $package = $stmt->fetch();

            if ($package) {
                $paymentAmount = $package['cost'];

                // Insert into payment_tbl
                $paymentQuery = "INSERT INTO payment_tbl (user_id, package_id, payment_amount) VALUES (?, ?, ?)";
                $stmt = $this->pdo->prepare($paymentQuery);
                $stmt->execute([$userId, $packageId, $paymentAmount]);

                $code = 200;
                $data = ["user_id" => $userId, "package_id" => $packageId, "amount" => $paymentAmount];
                return $this->generateResponse($data, "success", "Payment recorded successfully.", $code);
            } else {
                throw new Exception("Package not found");
            }
        } catch (\PDOException $e) {
            $errmsg = $e->getMessage();
            $code = 400;
        } catch (Exception $e) {
            $errmsg = $e->getMessage();
            $code = 404;
        }

        return $this->generateResponse(null, "failed", $errmsg, $code);
    }

    // New Method: postComment
    public function postComment($body) {
        if (is_array($body)) {
            $body = (object) $body;
        }
        
        try {
            $result = $this->postData(
                "comments_tbl",
                [
                    "fname" => $body->fname,
                    "lname" => $body->lname,
                    "email" => $body->email,
                    "package_name" => $body->package_name,
                    "comment" => $body->comment,
                    "rating" => $body->rating
                ],
                $this->pdo
            );
            
            if ($result['code'] == 200) {
                $this->logger("Congrats", "POST", "Added a new comment.");
                return $this->generateResponse(
                    $result['data'],
                    "success",
                    "Comment has been successfully added.",
                    201
                );
            }
            
            throw new Exception($result['errmsg']);
        } catch (\PDOException $e) {
            $this->logger("JonZeph and Zoie", "POST", $e->getMessage());
            return $this->generateResponse(null, "failed", $e->getMessage(), 400);
        } catch (Exception $e) {
            $this->logger("JonZeph and Zoie", "POST", $e->getMessage());
            return $this->generateResponse(null, "failed", $e->getMessage(), 500);
        }
    }
}
