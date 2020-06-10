<?php
    session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Caps</title>
    <link rel="stylesheet" href="default.css"/>
    <link rel="stylesheet" href="booking.css"/>
</head>
<body>    
    <form id="form">
    <h1 class="topic">Booking a Cab</h1>
    <p class="topic">Please fill the fields below to book a taxi</p>
        
        <label for="Pname">Passenger Name:</label>
        <input type="text" id="Pname" name="Pname" placeholder="Enter Name"/>

        <label for="phone">Contact Phone Number:</label>
        <input type="text" id="phone" name="phone" placeholder="10 Digits Number"/>
        
        <p class="pickupAddress">Pick up Address:
            <label for="unitNum" id="unitNumlabel">Unit Number:</label>
            <input type="text" id="unitNum" name="unitNum" placeholder="Enter Unit Number"/>
            <label for="stNum">Street Number:</label>
            <input type="text" id="stNum" name="stNum" placeholder="Enter Street Number"/>
            <label for="stName">Street Name:</label>
            <input type="text"id="stName" name="stName" placeholder="Enter Street Name"/>
            <label for="suburb">Suburb:</label>
            <input type="text" id="suburb" name="suburb" placeholder="Enter Suburb"/>
        </p>

        <label for="DestSub">Destination Suburb:</label>
        <input type="text" id="DestSub" name="DestSub" placeholder="Enter Destination Suburb"/>
        
        <div class="pickupBorder">
            <label for="pickupDate">Pickup Date:</label>
            <input type="text" id="pickupDate" name="pickupDate" placeholder="DD/MM/YYYY"/>
            <label for="pickupTime">Pickup Time:</label>
            <input type="text" id="pickupTime" name="pickupTime" placeholder="Military Time: HHMM without colon(:)"/>
        </div>
        <input type="submit" value="Book" name= "submit" id="submit"/>
    </form>
    <!--successful message upon booking-->
    <p>
        <?php
            function succeedMsg($bookingRefNum, $pickupTime, $pickupDate)
            {   
                $pickupTime = substr_replace($pickupTime, ":",2,0);
                echo "<div class ='warningBackground'>
                        <p class = 'warningMsgPhp'>Thank you! Your booking reference number is $bookingRefNum. <br>
                        We will pick up the passengers in front of your provided address at $pickupTime on $pickupDate <br>
                        A confirmation email including the following information will also be sent to the customer.
                        </p>
                    </div>";
            }
        ?>
    </p>

<?php
//connect to db
require_once("setting.php");
$conn = mysqli_connect(
    $host,
    $user,
    $pwd,
    $sql_db
);

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
* @param: $name, $phone, $unitNum, $stNum, $stName, $suburb, $DestSub, $pickupDate, $pickupTime
* @return: $errMsg
* function used for validation of data input by users
*/
function validate_input($name,$phone,$unitNum,$stNum,$stName,$suburb,$DestSub,$pickupDate,$pickupTime)
{
    $errMsg = ""; //error message
    if($name == "" || $phone == "" || $stNum == "" || $stName == "" || $suburb == "" || $DestSub == "" || $pickupDate == "" || $pickupTime == "")
    {
        $errMsg = "You Must fill in all fields";
        return $errMsg;
    }else 
    {
        if(!preg_match('/^[A-Za-z]+([\ A-Za-z]+)*/', $name))
        {
            $errMsg .= "- Name must be alphabetic chracters.<br>";
        } 
        if(!preg_match('/^[0-9]{10}+$/', $phone))
        {
            $errMsg .= "- phone number must be an arabic with 10 digit numbers.<br>";
        }
        if(!preg_match('/^[0-9]{1,10}+$/', $unitNum) && $unitNum != "")
        {
            $errMsg .= "- Unit Number must be an arabic number.<br>";
        }
        if(!preg_match('/^[0-9]{1,10}+$/', $stNum))
        {
            $errMsg .= "- Street Number must be an arabic number.<br>";
        }
        if(!preg_match('/^[A-Za-z]+([\ A-Za-z]+)*/', $stName))
        {
            $errMsg .= "- Street Name must be alphabetic chracters.<br>";
        }
        if(!preg_match('/^[A-Za-z]+([\ A-Za-z]+)*/',$suburb))
        {
            $errMsg .= "- Suburb must be alphabetic chracters.<br>";
        }
        if(!preg_match('/^[A-Za-z]+([\ A-Za-z]+)*/',$DestSub))
        {
            $errMsg .= "- Suburb must be alphabetic chracters.<br>";
        }
        if(!preg_match("/^\d{2}\/\d{2}\/\d{4}$/",$pickupDate))
        {
            $errMsg .= "- Date must be number with slash (/) to seperate numbers.<br>";
        }
        if(!preg_match( '/^([01][0-9]|2[0-3])([0-5][0-9])$/',$pickupTime))
        {
            $errMsg .= "- Pickup Time Error: Time must be number or Military Time format.<br>";
        }
        return $errMsg;
    }
}
/*
*@param: $pickupDate, $pickupTime, $currentTime
*@return: true or false
*function is used for calculating and validating a time in order to check pick up time is set after 40 minutes (ore more) of current time.
*/
function calculate_time($pickupDate,$pickupTime, $currentTime)
{   
    $pickupDate = str_replace("/", "-",$pickupDate);
    $pickupDate = date('Y-m-d', strtotime($pickupDate)); //pickup date
    
    $mergedPickupTime = date("Y-m-d H:i",strtotime("$pickupDate $pickupTime"));
    $pickupT = strtotime($mergedPickupTime);//pickup time
    
    
    $currentTime = strtotime($currentTime);//current time
    //$timeLimit = date("Y-m-d H:i", strtotime('+40 minutes', $currentTime));
    $timeLimit = strtotime('+40 minutes', $currentTime);//$currentTime + 40 minutes

    //echo "<p>".$pickupT ."</p>";
    //echo "<p>".$currentTime ."</p>";
    //echo "<p>".$timeLimit ."</p>";

    if($timeLimit <= $pickupT)
    {
        return true;
    }else 
    {
        return false;
    }
}
/*
*@param:
*@return: 8 digit unique random number 
*function is used for generating booking reference number
*/
function generate_bookingNum()
{
   return rand(100000,999999);
}
/*
*@param:
*@return: $_SESSION['email] 
*function is used for getting a session value (email) from register.php
*/
function get_session_token()
{
    if(!isset($_SESSION['email']))
    {
        return "error";
    }else {
        return $_SESSION['email'];
    }
}

