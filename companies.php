<?php
session_start();

// Check if the company is logged in
if (!isset($_SESSION['company_id'])) {
    header("Location: index.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "roomgenius_db");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Debug: Show session variables
echo "Debug: company_id from session is: " . $_SESSION['company_id'] . "<br>";
echo "Debug: user_id from session is: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "Not set") . "<br>";

// Debug: Let's see what's in the companies table
$debug_sql = "SELECT company_id, user_id, company_name FROM companies";
$debug_result = $conn->query($debug_sql);
echo "<p>Companies in database:</p>";
echo "<table border='1'><tr><th>company_id</th><th>user_id</th><th>company_name</th></tr>";
while ($row = $debug_result->fetch_assoc()) {
    echo "<tr><td>" . $row['company_id'] . "</td><td>" . $row['user_id'] . "</td><td>" . $row['company_name'] . "</td></tr>";
}
echo "</table>";

// Get the company ID from the session
$companyId = $_SESSION['company_id'];

// SQL query to fetch company and user details using company_id
$sql = "SELECT c.company_id, c.company_name, c.company_email, c.company_phone, c.company_address, 
                c.company_website, c.logo, u.name, u.email
        FROM companies c
        JOIN users u ON c.user_id = u.id
        WHERE c.company_id = ?";

// Add error handling for prepare statement
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error . " SQL: " . $sql);
}

$stmt->bind_param("i", $companyId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $company = $result->fetch_assoc();
} else {
    echo "Company not found.";
    exit();
}

// Handle logo upload (improving validation)
if (isset($_POST['submit'])) {
    // File upload logic
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        // Get the file info
        $logoTmpName = $_FILES['logo']['tmp_name'];
        $logoName = $_FILES['logo']['name'];
        $logoExtension = pathinfo($logoName, PATHINFO_EXTENSION);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif']; // Allowed file types
        $maxFileSize = 2 * 1024 * 1024; // 2MB max file size

        // Check if the file type is allowed
        if (in_array(strtolower($logoExtension), $allowedExtensions)) {
            // Check if file size is within the limit
            if ($_FILES['logo']['size'] <= $maxFileSize) {
                // Create a unique filename to avoid overwriting existing files
                $logoNewName = "logo_" . time() . "." . $logoExtension;
                $logoPath = "uploads/logos/" . $logoNewName;

                // Move the file to the 'uploads/logos/' directory
                if (move_uploaded_file($logoTmpName, $logoPath)) {
                    // Update the logo path in the database - FIXED: use company_id instead of user_id
                    $updateSql = "UPDATE companies SET logo = ? WHERE company_id = ?";
                    $updateStmt = $conn->prepare($updateSql);
                    $updateStmt->bind_param("si", $logoPath, $companyId);

                    if ($updateStmt->execute()) {
                        // Update the local company array with the new logo path
                        $company['logo'] = $logoPath;
                        echo "Company logo updated successfully!";
                    } else {
                        echo "Error updating logo: " . $conn->error;
                    }
                } else {
                    echo "Error uploading logo.";
                }
            } else {
                echo "File size exceeds the 2MB limit.";
            }
        } else {
            echo "Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.";
        }
    }
}

// Once the debug is done, you can remove the debug code above
// and uncomment the line below to hide debug information
// echo '<style>.debug{display:none;}</style>';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Company Dashboard</title>
    <link rel="stylesheet" href="companies.css">
</head>
<body>

<div class="container">
    <h1>Welcome, <?php echo htmlspecialchars($company['name']); ?></h1>

    <div class="company-info">
        <p><strong>Company Name:</strong> <?php echo htmlspecialchars($company['company_name']); ?></p>
        <p><strong>Company Email:</strong> <?php echo htmlspecialchars($company['company_email']); ?></p>
        <p><strong>Company Phone:</strong> <?php echo htmlspecialchars($company['company_phone']); ?></p>
        <p><strong>Company Address:</strong> <?php echo htmlspecialchars($company['company_address']); ?></p>
        <p><strong>Company Website:</strong> <a href="<?php echo htmlspecialchars($company['company_website']); ?>" target="_blank"><?php echo htmlspecialchars($company['company_website']); ?></a></p>
        
        <!-- Display logo -->
        <?php if ($company['logo']) { ?>
            <p><strong>Company Logo:</strong> <img src="<?php echo htmlspecialchars($company['logo']); ?>" alt="Company Logo" width="150"></p>
        <?php } else { ?>
            <p><strong>No logo uploaded yet.</strong></p>
        <?php } ?>
    </div>

    <!-- Form to update logo -->
    <div class="upload-form">
        <h3>Update Company Logo</h3>
        <form action="companies.php" method="POST" enctype="multipart/form-data">
            <label for="logo">Upload New Logo:</label>
            <input type="file" name="logo" id="logo" accept="image/*">
            <br><br>
            <input type="submit" name="submit" value="Update Logo" class="button">
        </form>
    </div>

    <br>

    <a href="edit_company.php" class="button">Edit Company Information</a>
</div>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>