<?php
function check($thingy){
    if (!$thingy) {
        echo "<center><h1>FileCrypt API is DOWN</h1></center>";
        throw new Exception( "filecrypt.cc api down" );
        exit();
    }
}

$USE_SSL = true;
include 'key.inc.php';
$postdata = http_build_query( array(
    "fn" => "user",
    "sub" => "earnings",
    "api_key" => $api_key,
) );
$opts = array( 'http' => array(
    "method" => "POST",
    "header" => "Content-type: application/x-www-form-urlencoded",
    "content" => $postdata
) );
$context = stream_context_create( $opts );
$stats = file_get_contents( 'http' . ( ( $USE_SSL ) ? 's' : '' ) . '://www.filecrypt.cc/api.php', false, $context );

$postdata = http_build_query( array(
    "fn" => "containerV2",
    "sub" => "listV2",
    "api_key" => $api_key,
) );
$opts = array( 'http' => array(
    "method" => "POST",
    "header" => "Content-type: application/x-www-form-urlencoded",
    "content" => $postdata
) );
$context = stream_context_create( $opts );
$containers = file_get_contents( 'http' . ( ( $USE_SSL ) ? 's' : '' ) . '://www.filecrypt.cc/api.php', false, $context );

if ( !isset( $_GET[ 'container' ] ) ) {
    check($stats);
    $stats = json_decode( $stats, true );
    
    check($containers);
    $containers = json_decode( $containers, true );
}

?>
<html>
<head>
    <title>FileCrypt Dashboard</title>
    <!--Cyberpunk-->
    <link rel="stylesheet" href="css/cyberpunk.css">

    <!--Load the AJAX API-->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        // Load the Visualization API and the corechart package.
        google.charts.load( 'current', {
            'packages': [ 'corechart', 'line', 'bar' ]
        } );

        // Set a callback to run when the Google Visualization API is loaded.
        google.charts.setOnLoadCallback( viewChart );
        google.charts.setOnLoadCallback( munyChart );

        function viewChart() {

            // Create the views table.
            var data = new google.visualization.DataTable();
            data.addColumn( 'string', 'Date' );
            data.addColumn( 'number', 'Views' );
            data.addRows( [ <?php foreach($stats['statistic'] as $key=>$value){ echo "['".$key."', ".$value['views']."],"; } ?> ] );

            // Set chart options
            var options = {
                'title': 'Views',
                /*'width':800,
                'height':'100%'*/
            };

            // Instantiate and draw our chart, passing in some options.
            var chart = new google.visualization.BarChart( document.getElementById( 'view' ) );

            chart.draw( data, options );
        }

        function munyChart() {

            // Create the muny table.
            var data = new google.visualization.DataTable();
            data.addColumn( 'string', 'Date' );
            data.addColumn( 'number', 'Muny' );
            data.addRows( [ <?php foreach($stats['statistic'] as $key=>$value){ $muny = str_replace(',', '.', $value['balance']); echo "['".$key."', ".$muny."],"; } ?> ] );

            // Set chart options
            var options = {
                'title': 'Muny',
                /*'width':800,
                'height':'100%'*/
            };

            // Instantiate and draw our chart, passing in some options.
            var chart = new google.visualization.BarChart( document.getElementById( 'muny' ) );

            chart.draw( data, options );
        }
    </script>
</head>

