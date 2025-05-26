<?php
// Include database configuration
require_once 'config.php';

// SQL to create accesslevel table
$sql_accesslevel = "CREATE TABLE IF NOT EXISTS accesslevel (
    AccessLevelID INT AUTO_INCREMENT PRIMARY KEY,
    LevelName VARCHAR(50) NOT NULL
)";

// SQL to create accesstype table
$sql_accesstype = "CREATE TABLE IF NOT EXISTS accesstype (
    AccessTypeID INT AUTO_INCREMENT PRIMARY KEY,
    AccessName VARCHAR(50) NOT NULL
)";

// SQL to create category table
$sql_category = "CREATE TABLE IF NOT EXISTS category (
    CategoryID INT AUTO_INCREMENT PRIMARY KEY,
    CategoryName VARCHAR(100) NOT NULL,
    ShortDescription TEXT
)";

// SQL to create user table
$sql_user = "CREATE TABLE IF NOT EXISTS user (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    FirstName VARCHAR(50) NOT NULL,
    MiddleName VARCHAR(50),
    LastName VARCHAR(50) NOT NULL,
    Extension VARCHAR(10),
    EmailAddress VARCHAR(100) NOT NULL UNIQUE,
    UserRole ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    AccessLevel INT NOT NULL,
    FOREIGN KEY (AccessLevel) REFERENCES accesslevel(AccessLevelID)
)";

// SQL to create document table
$sql_document = "CREATE TABLE IF NOT EXISTS document (
    DocumentID INT AUTO_INCREMENT PRIMARY KEY,
    Title VARCHAR(150) NOT NULL,
    FileType VARCHAR(10) NOT NULL,
    FileTypeDescription TEXT,
    UploadDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CategoryID INT,
    UserID INT NOT NULL,
    FileLocation TEXT NOT NULL,
    AccessLevel INT NOT NULL,
    IsDeleted TINYINT(1) NOT NULL DEFAULT 0,
    FlagReason TEXT,
    FOREIGN KEY (CategoryID) REFERENCES category(CategoryID),
    FOREIGN KEY (UserID) REFERENCES user(UserID),
    FOREIGN KEY (AccessLevel) REFERENCES accesslevel(AccessLevelID)
)";

// SQL to create fileaccesslog table
$sql_fileaccesslog = "CREATE TABLE IF NOT EXISTS fileaccesslog (
    LogID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    DocumentID INT NOT NULL,
    AccessType INT NOT NULL,
    Timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES user(UserID),
    FOREIGN KEY (DocumentID) REFERENCES document(DocumentID),
    FOREIGN KEY (AccessType) REFERENCES accesstype(AccessTypeID)
)";

// Execute the SQL statements
$tables = [
    'accesslevel' => $sql_accesslevel,
    'accesstype' => $sql_accesstype,
    'category' => $sql_category,
    'user' => $sql_user,
    'document' => $sql_document,
    'fileaccesslog' => $sql_fileaccesslog
];

$success = true;
foreach ($tables as $table => $sql) {
    if (!mysqli_query($conn, $sql)) {
        echo "ERROR: Could not create $table table. " . mysqli_error($conn) . "<br>";
        $success = false;
    }
}

// Insert default data if tables were created successfully
if ($success) {
    // Insert default access levels
    $access_levels = [
        ['LevelName' => 'Level 1 (Lowest)'],
        ['LevelName' => 'Level 2'],
        ['LevelName' => 'Level 3'],
        ['LevelName' => 'Level 4'],
        ['LevelName' => 'Level 5 (Highest)']
    ];
    
    foreach ($access_levels as $level) {
        $check = mysqli_query($conn, "SELECT * FROM accesslevel WHERE LevelName = '{$level['LevelName']}'");
        if (mysqli_num_rows($check) == 0) {
            mysqli_query($conn, "INSERT INTO accesslevel (LevelName) VALUES ('{$level['LevelName']}')");
        }
    }
    
    // Insert default access types
    $access_types = [
        ['AccessName' => 'View'],
        ['AccessName' => 'Download'],
        ['AccessName' => 'Upload'],
        ['AccessName' => 'Delete'],
        ['AccessName' => 'Flag']
    ];
    
    foreach ($access_types as $type) {
        $check = mysqli_query($conn, "SELECT * FROM accesstype WHERE AccessName = '{$type['AccessName']}'");
        if (mysqli_num_rows($check) == 0) {
            mysqli_query($conn, "INSERT INTO accesstype (AccessName) VALUES ('{$type['AccessName']}')");
        }
    }
    
    // Insert default categories
    $categories = [
        ['CategoryName' => 'Reports', 'ShortDescription' => 'Business and project reports'],
        ['CategoryName' => 'Contracts', 'ShortDescription' => 'Legal agreements and contracts'],
        ['CategoryName' => 'Presentations', 'ShortDescription' => 'Slides and presentation materials'],
        ['CategoryName' => 'Invoices', 'ShortDescription' => 'Financial invoices and receipts'],
        ['CategoryName' => 'Images', 'ShortDescription' => 'Photos and graphic designs']
    ];
    
    foreach ($categories as $category) {
        $check = mysqli_query($conn, "SELECT * FROM category WHERE CategoryName = '{$category['CategoryName']}'");
        if (mysqli_num_rows($check) == 0) {
            mysqli_query($conn, "INSERT INTO category (CategoryName, ShortDescription) VALUES ('{$category['CategoryName']}', '{$category['ShortDescription']}')");
        }
    }
    
    // Create default admin user if no users exist
    $check_users = mysqli_query($conn, "SELECT * FROM user");
    if (mysqli_num_rows($check_users) == 0) {
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        mysqli_query($conn, "INSERT INTO user (Username, Password, FirstName, LastName, EmailAddress, UserRole, AccessLevel) 
                            VALUES ('admin', '$admin_password', 'System', 'Administrator', 'admin@example.com', 'admin', 5)");
    }
    
    echo "Database setup completed successfully!";
} else {
    echo "Database setup encountered errors.";
}

// Close connection
closeConnection($conn);
?>