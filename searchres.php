<?php

session_start();

if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    $righttext = "Hello, ".$_SESSION["username"];
}
else {
    $righttext = "Login/Signup";
}

$search_err = "";
require_once "config.php";

if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(empty(trim($_POST["to"]))){
        $search_err = "Please fill all fields";
    } else{
        $to = trim($_POST["to"]);
    }

    if(empty(trim($_POST["from"]))){
        $search_err = "Please fill all fields";
    } else{
        $from = trim($_POST["from"]);
    }

    if(empty(trim($_POST["date"]))){
        $search_err = "Please fill all fields";
    } else{
        $when = trim($_POST["date"]);
    }

    if(empty($search_err)) {
        $sql = "select code from station";
        $flagto = 0;
        $flagfrom = 0;
        $res = mysqli_query($link, $sql);
        while($rows = mysqli_fetch_array($res)) {
            if($rows[0] == $to) {
                $flagto = 1;
            }
            if($rows[0] == $from) {
                $flagfrom = 1;
            }
        }
        if($flagto == 0 || $flagfrom == 0) {
            $search_err = "Invalid station code";
        }
        else if($to == $from){
            $search_err = "To and from stations cannot be the same";
        }
        else {
            $traincount = 0;
            $listoftrains = array();
            $trainnames = array();
            $trainseats = array();
            $sql = "select trainno from trains where stopsat = '$from'";
            $res = mysqli_query($link, $sql);
            while($rows = mysqli_fetch_array($res)) {
                $trainno = $rows[0];
                $sql2 = "select trainno from trains where stopsat = '$to' and trainno = $trainno";
                $res2 = mysqli_query($link, $sql2);
                if(mysqli_num_rows($res2) == 0) {

                }
                else {
                    array_push($listoftrains, $trainno);
                    $traincount += 1;
                    $sql3 = "select name, seats from traindet where trainnumber = $trainno";
                    $res3 = mysqli_query($link, $sql3);
                    $row = mysqli_fetch_array($res3);
                    $trainname = $row["name"];
                    $seats = $row["seats"];
                    array_push($trainnames, $trainname);
                    array_push($trainseats, $seats);
                }
            }
            $_SESSION["foundTrains"] = $listoftrains;
            $_SESSION["noTrains"] = $traincount;
            $_SESSION["trainNames"] = $trainnames;
            $_SESSION["trainSeats"] = $trainseats;
            $_SESSION["to"] = $to;
            $_SESSION["from"] = $from;
            $_SESSION["date"] = $when;
            header("location: searchres.php");
        }
    }
}

?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <script>
        function loginLogout() {
            var currentText = '<?php echo $righttext; ?>';
            if(currentText != "Login/Signup") {
                return window.location.href = 'logout.php';
            }
            else {
                return window.location.href = 'login.php';
            }
        }
    </script>
</head>
<body>
    <nav class = "topbar">
        <div class = "item large" onclick = "window.location.href = 'index.php'"> Indian Railways </div>
        <div class = "item" onclick = "window.location.href = 'search.php'"> <u>Search</u> </div>
        <div class = "item" onclick = "window.location.href = 'history.php'"> History </div>
        <div class = "item end" onclick = "loginLogout()"><?php echo $righttext ?></div>
    </nav>
    <div class = "float-container">
        <div class = "topleft">
                <h1> Train Search </h1>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label>To</label>
                        <input type="text" name="to" class="form-control <?php echo (!empty($search_err)) ? 'is-invalid' : ''; ?> blurInput">
                    </div>    
                    <div class="form-group">
                        <label>From</label>
                        <input type="text" name="from" class="form-control <?php echo (!empty($search_err)) ? 'is-invalid' : ''; ?> blurInput">
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="date" class="form-control <?php echo (!empty($search_err)) ? 'is-invalid' : ''; ?> blurInput">
                    </div>
                    <span style = "color: red;"><?php echo $search_err; ?></span>
                    <div class="form-group">
                        <input type="submit" class="btn btn-primary" value="Search" style = "width: 100%">
                    </div>
                </form>
        </div>
        <div class = "float-child">
            <h1> <?php echo $_SESSION["noTrains"] ?> trains found</h1> 
            <h3> <?php echo $_SESSION["from"]; ?> to <?php echo $_SESSION["to"]; ?> on <?php echo $_SESSION["date"]; ?></h3><br>
            <div id = "results">

            </div>
            <script defer>

                function booking(i) {
                    document.cookie = escape("selected") + "=" + escape(i);
                    window.location.href = "booking.php";
                }

                var id = document.getElementById("results");
                var n = <?php echo $_SESSION["noTrains"]; ?>;
                var trainlist = <?php echo json_encode($_SESSION["foundTrains"]); ?>;
                var trainnames = <?php echo json_encode($_SESSION["trainNames"]); ?>;
                var trainseats = <?php echo json_encode($_SESSION["trainSeats"]); ?>;

                for(let i = 0; i<n; i++) {
                    let outerbox = document.createElement("div");
                    let resbox = document.createElement("div");
                    let bookbox = document.createElement("div");
                    let trainheading = document.createElement("h3");
                    trainheading.innerHTML = trainnames[i];
                    let trainnumber = document.createElement("p");
                    trainnumber.innerHTML = trainlist[i];
                    let trainseat = document.createElement("p");
                    trainseat.innerHTML = "Available Seats: " + trainseats[i];
                    let book = document.createElement("button");
                    book.innerHTML = "Book";
                    book.onclick = () => booking(i);
                    resbox.appendChild(trainheading);
                    resbox.appendChild(trainnumber);
                    resbox.appendChild(trainseat);
                    bookbox.appendChild(book);
                    resbox.classList.add("searchInfo");
                    bookbox.classList.add("bookButton");
                    outerbox.classList.add("resultBoxes");
                    outerbox.appendChild(resbox);
                    outerbox.appendChild(bookbox);
                    id.appendChild(outerbox);
                }
            </script>
        </div>
    </div>
</body>
</html>