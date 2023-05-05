<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="refresh" content="86400">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard for OEE using www.chartjs3.com</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
        }

        .chartMenu {
            width: 100vw;
            height: 40px;
            background: #000099;
            color: rgba(255, 255, 255, 1);
        }

        .chartMenu p {
            padding: 10px;
            font-size: 20px;
        }

        .chartCard {
            background: rgba(0, 34, 102, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chartBox {
            margin-top: 50px;
            margin-bottom: 50px;
            width: 900px;
            padding: 20px;
            border-radius: 20px;
            border: solid 3px rgba(0, 0, 153, 1);
            background: #e6ffff;
        }

        #legend ul {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            margin: 0;
            padding: 0;
        }

        #legend ul li {
            display: inline-block;
            align-items: right;
            flex-direction: row;
            margin-bottom: 5px;
            flex-basis: 25%;
        }

        #legend ul li span {
            display: inline-block;
            width: 20px;
            padding: 10px;
            margin-right: 10px;
            text-align: right;
        }


        #legend ul li p {
            right: 10px;
        }
    </style>
</head>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chart.js/dist/chart.umd.min.js"></script>
<script script src="https://cdn.jsdelivr.net/npm/luxon@^2">
</script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-luxon@^1"></script>

<!-- <script>
    setTimeout(function() {
        location.reload();
    }, 10000);
</script> -->

