<?php
session_start();
require_once 'db.php';

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

file_put_contents(__DIR__ . '/logs/nexus.log', date('Y-m-d H:i:s') . " - SSE connection opened\n", FILE_APPEND);

function sendEvent($data) {
    echo "data: " . json_encode($data) . "\n\n";
    ob_flush();
    flush();
}

// Track last update timestamp
$last_update = isset($_SESSION['last_sse_update']) ? $_SESSION['last_sse_update'] : time();
$timeout = 30; // Seconds to keep connection open

while (true) {
    try {
        // Fetch recent likes
        $stmt = $pdo->prepare("SELECT id, likes FROM news_card WHERE updated_at > FROM_UNIXTIME(?)");
        $stmt->execute([$last_update]);
        $updated_likes = $stmt->fetchAll();

        // Fetch recent comments
        $stmt = $pdo->prepare("SELECT c.id AS comment_id, c.news_id, c.content, u.username, c.created_at 
                              FROM comments c 
                              JOIN users u ON c.user_id = u.id 
                              WHERE c.created_at > FROM_UNIXTIME(?)");
        $stmt->execute([$last_update]);
        $new_comments = $stmt->fetchAll();

        // Send updates
        foreach ($updated_likes as $like) {
            sendEvent([
                'type' => 'like',
                'news_id' => $like['id'],
                'likes' => $like['likes']
            ]);
        }

        foreach ($new_comments as $comment) {
            sendEvent([
                'type' => 'comment',
                'news_id' => $comment['news_id'],
                'comment_id' => $comment['comment_id'],
                'comment_count' => getCommentCount($pdo, $comment['news_id']),
                'content' => htmlspecialchars($comment['content']),
                'username' => htmlspecialchars($comment['username']),
                'created_at' => date('M j, Y H:i', strtotime($comment['created_at']))
            ]);
        }

        // Update last timestamp
        $last_update = time();
        $_SESSION['last_sse_update'] = $last_update;

        // Prevent infinite loop
        if (connection_aborted()) {
            file_put_contents(__DIR__ . '/logs/nexus.log', date('Y-m-d H:i:s') . " - SSE connection aborted\n", FILE_APPEND);
            break;
        }

        // Sleep to reduce server load
        sleep(3);

        // Timeout after 30 seconds
        if (time() - $last_update > $timeout) {
            sendEvent(['type' => 'heartbeat']);
            break;
        }
    } catch (PDOException $e) {
        sendEvent(['type' => 'error', 'message' => 'Database error']);
        file_put_contents(__DIR__ . '/logs/nexus.log', date('Y-m-d H:i:s') . " - SSE failed: " . $e->getMessage() . "\n", FILE_APPEND);
        break;
    }
}

function getCommentCount($pdo, $news_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE news_id = ?");
    $stmt->execute([$news_id]);
    return $stmt->fetchColumn();
}
?>