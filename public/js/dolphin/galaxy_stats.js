/*
 * Author: Alper Kucukural
 * Date: 26 Nov 2014
 * Description:
 **/

$(function() {
    "use strict";

    /* Morris.js Charts */
     var responseTopGalaxyUsers = '';
            $.ajax({ type: "GET",   
                     url: BASE_PATH+"/public/ajax/statquerydb.php",
                     data: { p: "getTopUsers", type:"Galaxy" },
                     async: false,
                     success : function(text)
                     {
                         responseTopGalaxyUsers = text;
                     }
            });

    /*Bar chart*/
    var barTop = new Morris.Bar({
        element: 'top-users-bar-chart',
        resize: true,
        stacked: true,
        data: responseTopGalaxyUsers,
        barColors: ['#00a65a'],
        xkey: 'name',
        ykeys: ['count'],
        labels: ['# of runs'],
        hideHover: 'auto'
    });

    $('.daterange').daterangepicker(
            {
                ranges: {
                    'Today': [moment().subtract('days', 1), moment()],
                    'Yesterday': [moment().subtract('days', 2), moment().subtract('days', 1)],
                    'Last 7 Days': [moment().subtract('days', 6), moment()],
                    'Last 30 Days': [moment().subtract('days', 29), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')],
					'This Year': [moment().startOf('year'), moment().endOf('year')],
					'All Time': [moment('2012-01-01'), moment()],
                },
                startDate: moment('2012-01-01'),
                endDate: moment()
            },
    function(start, end) {
        var responseTopGalaxyUsers = '';
            $.ajax({ type: "GET",
                     url: BASE_PATH+"/public/ajax/statquerydb.php",
                     data: { p: "getTopUsersTime", type:"Galaxy", start:start.format('YYYY-MM-DD'), end:end.format('YYYY-MM-DD') },
                     async: false,
                     success : function(text)
                     {
                         responseTopGalaxyUsers = text;
                     }
            });
        barTop.setData(responseTopGalaxyUsers);

    });

    /* Morris.js Charts */
     var responseDaily = '';
            $.ajax({ type: "GET",   
                     url: BASE_PATH+"/public/ajax/statquerydb.php",
                     data: { p: "getDailyRuns" },
                     async: false,
                     success : function(text)
                     {
                         responseDaily = text;
                     }
            }); 

    /*Bar chart*/
    var bar = new Morris.Bar({
        element: 'daily-bar-chart',
        resize: true,
        data: responseDaily,
        barColors: ['#00a65a'],
        xkey: 'day',
        ykeys: ['countTotal'],
        labels: ['Galaxy Runs'],
        hideHover: 'auto'
    });
    
    
     var userTable = $('#jsontable_User').dataTable();
     
        $.ajax({ type: "GET",   
                     url: BASE_PATH+"/public/ajax/statquerydb.php",
                     data: { p: "getUsersTime", type:"Galaxy"},
                     async: false,
                     success : function(s)
                     {
                        userTable.fnClearTable();
                        for(var i = 0; i < s.length; i++) {
                        userTable.fnAddData([
                        s[i].name,
                        s[i].lab,
                        s[i].count                      
                        ]);
                        } // End For
                     }
            });

     $('.daterange_User').daterangepicker(
            {
                ranges: {
                    'Today': [moment().subtract('days', 1), moment()],
                    'Yesterday': [moment().subtract('days', 2), moment().subtract('days', 1)],
                    'Last 7 Days': [moment().subtract('days', 6), moment()],
                    'Last 30 Days': [moment().subtract('days', 29), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')],
					'This Year': [moment().startOf('year'), moment().endOf('year')],
					'All Time': [moment('2012-01-01'), moment()],
                },
                startDate: moment('2012-01-01'),
                endDate: moment()
            },
            function(start, end) {
            $.ajax({ type: "GET",   
                     url: BASE_PATH+"/public/ajax/statquerydb.php",
                     data: { p: "getUsersTime", type:"Galaxy", start:start.format('YYYY-MM-DD'), end:end.format('YYYY-MM-DD') },
                     async: false,
                     success : function(s)
                     {
                        userTable.fnClearTable();
                        for(var i = 0; i < s.length; i++) {
                        userTable.fnAddData([
                        s[i].name,
                        s[i].lab,
                        s[i].count                      
                        ]);
                        } // End For
                     }
            });

    });
    userTable.fnSort( [ [2,'desc'] ] );
   
    
    var labTable = $('#jsontable_Lab').dataTable();
                $.ajax({ type: "GET",   
                     url: BASE_PATH+"/public/ajax/statquerydb.php",
                     data: { p: "getLabsTime", type:"Galaxy" },
                     async: false,
                     success : function(s)
                     {
                        labTable.fnClearTable();
                        for(var i = 0; i < s.length; i++) {
                        labTable.fnAddData([
                        s[i].lab,
                        s[i].count                      
                        ]);
                        } // End For
                     }
            });
    $('.daterange_Lab').daterangepicker(
            {
                ranges: {
                    'Today': [moment().subtract('days', 1), moment()],
                    'Yesterday': [moment().subtract('days', 2), moment().subtract('days', 1)],
                    'Last 7 Days': [moment().subtract('days', 6), moment()],
                    'Last 30 Days': [moment().subtract('days', 29), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')],
					'This Year': [moment().startOf('year'), moment().endOf('year')],
					'All Time': [moment('2012-01-01'), moment()],
                },
                startDate: moment('2012-01-01'),
                endDate: moment()
            },
            function(start, end) {
            $.ajax({ type: "GET",   
                     url: BASE_PATH+"/public/ajax/statquerydb.php",
                     data: { p: "getLabsTime", type:"Galaxy", start:start.format('YYYY-MM-DD'), end:end.format('YYYY-MM-DD') },
                     async: false,
                     success : function(s)
                     {
                        labTable.fnClearTable();
                        for(var i = 0; i < s.length; i++) {
                        labTable.fnAddData([
                        s[i].lab,
                        s[i].count                      
                        ]);
                        } // End For
                     }
            });
        
    });
    
    labTable.fnSort( [ [1,'desc'] ] );
    
    var toolTable = $('#jsontable_Tool').dataTable();
    $.ajax({ type: "GET",   
                     url: BASE_PATH+"/public/ajax/statquerydb.php",
                     data: { p: "getToolTime", type:"Galaxy"},
                     async: false,
                     success : function(s)
                     {
                        toolTable.fnClearTable();
                        for(var i = 0; i < s.length; i++) {
                        toolTable.fnAddData([
                        s[i].tool_name,
                        s[i].count                      
                        ]);
                        } // End For
                     }
            });
    $('.daterange_Tool').daterangepicker(
            {
                ranges: {
                    'Today': [moment().subtract('days', 1), moment()],
                    'Yesterday': [moment().subtract('days', 2), moment().subtract('days', 1)],
                    'Last 7 Days': [moment().subtract('days', 6), moment()],
                    'Last 30 Days': [moment().subtract('days', 29), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')],
					'This Year': [moment().startOf('year'), moment().endOf('year')],
					'All Time': [moment('2012-01-01'), moment()],
                },
                startDate: moment('2012-01-01'),
                endDate: moment()
            },
    function(start, end) {
            $.ajax({ type: "GET",   
                     url: BASE_PATH+"/public/ajax/statquerydb.php",
                     data: { p: "getToolTime", type:"Galaxy", start:start.format('YYYY-MM-DD'), end:end.format('YYYY-MM-DD') },
                     async: false,
                     success : function(s)
                     {
                        toolTable.fnClearTable();
                        for(var i = 0; i < s.length; i++) {
                        toolTable.fnAddData([
                        s[i].tool_name,
                        s[i].count                      
                        ]);
                        } // End For
                     }
            });
    });
    
    toolTable.fnSort( [ [1,'desc'] ] );
});
