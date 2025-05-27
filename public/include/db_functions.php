<?php
// Include database configuration
require_once 'config.php';

// Function to sanitize input data
function sanitize($conn, $data) {
    return mysqli_real_escape_string($conn, trim($data));
}

// Function to log file access
function logFileAccess($conn, $userId, $documentId, $accessTypeId) {
    $userId = (int)$userId;
    $documentId = (int)$documentId;
    $accessTypeId = (int)$accessTypeId;
    
    $sql = "INSERT INTO fileaccesslog (UserID, DocumentID, AccessType) VALUES ($userId, $documentId, $accessTypeId)";
    return mysqli_query($conn, $sql);
}

// Function to get user by email
function getUserByEmail($conn, $email) {
    $email = sanitize($conn, $email);
    $sql = "SELECT * FROM user WHERE EmailAddress = '$email'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

// Function to get user by ID
function getUserById($conn, $userId) {
    $userId = (int)$userId;
    $sql = "SELECT * FROM user WHERE UserID = $userId";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

// Function to get all users
function getAllUsers($conn) {
    $sql = "SELECT u.*, a.LevelName FROM user u 
            JOIN accesslevel a ON u.AccessLevel = a.AccessLevelID 
            ORDER BY u.UserID";
    $result = mysqli_query($conn, $sql);
    
    $users = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
    }
    
    return $users;
}

// Function to get document by ID
function getDocumentById($conn, $documentId) {
    $documentId = (int)$documentId;
    $sql = "SELECT d.*, u.FirstName, u.LastName, c.CategoryName, a.LevelName 
            FROM document d 
            JOIN user u ON d.UserID = u.UserID 
            LEFT JOIN category c ON d.CategoryID = c.CategoryID 
            JOIN accesslevel a ON d.AccessLevel = a.AccessLevelID 
            WHERE d.DocumentID = $documentId";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

// Function to get documents by user ID
function getDocumentsByUserId($conn, $userId) {
    $userId = (int)$userId;
    $sql = "SELECT d.*, c.CategoryName 
            FROM document d 
            LEFT JOIN category c ON d.CategoryID = c.CategoryID 
            WHERE d.UserID = $userId AND d.IsDeleted = 0 
            ORDER BY d.UploadDate DESC";
    $result = mysqli_query($conn, $sql);
    
    $documents = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $documents[] = $row;
        }
    }
    
    return $documents;
}

// Function to get trashed documents by user ID (only show user-deleted documents, not admin-flagged)
function getTrashedDocuments($conn, $userId) {
    $userId = (int)$userId;
    $sql = "SELECT d.*, c.CategoryName 
            FROM document d 
            LEFT JOIN category c ON d.CategoryID = c.CategoryID 
            WHERE d.UserID = $userId AND d.IsDeleted = 1 
            AND (d.FlagReason = 'Deleted by user' OR d.FlagReason IS NULL)
            ORDER BY d.UploadDate DESC";
    
    $result = mysqli_query($conn, $sql);
    
    $documents = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $documents[] = $row;
        }
    }
    
    return $documents;
}

// Function to get all documents (for admin)
function getAllDocuments($conn) {
    $sql = "SELECT d.*, u.FirstName, u.LastName, c.CategoryName, a.LevelName 
            FROM document d 
            JOIN user u ON d.UserID = u.UserID 
            LEFT JOIN category c ON d.CategoryID = c.CategoryID 
            JOIN accesslevel a ON d.AccessLevel = a.AccessLevelID 
            ORDER BY d.UploadDate DESC";
    $result = mysqli_query($conn, $sql);
    
    $documents = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $documents[] = $row;
        }
    }
    
    return $documents;
}

// Function to get flagged documents (admin-flagged only, not user-deleted)
function getFlaggedDocuments($conn) {
    $sql = "SELECT d.*, u.FirstName, u.LastName, c.CategoryName, a.LevelName 
            FROM document d 
            JOIN user u ON d.UserID = u.UserID 
            LEFT JOIN category c ON d.CategoryID = c.CategoryID 
            JOIN accesslevel a ON d.AccessLevel = a.AccessLevelID 
            WHERE d.IsDeleted = 1 
            AND d.FlagReason IS NOT NULL 
            AND d.FlagReason != 'Deleted by user'
            ORDER BY d.UploadDate DESC";
    $result = mysqli_query($conn, $sql);
    
    $documents = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $documents[] = $row;
        }
    }
    
    return $documents;
}

// Function to get all access levels
function getAllAccessLevels($conn) {
    $sql = "SELECT * FROM accesslevel ORDER BY AccessLevelID";
    $result = mysqli_query($conn, $sql);
    
    $levels = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $levels[] = $row;
        }
    }
    
    return $levels;
}

// Function to get all categories
function getAllCategories($conn) {
    $sql = "SELECT * FROM category ORDER BY CategoryName";
    $result = mysqli_query($conn, $sql);
    
    $categories = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row;
        }
    }
    
    return $categories;
}

