<?php 
// Database connection 
$conn = new mysqli("localhost", "root", "", "roomgenius_db");  

if ($conn->connect_error) {     
    die("Connection failed: " . $conn->connect_error); 
}  

// Check if form is submitted 
if ($_SERVER["REQUEST_METHOD"] == "POST") {     
    // Get form data and sanitize inputs     
    $name = $conn->real_escape_string($_POST['name']);     
    $email = $conn->real_escape_string($_POST['email']);     
    $message = $conn->real_escape_string($_POST['message']);      
    
    // Prepare and bind - ADDING is_read=0 for new messages
    $stmt = $conn->prepare("INSERT INTO messages (name, email, message, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
    $stmt->bind_param("sss", $name, $email, $message); // "sss" means three strings      
    
    // Execute the statement     
    if ($stmt->execute()) {         
        // Redirect back to contact page with success message         
        header("Location: contact.php?success=1");         
        exit(); // Ensure no further code is executed     
    } else {         
        echo "Error: " . $stmt->error;     
    }      
    
    // Close the statement and connection     
    $stmt->close();     
    $conn->close(); 
} 
?>