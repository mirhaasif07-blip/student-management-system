<?php
// ================= DATABASE CONNECTION =================
$host="localhost";
$user="root";
$pass="";
$db="testdb";

$conn=new mysqli($host,$user,$pass);
$conn->query("CREATE DATABASE IF NOT EXISTS $db");
$conn->select_db($db);

// ================= STUDENT TABLE =================
$conn->query("CREATE TABLE IF NOT EXISTS students(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    city VARCHAR(50),
    gender VARCHAR(10),
    course VARCHAR(50),
    age INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// ================= ACTIVITY LOG TABLE =================
$conn->query("CREATE TABLE IF NOT EXISTS activity_logs(
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    action VARCHAR(20),
    action_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// ================= DELETE STUDENT =================
if(isset($_GET['delete'])){
    $id=$_GET['delete'];
    $conn->query("INSERT INTO activity_logs(student_id,action) VALUES('$id','DELETE')");
    $conn->query("DELETE FROM students WHERE id=$id");
    header("Location: db.php");
    exit();
}

// ================= EDIT FETCH =================
$editData=null;
if(isset($_GET['edit'])){
    $id=$_GET['edit'];
    $res=$conn->query("SELECT * FROM students WHERE id=$id");
    $editData=$res ? $res->fetch_assoc() : null;
}

// ================= INSERT STUDENT =================
if(isset($_POST['submit'])){
    $name=$_POST['name'] ?? '';
    $email=$_POST['email'] ?? '';
    $phone=$_POST['phone'] ?? '';
    $city=$_POST['city'] ?? '';
    $gender=$_POST['gender'] ?? '';
    $course=$_POST['course'] ?? '';
    $age=$_POST['age'] ?? 0;

    $conn->query("INSERT INTO students(name,email,phone,city,gender,course,age)
        VALUES('$name','$email','$phone','$city','$gender','$course','$age')");
    $student_id=$conn->insert_id;
    $conn->query("INSERT INTO activity_logs(student_id,action) VALUES('$student_id','INSERT')");
    header("Location: db.php");
    exit();
}

// ================= UPDATE STUDENT =================
if(isset($_POST['update'])){
    $id=$_POST['id'] ?? 0;
    $name=$_POST['name'] ?? '';
    $email=$_POST['email'] ?? '';
    $phone=$_POST['phone'] ?? '';
    $city=$_POST['city'] ?? '';
    $gender=$_POST['gender'] ?? '';
    $course=$_POST['course'] ?? '';
    $age=$_POST['age'] ?? 0;

    $conn->query("UPDATE students SET
        name='$name',
        email='$email',
        phone='$phone',
        city='$city',
        gender='$gender',
        course='$course',
        age='$age'
        WHERE id=$id");

    $conn->query("INSERT INTO activity_logs(student_id,action) VALUES('$id','UPDATE')");
    header("Location: db.php");
    exit();
}

// ================= RESET ALL DATA =================
if(isset($_POST['reset'])){
    $conn->query("TRUNCATE TABLE students");
    $conn->query("TRUNCATE TABLE activity_logs");
    header("Location: db.php");
    exit();
}

// ================= FETCH STUDENTS =================
$result=$conn->query("SELECT * FROM students ORDER BY id DESC");

// ================= FETCH LOGS =================
$logs=$conn->query("
SELECT activity_logs.*, students.name
FROM activity_logs
LEFT JOIN students ON students.id=activity_logs.student_id
ORDER BY log_id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Database</title>
<style>
body{background:#0b1120;font-family:Arial;color:#e5e7eb;}
.container{width:80%;margin:auto;background:#111827;padding:25px;border-radius:12px;box-shadow:0 0 25px rgba(0,0,0,.6);}
h2{text-align:center;}
label{font-weight:bold;color:#cbd5e1;}
input,select{width:100%;padding:12px;margin:8px 0;background:#020617;border:1px solid #334155;border-radius:6px;color:#f8fafc;}
button{width:100%;padding:12px;background:#6366f1;border:none;border-radius:6px;color:white;font-size:16px;cursor:pointer;}
button:hover{background:#4f46e5;}
table{width:100%;border-collapse:collapse;margin-top:25px;}
th{background:#020617;padding:12px;}
td{background:#111827;padding:10px;text-align:center;border-bottom:1px solid #334155;}
tr:hover td{background:#1e293b;}
.action-btn{padding:6px 10px;border-radius:5px;text-decoration:none;color:white;font-size:13px;}
.edit{ background:#22c55e; }
.delete{ background:#ef4444; }
</style>
</head>
<body>
<div class="container">

<h2>Student Registration</h2>
<form method="POST">
<input type="hidden" name="id" value="<?= isset($editData['id']) ? htmlspecialchars($editData['id']) : '' ?>">

<label>Name</label>
<input type="text" name="name" required value="<?= isset($editData['name']) ? htmlspecialchars($editData['name']) : '' ?>">

<label>Email</label>
<input type="email" name="email" required value="<?= isset($editData['email']) ? htmlspecialchars($editData['email']) : '' ?>">

<label>Phone</label>
<input type="text" name="phone" required value="<?= isset($editData['phone']) ? htmlspecialchars($editData['phone']) : '' ?>">

<label>City</label>
<input type="text" name="city" required value="<?= isset($editData['city']) ? htmlspecialchars($editData['city']) : '' ?>">

<label>Gender</label>
<select name="gender">
<option <?= (isset($editData['gender']) && $editData['gender']=="Male")?"selected":"" ?>>Male</option>
<option <?= (isset($editData['gender']) && $editData['gender']=="Female")?"selected":"" ?>>Female</option>
</select>

<label>Course</label>
<input type="text" name="course" required value="<?= isset($editData['course']) ? htmlspecialchars($editData['course']) : '' ?>">

<label>Age</label>
<input type="number" name="age" required value="<?= isset($editData['age']) ? htmlspecialchars($editData['age']) : '' ?>">

<?php if($editData): ?>
<button name="update">Update Record</button>
<?php else: ?>
<button name="submit">Add Record</button>
<?php endif; ?>
</form>

<form method="POST" onsubmit="return confirm('Are you sure you want to reset all data?');">
<button name="reset" style="background:#ef4444; margin-top:15px;">Reset All Data</button>
</form>

<h2>Student Records</h2>
<table>
<tr>
<th>ID</th><th>Name</th><th>Email</th><th>Phone</th>
<th>City</th><th>Gender</th><th>Course</th>
<th>Age</th><th>Date</th><th>Action</th>
</tr>
<?php while($row=$result->fetch_assoc()): ?>
<tr>
<td><?= isset($row['id']) ? htmlspecialchars($row['id']) : '' ?></td>
<td><?= isset($row['name']) ? htmlspecialchars($row['name']) : '' ?></td>
<td><?= isset($row['email']) ? htmlspecialchars($row['email']) : '' ?></td>
<td><?= isset($row['phone']) ? htmlspecialchars($row['phone']) : '' ?></td>
<td><?= isset($row['city']) ? htmlspecialchars($row['city']) : '' ?></td>
<td><?= isset($row['gender']) ? htmlspecialchars($row['gender']) : '' ?></td>
<td><?= isset($row['course']) ? htmlspecialchars($row['course']) : '' ?></td>
<td><?= isset($row['age']) ? htmlspecialchars($row['age']) : '' ?></td>
<td><?= isset($row['created_at']) ? htmlspecialchars($row['created_at']) : '' ?></td>
<td>
<a class="action-btn edit" href="db.php?edit=<?= isset($row['id']) ? urlencode($row['id']) : '' ?>">Edit</a>
<a class="action-btn delete" href="db.php?delete=<?= isset($row['id']) ? urlencode($row['id']) : '' ?>" onclick="return confirm('Delete record?')">Delete</a>
</td>
</tr>
<?php endwhile; ?>
</table>

<h2>Activity Logs</h2>
<table>
<tr>
<th>Log ID</th>
<th>Student</th>
<th>Action</th>
<th>Time</th>
</tr>
<?php while($log=$logs->fetch_assoc()): ?>
<tr>
<td><?= isset($log['log_id']) ? htmlspecialchars($log['log_id']) : '' ?></td>
<td><?= isset($log['name']) && $log['name'] !== null ? htmlspecialchars($log['name']) : "Deleted Record" ?></td>
<td><?= isset($log['action']) ? htmlspecialchars($log['action']) : '' ?></td>
<td><?= isset($log['action_time']) ? htmlspecialchars($log['action_time']) : '' ?></td>
</tr>
<?php endwhile; ?>
</table>

</div>
</body>
</html>