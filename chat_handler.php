<?php
// chat_handler.php - Place this in the root directory
require_once 'db.php';

// Clear any output buffers that might have been started
if (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/json');

// Error logging function
function logError($message, $data = null) {
    error_log("[CHAT_HANDLER] $message " . ($data ? json_encode($data) : ''));
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;
$user_role = $_SESSION['role'] ?? '';

logError("Action: $action, User ID: $user_id, Role: $user_role");

if (!$user_id || !$user_role) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized - No session found']);
    exit;
}

$user_type = ($user_role === 'teacher') ? 'teacher' : 'student';

try {
    switch ($action) {
        case 'get_contacts':
            getContacts($conn, $user_id, $user_type);
            break;
        
        case 'get_messages':
            $contact_id = intval($_POST['contact_id'] ?? 0);
            $contact_type = $_POST['contact_type'] ?? '';
            getMessages($conn, $user_id, $user_type, $contact_id, $contact_type);
            break;
        
        case 'send_message':
            $receiver_id = intval($_POST['receiver_id'] ?? 0);
            $receiver_type = $_POST['receiver_type'] ?? '';
            $message = trim($_POST['message'] ?? '');
            sendMessage($conn, $user_id, $user_type, $receiver_id, $receiver_type, $message);
            break;
        
        case 'mark_read':
            $contact_id = intval($_POST['contact_id'] ?? 0);
            $contact_type = $_POST['contact_type'] ?? '';
            markAsRead($conn, $user_id, $user_type, $contact_id, $contact_type);
            break;
        
        case 'get_unread_count':
            getUnreadCount($conn, $user_id, $user_type);
            break;
        
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action: ' . $action]);
    }
} catch (Exception $e) {
    logError("Exception: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function getContacts($conn, $user_id, $user_type) {
    logError("Getting contacts for user $user_id ($user_type)");
    $contacts = [];
    
    if ($user_type === 'teacher') {
        // Get ALL students (not just from teacher's classes)
        // This allows teachers to message any student in the system
        $query = "SELECT DISTINCT 
                  s.id, 
                  s.full_name, 
                  s.email, 
                  s.roll_number,
                  s.admission_number,
                  s.year as student_year,
                  s.semester as student_semester,
                  c.section, 
                  c.class_name,
                  c.year as class_year,
                  c.semester as class_semester,
                  d.dept_name,
                  d.dept_code,
                  (SELECT COUNT(*) 
                   FROM chat_messages 
                   WHERE sender_id = s.id 
                   AND sender_type = 'student' 
                   AND receiver_id = ? 
                   AND receiver_type = 'teacher' 
                   AND is_read = 0) as unread_count,
                  (SELECT message 
                   FROM chat_messages 
                   WHERE (sender_id = ? AND sender_type = 'teacher' AND receiver_id = s.id AND receiver_type = 'student')
                   OR (sender_id = s.id AND sender_type = 'student' AND receiver_id = ? AND receiver_type = 'teacher')
                   ORDER BY created_at DESC 
                   LIMIT 1) as last_message,
                  (SELECT created_at 
                   FROM chat_messages 
                   WHERE (sender_id = ? AND sender_type = 'teacher' AND receiver_id = s.id AND receiver_type = 'student')
                   OR (sender_id = s.id AND sender_type = 'student' AND receiver_id = ? AND receiver_type = 'teacher')
                   ORDER BY created_at DESC 
                   LIMIT 1) as last_message_time
                  FROM students s
                  LEFT JOIN classes c ON s.class_id = c.id
                  LEFT JOIN departments d ON s.department_id = d.id
                  WHERE s.is_active = 1
                  ORDER BY last_message_time DESC, s.full_name ASC";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            logError("Query preparation failed: " . $conn->error);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
            return;
        }
        
        $stmt->bind_param("iiiii", $user_id, $user_id, $user_id, $user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        logError("Query executed, fetching results...");
        
        while ($row = $result->fetch_assoc()) {
            // Build subtitle with Roll Number or Admission Number
            $subtitle_parts = [];
            
            // Prioritize Roll Number, fallback to Admission Number
            if (!empty($row['roll_number'])) {
                $subtitle_parts[] = 'Roll: ' . $row['roll_number'];
            } elseif (!empty($row['admission_number'])) {
                $subtitle_parts[] = 'Adm: ' . $row['admission_number'];
            }
            
            if (!empty($row['class_name'])) {
                $subtitle_parts[] = $row['class_name'];
            }
            
            if (!empty($row['section'])) {
                $subtitle_parts[] = 'Sec ' . $row['section'];
            }
            
            $subtitle = !empty($subtitle_parts) ? implode(' • ', $subtitle_parts) : 'Student';
            
            // Build meta information with department
            $meta_parts = [];
            
            if (!empty($row['dept_name'])) {
                $meta_parts[] = !empty($row['dept_code']) ? $row['dept_code'] : $row['dept_name'];
            }
            
            $year = !empty($row['student_year']) ? $row['student_year'] : $row['class_year'];
            if (!empty($year)) {
                $meta_parts[] = 'Year ' . $year;
            }
            
            $sem = !empty($row['student_semester']) ? $row['student_semester'] : $row['class_semester'];
            if (!empty($sem)) {
                $meta_parts[] = 'Sem ' . $sem;
            }
            
            $meta = !empty($meta_parts) ? implode(' • ', $meta_parts) : '';
            
            $contacts[] = [
                'id' => (int)$row['id'],
                'name' => $row['full_name'] ?? 'Unknown',
                'type' => 'student',
                'subtitle' => $subtitle,
                'meta' => $meta,
                'roll_number' => $row['roll_number'] ?? '',
                'admission_number' => $row['admission_number'] ?? '',
                'unread_count' => (int)$row['unread_count'],
                'last_message' => $row['last_message'] ?? 'No messages yet',
                'last_message_time' => !empty($row['last_message_time']) ? date('g:i A', strtotime($row['last_message_time'])) : ''
            ];
        }
        
        $stmt->close();
        logError("Found " . count($contacts) . " students");
        
    } else {
        // Student: Get all teachers with their department information
        $query = "SELECT DISTINCT 
                  u.id, 
                  u.full_name, 
                  u.email, 
                  d.dept_name,
                  d.dept_code,
                  (SELECT COUNT(*) 
                   FROM chat_messages 
                   WHERE sender_id = u.id 
                   AND sender_type = 'teacher' 
                   AND receiver_id = ? 
                   AND receiver_type = 'student' 
                   AND is_read = 0) as unread_count,
                  (SELECT message 
                   FROM chat_messages 
                   WHERE (sender_id = ? AND sender_type = 'student' AND receiver_id = u.id AND receiver_type = 'teacher')
                   OR (sender_id = u.id AND sender_type = 'teacher' AND receiver_id = ? AND receiver_type = 'student')
                   ORDER BY created_at DESC 
                   LIMIT 1) as last_message,
                  (SELECT created_at 
                   FROM chat_messages 
                   WHERE (sender_id = ? AND sender_type = 'student' AND receiver_id = u.id AND receiver_type = 'teacher')
                   OR (sender_id = u.id AND sender_type = 'teacher' AND receiver_id = ? AND receiver_type = 'student')
                   ORDER BY created_at DESC 
                   LIMIT 1) as last_message_time
                  FROM users u
                  LEFT JOIN departments d ON u.department_id = d.id
                  WHERE u.role = 'teacher' 
                  AND u.is_active = 1
                  ORDER BY last_message_time DESC, u.full_name ASC";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            logError("Query preparation failed: " . $conn->error);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
            return;
        }
        
        $stmt->bind_param("iiiii", $user_id, $user_id, $user_id, $user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $subtitle = 'Teacher';
            if (!empty($row['dept_name'])) {
                $subtitle = !empty($row['dept_code']) ? $row['dept_code'] : $row['dept_name'];
            }
            
            $contacts[] = [
                'id' => (int)$row['id'],
                'name' => $row['full_name'] ?? 'Unknown',
                'type' => 'teacher',
                'subtitle' => $subtitle,
                'meta' => $row['dept_name'] ?? '',
                'unread_count' => (int)$row['unread_count'],
                'last_message' => $row['last_message'] ?? 'No messages yet',
                'last_message_time' => !empty($row['last_message_time']) ? date('g:i A', strtotime($row['last_message_time'])) : ''
            ];
        }
        
        $stmt->close();
        logError("Found " . count($contacts) . " teachers");
    }
    
    echo json_encode([
        'success' => true, 
        'contacts' => $contacts, 
        'total' => count($contacts)
    ]);
}

function getMessages($conn, $user_id, $user_type, $contact_id, $contact_type) {
    logError("Getting messages between $user_id ($user_type) and $contact_id ($contact_type)");
    
    $query = "SELECT 
              cm.id,
              cm.sender_id,
              cm.sender_type,
              cm.receiver_id,
              cm.receiver_type,
              cm.message,
              cm.is_read,
              cm.created_at,
              CASE 
                WHEN cm.sender_type = 'teacher' THEN u.full_name
                WHEN cm.sender_type = 'student' THEN s.full_name
              END as sender_name
              FROM chat_messages cm
              LEFT JOIN users u ON (cm.sender_id = u.id AND cm.sender_type = 'teacher')
              LEFT JOIN students s ON (cm.sender_id = s.id AND cm.sender_type = 'student')
              WHERE ((cm.sender_id = ? AND cm.sender_type = ? AND cm.receiver_id = ? AND cm.receiver_type = ?)
              OR (cm.sender_id = ? AND cm.sender_type = ? AND cm.receiver_id = ? AND cm.receiver_type = ?))
              ORDER BY cm.created_at ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isisisis", 
        $user_id, $user_type, $contact_id, $contact_type,
        $contact_id, $contact_type, $user_id, $user_type
    );
    $stmt->execute();
    $result = $stmt->get_result();
    
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $is_sent = ($row['sender_id'] == $user_id && $row['sender_type'] == $user_type);
        $messages[] = [
            'id' => (int)$row['id'],
            'message' => $row['message'] ?? '',
            'is_sent' => $is_sent,
            'is_read' => (int)$row['is_read'],
            'time' => date('g:i A', strtotime($row['created_at'])),
            'date' => date('Y-m-d', strtotime($row['created_at'])),
            'created_at' => $row['created_at']
        ];
    }
    
    $stmt->close();
    logError("Found " . count($messages) . " messages");
    echo json_encode(['success' => true, 'messages' => $messages]);
}

