
<?php
    //session
    session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="stylesheet" href="register.css"/>
</head>
<body>
<form id="form">

    <h1 class="topic">Register Caps Online</h1>
    <p class="topic">Please fill the fields below to complete your registration.</p>

    <label for="name" class="regField">Name </label>
    <input type="text" id="name" class="regField" placeholder="Enter Name" name="name"/>
    <br>
    <label for="pwd" class="regField">Password </label>
    <input type="password" id="pwd" class="regField" placeholder="Enter Password" name="pwd"/>
    <br>
    <label for="confirmPwd" class="regField">Confirm Password</label>
    <input type="text" id="confirmPwd" class="regField" placeholder="Confirm Password" name="confirmPwd"/>
    <br>
    <label for="email" class="regField">Email </label>
    <input type="email" id="email" class="regField" placeholder="Enter Email" name="email"/>
    <br>
    <label for="phone" class="regField">Phone </label>
    <input type="text" id="phone" class="regField" placeholder="Enter Phone Number" name="phone"/>
    <br>
    <input type="submit" value="Submit" name="submit" id="submit"/>
    <h5 id="loginLink"><strong>Already Registered? <a href="https://taxibookingystem.herokuapp.com/login.php">Login Here</a></strong></h5>
</form>
<!--embedded php-->
<p class="php">
<?php

/*
* @param: $data
* @return: $data
* function used for sanitizing data input by clients
*/
function sanitise_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/*
* @param: $name, $pwd, $confirmPwd, $email, $phone
* @return: $errMsg
* function used for validation of data input by users
*/
function validate_input($name, $pwd ,$confirmPwd ,$email ,$phone)
{   
    $errMsg = "";//error message

    if($name == "" || $pwd == "" || $confirmPwd == "" || $email == "" || $phone == "")
    {
        $errMsg = "<p class='warningMsgPhp'>- Some fields are empty. Please fill all the fields</p>";
        return $errMsg;
    }else 
    {
        if(!preg_match('/^[A-Za-z]+([\ A-Za-z]+)*/', $name))
        {
            $errMsg .= "<p class='warningMsgPhp'>- Only alphabets are allowed in the Name field.</p>";
        } 
        if(!preg_match('/^(?=.*\d)(?=.*[A-Za-z])[0-9A-Za-z!@#$%]{8,15}$/', $pwd))
        {
            $errMsg .= "<p class='warningMsgPhp'>- The password must have <strong>Number, Letter, Symbols(!@#$%) between 8 to 15 charaters</p>";
        }

        if(strcmp($pwd,$confirmPwd) != 0)
        {
            $errMsg .= "<p class='warningMsgPhp'>- Password is different. Please try again</p>";
        }

        if(!filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            $errMsg .= "<p class='warningMsgPhp'>- Email form is not right.</p>";
        }
        
        if(!preg_match('/^[0-9]{10}+$/', $phone))
        {
            $errMsg .= "<p class='warningMsgPhp'>- phone number must be an arabic with 10 digit numbers.</p>";
        }
    }

    return $errMsg;
}

//connection to MySQL
require_once("setting.php");
$conn = mysqli_connect(
    $host,
    $user,
    $pwd,
    $sql_db
);

//Logic when submit the registration form
    if(isset($_GET['submit']))
    {    
        $name = sanitise_input($_GET['name']); //name 
        $pwd = sanitise_input($_GET['pwd']);// password
        $confirmPwd= sanitise_input($_GET['confirmPwd']);//re-typed password
        $email = sanitise_input($_GET['email']);//email addr.
        $phone = sanitise_input($_GET['phone']); //phone number

        //validation
        $errMsg = validate_input($name, $pwd ,$confirmPwd ,$email ,$phone);
        if($errMsg !== "" || !isset($name,$pwd,$confirmPwd,$email))
        {   
            echo "<div class='warningBackground'>
                    <h1>Something went wrong!</h1>
                    <p>$errMsg</p><br>
                    <p >Please wait, Redirecting<span class ='redirecting'>.....</span></p>
                  </div>";
            header("refresh:4 url=https://taxibookingystem.herokuapp.com/register.php"); 
            
        } else 
        {// execute registration logic here
            if(!$conn)
            {
                die("<p>Sorry, the data server is not available at the moment!</p>");
            }else 
            {   
                $query = "SELECT Email from Customer WHERE Email = '$email'";//query email against Customer table
                $result = mysqli_query($conn, $query);

                //if email and pwd already exist
                if(mysqli_num_rows($result))
                {
                    echo"<div class='warningBackground'>
                            <h1>Something went wrong!</h1>
                            <p class='warningMsgPhp'>Your account is already registered!</p>
                            <p >Please wait, Redirecting<span class ='redirecting'>.....</span></p>
                         </div>";
                    header("refresh:4 url=https://taxibookingystem.herokuapp.com/register.php");
                }else 
                {   //proceed registration and forward email
                    $_SESSION['email'] = $email; 
                    $query = "INSERT INTO Customer (Email, Name, Password, Phone) Values ('$email', '$name', '$pwd','$phone')";
                    $customerInfo = mysqli_query($conn, $query);
                    header("Location: https://taxibookingystem.herokuapp.com/booking.php");//redirect to a booking.php page. 
                    exit;
            
                }

                mysqli_free_result($result);
            }
            mysqli_close($conn);
        }
    }
    //perhaps need redirection to register page itself here


?>
</p>
<!--embedded php ends up here-->
</body>
</html>