// Function to check if user has access to document
function hasDocumentAccess($conn, $userId, $documentId) {
    $userId = (int)$userId;
    $documentId = (int)$documentId;
    
    // Get user information
    $user = getUserById($conn, $userId);
    if (!$user) {
        return false;
    }
    
    // Get document information
    $document = getDocumentById($conn, $documentId);
    if (!$document) {
        return false;
    }
    
    // Admin has access to all documents
    if ($user['UserRole'] == 'admin') {
        return true;
    }
    
    // User has access to their own documents
    if ($document['UserID'] == $userId) {
        return true;
    }
    
    // User has access if their access level is higher or equal to document's access level
    if ($user['AccessLevel'] >= $document['AccessLevel']) {
        return true;
    }
    
    return false;
}

// Function to count documents by type
function countDocumentsByType($conn, $userId = null) {
    $whereClause = "WHERE IsDeleted = 0";
    if ($userId !== null) {
        $userId = (int)$userId;
        $whereClause .= " AND UserID = $userId";
    }
    
    $counts = [
        'total' => 0,
        'pdf' => 0,
        'jpg' => 0,
        'png' => 0
    ];
    
    // Total count
    $sql = "SELECT COUNT(*) as count FROM document $whereClause";
    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $counts['total'] = $row['count'];
    }
    
    // PDF count
    $sql = "SELECT COUNT(*) as count FROM document $whereClause AND FileType = 'pdf'";
    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $counts['pdf'] = $row['count'];
    }
    
    // JPG count
    $sql = "SELECT COUNT(*) as count FROM document $whereClause AND FileType = 'jpg'";
    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $counts['jpg'] = $row['count'];
    }
    
    // PNG count
    $sql = "SELECT COUNT(*) as count FROM document $whereClause AND FileType = 'png'";
    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $counts['png'] = $row['count'];
    }
    
    return $counts;
}

// Function to count users
function countUsers($conn) {
    $sql = "SELECT COUNT(*) as count FROM user";
    $result = mysqli_query($conn, $sql);
    
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row['count'];
    }
    
    return 0;
}

// Function to count flagged documents
function countFlaggedDocuments($conn) {
    $sql = "SELECT COUNT(*) as count FROM document WHERE IsDeleted = 1 AND FlagReason IS NOT NULL AND FlagReason != 'Deleted by user'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row['count'];
    }
    
    return 0;
}

// Function to add a new user
function addUser($conn, $userData) {
    $username = sanitize($conn, $userData['username']);
    $password = password_hash($userData['password'], PASSWORD_DEFAULT);
    $firstName = sanitize($conn, $userData['firstName']);
    $middleName = isset($userData['middleName']) ? sanitize($conn, $userData['middleName']) : '';
    $lastName = sanitize($conn, $userData['lastName']);
    $extension = isset($userData['extension']) ? sanitize($conn, $userData['extension']) : '';
    $email = sanitize($conn, $userData['email']);
    $userRole = sanitize($conn, $userData['userRole']);
    $accessLevel = (int)$userData['accessLevel'];
    
    $sql = "INSERT INTO user (Username, Password, FirstName, MiddleName, LastName, Extension, EmailAddress, UserRole, AccessLevel) 
            VALUES ('$username', '$password', '$firstName', '$middleName', '$lastName', '$extension', '$email', '$userRole', $accessLevel)";
    
    if (mysqli_query($conn, $sql)) {
        return mysqli_insert_id($conn);
    }
    
    return false;
}

// Function to update user
function updateUser($conn, $userId, $userData) {
    $userId = (int)$userId;
    $firstName = sanitize($conn, $userData['firstName']);
    $middleName = isset($userData['middleName']) ? sanitize($conn, $userData['middleName']) : '';
    $lastName = sanitize($conn, $userData['lastName']);
    $extension = isset($userData['extension']) ? sanitize($conn, $userData['extension']) : '';
    $email = sanitize($conn, $userData['email']);
    $userRole = sanitize($conn, $userData['userRole']);
    $accessLevel = (int)$userData['accessLevel'];
    
    $sql = "UPDATE user SET 
            FirstName = '$firstName', 
            MiddleName = '$middleName', 
            LastName = '$lastName', 
            Extension = '$extension', 
            EmailAddress = '$email', 
            UserRole = '$userRole', 
            AccessLevel = $accessLevel 
            WHERE UserID = $userId";
    
    return mysqli_query($conn, $sql);
}

// Function to update user password
function updateUserPassword($conn, $userId, $newPassword) {
    $userId = (int)$userId;
    $password = password_hash($newPassword, PASSWORD_DEFAULT);
    
    $sql = "UPDATE user SET Password = '$password' WHERE UserID = $userId";
    
    return mysqli_query($conn, $sql);
}

