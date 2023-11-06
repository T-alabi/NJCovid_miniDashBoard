<?php
include "dbconfig.php";
$con = mysqli_connect($host, $username, $password, $dbname)
    or die("<br>Cannot connect to DB:$dbname on $host\n, error" . mysqli_connect_error());

$successfulQuery = "select County, caseNum, deathNum from CountyTotals where deathNum > 0 order by caseNum desc;";

$UnsuccessfulQuery = "SELECT City, caseNum FROM CountyTotals;";

// detect which query we are supposed to carry out, good or bad, and set query var to that value
if (isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'good') {
        $query = $successfulQuery;
    } elseif ($action === 'bad') {
        $query = $UnsuccessfulQuery;
    }

    // run the SQL query chosen by the user 
    $result = mysqli_query($con, $query);

    if ($result) {
        // Fetch and display the data as an HTML table
        $output = "<table  class='table'>";
        $output .= "<thead> <tr> <th>County</th>  <th>CaseNum</th> <th>Death Num</th> </tr> </thead>";

        $data = array();
        $ScatterData = array();
        while ($row = mysqli_fetch_assoc($result)) {

            $output .= "<tr><td>" . $row['County'] . "</td><td>" . $row['caseNum'] . "</td><td>" . $row['deathNum'] . "</td></tr>";
            $data[] = array($row['County'], (int)$row['caseNum']);

            $ScatterData[] = array((int)$row['caseNum'], (int)$row['deathNum']);

        }

        $output .= "</table>";
        // Here, we're storing the JSON data in a JavaScript variable for the chart
        echo '<script>var jsonData = ' . json_encode($data) . ';</script>';
        echo '<script>var ScatterDataJSON = ' . json_encode($ScatterData) . ';</script>';
        echo $output; // Sending the HTML table as a response
    } else {
        echo "Query failed: " . mysqli_error($con);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Jersey County Data</title>
    
    <!-- Load the Google Charts library -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">


    <script type="text/javascript">
        google.charts.load('current', {'packages': ['corechart']});
        google.charts.setOnLoadCallback(initalizeCharts);

        function initalizeCharts(){
            // just used to call the 2 seperate charts explicitly for clearity of code
            drawChart();
            drawChart2();
        }

        function drawChart() {
            // Use the JSON data stored earlier
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'County');
            data.addColumn('number', 'Case Number');
            data.addRows(jsonData);

            var options = {
                title: 'New Jersey County Cases',
                width: 800,
                height: 600,
                chartArea: {width: '70%'},
                hAxis: {
                    title: 'Case Number',
                    minValue: 0
                },
                vAxis: {
                    title: 'County'
                }
            };

            var chart = new google.visualization.BarChart(document.getElementById('chart_div'));
            chart.draw(data, options);
        }

        function drawChart2() {
            //use JSON data stored in scatterData variable 
            var scatterDataJS = new google.visualization.DataTable();
            scatterDataJS.addColumn('number', 'caseNum'); // make sure these values are the same as inside ur SQL DB
            scatterDataJS.addColumn('number', 'deathNum'); 
            scatterDataJS.addRows(ScatterDataJSON);

            var options = {
                title: 'New Jersey Covid-19 Case Rate vs Death Rate by County',
                hAxis : {title: 'Case Ct'},
                vAxis: {title: 'Death Ct'},
                legend: 'none'
            };
            var chart = new google.visualization.ScatterChart(document.getElementById('scatter_div'));
            chart.draw(scatterDataJS, options);
        }



    </script>
</head>
<body>
    <div class="container">
        <h1 class="text-center" >New Jersey County Data</h1>

        <div class="mx-auto" id="chart_div"></div>
        <div class="mx-auto" id="scatter_div"></div>
    </div>

</body>
</html>
