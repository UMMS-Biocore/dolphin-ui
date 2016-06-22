/*
 * Author: Alper Kucukural
 * Date: 26 Nov 2014
 * Description:
 **/

$(function() {
    "use strict";
    /* Morris.js Charts */
     var responseTopDolphinUsers = '';
            $.ajax({ type: "GET",   
                     url: BASE_PATH+"/public/ajax/adminstatquerydb.php",
                     data: { p: "getTopUsers", type:"Dolphin" },
                     async: false,
                     success : function(text)
                     {
                         responseTopDolphinUsers = text;
						 console.log(responseTopDolphinUsers)
                     }
            });

    /*Bar chart*/
    var barTop = new Morris.Bar({
        element: 'top-users-bar-chart',
        resize: true,
        stacked: true,
        data: responseTopDolphinUsers,
        barColors: ['#00a65a'],
        xkey: 'name',
        ykeys: ['count'],
        labels: ['# of runs'],
        hideHover: 'auto'
    });
    $('.daterange').daterangepicker(
            {
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
                    'Last 7 Days': [moment().subtract('days', 6), moment()],
                    'Last 30 Days': [moment().subtract('days', 29), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
                },
                startDate: moment().subtract('days', 29),
                endDate: moment()
            },
    function(start, end) {
        var responseTopDolphinUsers = '';
            $.ajax({ type: "GET",   
                     url: BASE_PATH+"/public/ajax/adminstatquerydb.php",
                     data: { p: "getTopUsersTime", type:"Dolphin", start:start.format('YYYY-MM-DD'), end:end.format('YYYY-MM-DD') },
                     async: false,
                     success : function(text)
                     {
                         responseTopDolphinUsers = text;
                     }
            });
	barTop.setData(responseTopDolphinUsers);

    });


    //The Calender
    $("#calendar").datepicker();

    /* Morris.js Charts */
     var responseDaily = '';
            $.ajax({ type: "GET",   
                     url: BASE_PATH+"/public/ajax/adminstatquerydb.php",
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
        ykeys: ['countDolphin'],
        labels: ['Dolphin Runs'],
        hideHover: 'auto'
    });

	/*##### USER TABLE #####*/
	
     var userTable = $('#jsontable_User').dataTable(); 
     $.ajax({ type: "GET",   
                     url: BASE_PATH+"/public/ajax/adminstatquerydb.php",
                     data: { p: "getUsersTime", type:"Dolphin" },
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
                },
                startDate: moment().subtract('days', 29),
                endDate: moment()
            },
    function(start, end) {
            $.ajax({ type: "GET",   
                     url: BASE_PATH+"/public/ajax/adminstatquerydb.php",
                     data: { p: "getUsersTime", type:"Dolphin", start:start.format('YYYY-MM-DD'), end:end.format('YYYY-MM-DD') },
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
    
	/*##### LAB TABLE #####*/
     
    var labTable = $('#jsontable_Lab').dataTable();
     
     $.ajax({ type: "GET",   
                     url: BASE_PATH+"/public/ajax/adminstatquerydb.php",
                     data: { p: "getLabsTime", type:"Dolphin" },
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
                },
                startDate: moment().subtract('days', 29),
                endDate: moment()
            },
    function(start, end) {
            $.ajax({ type: "GET",   
                     url: BASE_PATH+"/public/ajax/adminstatquerydb.php",
                     data: { p: "getLabsTime", type:"Dolphin", start:start.format('YYYY-MM-DD'), end:end.format('YYYY-MM-DD') },
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
	
	/*##### SERVICE TABLE #####*/
    
    var serviceTable = $('#jsontable_Service').dataTable();
    
    $.ajax({ type: "GET",   
                     url: BASE_PATH+"/public/ajax/adminstatquerydb.php",
                     data: { p: "getServiceTime", type:"Dolphin" },
                     async: false,
                     success : function(s)
                     {
						console.log(s);
                        serviceTable.fnClearTable();
                        for(var i = 0; i < s.length; i++) {
                        serviceTable.fnAddData([
                        s[i].servicename,
                        s[i].count                      
                        ]);
                        } // End For
                     }
            });
    
    $('.daterange_Service').daterangepicker(
            {
                ranges: {
                    'Today': [moment().subtract('days', 1), moment()],
                    'Yesterday': [moment().subtract('days', 2), moment().subtract('days', 1)],
                    'Last 7 Days': [moment().subtract('days', 6), moment()],
                    'Last 30 Days': [moment().subtract('days', 29), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')],
                    'This Year': [moment().startOf('year'), moment().endOf('year')],
                },
                startDate: moment().subtract('days', 29),
                endDate: moment()
            },
    function(start, end) {
            $.ajax({ type: "GET",   
                     url: BASE_PATH+"/public/ajax/adminstatquerydb.php",
                     data: { p: "getServiceTime", type:"Dolphin", start:start.format('YYYY-MM-DD'), end:end.format('YYYY-MM-DD') },
                     async: false,
                     success : function(s)
                     {
						console.log(s);
                        serviceTable.fnClearTable();
                        for(var i = 0; i < s.length; i++) {
                        serviceTable.fnAddData([
                        s[i].servicename,
                        s[i].count                      
                        ]);
                        } // End For
                     }
            });

    });
    
    serviceTable.fnSort( [ [1,'desc'] ] );
    
    /*##### JOB TABLE #####*/
    
    var jobTable = $('#jsontable_Job').dataTable();
    
    $.ajax({ type: "GET",   
                     url: BASE_PATH+"/public/ajax/adminstatquerydb.php",
                     data: { p: "getJobTime", type:"Dolphin" },
                     async: false,
                     success : function(s)
                     {
						console.log(s);
                        jobTable.fnClearTable();
                        for(var i = 0; i < s.length; i++) {
                        jobTable.fnAddData([
                        s[i].servicename,
                        s[i].count                      
                        ]);
                        } // End For
                     }
            });
    
    $('.daterange_Job').daterangepicker(
            {
                ranges: {
                    'Today': [moment().subtract('days', 1), moment()],
                    'Yesterday': [moment().subtract('days', 2), moment().subtract('days', 1)],
                    'Last 7 Days': [moment().subtract('days', 6), moment()],
                    'Last 30 Days': [moment().subtract('days', 29), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')],
                    'This Year': [moment().startOf('year'), moment().endOf('year')],
                },
                startDate: moment().subtract('days', 29),
                endDate: moment()
            },
    function(start, end) {
            $.ajax({ type: "GET",   
                     url: BASE_PATH+"/public/ajax/adminstatquerydb.php",
                     data: { p: "getJobTime", type:"Dolphin", start:start.format('YYYY-MM-DD'), end:end.format('YYYY-MM-DD') },
                     async: false,
                     success : function(s)
                     {
						console.log(s);
                        jobTable.fnClearTable();
                        for(var i = 0; i < s.length; i++) {
                        jobTable.fnAddData([
                        s[i].servicename,
                        s[i].count                      
                        ]);
                        } // End For
                     }
            });

    });
    
    jobTable.fnSort( [ [1,'desc'] ] );
});