<body>
    <div class="chartMenu">
        <p>
            <bold>
                <center>OEE Machine Operation Time <span id="currentDate"> </span></center>
            </bold>
        </p>
    </div>
    <div class="chartCard">
        <div class="chartBox">

            <canvas id="myChart"></canvas>

            <div id="legend">
                <ul>
                    <li><span style="border-color: rgba(51, 51, 204, 0.5); background-color: rgba(51, 51, 204, 0.5);"></span>
                        <p>Estimate time</p>
                    </li>
                    <li><span style="border-color: rgba(255, 204, 0, 1); background-color: rgba(255, 204, 0, 1);"></span>
                        <p>Setup</p>
                    </li>
                    <li><span style="border-color: rgba(41, 163, 41, 1); background-color: rgba(41, 163, 41, 1);"></span>
                        <p>Start</p>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <?php
    // Connect to the MS SQL server
    $serverName = "qdos-dw";
    $connectionInfo = array("Database" => "ExcelAppDB", "UID" => "daexcel", "PWD" => "analytics2018");
    $conn = sqlsrv_connect($serverName, $connectionInfo);

    // Retrieve data for setup bar
    $sql = "SELECT [Parts Name],CONVERT(VARCHAR(19), [Start Date], 126) AS StartDate,CONVERT(VARCHAR(19), [Setup Date], 126) AS SetupDate FROM [dbo].[JMP_OEE_Part_LotL] WHERE [Start Date] > (SELECT CONVERT(DATE, [Start Date]) AS ConvertedDate FROM [dbo].[JMP_OEE_Part_LotL] WHERE [ID] = (SELECT MAX([ID]) FROM [dbo].[JMP_OEE_Part_LotL])) AND ([Mount CT Ave A] <> 0 OR [Transfer CT Ave] <> 0 OR [Standby CT Ave] <> 0) order by [Parts Name] asc";
    // $sql = "SELECT [Parts Name],[Working Ratio] FROM [dbo].[JMP_OEE_Part_LotL] WHERE [Start Date] > DATEADD(day, -1, CONVERT(datetime, CONVERT(date, GETDATE())))AND ([Mount CT Ave A] <> 0 OR [Transfer CT Ave] <> 0 OR [Standby CT Ave] <> 0)";
    $stmt = sqlsrv_query($conn, $sql);
    $data = array();
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $data[] = $row;
    }
    // Retrieve data for start bar
    $sql = "SELECT [Parts Name],CONVERT(VARCHAR(19), [Setup Date], 126) AS SetupDate,CONVERT(VARCHAR(19), [Finish Date], 126) AS FinishDate FROM [dbo].[JMP_OEE_Part_LotL] WHERE [Start Date] > (SELECT CONVERT(DATE, [Start Date]) AS ConvertedDate FROM [dbo].[JMP_OEE_Part_LotL] WHERE [ID] = (SELECT MAX([ID]) FROM [dbo].[JMP_OEE_Part_LotL])) AND ([Mount CT Ave A] <> 0 OR [Transfer CT Ave] <> 0 OR [Standby CT Ave] <> 0) order by [Parts Name] asc";
    $stmt = sqlsrv_query($conn, $sql);
    $data2 = array();
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {

        $data2[] = $row;
    }

    // Retrieve data for start bar
    $sql = "SELECT [Parts Name] FROM [dbo].[JMP_OEE_Part_LotL] WHERE [Start Date] > (SELECT CONVERT(DATE, [Start Date]) AS ConvertedDate FROM [dbo].[JMP_OEE_Part_LotL] WHERE [ID] = (SELECT MAX([ID]) FROM [dbo].[JMP_OEE_Part_LotL])) AND ([Mount CT Ave A] <> 0 OR [Transfer CT Ave] <> 0 OR [Standby CT Ave] <> 0) order by [Parts Name] asc";
    $stmt = sqlsrv_query($conn, $sql);
    $data3 = array();
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {

        $data3[] = $row;
    }
    // Close the MS SQL server connection
    sqlsrv_close($conn);

    ?>

    <script>
        // Get the current date
        let todayinhtml = new Date();
        todayinhtml.setDate(todayinhtml.getDate() - 1);

        // Format the date as desired
        let formattedDate = todayinhtml.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        // Set the formatted date as the content of the 'currentDate' element
        document.getElementById('currentDate').textContent = formattedDate;

        // setup
        //time range in x-axis
        var today = new Date();
        today.setDate(today.getDate() - 1);
        today.setHours(0, 0, 0, 0); // set time to 00:00:00.000
        var endDate = new Date();
        endDate.setDate(endDate.getDate() - 1);
        endDate.setHours(24, 0, 0, 0); // set time to 23:59:59.999

        //array for length of loop
        var arry = <?php echo json_encode($data3); ?>;

        //arry for all labels include Estimate Time
        var arry1 = [];
        var arry1 = <?php echo json_encode($data3); ?>;
        var arry11 = [];

        for (let i = 0; i < arry1.length; i++) {
            arry11.push('Estimate Time ' + arry1[i]['Parts Name'], arry1[i]['Parts Name']);
        }
        // Bar chart data for setup bar
        var barData = <?php echo json_encode($data); ?>;
        var barLabels = [];
        var barSetupValues1 = [];
        var barSetupValues2 = [];
        for (var i = 0; i < barData.length; i++) {
            barLabels.push(barData[i]["Parts Name"]);
            barSetupValues1.push(barData[i]["StartDate"]);
            barSetupValues2.push(barData[i]["SetupDate"]);
        }

        // Bar chart data for start bar
        var barData2 = <?php echo json_encode($data2); ?>;
        var barLabels2 = [];
        var barStartValues1 = [];
        var barStartValues2 = [];
        for (var i = 0; i < barData2.length; i++) {
            barLabels2.push(barData2[i]["Parts Name"]);
            barStartValues1.push(barData2[i]["SetupDate"]);
            barStartValues2.push(barData2[i]["FinishDate"]);
        }

        //array for dataset loop all data------------------------------arry.length
        var datasetcombine2 = [];
        for (let i = 0; i < arry.length; i++) {

            let date = new Date(barSetupValues1[i]);
            date.setMinutes(date.getMinutes() + i);
            date.setHours(date.getHours() + 2);



            var datasetcombine1 = [{
                label: 'Estimate Time ' + barLabels[i], //'Estimate Time1',//arry1
                data: [{
                    x: [barSetupValues1[i], date], //Estimate Time*estimatetime[i]
                    y: 'Estimate Time ' + barLabels[i], //'Estimate Time1'//arry1
                }],
                backgroundColor: ['rgba(51, 51, 204, 0.5)'],
                borderColor: ['rgba(51, 51, 204, 0.5)'],
                borderWidth: 1
            }, {
                label: 'setup1',
                data: [{
                    x: [barSetupValues1[i], barSetupValues2[i]], //barSetupValues1,barSetupValues2
                    y: barLabels[i], //'lot1'//arry1
                }],
                backgroundColor: ['rgba(255, 204, 0, 1)'],
                borderColor: ['rgba(255, 204, 0, 1)'],
                borderWidth: 1
            }, {
                label: 'Start1',
                data: [{
                    x: [barStartValues1[i], barStartValues2[i]], //barStartValues1,barStartValues2
                    y: barLabels[i], //'lot1'//arry1
                }],
                backgroundColor: ['rgba(41, 163, 41, 1)'],
                borderColor: ['rgba(41, 163, 41, 1)'],
                borderWidth: 1
            }];
            for (let j = 0; j < datasetcombine1.length; j++) {
                datasetcombine2.push(datasetcombine1[j]);
            }

        }
        // console.log(datasetcombine2);
        // const scriptwwww = JSON.stringify(datasetcombine2, null, 2);
        // console.log(scriptwwww);

        const data = {
            //labels: arry11,//arry1
            datasets: datasetcombine2 //datasetcombine
        };

        // config
        const config = {
            type: 'bar',
            data,
            options: {
                barPercentage: 0.9,
                categoryPercentage: 1,
                indexAxis: 'y',
                aspectRatio: 2, //3,

                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'hour'
                        },
                        min: today,
                        max: endDate

                    },
                    y: {
                        beginAtZero: true,
                        stacked: true,

                    }
                },
                plugins: {
                    responsive: true,
                    title: {
                        display: true,
                        text: 'Estimate Time va Actual Time'
                    },
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                const starttime = myChart.data.datasets[tooltipItem.datasetIndex].data[0.0].x[0];
                                const starttime1 = new Date(starttime);
                                const formattstartDate = starttime1.toLocaleString('en-US', {
                                    timeZone: 'Asia/Kuala_Lumpur',
                                    formatMatcher: 'basic',
                                    year: 'numeric',
                                    month: '2-digit',
                                    day: '2-digit',
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    second: '2-digit'
                                }).replace(',', '');

                                const endtime = myChart.data.datasets[tooltipItem.datasetIndex].data[0.0].x[1];
                                const endtime1 = new Date(endtime);
                                const formattedDate = endtime1.toLocaleString('en-US', {
                                    timeZone: 'Asia/Kuala_Lumpur',
                                    formatMatcher: 'basic',
                                    year: 'numeric',
                                    month: '2-digit',
                                    day: '2-digit',
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    second: '2-digit'
                                }).replace(',', '');

                                return formattstartDate + " - " + formattedDate; //+ ", Estimate Time: " + formattedDate;
                            }
                        }
                    }
                }
            }
        };

        // render init block
        const myChart = new Chart(
            document.getElementById('myChart'),
            config
        );

        // Instantly assign Chart.js version
        const chartVersion = document.getElementById('myChart');
        chartVersion.innerText = Chart.version;
    </script>

</body>

</html>