<body>
    <?php
    if ( isset( $_GET[ 'container' ] ) ) {
        $id = $_GET[ 'container' ];
        $postdata = http_build_query( array(
            "fn" => "containerV2",
            "sub" => "statusV2",
            "container_id" => $id,
            "api_key" => $api_key,
        ) );
        $opts = array( 'http' => array(
            "method" => "POST",
            "header" => "Content-type: application/x-www-form-urlencoded",
            "content" => $postdata
        ) );
        $context = stream_context_create( $opts );
        $info = file_get_contents( 'http' . ( ( $USE_SSL ) ? 's' : '' ) . '://www.filecrypt.cc/api.php', false, $context );
        check($info);
        $postdata = http_build_query( array(
            "fn" => "containerV2",
            "sub" => "info",
            "container_id" => $id,
            "api_key" => $api_key,
        ) );
        $opts = array( 'http' => array(
            "method" => "POST",
            "header" => "Content-type: application/x-www-form-urlencoded",
            "content" => $postdata
        ) );
        $context = stream_context_create( $opts );
        $links = file_get_contents( 'http' . ( ( $USE_SSL ) ? 's' : '' ) . '://www.filecrypt.cc/api.php', false, $context );
        check($links);
        $info = json_decode( $info, true );
        $links = json_decode( $links, true );

        echo "<div class='toolbar'>";
        echo "<div class='body'>";
        echo "<span>Container Info</span>";
        echo "<span style='float:right'>[ <a class='bracketButton' href='" . substr( $_SERVER[ 'REQUEST_URI' ], 0, strrpos( $_SERVER[ 'REQUEST_URI' ], '?' ) ) . "'>Back to Dashboard</a> ]</span>";
        echo "</div>";
        echo "</div>";
        echo "<center>";
        echo "<br>";
        echo "<div class='msgBox'>" . $info[ 'container' ][ 'name' ] . "</div>";
        echo "<br><br>";
        echo "<table width='20%' border=1>";
        echo "<tr>";
        echo "<th colspan=2>General</th>";
        echo "</tr>";
        echo "<tr>";
        echo "<td>Status</td>";
        echo "<td align=center><img src='https://filecrypt.cc/Stat/TEXT2/" . $info[ 'container' ][ 'statusimg_id' ] . ".png'</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td>Size</td>";
        echo "<td align='center'>" . $info[ 'container' ][ 'size_human' ] . "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td>Link Count</td>";
        echo "<td align='center'>" . $info[ 'container' ][ 'links' ] . "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td>Tags</td>";
        echo "<td align=center>";
        foreach ( $info[ 'container' ][ 'tags' ] as $tag ) {
            echo $tag . "<br>";
        }
        echo "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td>Created</td>";
        echo "<td align='center'>" . date( 'd.m.Y H:i:s', $info[ 'container' ][ 'created' ] ) . "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td>Modified</td>";
        echo "<td align='center'>" . date( 'd.m.Y H:i:s', $info[ 'container' ][ 'edited' ] ) . "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<th colspan=2>Hoster</th>";
        echo "</tr>";
        foreach ( $info[ 'container' ][ 'hoster' ] as $hoster ) {
            echo "<tr><td colspan=2 align=center>" . $hoster . "</td></tr>";
        }
        echo "<tr>";
        echo "<th colspan=2 align=center>Views</th>";
        echo "</tr>";
        echo "<tr>";
        echo "<td>Today</td>";
        echo "<td align=center>" . $info[ 'container' ][ 'views' ][ 'today' ] . "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td>This Week</td>";
        echo "<td align=center>" . $info[ 'container' ][ 'views' ][ 'week' ] . "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td>All-Time</td>";
        echo "<td align=center>" . $info[ 'container' ][ 'views' ][ 'all' ] . "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<th colspan=2 align=center>Links</th>";
        echo "</tr>";
        echo "<tr>";
        echo "<td colspan=2>";
        echo "<textarea rows='10' style='width:100%;resize:none;' readonly>";
        foreach ( $links[ 'container' ][ 'mirror_1' ][ 'links' ] as $link ) {
            echo $link . "\n";
        }
        echo "</textarea>";

        exit();
    }
    ?>
    <center>
        <div class="toolbar">
            <div class="body">
                <span>FileCrypt Dashboard</span>
            </div>
        </div>
        <br>
        <div class="msgBox">TOTAL MUNY: <?php $muny = str_replace(',', '.', $stats['balance']); echo $muny; ?>€</div>
        <br><br>
        <table width="95%" border=1 class="collumns">
            <tr>
                <td style="border: 1px solid #ccc" width="44%" id="view"></td>
                <td>
                    <table width="100%" border=1>
                        <tr>
                            <th>Date</th>
                            <th>Views</th>
                            <th>Muny</th>
                        </tr>
                        <?php
                        foreach ( $stats[ 'statistic' ] as $key => $value ) {
                            $muny = str_replace( ',', '.', $value[ 'balance' ] );
                            echo "<tr>";
                            echo "<td align='center'>" . $key . "</td>";
                            echo "<td align='center'>" . $value[ 'views' ] . "</td>";
                            echo "<td align='center'>" . $muny . "€</td>";
                            echo "</tr>";
                        }
                        ?>
                    </table>
                </td>
                <td style="border: 1px solid #ccc" width="44%" id="muny"></td>
            </tr>
            <tr>
                <td colspan="3">
                    <div id="containers" style="border: 1px solid #ccc">
                        <table width="100%" border=1>
                            <tr>
                                <th>Name</th>
                                <th width="5%">Status</th>
                                <th width="10%">Hoster</th>
                                <th width="5%">Link Count</th>
                                <th width="5%">Size</th>
                                <th width="5%">Today</th>
                                <th width="5%">Week</th>
                                <th width="5%">All-Time</th>
                            </tr>
                            <?php
                            foreach ( $containers[ 'container' ] as $key => $value ) {
                                echo "<tr>";
                                echo "<td>[ <a class='bracketButton' href='" . $_SERVER[ 'REQUEST_URI' ] . "?container=" . $value[ 'id' ] . "'>" . $value[ 'name' ] . "</a> ]</td>";
                                echo "<td><img src='https://filecrypt.cc/Stat/TEXT2/" . $value[ 'statusimg_id' ] . ".png'></td>";
                                echo "<td>";
                                foreach ( $value[ 'hoster' ] as $hoster ) {
                                    echo $hoster;
                                }
                                echo "</td>";
                                echo "<td>" . $value[ 'links' ] . "</td>";
                                echo "<td>" . $value[ 'size_human' ] . "</td>";
                                echo "<td align='center'>" . $value[ 'views' ][ 'today' ] . "</td>";
                                echo "<td align='center'>" . $value[ 'views' ][ 'week' ] . "</td>";
                                echo "<td align='center'>" . $value[ 'views' ][ 'all' ] . "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>
