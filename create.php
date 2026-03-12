<?php
include 'connect.php';

if(isset($_POST['submit'])){
  $first_name=$_POST['firstname'];
  $last_name=$_POST['lastname'];
  $password=password_hash($_POST['password'],PASSWORD_DEFAULT);
  $email=$_POST['email'];
  $gender=$_POST['gender'];

$sql = "INSERT INTO employees (firstname,lastname,password,email,gender) VALUES ('$first_name','$last_name','$password','$email','$gender')";
$result = $conn->query($sql);

if($result==true){
    echo "New record saved successfully!";
}
else{
     echo "Error: ".$sql.'<br>'.$conn->error;
}
$conn->close();
}
?>
<html>
    <a class='btn btn-info' href="signup.html"> <br><br>Back</a>
</html>