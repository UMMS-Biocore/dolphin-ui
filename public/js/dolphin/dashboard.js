/*
 * Author: Alper Kucukural
 * Date: 4 Jan 2014
 * Description:
 *      This is a js file for the user dashboard
 **/

$(function() {
    "use strict";

    /* jQueryKnob */
    $(".knob").knob();

    //The Calender
    $("#calendar").datepicker();

    /* smallBoxes */
    $.ajax({ type: "GET",   
            url: BASE_PATH+"/public/ajax/dashboardquerydb.php",
            data: { p: "getGalaxyRuns" },
            async: true,
            success : function(text)
            {
                $('#totalGalaxyRunsHeader').html(text);
                $('#totalGalaxyRunsText').html("Total Galaxy runs");
            }
    });
    $.ajax({ type: "GET",   
            url: BASE_PATH+"/public/ajax/dashboardquerydb.php",
            data: { p: "getDolphinRuns" },
            async: true,
            success : function(text)
            {
                $('#totalDolphinRunsHeader').html(text);
                $('#totalDolphinRunsText').html("Total Dolphin runs");
            }
    });
    $.ajax({ type: "GET",   
            url: BASE_PATH+"/public/ajax/dashboardquerydb.php",
            data: { p: "getTotalSamples" },
            async: true,
            success : function(text)
            {
                $('#totalSamplesHeader').html(text);
                $('#totalSamplesText').html("Total Samples");
            }
    });
    $.ajax({ type: "GET",   
            url: BASE_PATH+"/public/ajax/dashboardquerydb.php",
            data: { p: "getTotalJobs" },
            async: true,
            success : function(text)
            {
                $('#totalClusterJobsHeader').html(text);
                $('#totalClusterJobsText').html("Total cluster submissions");
            }
    });
    
    /* Morris.js Charts */
     var responseJobs = '';
            $.ajax({ type: "GET",   
                     url: BASE_PATH+"/public/ajax/dashboardquerydb.php",
                     data: { p: "getMonthlyJobs" },
                     async: false,
                     success : function(text)
                     {
                         responseJobs = text;
                     }
            });
    var area = new Morris.Area({
        element: 'monthly-chart',
        resize: true,
        data: responseJobs,
        xkey: 'month',
        ykeys: ['countSucess', 'countFailed'],
        labels:  ['Success', 'Failed'],
        lineColors: ['#a0d0e0', '#3c8dbc'],
        hideHover: 'auto'
    });
     var response = '';
            $.ajax({ type: "GET",   
                     url: BASE_PATH+"/public/ajax/dashboardquerydb.php",
                     data: { p: "getMonthlyRuns" },
                     async: false,
                     success : function(text)
                     {
                         response = text;
                     }
            });
    var line = new Morris.Line({
        element: 'line-chart',
        resize: true,
        data: response,
        xkey: 'month',
        ykeys: ['countTotal'],
        labels: ['monthly total galaxy runs'],
        lineColors: ['#efefef'],
        lineWidth: 2,
        hideHover: 'auto',
        gridTextColor: "#fff",
        gridStrokeWidth: 0.4,
        pointSize: 4,
        pointStrokeColors: ["#efefef"],
        gridLineColor: "#efefef",
        gridTextFamily: "Open Sans",
        gridTextSize: 10
    });

    //Donut Chart
    var donut = new Morris.Donut({
        element: 'total-chart',
        resize: true,
        colors: ["#3c8dbc", "#f56954"],
        data: [
            {label: "Galaxy Runs", value: 6895},
            {label: "Dolphin Runs", value: 4154},
        ],
        hideHover: 'auto'
    });

    /*Bar chart*/
    var bar = new Morris.Bar({
        element: 'bar-chart',
        resize: true,
        data: response,
        barColors: ['#00a65a', '#f56954'],
        xkey: 'month',
        ykeys: ['countGalaxy', 'countDolphin'],
        labels: ['Galaxy Runs', 'Dolphin Runs'],
        hideHover: 'auto'
    });
    //Fix for charts under tabs
    $('.box ul.nav a').on('shown.bs.tab', function(e) {
        area.redraw();
        donut.redraw();
        Bar.redraw();
    });


    /* BOX REFRESH PLUGIN EXAMPLE (usage with morris charts) */
    $("#loading-example").boxRefresh({
        source: "ajax/dashboard-boxrefresh-demo.php",
        onLoadDone: function(box) {
            bar = new Morris.Bar({
                element: 'bar-chart',
                resize: true,
                data: [
                    {y: '2006', a: 100, b: 90},
                    {y: '2007', a: 75, b: 65},
                    {y: '2008', a: 50, b: 40},
                    {y: '2009', a: 75, b: 65},
                    {y: '2010', a: 50, b: 40},
                    {y: '2011', a: 75, b: 65},
                    {y: '2012', a: 100, b: 90}
                ],
                barColors: ['#00a65a', '#f56954'],
                xkey: 'y',
                ykeys: ['a', 'b'],
                labels: ['CPU', 'DISK'],
                hideHover: 'auto'
            });
        }
    });

});
