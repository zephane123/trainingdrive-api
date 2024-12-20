<?php
class PostController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function addCommentAndRecalculateRating($userId, $packageName, $comment, $rating) {
        try {
            // Begin transaction
            $this->pdo->beginTransaction();

            // Insert the new comment and rating
            $sqlInsert = "
                INSERT INTO comment (user_id, package_name, comment, rating)
                VALUES (:user_id, :package_name, :comment, :rating)
            ";
            $stmtInsert = $this->pdo->prepare($sqlInsert);
            $stmtInsert->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmtInsert->bindParam(':package_name', $packageName, PDO::PARAM_STR);
            $stmtInsert->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmtInsert->bindParam(':rating', $rating, PDO::PARAM_INT);
            $stmtInsert->execute();

            // Recalculate the total and average ratings
            $sqlRecalculate = "
                SELECT 
                    COUNT(*) AS total_ratings, 
                    AVG(rating) AS average_rating
                FROM 
                    comment
                WHERE 
                    package_name = :package_name
            ";
            $stmtRecalculate = $this->pdo->prepare($sqlRecalculate);
            $stmtRecalculate->bindParam(':package_name', $packageName, PDO::PARAM_STR);
            $stmtRecalculate->execute();
            $result = $stmtRecalculate->fetch(PDO::FETCH_ASSOC);

            // Commit the transaction
            $this->pdo->commit();

            // Return success response with updated ratings
            return $this->generateResponse(
                [
                    "new_comment" => [
                        "user_id" => $userId,
                        "package_name" => $packageName,
                        "comment" => $comment,
                        "rating" => $rating,
                    ],
                    "summary" => [
                        "total_ratings" => $result['total_ratings'],
                        "average_rating" => round($result['average_rating'], 2),
                    ]
                ],
                "success",
                "Comment added and ratings updated successfully.",
                200
            );

        } catch (Exception $e) {
            // Rollback the transaction on error
            $this->pdo->rollBack();
            return $this->generateResponse(null, "failed", $e->getMessage(), 500);
        }
    }

    private function generateResponse($data, $status, $message, $code) {
        return [
            "status" => $status,
            "message" => $message,
            "code" => $code,
            "data" => $data
        ];
    }
}
?>
