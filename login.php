<?php
    //use session for email 
    session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="default.css"/>
    <link rel="stylesheet" href="login.css"/>
</head>
<body>
    <form id="form">
    <h1>Login to CapsOnline</h1>
        <label for="email">Email </label>
        <input type="text" name="email" placeholder="Enter Email" id="email"/>

        <label for="password">Password</label>
        <input type="password" name="password" placeholder="Enter Password" id="password"/>

        <input type="submit" value="Log in" name="submit" id="submit"/>
        <h4 id="pageMover"><strong>New Member? <a href="https://mercury.swin.edu.au/cos80021/s102524337/assignment1/register.php">Register Now</a></strong></h4>
    </form>

</body>

<?php

//connection to MySQL
require_once("setting.php");
$conn = mysqli_connect(
    $host,
    $user,
    $pwd,
    $sql_db
);

//when submit
if(isset($_GET['submit']))
{
    $email = $_GET['email'];
    $pwd = $_GET['password'];

    if(!isset($email, $pwd) || $email == "" || $pwd == "")
    {  
        echo "<script type= 'text/javascript'>alert('You must enter email and password');</script>";
    }else 
    {
        if(!$conn)
        {
            die("<p>Sorry, the data server is not available at the moment!</p>");
        }else
        {
            $query = "SELECT Email, Password from Customer WHERE Email = '$email' AND Password = '$pwd' ";//query to check if email or pwd already exists
            $result = mysqli_query($conn, $query);

            $emailQuery = "SELECT Email FROM Customer WHERE Email = '$email'";
            $emailResult = mysqli_query($conn,$emailQuery);

            
            if(mysqli_num_rows($result)>0)
            {//login success
                $_SESSION['email'] = $email;//email session to booking page
                echo "<div class='warningBackground'>
                        <p class='warningMsgPhp'>Successfully logged in! Redirecting you to booking page <span class ='redirecting'>.....</span></p>
                      </div>";
                header("refresh:3 url = https://mercury.swin.edu.au/cos80021/s102524337/assignment1/booking.php");
                exit;
            }elseif(mysqli_num_rows($emailResult)== 0)
            {//account does not exist
                echo "<div class='warningBackground'>
                        <p class='warningMsgPhp'>Your email does not exist in the System. <br>Refreshing page <span class ='redirecting'>.....</span></p>
                      </div>";
                header("refresh:3 url = https://mercury.swin.edu.au/cos80021/s102524337/assignment1/login.php");
                exit;
            }else
            {//email and password not matched
                echo "<div class='warningBackground'>
                        <p class='warningMsgPhp'>Email or Password does not match. 
                        Please try again or Register if you do not have your account.<br> Refreshing<span class ='redirecting'>.....</span></p>
                      </div>";
                header("refresh:3 url = https://mercury.swin.edu.au/cos80021/s102524337/assignment1/login.php");
                exit;
            }
            mysqli_free_result($query);
            mysqli_free_result($emailResult);
        }
    }mysqli_close($conn);
}



?>
</html>


<?php
?>