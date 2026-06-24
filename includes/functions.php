<?php
require_once 'db.php';

// =====================
// Session & Auth Helpers
// =====================

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

function isSuperAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'superadmin';
}

function redirect($url) {
    header('Location: ' . $url);
    exit();
}

// =====================
// Input Sanitization
// =====================

function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// =====================
// File Upload
// =====================

function uploadFile($file) {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return null;
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }

    if ($file['size'] > $maxSize) {
        return false;
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . strtolower($ext);
    $uploadPath = UPLOAD_DIR . $filename;

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return $filename;
    }

    return false;
}

// =====================
// Posts
// =====================

function getPosts($limit = null, $publishedOnly = true) {
    $db = Database::getInstance();
    $sql = "SELECT * FROM posts";
    if ($publishedOnly) {
        $sql .= " WHERE is_published = 1";
    }
    $sql .= " ORDER BY created_at DESC";
    if ($limit) {
        $sql .= " LIMIT " . intval($limit);
    }
    return $db->query($sql);
}

function getPost($id) {
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function createPost($data, $image = null) {
    $db = Database::getInstance();
    $sql = "INSERT INTO posts (title, content, category, author, is_published, image, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("ssssis", $data['title'], $data['content'], $data['category'], $data['author'], $data['is_published'], $image);
    return $stmt->execute();
}

function updatePost($id, $data, $image = null) {
    $db = Database::getInstance();
    if ($image) {
        $sql = "UPDATE posts SET title = ?, content = ?, category = ?, author = ?, is_published = ?, image = ? WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ssssisi", $data['title'], $data['content'], $data['category'], $data['author'], $data['is_published'], $image, $id);
    } else {
        $sql = "UPDATE posts SET title = ?, content = ?, category = ?, author = ?, is_published = ? WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ssssii", $data['title'], $data['content'], $data['category'], $data['author'], $data['is_published'], $id);
    }
    return $stmt->execute();
}

function deletePost($id) {
    $db = Database::getInstance();
    $stmt = $db->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// =====================
// Events
// =====================

function getEvents($upcomingOnly = null) {
    $db = Database::getInstance();
    $sql = "SELECT *, (date >= CURDATE()) AS is_upcoming FROM events";
    if ($upcomingOnly === true) {
        $sql .= " WHERE date >= CURDATE()";
    } elseif ($upcomingOnly === false) {
        $sql .= " WHERE date < CURDATE()";
    }
    $sql .= " ORDER BY date DESC";
    return $db->query($sql);
}

function getEvent($id) {
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function createEvent($data, $image = null) {
    $db = Database::getInstance();
    $sql = "INSERT INTO events (title, description, date, time, venue, participants, image, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("sssssis", $data['title'], $data['description'], $data['date'], $data['time'], $data['venue'], $data['participants'], $image);
    return $stmt->execute();
}

function updateEvent($id, $data, $image = null) {
    $db = Database::getInstance();
    if ($image) {
        $sql = "UPDATE events SET title = ?, description = ?, date = ?, time = ?, venue = ?, participants = ?, image = ? WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("sssssssi", $data['title'], $data['description'], $data['date'], $data['time'], $data['venue'], $data['participants'], $image, $id);
    } else {
        $sql = "UPDATE events SET title = ?, description = ?, date = ?, time = ?, venue = ?, participants = ? WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ssssssi", $data['title'], $data['description'], $data['date'], $data['time'], $data['venue'], $data['participants'], $id);
    }
    return $stmt->execute();
}

function deleteEvent($id) {
    $db = Database::getInstance();
    $stmt = $db->prepare("DELETE FROM events WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// =====================
// Contact Requests
// =====================

function getRequests() {
    $db = Database::getInstance();
    return $db->query("SELECT * FROM requests ORDER BY created_at DESC");
}

function createRequest($data) {
    $db = Database::getInstance();
    $sql = "INSERT INTO requests (name, email, phone, subject, message, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("sssss", $data['name'], $data['email'], $data['phone'], $data['subject'], $data['message']);
    return $stmt->execute();
}

function updateRequestStatus($id, $status) {
    $db = Database::getInstance();
    $stmt = $db->prepare("UPDATE requests SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    return $stmt->execute();
}

function deleteRequest($id) {
    $db = Database::getInstance();
    $stmt = $db->prepare("DELETE FROM requests WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// =====================
// Dashboard Stats
// =====================

function getStats() {
    $db = Database::getInstance();
    $stats = [];

    $result = $db->query("SELECT COUNT(*) as count FROM posts");
    $stats['total_posts'] = $result->fetch_assoc()['count'];

    $result = $db->query("SELECT COUNT(*) as count FROM events");
    $stats['total_events'] = $result->fetch_assoc()['count'];

    $result = $db->query("SELECT COUNT(*) as count FROM requests");
    $stats['total_requests'] = $result->fetch_assoc()['count'];

    $result = $db->query("SELECT COUNT(*) as count FROM requests WHERE status = 'pending'");
    $stats['pending_requests'] = $result->fetch_assoc()['count'];

    return $stats;
}
