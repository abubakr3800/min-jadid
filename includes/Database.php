<?php
require_once __DIR__ . '/../config/firebase-config.php';

class Database {
    private $dataDir;
    
    public function __construct() {
        $this->dataDir = DATA_DIR;
    }
    
    // Generic method to read JSON file
    public function readJsonFile($filename) {
        $filepath = $this->dataDir . $filename;
        if (!file_exists($filepath)) {
            return [];
        }
        
        $content = file_get_contents($filepath);
        return json_decode($content, true) ?: [];
    }
    
    // Generic method to write JSON file
    public function writeJsonFile($filename, $data) {
        $filepath = $this->dataDir . $filename;
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return file_put_contents($filepath, $json) !== false;
    }
    
    // Get all users
    public function getUsers() {
        return $this->readJsonFile('users.json');
    }
    
    // Get user by ID
    public function getUserById($id) {
        $users = $this->getUsers();
        foreach ($users as $user) {
            if ($user['id'] == $id) {
                return $user;
            }
        }
        return null;
    }
    
    // Get user by email
    public function getUserByEmail($email) {
        $users = $this->getUsers();
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                return $user;
            }
        }
        return null;
    }
    
    // Add new user
    public function addUser($userData) {
        $users = $this->getUsers();
        $userData['id'] = (string)(count($users) + 1);
        $userData['createdAt'] = date('Y-m-d');
        $users[] = $userData;
        
        if ($this->writeJsonFile('users.json', $users)) {
            return $userData;
        }
        return false;
    }
    
    // Update user
    public function updateUser($userData) {
        $users = $this->getUsers();
        $updated = false;
        
        foreach ($users as &$user) {
            if ($user['id'] == $userData['id']) {
                // Update user data
                $user['firstName'] = $userData['firstName'];
                $user['lastName'] = $userData['lastName'];
                $user['bio'] = $userData['bio'] ?? '';
                $user['linkedin'] = $userData['linkedin'] ?? '';
                
                // Update avatar if provided
                if (!empty($userData['avatar'])) {
                    $user['avatar'] = $userData['avatar'];
                }
                
                $updated = true;
                break;
            }
        }
        
        if ($updated) {
            return $this->writeJsonFile('users.json', $users);
        }
        
        return false;
    }
    
    // Get all posts
    public function getPosts($filters = []) {
        $posts = $this->readJsonFile('posts.json');
        
        // Apply filters
        if (!empty($filters['categoryId'])) {
            $posts = array_filter($posts, function($post) use ($filters) {
                return $post['categoryId'] == $filters['categoryId'];
            });
        }
        
        if (!empty($filters['userId'])) {
            $posts = array_filter($posts, function($post) use ($filters) {
                return $post['userId'] == $filters['userId'];
            });
        }
        
        if (!empty($filters['featured'])) {
            $posts = array_filter($posts, function($post) {
                return $post['featured'] == true;
            });
        }
        
        // Sort by creation date (newest first)
        usort($posts, function($a, $b) {
            return strtotime($b['createdAt']) - strtotime($a['createdAt']);
        });
        
        return array_values($posts);
    }
    
    // Get post by ID
    public function getPostById($id) {
        $posts = $this->getPosts();
        foreach ($posts as $post) {
            if ($post['id'] == $id) {
                return $post;
            }
        }
        return null;
    }
    
    // Add new post
    public function addPost($postData) {
        $posts = $this->getPosts();
        $postData['id'] = (string)(count($posts) + 1);
        $postData['createdAt'] = date('c');
        $postData['totalRatings'] = 0;
        $postData['averageRating'] = 0;
        $postData['views'] = 0;
        $postData['likes'] = 0;
        $postData['featured'] = false;
        
        // Get user data
        $user = $this->getUserById($postData['userId']);
        if ($user) {
            $postData['user'] = [
                'id' => $user['id'],
                'firstName' => $user['firstName'],
                'lastName' => $user['lastName'],
                'email' => $user['email'],
                'linkedin' => $user['linkedin']
            ];
        }
        
        $posts[] = $postData;
        
        if ($this->writeJsonFile('posts.json', $posts)) {
            return $postData;
        }
        return false;
    }
    
    // Get all categories
    public function getCategories() {
        return $this->readJsonFile('categories.json');
    }
    
    // Get category by ID
    public function getCategoryById($id) {
        $categories = $this->getCategories();
        foreach ($categories as $category) {
            if ($category['id'] == $id) {
                return $category;
            }
        }
        return null;
    }
    
    // Get ratings for a post
    public function getPostRatings($postId) {
        $ratings = $this->readJsonFile('ratings.json');
        return array_filter($ratings, function($rating) use ($postId) {
            return $rating['postId'] == $postId;
        });
    }
    
    // Add rating
    public function addRating($ratingData) {
        $ratings = $this->readJsonFile('ratings.json');
        $ratingData['id'] = (string)(count($ratings) + 1);
        $ratingData['createdAt'] = date('c');
        $ratings[] = $ratingData;
        
        if ($this->writeJsonFile('ratings.json', $ratings)) {
            // Update post rating statistics
            $this->updatePostRatingStats($ratingData['postId']);
            return $ratingData;
        }
        return false;
    }
    
    // Update post rating statistics
    private function updatePostRatingStats($postId) {
        $ratings = $this->getPostRatings($postId);
        $posts = $this->getPosts();
        
        $totalRatings = count($ratings);
        $averageRating = 0;
        
        if ($totalRatings > 0) {
            $sum = array_sum(array_column($ratings, 'rating'));
            $averageRating = round($sum / $totalRatings, 1);
        }
        
        // Update post
        foreach ($posts as &$post) {
            if ($post['id'] == $postId) {
                $post['totalRatings'] = $totalRatings;
                $post['averageRating'] = $averageRating;
                break;
            }
        }
        
        $this->writeJsonFile('posts.json', $posts);
    }
    
    // Get likes for a post
    public function getPostLikes($postId) {
        $likes = $this->readJsonFile('likes.json');
        return array_filter($likes, function($like) use ($postId) {
            return $like['postId'] == $postId;
        });
    }
    
    // Check if user has liked a post
    public function hasUserLikedPost($userId, $postId) {
        $likes = $this->getPostLikes($postId);
        foreach ($likes as $like) {
            if ($like['userId'] == $userId) {
                return true;
            }
        }
        return false;
    }
    
    // Add like
    public function addLike($likeData) {
        $likes = $this->readJsonFile('likes.json');
        
        // Check if user already liked this post
        if ($this->hasUserLikedPost($likeData['userId'], $likeData['postId'])) {
            return false; // Already liked
        }
        
        $likeData['id'] = (string)(count($likes) + 1);
        $likeData['createdAt'] = date('c');
        $likes[] = $likeData;
        
        if ($this->writeJsonFile('likes.json', $likes)) {
            // Update post like count
            $this->updatePostLikeCount($likeData['postId']);
            return $likeData;
        }
        return false;
    }
    
    // Remove like
    public function removeLike($userId, $postId) {
        $likes = $this->readJsonFile('likes.json');
        $originalCount = count($likes);
        
        $likes = array_filter($likes, function($like) use ($userId, $postId) {
            return !($like['userId'] == $userId && $like['postId'] == $postId);
        });
        
        if (count($likes) < $originalCount) {
            if ($this->writeJsonFile('likes.json', array_values($likes))) {
                // Update post like count
                $this->updatePostLikeCount($postId);
                return true;
            }
        }
        return false;
    }
    
    // Update post like count
    private function updatePostLikeCount($postId) {
        $likes = $this->getPostLikes($postId);
        $posts = $this->getPosts();
        
        $likeCount = count($likes);
        
        // Update post
        foreach ($posts as &$post) {
            if ($post['id'] == $postId) {
                $post['likes'] = $likeCount;
                break;
            }
        }
        
        $this->writeJsonFile('posts.json', $posts);
    }
    
    // Get user rating for a post
    public function getUserRating($userId, $postId) {
        $ratings = $this->getPostRatings($postId);
        foreach ($ratings as $rating) {
            if ($rating['userId'] == $userId) {
                return $rating['rating'];
            }
        }
        return 0;
    }
    
    // Update or add rating
    public function updateRating($ratingData) {
        $ratings = $this->readJsonFile('ratings.json');
        
        // Check if user already rated this post
        $existingRatingIndex = -1;
        foreach ($ratings as $index => $rating) {
            if ($rating['userId'] == $ratingData['userId'] && $rating['postId'] == $ratingData['postId']) {
                $existingRatingIndex = $index;
                break;
            }
        }
        
        if ($existingRatingIndex >= 0) {
            // Update existing rating
            $ratings[$existingRatingIndex]['rating'] = $ratingData['rating'];
            $ratings[$existingRatingIndex]['updatedAt'] = date('c');
        } else {
            // Add new rating
            $ratingData['id'] = (string)(count($ratings) + 1);
            $ratingData['createdAt'] = date('c');
            $ratings[] = $ratingData;
        }
        
        if ($this->writeJsonFile('ratings.json', $ratings)) {
            // Update post rating statistics
            $this->updatePostRatingStats($ratingData['postId']);
            return true;
        }
        return false;
    }
    
    // Update existing post
    public function updatePost($postData) {
        $posts = $this->getPosts();
        
        foreach ($posts as &$post) {
            if ($post['id'] == $postData['id']) {
                // Update fields
                $post['title'] = $postData['title'];
                $post['content'] = $postData['content'];
                $post['categoryId'] = $postData['categoryId'];
                $post['tags'] = $postData['tags'];
                $post['updatedAt'] = date('c');
                
                // Update cover image if provided
                if (isset($postData['coverImage'])) {
                    $post['coverImage'] = $postData['coverImage'];
                }
                
                // Get updated category info
                $category = $this->getCategoryById($post['categoryId']);
                if ($category) {
                    $post['category'] = $category;
                }
                
                if ($this->writeJsonFile('posts.json', $posts)) {
                    return $post;
                }
                return false;
            }
        }
        
        return false;
    }
    
    // Get comments for a post
    public function getPostComments($postId) {
        $comments = $this->readJsonFile('comments.json');
        $postComments = array_filter($comments, function($comment) use ($postId) {
            return $comment['postId'] == $postId;
        });
        
        // Sort by creation date (newest first)
        usort($postComments, function($a, $b) {
            return strtotime($b['createdAt']) - strtotime($a['createdAt']);
        });
        
        // Add user information to each comment
        foreach ($postComments as &$comment) {
            $user = $this->getUserById($comment['userId']);
            if ($user) {
                $comment['user'] = [
                    'id' => $user['id'],
                    'firstName' => $user['firstName'],
                    'lastName' => $user['lastName'],
                    'email' => $user['email']
                ];
            }
        }
        
        return array_values($postComments);
    }
    
    // Add comment
    public function addComment($commentData) {
        $comments = $this->readJsonFile('comments.json');
        
        $commentData['id'] = (string)(count($comments) + 1);
        $commentData['createdAt'] = date('c');
        $comments[] = $commentData;
        
        if ($this->writeJsonFile('comments.json', $comments)) {
            return $commentData;
        }
        return false;
    }
    
    // Get comment by ID
    public function getCommentById($id) {
        $comments = $this->readJsonFile('comments.json');
        foreach ($comments as $comment) {
            if ($comment['id'] == $id) {
                return $comment;
            }
        }
        return null;
    }
    
    // Delete comment (only by comment author or post author)
    public function deleteComment($commentId, $userId) {
        $comments = $this->readJsonFile('comments.json');
        $originalCount = count($comments);
        
        $comments = array_filter($comments, function($comment) use ($commentId, $userId) {
            // Allow deletion if user is comment author or post author
            if ($comment['id'] == $commentId) {
                if ($comment['userId'] == $userId) {
                    return false; // Delete comment
                }
                
                // Check if user is post author
                $post = $this->getPostById($comment['postId']);
                if ($post && $post['userId'] == $userId) {
                    return false; // Delete comment
                }
            }
            return true; // Keep comment
        });
        
        if (count($comments) < $originalCount) {
            if ($this->writeJsonFile('comments.json', array_values($comments))) {
                return true;
            }
        }
        return false;
    }
    
    // Increment post views
    public function incrementViews($postId) {
        $posts = $this->getPosts();
        
        foreach ($posts as &$post) {
            if ($post['id'] == $postId) {
                $post['views'] = ($post['views'] ?? 0) + 1;
                if ($this->writeJsonFile('posts.json', $posts)) {
                    return $post['views'];
                }
                return false;
            }
        }
        
        return false;
    }
    
    // Delete post
    public function deletePost($postId) {
        $posts = $this->getPosts();
        $originalCount = count($posts);
        
        // Remove the post
        $posts = array_filter($posts, function($post) use ($postId) {
            return $post['id'] != $postId;
        });
        
        if (count($posts) < $originalCount) {
            // Also delete related data (comments, likes, ratings)
            $this->deletePostRelatedData($postId);
            
            if ($this->writeJsonFile('posts.json', array_values($posts))) {
                return true;
            }
        }
        
        return false;
    }
    
    // Delete post related data (comments, likes, ratings)
    private function deletePostRelatedData($postId) {
        // Delete comments
        $comments = $this->readJsonFile('comments.json');
        $comments = array_filter($comments, function($comment) use ($postId) {
            return $comment['postId'] != $postId;
        });
        $this->writeJsonFile('comments.json', array_values($comments));
        
        // Delete likes
        $likes = $this->readJsonFile('likes.json');
        $likes = array_filter($likes, function($like) use ($postId) {
            return $like['postId'] != $postId;
        });
        $this->writeJsonFile('likes.json', array_values($likes));
        
        // Delete ratings
        $ratings = $this->readJsonFile('ratings.json');
        $ratings = array_filter($ratings, function($rating) use ($postId) {
            return $rating['postId'] != $postId;
        });
        $this->writeJsonFile('ratings.json', array_values($ratings));
    }
    
    // Get user by Firebase UID
    public function getUserByFirebaseUid($firebaseUid) {
        $users = $this->getUsers();
        foreach ($users as $user) {
            if (isset($user['firebaseUid']) && $user['firebaseUid'] === $firebaseUid) {
                return $user;
            }
        }
        return null;
    }
    
    // Create new user (for social auth)
    public function createUser($userData) {
        $users = $this->getUsers();
        $userData['id'] = (string)(count($users) + 1);
        $userData['createdAt'] = date('Y-m-d');
        $users[] = $userData;
        
        if ($this->writeJsonFile('users.json', $users)) {
            return $userData['id'];
        }
        return false;
    }
    
    // Update user by ID (for social auth)
    public function updateUserById($userId, $updateData) {
        $users = $this->getUsers();
        $updated = false;
        
        foreach ($users as &$user) {
            if ($user['id'] == $userId) {
                // Update user data
                foreach ($updateData as $key => $value) {
                    $user[$key] = $value;
                }
                $updated = true;
                break;
            }
        }
        
        if ($updated) {
            return $this->writeJsonFile('users.json', $users);
        }
        
        return false;
    }
    
    // Check if user is admin
    public function isAdmin($email) {
        $users = $this->getUsers();
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                return isset($user['isAdmin']) && $user['isAdmin'] === true;
            }
        }
        return false;
    }
    
    // Make user admin
    public function makeAdmin($email) {
        $users = $this->getUsers();
        $updated = false;
        
        foreach ($users as &$user) {
            if ($user['email'] === $email) {
                $user['isAdmin'] = true;
                $user['adminRole'] = 'super_admin';
                $user['adminPermissions'] = [
                    'delete_any_post' => true,
                    'view_all_users' => true,
                    'manage_users' => true,
                    'manage_categories' => true,
                    'view_analytics' => true
                ];
                $updated = true;
                break;
            }
        }
        
        if ($updated) {
            return $this->writeJsonFile('users.json', $users);
        }
        
        return false;
    }
    
    // Delete any post (admin power)
    public function deleteAnyPost($postId, $adminEmail) {
        // Verify admin status
        if (!$this->isAdmin($adminEmail)) {
            return false;
        }
        
        $posts = $this->getPosts();
        $originalCount = count($posts);
        
        // Remove the post
        $posts = array_filter($posts, function($post) use ($postId) {
            return $post['id'] != $postId;
        });
        
        if (count($posts) < $originalCount) {
            // Also delete related data (comments, likes, ratings)
            $this->deletePostRelatedData($postId);
            
            if ($this->writeJsonFile('posts.json', array_values($posts))) {
                return true;
            }
        }
        
        return false;
    }
    
    // Get all users for admin view
    public function getAllUsersForAdmin() {
        $users = $this->getUsers();
        
        // Add additional info for each user
        foreach ($users as &$user) {
            // Count user's posts
            $userPosts = $this->getPosts(['userId' => $user['id']]);
            $user['postsCount'] = count($userPosts);
            
            // Get user's total likes received
            $totalLikes = 0;
            foreach ($userPosts as $post) {
                $totalLikes += $post['likes'] ?? 0;
            }
            $user['totalLikesReceived'] = $totalLikes;
            
            // Get user's total views
            $totalViews = 0;
            foreach ($userPosts as $post) {
                $totalViews += $post['views'] ?? 0;
            }
            $user['totalViews'] = $totalViews;
            
            // Format creation date
            $user['formattedCreatedAt'] = date('Y-m-d H:i', strtotime($user['createdAt']));
            
            // Hide sensitive information
            unset($user['password']);
            unset($user['firebaseUid']);
        }
        
        return $users;
    }
    
    // Get admin dashboard statistics
    public function getAdminStats() {
        $users = $this->getUsers();
        $posts = $this->getPosts();
        $comments = $this->readJsonFile('comments.json');
        $likes = $this->readJsonFile('likes.json');
        
        $stats = [
            'totalUsers' => count($users),
            'totalPosts' => count($posts),
            'totalComments' => count($comments),
            'totalLikes' => count($likes),
            'adminUsers' => 0,
            'activeUsers' => 0,
            'topAuthors' => [],
            'recentActivity' => []
        ];
        
        // Count admin users
        foreach ($users as $user) {
            if (isset($user['isAdmin']) && $user['isAdmin']) {
                $stats['adminUsers']++;
            }
        }
        
        // Count active users (users with posts)
        foreach ($users as $user) {
            $userPosts = $this->getPosts(['userId' => $user['id']]);
            if (count($userPosts) > 0) {
                $stats['activeUsers']++;
            }
        }
        
        // Get top authors
        $authorStats = [];
        foreach ($users as $user) {
            $userPosts = $this->getPosts(['userId' => $user['id']]);
            if (count($userPosts) > 0) {
                $totalLikes = 0;
                $totalViews = 0;
                foreach ($userPosts as $post) {
                    $totalLikes += $post['likes'] ?? 0;
                    $totalViews += $post['views'] ?? 0;
                }
                
                $authorStats[] = [
                    'id' => $user['id'],
                    'name' => $user['firstName'] . ' ' . $user['lastName'],
                    'email' => $user['email'],
                    'postsCount' => count($userPosts),
                    'totalLikes' => $totalLikes,
                    'totalViews' => $totalViews
                ];
            }
        }
        
        // Sort by posts count
        usort($authorStats, function($a, $b) {
            return $b['postsCount'] - $a['postsCount'];
        });
        
        $stats['topAuthors'] = array_slice($authorStats, 0, 5);
        
        // Get recent activity (recent posts)
        usort($posts, function($a, $b) {
            return strtotime($b['createdAt']) - strtotime($a['createdAt']);
        });
        
        $stats['recentActivity'] = array_slice($posts, 0, 10);
        
        return $stats;
    }
    
    // Delete a user and all their data
    public function deleteUserAndData($userId) {
        // Delete user's posts
        $posts = $this->getPosts(['userId' => $userId]);
        foreach ($posts as $post) {
            $this->deletePost($post['id']);
        }

        // Delete user's comments
        $comments = $this->readJsonFile('comments.json');
        $comments = array_filter($comments, function($comment) use ($userId) {
            return $comment['userId'] != $userId;
        });
        $this->writeJsonFile('comments.json', array_values($comments));

        // Delete user's likes
        $likes = $this->readJsonFile('likes.json');
        $likes = array_filter($likes, function($like) use ($userId) {
            return $like['userId'] != $userId;
        });
        $this->writeJsonFile('likes.json', array_values($likes));

        // Delete user's ratings
        $ratings = $this->readJsonFile('ratings.json');
        $ratings = array_filter($ratings, function($rating) use ($userId) {
            return $rating['userId'] != $userId;
        });
        $this->writeJsonFile('ratings.json', array_values($ratings));

        // Delete user from users.json
        $users = $this->getUsers();
        $users = array_filter($users, function($user) use ($userId) {
            return $user['id'] != $userId;
        });
        $this->writeJsonFile('users.json', array_values($users));

        return true;
    }

    // Add a notification for a user
    public function addNotification($userId, $message, $link = '', $type = 'info') {
        $notifications = $this->readJsonFile('notifications.json');
        $notifications[] = [
            'id' => uniqid('notif_'),
            'userId' => $userId,
            'message' => $message,
            'link' => $link,
            'type' => $type,
            'read' => false,
            'createdAt' => date('c')
        ];
        $this->writeJsonFile('notifications.json', $notifications);
    }

    // Get notifications for a user
    public function getUserNotifications($userId) {
        $notifications = $this->readJsonFile('notifications.json');
        return array_values(array_filter($notifications, function($n) use ($userId) {
            return $n['userId'] == $userId;
        }));
    }

    // Mark a notification as read
    public function markNotificationRead($notifId, $userId) {
        $notifications = $this->readJsonFile('notifications.json');
        foreach ($notifications as &$notif) {
            if ($notif['id'] === $notifId && $notif['userId'] == $userId) {
                $notif['read'] = true;
            }
        }
        $this->writeJsonFile('notifications.json', $notifications);
    }
}
?> 