function sendMessage($conn, $sender_id, $sender_type, $receiver_id, $receiver_type, $message) {
    if (empty($message) || !$receiver_id || !$receiver_type) {
        echo json_encode(['success' => false, 'message' => 'Invalid message data']);
        return;
    }
    
    $query = "INSERT INTO chat_messages (sender_id, sender_type, receiver_id, receiver_type, message, is_read, created_at) 
              VALUES (?, ?, ?, ?, ?, 0, NOW())";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isiss", $sender_id, $sender_type, $receiver_id, $receiver_type, $message);
    
    if ($stmt->execute()) {
        logError("Message sent successfully");
        echo json_encode([
            'success' => true,
            'message_id' => $stmt->insert_id,
            'time' => date('g:i A')
        ]);
    } else {
        logError("Failed to send message: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Failed to send message: ' . $stmt->error]);
    }
    
    $stmt->close();
}

function markAsRead($conn, $user_id, $user_type, $contact_id, $contact_type) {
    $query = "UPDATE chat_messages 
              SET is_read = 1 
              WHERE receiver_id = ? 
              AND receiver_type = ? 
              AND sender_id = ? 
              AND sender_type = ? 
              AND is_read = 0";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isis", $user_id, $user_type, $contact_id, $contact_type);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    
    logError("Marked $affected messages as read");
    echo json_encode(['success' => true, 'marked' => $affected]);
}

function getUnreadCount($conn, $user_id, $user_type) {
    $query = "SELECT COUNT(*) as count 
              FROM chat_messages 
              WHERE receiver_id = ? 
              AND receiver_type = ? 
              AND is_read = 0";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $user_id, $user_type);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    echo json_encode(['success' => true, 'count' => (int)$row['count']]);
}
?>