// Function to add a new document
function addDocument($conn, $documentData, $filePath) {
    $title = sanitize($conn, $documentData['title']);
    $fileType = sanitize($conn, $documentData['fileType']);
    $fileTypeDescription = isset($documentData['description']) ? sanitize($conn, $documentData['description']) : '';
    $userId = (int)$documentData['userId'];
    $fileLocation = sanitize($conn, $filePath);
    $accessLevel = (int)$documentData['accessLevel'];
    
    // Handle categoryId properly - if empty, set to NULL in the query
    if (empty($documentData['categoryId'])) {
        $sql = "INSERT INTO document (Title, FileType, FileTypeDescription, CategoryID, UserID, FileLocation, AccessLevel, IsDeleted) 
                VALUES ('$title', '$fileType', '$fileTypeDescription', NULL, $userId, '$fileLocation', $accessLevel, 0)";
    } else {
        $categoryId = (int)$documentData['categoryId'];
        $sql = "INSERT INTO document (Title, FileType, FileTypeDescription, CategoryID, UserID, FileLocation, AccessLevel, IsDeleted) 
                VALUES ('$title', '$fileType', '$fileTypeDescription', $categoryId, $userId, '$fileLocation', $accessLevel, 0)";
    }
    
    if (mysqli_query($conn, $sql)) {
        return mysqli_insert_id($conn);
    }
    
    return false;
}

// Function to flag a document (move to trash)
function flagDocument($conn, $documentId, $reason) {
    $documentId = (int)$documentId;
    $reason = sanitize($conn, $reason);
    $currentDate = date('Y-m-d H:i:s');
    
    $sql = "UPDATE document SET IsDeleted = 1, FlagReason = '$reason', DeletedDate = '$currentDate' WHERE DocumentID = $documentId";
    
    return mysqli_query($conn, $sql);
}

// Function to restore a document from trash
function restoreDocument($conn, $documentId) {
    $documentId = (int)$documentId;
    
    $sql = "UPDATE document SET IsDeleted = 0, FlagReason = NULL, DeletedDate = NULL WHERE DocumentID = $documentId";
    
    return mysqli_query($conn, $sql);
}

// Function to permanently delete a document
function permanentlyDeleteDocument($conn, $documentId) {
    $documentId = (int)$documentId;
    
    // First get the document to get the file path
    $document = getDocumentById($conn, $documentId);
    if (!$document) {
        return false;
    }
    
    // Delete the file from the server
    if (file_exists($document['FileLocation'])) {
        unlink($document['FileLocation']);
    }
    
    // IMPORTANT: First delete related log entries to avoid foreign key constraint error
    $sql_delete_logs = "DELETE FROM fileaccesslog WHERE DocumentID = $documentId";
    mysqli_query($conn, $sql_delete_logs);
    
    // Now delete the document record from the database
    $sql = "DELETE FROM document WHERE DocumentID = $documentId";
    
    return mysqli_query($conn, $sql);
}

// Function to empty trash for a specific user
function emptyTrash($conn, $userId) {
    $userId = (int)$userId;
    
    // Get all trashed documents for the user
    $trashedDocuments = getTrashedDocuments($conn, $userId);
    
    // Delete all files from the server
    foreach ($trashedDocuments as $document) {
        if (file_exists($document['FileLocation'])) {
            unlink($document['FileLocation']);
        }
        
        // IMPORTANT: Delete related log entries for each document
        $documentId = (int)$document['DocumentID'];
        $sql_delete_logs = "DELETE FROM fileaccesslog WHERE DocumentID = $documentId";
        mysqli_query($conn, $sql_delete_logs);
    }
    
    // Now delete all trashed documents from the database - only user-deleted ones, not admin-flagged
    $sql = "DELETE FROM document WHERE UserID = $userId AND IsDeleted = 1 
           AND (FlagReason = 'Deleted by user' OR FlagReason IS NULL)";
    
    return mysqli_query($conn, $sql);
}

// Function to get document statistics for admin dashboard
function getDocumentStatistics($conn) {
    $stats = [
        'by_category' => [],
        'by_access_level' => [],
        'by_month' => []
    ];
    
    // Documents by category
    $sql = "SELECT c.CategoryName, COUNT(d.DocumentID) as count 
            FROM document d 
            LEFT JOIN category c ON d.CategoryID = c.CategoryID 
            WHERE d.IsDeleted = 0 
            GROUP BY d.CategoryID";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $categoryName = $row['CategoryName'] ? $row['CategoryName'] : 'Uncategorized';
            $stats['by_category'][$categoryName] = $row['count'];
        }
    }
    
    // Documents by access level
    $sql = "SELECT a.LevelName, COUNT(d.DocumentID) as count 
            FROM document d 
            JOIN accesslevel a ON d.AccessLevel = a.AccessLevelID 
            WHERE d.IsDeleted = 0 
            GROUP BY d.AccessLevel";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $stats['by_access_level'][$row['LevelName']] = $row['count'];
        }
    }
    
    // Documents by month (last 6 months)
    $sql = "SELECT 
                DATE_FORMAT(UploadDate, '%Y-%m') as month,
                SUM(CASE WHEN FileType = 'pdf' THEN 1 ELSE 0 END) as pdf_count,
                SUM(CASE WHEN FileType IN ('jpg', 'png') THEN 1 ELSE 0 END) as image_count
            FROM document 
            WHERE IsDeleted = 0 AND UploadDate >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(UploadDate, '%Y-%m')
            ORDER BY month";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $stats['by_month'][$row['month']] = [
                'pdf' => $row['pdf_count'],
                'image' => $row['image_count']
            ];
        }
    }
    
    return $stats;
}
?>