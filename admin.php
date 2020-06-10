<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>admin</title>
    <link rel="stylesheet" href="admin.css"/>
</head>
<body>

<div class="wrapper">
<h1>Admin Page of Cabs Online</h1>
<!--form_1-->
    <div class="form1Wrapper">
        <p>1. Click below button to search for all unassigned booking requeists with a pick-up time within 3 hours</p>
        <form >
            <input type="submit" name="submit" id="submit1" value="List All"/>
        </form>
    
<!--embedded php for form_1-->
<?php
        //connection to MySQL
        require_once("setting.php");
        $conn = mysqli_connect(
            $host,
            $user,
            $pwd,
            $sql_db
        );

        /*
        *@param: $pickupDate, $pickupTime - results from Booking table 
        *@return true or false
        *Function is used for calculating date with time and returns true if pickup time is less than 3 hours than current time; Otherwise, false is returned. 
        */
        function calculate_date_time($pickupDate, $pickupTime)
        {
            $pickupDate = str_replace("/", "-",$pickupDate);
            $pickupDate = date('Y-m-d', strtotime($pickupDate)); //pickup date
            $mergedPickupTime = date("Y-m-d H:i",strtotime("$pickupDate $pickupTime"));
            $pickupT = strtotime($mergedPickupTime);//pickup date and time

            $currentTime = date("Y-m-d H:i");//current time
            $currentTime = strtotime($currentTime);
            $threeHoursRange = strtotime('+3 hours' , $currentTime);

            if($threeHoursRange>= $pickupT && $pickupT>=$currentTime)
            {
                return true;
            }else{
                return false;
            }
        }

        /*
        *
        *@param: $pickupDate, $pickupTime - results from Booking table
        *@return: $pickupDateArr[0] => date in number + "/" + $month => month in Enligh +  "&nbsp;"=> space + $time => time with delimiter(":") ;
        *Function is used for refine input date and time into more human readible characters. 
        */
        function refine_date_time($pickupDate, $pickupTime)
        {   
            //refine time
            $time = substr_replace($pickupTime, ":", 2,0);

            //refine date (month)
            $pickupDateArr = explode("/", $pickupDate);
            $month = "";

            switch($pickupDateArr[1])
            {
                case "01":
                    $month = "Jan";
                    break;
                case "02":
                    $month = "Feb";
                    break;
                case "03":
                    $month = "Mar";
                    break;
                case "04":
                    $month = "Apr";
                    break;
                case "05":
                    $month = "May";
                    break;
                case "06":
                    $month = "Jun";
                    break;
                case "07":
                    $month = "Jul";
                    break;
                case "08":
                    $month = "Aug";
                    break;
                case "09":
                    $month = "Sep";
                    break;
                case "10":
                    $month = "Oct";
                    break;
                case "11":
                    $month = "Nov";
                    break;
                case "12":
                    $month = "Dec";
                    break;
            }

            return $pickupDateArr[0] . "/" .$month . "&nbsp;" .$time;
        }

        //logic starts here
        if(isset($_GET['submit']))
        {   //Name = customer name
            //name = passenger name
            $query = "SELECT booking_number, Name ,name, phone, unit_number,street_number, street_name, suburb, destination_suburb, pickup_date, pickup_time 
            FROM Booking WHERE Status = 'unassigned'";

            $result = mysqli_query($conn, $query);

            if(mysqli_num_rows($result)>0)
            {   

                //logic to show table here...
                echo "<div class = 'table'>";
                echo "<table border = \"1\">\n";
                echo "<tr>\n"
                ."<th scope = \"col\">Reference#</th>"
                ."<th scope = \"col\">Customer Name</th>"
                ."<th scope = \"col\">Passenger Name</th>"
                ."<th scope = \"col\">Passenger Contact Phone</th>"
                ."<th scope = \"col\">Pick-up Address</th>"
                ."<th scope = \"col\">Destination Suburb</th>"
                ."<th scope = \"col\">Pick-up Time</th>"
                ."</tr>\n";

                while($row = mysqli_fetch_assoc($result))
                {   
                    $delimiter ="";
                    if($row["unit_number"] != "")
                    {
                        $delimiter = "/";
                    }
                    if(calculate_date_time($row["pickup_date"], $row["pickup_time"]))
                    {    
                        $dateAndTime = refine_date_time($row["pickup_date"], $row["pickup_time"]);                        
                        echo "<tr>\n";
                            echo "<td>", $row["booking_number"], "</td>\n" ;
                            echo "<td>", $row["Name"], "</td>\n" ;//customer name
                            echo "<td>", $row["name"], "</td>\n" ;//passenger name
                            echo "<td>", $row["phone"], "</td>\n" ;
                            echo "<td>", $row["unit_number"],"$delimiter", $row["street_number"]," ", $row["street_name"], ", " ,$row["suburb"], "</td>\n" ;
                            echo "<td>", $row["destination_suburb"], "</td>\n" ;
                            echo "<td>",$dateAndTime,"</td>\n" ;
                        echo"</tr>\n";

                    }                    
                }
                echo"</table>\n ";
                echo "</div>";
            
            }else
            {
                echo "<p>0 result</p>";
            }
            mysqli_free_result($result);
        }
    ?>
</div>
<!--form_2-->

<div class="form2Wrapper">
<p >2.Input a reference number below and click "update" button to assign a taxi to that request</p>
    <form >
        <label for="refNum" id="refNumLabel">Reference Number</label>
            <input type="text" name="refNum" id="refNum" placeholder="Enter Booking Reference Number" />
        
        <input type="submit" value="Update" id="submit2" name="update"/>
    </form>
</div>
<!--embedded php for form_2-->
<?php

    /*
    * @param: $data
    * @eturn: $data
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
    * @param: $refNum - booking reference number
    * @return: true or false - !!return true if booking reference number is not valid
    * Function is used for validation of booking reference number
    */
    function input_validation($refNum)
    {
        $errMsg = "";
        if(!preg_match('/^[0-9]{6}+$/',$refNum))
        {
            return true;
        }else 
        {
            return false;
        }
    }

    if(isset($_GET["update"]))
    {   
        if($_GET["refNum"] == "" || !isset($_GET["refNum"]))
        {
            echo "<p class = 'phpWarningMsg'>Please enter booking reference number. Do not submit with empty input.</p>";

        }elseif(input_validation($_GET["refNum"]))
        {
            echo "<p class = 'phpWarningMsg'>You must enter number with 6 digits.</p>";
        }
        else
        {
            $bookingRefNum = sanitise_input($_GET["refNum"]);//booking reference number
            $query = "UPDATE Booking SET Status = 'assigned' WHERE booking_number = '$bookingRefNum'";
            $update = mysqli_query($conn, $query);

            if(!$update || mysqli_affected_rows($conn)== 0)
            {
                echo "<p class = 'phpWarningMsg'>Updating went wrong. Please check booking reference number and try again!</p>";
            }else
            {
                echo "<p class = 'phpSucMsg'>The booking request $bookingRefNum has been properly assigned</p>";
            }
        }
        mysqli_close($conn);
    }
    ?>
</div>
</body>
</html>
