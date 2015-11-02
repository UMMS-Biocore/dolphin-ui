/*
 *  * Author: Alper Kucukural
 *   * Date: 4 Jan 2014
 *    * Description:
 *     *      This is a js file for the user dashboard
 *      **/

$(function() {
    "use strict";

    /* jQueryKnob */
    $(".knob").knob();

    /* smallBoxes */
    $.ajax({ type: "GET",   
            url: BASE_PATH+"/public/ajax/dashboardquerydb.php",
            data: { p: "getGalaxyRuns" },
            async: true,
            success : function(text)
            {
                $('#totalGalaxyRunsHeader').html(text.item.galaxy);
                $('#totalGalaxyRunsText').html("Total Galaxy runs");
            }
    });
    $.ajax({ type: "GET",   
            url: BASE_PATH+"/public/ajax/dashboardquerydb.php",
            data: { p: "getDolphinRuns" },
            async: true,
            success : function(text)
            {
                $('#totalDolphinRunsHeader').html(text.item.dolphin);
                $('#totalDolphinRunsText').html("Total Dolphin runs");
            }
    });
    $.ajax({ type: "GET",   
            url: BASE_PATH+"/public/ajax/dashboardquerydb.php",
            data: { p: "getTotalSamples" },
            async: true,
            success : function(text)
            {
                $('#totalSamplesHeader').html(text.item.samples);
                $('#totalSamplesText').html("Total Samples");
            }
    });
    $.ajax({ type: "GET",   
            url: BASE_PATH+"/public/ajax/dashboardquerydb.php",
            data: { p: "getTotalJobs" },
            async: true,
            success : function(text)
            {
                $('#totalClusterJobsHeader').html(text.item.jobs);
                $('#totalClusterJobsText').html("Total cluster submissions");
            }
    });
    
    /* Morris.js Charts */
     var responseJobs = '';
            $.ajax({ type: "GET",   
                     url: BASE_PATH+"/public/ajax/admindashboardquerydb.php",
                     data: { p: "getMonthlyJobs" },
                     async: true,
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
                     url: BASE_PATH+"/public/ajax/admindashboardquerydb.php",
                     data: { p: "getMonthlyRuns" },
                     async: true,
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

});