//Logic Once booking form is submitted
if(isset($_GET["submit"]))
{   
    $bookingRefNum = generate_bookingNum();// unique booking reference number;
    $email = get_session_token();
    $name = sanitise_input($_GET['Pname']); //Passenger name 
    $phone = sanitise_input($_GET["phone"]); //phone number
    $unitNum = sanitise_input($_GET["unitNum"]); //unit number
    $stNum = sanitise_input($_GET["stNum"]); //street Number
    $stName = sanitise_input($_GET["stName"]);//street Name
    $suburb = sanitise_input(($_GET["suburb"])); //suburb
    $DestSub = sanitise_input($_GET["DestSub"]); //destination suburb
    $pickupDate = sanitise_input($_GET["pickupDate"]); // pickup date
    $pickupTime = sanitise_input($_GET["pickupTime"]); //pickup time

    $generatedTimeSet = date("Y-m-d H:i");//entire current time set Date + Hours + Minutes
    $generatedDate = date("Y-m-d"); //Current Date
    $generatedTime = date("H:i"); //Current Time
    $status = "unassigned"; //Status

    $errMsg = validate_input($name,$phone,$unitNum,$stNum,$stName,$suburb,$DestSub,$pickupDate,$pickupTime);
    $isTimeLater = calculate_time($pickupDate, $pickupTime, $generatedTimeSet);

    if($errMsg !== "" || !isset($name,$phone,$unitNum,$stNum,$stName,$suburb,$DestSub,$pickupDate,$pickupTime))
    {
        echo 
        "<div class ='warningBackground'>
            <h3>Something Went Wrong!</h3>
            <p class = 'warningMsgPhp'>$errMsg or you have to fill in with right way</p>
        </div>";
    }elseif(!$isTimeLater)
    {
        echo "<div class ='warningBackground'>"
                ."<h3>Something Went Wrong!</h3>"
                ."<p class = 'warningMsgPhp'>- You must book a cap 40 minutes after the current time.</p>" 
                ."<p class = 'warningMsgPhp'>- Please also check if you accidently entered date earlier than today.</p>"
                ."<p class = 'warningMsgPhp'><strong>Enter the valid booking time or date</strong></p>"
            ."</div>";
    }else 
    {
        if(!$conn)
        {
            die("<p>Sorry, the data server is not available at the moment!</p>");
        }else 
        {   
            $query = "INSERT INTO Booking (booking_number, email, name, phone, unit_number, 
                    street_number, street_name, suburb, destination_suburb, pickup_date,pickup_time,GeneratedDate, GeneratedTime, Status)
                    Values ('$bookingRefNum','$email','$name','$phone','$unitNum','$stNum','$stName',
                            '$suburb','$DestSub','$pickupDate','$pickupTime','$generatedDate','$generatedTime', '$status')";

            $result = mysqli_query($conn, $query);
            if(!$result)
            {   
                unset($_SESSION['email']);//unset session variable
                session_destroy(); //destroy all sessions
                echo "<div class ='warningBackground'>
                        <h3>Something Went Wrong!</h3>
                        <p class='warningMsgPhp'>System has failed to store your information.<br> This can cause if you are <strong>NOT logged in or registered</strong>. 
                        <br>Sending you to Login page <span class='redirecting'>...</span> Redirecting <span class='redirecting'>.....</span>
                        </p>
                     </div>";
                header("refresh:4 url=https://mercury.swin.edu.au/cos80021/s102524337/assignment1/login.php");
                
            }else 
            {
                succeedMsg($bookingRefNum, $pickupTime, $pickupDate);//showing successful message inside html
                $to = $email;
                $subject = "Your booking request with CabsOnline!";
                $msg = "Dear $name, Thanks for booking with CabsOnline! Your
                        booking reference number is $bookingRefNum. We will pick up the
                        passengers in front of your provided address at $pickupTime on
                        $pickupDate.";
                $header ="From: booking@cabsonline.com.au";
                mail($to, $subject, $msg, $header, "-r 102524337@student.swin.edu.au");

                unset($_SESSION['email']);//unset session variable
                session_destroy(); //destroy all sessions
            }
        }
        mysqli_close($conn);
    }
}
?>

</body>
</html>