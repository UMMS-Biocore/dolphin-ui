
var wkey;

function populateFileList(){
  var tsv_files = [];
  wkey = getWKey();
  $.ajax({ type: "GET",
			url: BASE_PATH + "/public/ajax/ngsquerydb.php",
            data: { p: "getTSVFileList", wkey:wkey },
			async: false,
			success : function(s)
              {
                for (var x = 0; x < s.length; x++){
                  var opt = createElement('option', ['value'], [s[x].file]);
                  opt.innerHTML = s[x].file;
                  document.getElementById('source_1').appendChild(opt);
                }
              }
		});
  /*
  $.ajax({ type: "GET",
			url: BASE_PATH + "/public/ajax/ngsquerydb.php",
            data: { p: "getCustomTSV" },
			async: false,
			success : function(s)
              {
				console.log(s);
                for (var x = 0; x < s.length; x++){
                  var opt = createElement('option', ['value'], [s[x].file]);
                  opt.innerHTML = "Created || " +s[x].name;
                  document.getElementById('source_1').appendChild(opt);
                }
              }
		});
  */
	$('.panel').overflow = scroll;
}

function checkGeneratedTable(){
  var table_file = '';
  $.ajax({ type: "GET",
			url: BASE_PATH + "/public/ajax/sessionrequests.php",
            data: { p: "getPlotToggle"},
			async: false,
			success : function(s)
              {
                table_file = s;
              }
		});
  console.log(table_file);
  if (table_file != '') {
	document.getElementById('source_1').innerHTML = '';
	var opt2 = createElement('option', ['value'], ['input']);
	opt2.innerHTML = 'Generated File';
    document.getElementById('source_1').appendChild(opt2);
	
	console.log(BASE_PATH + '/public/tmp/files/' + table_file);
	document.getElementById('source').innerHTML = BASE_PATH + '/public/tmp/files/' + table_file;
  }
  $.ajax({ type: "GET",
		  url: BASE_PATH+"/public/ajax/sessionrequests.php",
		  data: { p: "setPlotToggle", type: '', file: '' },
		  async: false,
		  success : function(s)
		  {
		  }
  });
}

populateFileList();
checkGeneratedTable();

(function (S,$) {
    $("#source_1").on("change",
    function(d){
    if (this.value=="input") {
        $("#source_input").prop("hidden",false)
        getSrc()
    }else{ 
        $("#source_input").prop("hidden",true)
        getSrc()
    }
})
window.src_handle_sheet_index=function(data){
	var s=""
    data.table.rows.forEach(function(d) {
		s+="<option value='"+d.c[1].v+"' timen='"+d.c[2].v+"'>"+d.c[0].v+"</option>";
	})
    $("#gsheet").html(s)
            
}
//S.gsheet.query("select *","1KfB5nVwLB9B095l82oBA_L7Hg7keBcUHx4pp-sU3ONg","window.src_handle_sheet_index");
        
        
$("#gsheet").on("change",function(d) {getSrc()});
window.handle_sheet_index=function(data){
    var s=""
    data.table.cols.forEach(function(d,i) {
		s+="<option value="+i+">"+d.label+"</option>";
    })
	/*
	$("#xi").html(s)
	$("#yi").html(s)
	$("#zi").html(s)
	$("#selected_cols").html(s)
	$("#selected_cols_2").html(s)
	$("#cx").html(s)
	*/
	$(".col_input").html(s)
	/*
	$("#gsheet").html(s)
	window.view();
	*/
    d3.selectAll(".loading").property("hidden",true);
    if($("#source_option").val()=="input" || $("#source_option").val()=="local_file"){
        $("#xi").val(0)
        $("#yi").val(0)
        $("#zi").val(0)
    }else{
		$("#xi").html(s)
        $("#yi").html(s)
        $("#zi").html(s)
        $("#selected_cols").html(s)
        $("#xi").val(2)
        $("#yi").val(3)
        $("#zi").val(3)
		$("#zi_type").val('value_threshold')
    }
    window.data=data;
    window.view();
}
// S.gsheet.query("select *","1KfB5nVwLB9B095l82oBA_L7Hg7keBcUHx4pp-sU3ONg","window.handle_sheet_index");
var files;
function prepareUpload(event) {
    flles=event.target.files;
}
var getSrc=function(){
    var src;
    d3.selectAll(".loading").property("hidden",false);
    d3.selectAll(".col_input").selectAll("option").remove();
    d3.selectAll(".panel").selectAll("svg").selectAll("*").remove();
    
    if($("#source_1").val()=="input"){
		src= document.getElementById("source").value || document.getElementById("source").placeholder;
		console.log(src);
		var re=/^http/;
		var OK=re.exec(src);
		if(!OK){
			S.gsheet.query("select *", src, "window.handle_sheet_index")   
		}else{
			d3.json(src,function(error,data){
				window.handle_sheet_index(data);})
		}
	}else{
	  if (document.getElementById("source_1").value.indexOf('.json2') > -1) {
		src= document.getElementById("source_1").value || document.getElementById("source").placeholder;
		src = BASE_PATH+'/public/tmp/files/'+src;
	  }else{
		src= document.getElementById("source_1").value || document.getElementById("source").placeholder;
		src = BASE_PATH+'/public/pub/'+wkey+'/'+src;
		src = API_PATH + "/public/api/?format=json2&source="+src
	  }
	  console.log(src);
	  var re=/^http/;
	  var OK=re.exec(src);
	  if(!OK){
		  S.gsheet.query("select *", src, "window.handle_sheet_index")   
	  }else{
		  d3.json(src,function(error,data){
			  window.handle_sheet_index(data);
		  })
		  
	  }
	}
}
getSrc();
var reset_zi_scale=function(d) {
	var a=[];
		  
	data.table.rows.forEach(function (row) {
		a.push(parseFloat(row.c[d].v))
	})
		   
	d3.select("#zi_value_cutoff").attr("max",Math.max.apply(null,a) || 1);
	d3.select("#zi_value_cutoff").attr("min",Math.min.apply(null,a) || 0);
			
}
$("#source").on("change",function(d) {getSrc();view()});
$("#xi").on("change",function(d) {view()});
$("#yi").on("change",function(d) {view()});
$("#zi").on("change",function(d) {
	if($("#zi_type").val()=="value") {
		reset_zi_scale($("#zi").val())
	}
	view()
});
$("#xi_scale").on("change",function(d){view()});
$("#yi_scale").on("change",function(d){view()});
$("#pseudocount").on("change",function(d){view()});
$("#zi_type").on("change",function(d) {console.log(d);
	if($("#zi_type").val()=="value_threshold" ) {
		$("#zi_value_cutoff").prop("disabled",false)
		$("#zi_value_cutoff").prop("hidden",false)
		$("#zi_value").prop("disabled",false)
		$("#zi_value").prop("hidden",false)
		reset_zi_scale($("#zi").val())
	}else{
		$("#zi_value_cutoff").prop("disabled",true)
		$("#zi_value_cutoff").prop("hidden",true)
		$("#zi_value").prop("disabled",true)
		$("#zi_value").prop("hidden",true)
		if($("#zi_type").val()=="value") {}
	}
	view()
});
$("#zi_value_cutoff").on("change",function(d) { 
	$("#zi_value").val($("#zi_value_cutoff").val());
	view()})
$("#zi_value").on("change",function(d) {
	$("#zi_value_cutoff").val($("#zi_value").val());
	view();
})
		var panels=[{"id":"svg","title":"Scatter plot"},{"id":"svg3","title":"Barplot monitor"},{"id":"svg1","title":"Heatmap monitor"}];
        var panel_divs=d3.select("#canvas").selectAll("div").data(panels).enter().append("div").attr("class","panel panel-primary col-md-6")
        panel_divs.append("div").attr("class","panel-heading").append("h2").attr("class","panel-title").text(function(d) {return d.title});
        panel_divs.append("div").property("hidden",true).attr("class","loading").append("text").text("Loading Data ...");
        panel_divs.append("div").attr("class","panel-body").selectAll("svg").data(function(d){console.log(d);return [d]}).enter().append("svg").attr("id",function(d) {return d.id}).attr("height", 500).attr("width", 500);
        var svg=d3.select("#svg");
        var xi, yi, zi;
        
		var scatter_par = document.getElementById('canvas').firstChild.setAttribute("class", "panel panel-primary col-md-12");
		
        window.view = function () {
			xi=$("#xi").val();
			yi=$("#yi").val();
			zi=$("#zi").val();
			window.plotHandler();
        }
        var iBrushHandler = function(d) {
            $("#gene_source").html("Selected Region");
            brushHandler(d);
        }
        var brushHandler = function (d) {

            var n = [];
            var samples=[]
            var td=[]
            var slt=$("#selected_cols").val();
            var colnames=[]
            var rawdata=[]
            slt.forEach(function(e)
            {
                colnames.push(window.data.table.cols[parseInt(e)].label)
            })
            d.forEach(function (d1) {
                var a = window.data.table.rows[d1].c;
                n.push(a[0].v)
                var l=[]
                slt.forEach( function(e) {
                    l.push(parseFloat(a[parseInt(e)].v))
                })
                samples.push(norm(l));
                rawdata.push(l);
                
                td.push(a);
                
            })
            $("#console").html("Number: <b>"+ n.length+"</b><br><textarea id='mytextarea' rows='24' class='form-control'>" + n.join() + "</textarea>");
            var m = {}
            d.forEach(function (d0) {
                m[d0] = 1
            });
            window.json.modules[0].dots.attr("r",2.0)
            window.json.modules[0].dots.filter(function (d0, i) {
                return i in m
            }).attr("r", 3.0)
           if($("#heatmap_mode").val()=="proportion"){ 
            heatmap(samples,"#heatmap",n,colnames,rawdata);
           } else if($("#heatmap_mode").val()=="foldchange"){
            heatmapFoldChange(samples,"#heatmap",n,colnames,rawdata);
	  } 
            d3.select("#svg1").attr("height",n.length*5+100);
            
            if( $("#cmp_with").is(":checked")) {
            (function(d) {
                    var slt=$("#selected_cols_2").val();
                    var colnames2=[]
                    var rawdata2=[]
                    var n2=[]
                    var samples2=[]
                    slt.forEach(function(e)
                    {
                    colnames2.push(window.data.table.cols[parseInt(e)].label)
                    })
                d.forEach(function (d1) {
                var a = window.data.table.rows[d1].c;
                n2.push(a[0].v)
                var l2=[]
                slt.forEach( function(e) {
                    l2.push(parseFloat(a[parseInt(e)].v))
                })
                samples2.push(norm(l2));
                rawdata2.push(l2);
            })
 	if($("#heatmap_mode").val()=="proportion"){ 
            heatmap(samples2,"#heatmap",n2,colnames2,rawdata2);
           } else if($("#heatmap_mode").val()=="foldchange"){
            heatmapFoldChange(samples2,"#heatmap",n2,colnames2,rawdata2);
	  } 
            
            })(d)
        }

			table(td,"#output_table_tbody")
        }
		var _render_table=function(d,el)
        {
            var sl=d3.select(el).selectAll(".td").data(d)
            
            sl.selectAll("td")
                    .data(function(d0,i){return d0})
                    .text(function(d,i) {if (i==2 || i==3 ) {return Math.round(d.v*100)/100} else {return d.v}})
            
            sl.enter()
                    .append("tr")
                    .attr("class","td")
                    .selectAll("td")
                    .data(function(d0,i){return d0})
                    .enter()
                    .append("td")
                    .text(function(d,i){if (i==2 || i==3 ) {return Math.round(d.v*100)/100} else {return d.v}})
            sl.exit().remove();
            
            var $table = $('#output_table'),
                    $headCells = $table.find('thead tr:first').children(),
                    headColWidth,bodyColWidth,colWidth,
                    $bodyCells = $table.find('tbody tr:first').children();
            
// Adjust the width of thead cells when window resizes
            $(window).resize(function() {
                // Get the tbody columns width array
                headColWidth = $headCells.map(function() {
                    return $(this).width();
                }).get();
                bodyColWidth = $bodyCells.map(function() {
                    return $(this).width();
                }).get();
                colWidth=[]
                var i;
                for (i in headColWidth) {
                    if (headColWidth[i] > bodyColWidth[i]) {
                        colWidth.push(headColWidth[i])
                    }
                    else
                    {
                        colWidth.push(bodyColWidth[i])
                    }
                }
                var s=0; for(i in colWidth) {s+=colWidth[i]};
                // Set the width of tbody columns
                $table.find('tbody tr').children().each(function(i, v) {
                    $(v).width(colWidth[i%colWidth.length]);

                });
                $table.find('thead tr').children().each(function(i, v) {
                           $(v).width(colWidth[i]);
                        } );
                // $table.setAttribute("width",s+50)

                ;
            }).resize(); // Trigger resize handler
            
        }
        var table=function(d,el) {
            
            if(d.length>100)
            {
                var sl=d3.select(el).selectAll(".td").data([]).exit().remove();
                _render_table(d.slice(0,100),el);
                var a=d3.select("#table").selectAll("button").data(["Show entire table"]).enter().append("button").attr("value",function(d0){return d0}).attr("class", "btn btn-primary").text(function(d0){return d0});
                a.data(["show_table"]).on("click",function(d0){_render_table(d,el);d3.select(this).remove();})
            }
            else
            {
                d3.select("#table").selectAll("button").remove();
				console.log(d);
				console.log (el);
                _render_table(d,el);
            }
        }
        var mouseoverHandler = function (d) {

            var a=data.table.rows[d].c
            var slt=$("#selected_cols").val();
            var colnames=[];
            var values=[]
            slt.forEach(function(e)
            {
                colnames.push(window.data.table.cols[parseInt(e)].label)
                values.push(a[parseInt(e)].v)
            })
            
            bar(values,"#barplot1",colnames,a[0].v);
            
            
        }
        var legendMouseoverHandler = function (array, name) {
            $("#gene_source").html("Group")
            brushHandler(array)
        }
        var legendMouseoutHandler = function (array, name) {
            var m = {}
            array.forEach(function (d) {
                m[d] = 1
            });
        }
        var legendClickHandler = function(array,name) {
            console.log(array);
        }

        window.plotHandler = function () {
            //window.data = data;
            svg.selectAll("*").remove();
	    var xlabel,ylabel;
	    if (xi.length==1) {xlabel=data.table.cols[xi[0]].label}
		else { xlabel="average of selected X axis"}
	    if (yi.length==1) {ylabel=data.table.cols[yi[0]].label}
		else { ylabel="average of selected Y axis"}
            window.json = {
                "type": "panel",
                "el": svg,
                "border": 0,
                "height": 450,
                "width": 450,
                "x": 40,
                "y": 0,
                "modules": [ {
                    "type": "scatter",
                    "x": 20,
                    "y": 50,
                    "height": 400,
                    "width": 400,
                    "scale": 0.9,
                    "data": [],
                    "xlabel":xlabel,
                    "ylabel":ylabel,    
                    "zi_type":$("#zi_type").val(),

                    "brushCb": iBrushHandler,
                    "mouseoverCb": mouseoverHandler,
                    "legendMouseoverCb": legendMouseoverHandler,
                    "legendMouseoutCb": legendMouseoutHandler
                }

                ]

            }
            if($("#xi_scale").is(":checked")) {
                    window.json.modules[0].xlabel+="(log scale)"
                }

            if($("#yi_scale").is(":checked")) {
                    window.json.modules[0].ylabel+="(log scale)"
            }
            data.table.rows.forEach(function (d) {
                var x,y;
		var pseudo= parseFloat($("#pseudocount").val() || document.getElementById("pseudocount").placeholder)
                if($("#xi_scale").is(":checked")) {
		    x=0.0
		    xi.forEach(function(i0) {
                    x+=Math.log( parseFloat(d.c[i0].v) + pseudo);
        	   } ) 
		   x=x/xi.length      
		}
                else
                {
		   x=0.0
		   xi.forEach(function(i0){
			x+=parseFloat(d.c[i0].v)
			})
                    x=x/xi.length
                }
                if($("#yi_scale").is(":checked")) {
		    y=0.0
		    yi.forEach(function(i0) {
                    y+=Math.log( parseFloat(d.c[i0].v) + pseudo)
                   })
		   y=y/yi.length
		}
                else
                {
		    y=0.0
	   	    yi.forEach(function(i0){
			y+=parseFloat(d.c[i0].v)
			})
                    y=y/yi.length
                    y=d.c[yi].v
                }

                if ($("#zi_type").val()=="category" || $("#zi_type").val()=="value" ||$("#zi_type").val()=="logvalue")
                {
                window.json.modules[0].data.push({
                    "x": x,
                    "y": y,
                    "z": d.c[zi].v
                })
                }
                else if ($("#zi_type").val()=="value_threshold") {
                var z;
                if(d.c[zi].v<parseFloat($("#zi_value_cutoff").val())) {z="S"}
                else {z="L"}
                window.json.modules[0].data.push({
                    "x": x,
                    "y": y,
                    "z": z
                })
                }

            })
			console.log(json.modules[0].data);
			//var labels=[[1,data.table.cols[1].label],[4,data.table.cols[4].label]];
            // d3.select("#zi").selectAll("option").data(labels).attr("value",function(d){return d[0]}).text(function(d){return d[1]});
			console.log(data.table.cols);
            d3.select("#output_table_thead")
					.remove();
            d3.select("#output_table_tbody")
					.remove();
					
			d3.select("#output_table")
					.append("thead")
					.attr("id","output_table_thead")
					.attr("class","thead")
					.append("tr")
					.attr("class","th")
					.selectAll("th")
					.data(data.table.cols)
					.enter()
					.append("th")
					.text(function(d){console.log(d);return d.label});
			d3.select("#output_table").append("tbody").attr("id","output_table_tbody");
			
            var figure=S.plot_json(window.json);
            
            d3.select("#svg3").append("g").attr("id","barplot1").attr("transform","translate(50,40)");
            d3.select("#svg1").append("g").attr("id","heatmap").attr("transform","translate(50,40)");
            d3.select("#svg1").append("g").attr("id","heatmap2").attr("transform","translate(150,40)");
            //figure.el.append("g").attr("transform","translate(50,20)").append("text").text("Scatter Plot")
            //d3.select("#svg3").append("g").attr("transform","translate(50,20)").append("text").text("Interactive Gene Pattern Barplot")
            //d3.select("#svg1").append("g").attr("transform","translate(50,20)").append("text").text("Interactive Gene Pattern Heatmap")
            /*
            if(data.table.cols[4].label!="transcript") {
                //draw_group_cmp();
            }*/
        }
   // MODULE FUNCTIONS
    /**
     *
     * @param data           google sheet table
     * @param el             string id or d3.select(el)
     * @param attributes     {width,height,x,y,colA,colB,colName}
     * @param callback       return list of index. { brushHanlder e.g }
     */
    var contingency_table= function(el,attributes,callback) {
        if (typeof el == "string") {
            el = d3.select(el)
        }
        var data=window.data;
        el.select("g").remove();
        var x=attributes.x || 0;
        var y=attributes.y || 0;
        var svg1=el.append("g").attr("transform","translate("+x+","+y+")");
        /*
        var getColIndex = function(s) {
            var i0=0;
            data.table.cols.forEach(function(d,i) {if(d.id== s.toUpperCase()) {i0=i;}})
            return i0;
        }*/
        var getColToArray = function(s) {
            var i0 = s;
            var a = [];
            data.table.rows.forEach(function(d,i) {a.push(d.c[i0].v)})
            return a;
        }
        
        var colA=attributes.colA || 1
        var colB=attributes.colB || 2
        var colName=attributes.colName || 0
        var a=getColToArray(colA);
        var b=getColToArray(colB);
        var names=getColToArray(colName);
        var mapA={};
        var mapB={};
        var mapAB={};
        var width= attributes.width || 300;
        var height=attributes.height || 300;
        var color=d3.scale.category10();
        console.log(a);
        console.log(b);
        console.log(names);
        for(var i=0;i<names.length;i++) {
            var n=i;
            var A=a[i];
            var B=b[i];
            if (mapA[A]) {mapA[A].push(n)} else (mapA[A]=[n]);
            if (mapB[B]) {mapB[B].push(n)} else (mapB[B]=[n]);
            if (!mapAB[A]) {
                mapAB[A]={}
            }
            if (!mapAB[A][B]) {mapAB[A][B]=[]}
            mapAB[A][B].push(n)
        }
        console.log(mapA)
        console.log(mapB)
        console.log(mapAB)

        var fa=svg1.append("g")
                .attr("transform","translate(20,20)")
                .attr("id","aBar");
        var fb=svg1.append("g")
                .attr("transform","translate(20,20) rotate(90)")
                .attr("id","bBar");
        var fab=svg1.append("g")
                .attr("transform","translate(20,20)")
                .attr("id","abBox");
        var fgrid=svg1.append("g")
                .attr("transform","translate(20,20)")
                .attr("id","grid");
        var fvenn=svg1.append("g").attr("transform","translate("+width/5+","+(height+100)+")").attr("id","fvenn")
        var scale_a=d3.scale.linear().domain([0,names.length]).range([0,width]);
        var scale_b=d3.scale.linear().domain([0,names.length]).range([0,height]);
        var ds=0;
        //var array_a=mapA.keys();
        var list_a=[], list_b=[];
        var x_a=[];
        var grid=["grey","aliceblue"];
        for (var k in mapA) list_a.push(k);
        for (var k in mapB) list_b.push(k);
        list_a.sort();
        list_b.sort();
        var S= a.length;
        //var len_a= $.map(list_a,function(d,i){return mapA[d].length});
        //console.log(len_a);
         /*
         calculated chi square.
          */
        //var S=names.length;
        var SA=[];
        var SB=[]
        var SAB=[]
        for(var i in list_a) {SA.push(mapA[list_a[i]].length)}

        for(var i in list_b) {
            SB.push(mapB[list_b[i]].length)
        }

       console.log(SA,SB);
        for(var i in list_a) {
            SAB.push([])
            for(var j in list_b){
                SAB[i].push([]);
                if (mapAB[list_a[i]] && mapAB[list_a[i]][list_b[j]]) {SAB[i].push(mapAB[list_a[i]][list_b[j]].length)}

            }
        }
        var freedom_degree=(SA.length-1)*(SB.length-1);
        var chi=0.0;
        for(var i in SA) {
            for(var j in SB) {
                var Eij=SA[i]/S*SB[j]   ///S*S
                if (Eij>0) {
                    var d0=SAB[i][j]-Eij
                    chi+=d0*d0/Eij
                }
            }
        }
        console.log(chi,freedom_degree);
        /* end of this block.
         */

        var fchi=svg1.append("g").attr("transform","translate("+width/5+","+(height+50)+")").attr("id","fchi")
        fchi.selectAll("text").data([[chi,freedom_degree]]).enter().append("text")
        fchi.selectAll("text").data([[chi,freedom_degree]]).text(function(d0){return " Chi Square: "+Math.round(d0[0]*1000)/1000+", Degree: "+d0[1]})




        var rect_a=fa.selectAll("rect").data(list_a).enter().append("rect")
                .attr("x",function(d,i) {x_a.push(ds);ds+=scale_a(mapA[d].length);return ds-scale_a(mapA[d].length)})
                .attr("y","-7")
                .attr("width",function(d,i){console.log(d);return scale_a(mapA[d].length)})
                .attr("height",function(d,i){return 5})
                .attr("fill",function(d,i) {return color(i)})
                .on("click", function(d,i) {console.log(d);var s=mapA[d];
                    //$("#gene_source").html("X Group "+d);
                    fvenn.selectAll("text").data([[mapA[d].length]]).enter().append("text")
                    fvenn.selectAll("text").data([[mapA[d].length]]).text(function(d0){return " A Group "+d+":"+d0});
                    callback(s)})

        fgrid.append("g").selectAll(".y").data(x_a).enter().append("rect").attr("class","y")
                .attr("x",function(d,i) {return x_a[i]})
                .attr("y",0)
                .attr("height",height)
                .attr("width",1)
                .attr("fill","grey")
                .attr("opacity",0.3)
        ds=0;

        var x_b=[];

        var rect_b=fb.selectAll("rect").data(list_b).enter().append("rect")
                .attr("x",function(d,i) {x_b.push(ds);ds+=scale_a(mapB[d].length);return ds-scale_b(mapB[d].length)})
                .attr("y","2")
                .attr("width",function(d,i){console.log(d);return scale_b(mapB[d].length)})
                .attr("height",function(d,i){return 5})
                .attr("fill",function(d,i) {return color(i)})
                .on("click", function(d,i) {console.log(d);var s=mapB[d];
                    //$("#gene_source").html("Y Group "+d);
                    fvenn.selectAll("text").data([[mapB[d].length]]).enter().append("text")
                    fvenn.selectAll("text").data([[mapB[d].length]]).text(function(d0){return " B Group "+d+":"+d0});
                    callback(s)})
        fgrid.append("g").selectAll(".x").data(x_b).enter().append("rect").attr("class","x")
                .attr("y",function(d,i) {return x_b[i]})
                .attr("x",0)
                .attr("width",width)
                .attr("height",1)
                .attr("fill","grey")
                .attr("opacity",0.3)
        var data_ab=[];
        var heatscale=d3.scale.linear().range(["white","red"]).domain([0,1]);
        for (var ia in list_a)
        {

            for (var ib in list_b)
            {
                if (mapAB[list_a[ia]][list_b[ib]])
                {
                    data_ab.push([ia, ib, list_a[ia], list_b[ib], mapAB[list_a[ia]][list_b[ib]]])
                }
            }

        }

        console.log(data_ab);

        var rect_ab=fab.selectAll("rect").data(data_ab).enter().append("rect")
                .attr("x",function(d,i) {return x_a[d[0]]})
                .attr("y",function(d,i) { return x_b[d[1]]})
                .attr("width",function(d,i){return scale_a(d[4].length)})
                .attr("height",function(d,i){return scale_b(d[4].length)})
                .attr("fill",function(d,i) {var r=2*d[4].length/(mapA[d[2]].length+mapB[d[3]].length);return heatscale(r);})
                .attr("opacity",0.9)
                .on("mouseover",function(d,i){d3.select(this).attr("opacity",1.0)})
                .on("click", function(d,i) {

                    //$("#gene_source").html("Overlap X Group "+d[2]+" vs Y Group "+d[3])
                    var a=mapA[d[2]].length
                    var b=mapB[d[3]].length
                    var c=d[4].length;
                    console.log(c,a,b);
                    fvenn.selectAll("text").data([[c,a,b]]).enter().append("text")
                    fvenn.selectAll("text").data([[c,a,b]]).text(function(d0){return "Overlap:"+d0[0]+" A Group "+d[2]+":"+d0[1]+" B Group "+d[3]+":"+d0[2]});

                    var X11= c,X12=a-c,X21=b-c,X22=S-a-b+c;
                    var E11 = a*b/S, E12=(S-b)*a/S,E21=(S-a)*b/S,E22=(S-a)*(S-b)/S
                    var local_chi=(X11-E11)*(X11-E11)/E11+(X12-E12)*(X12-E12)/E12+(X21-E21)*(X21-E21)/E21+(X22-E22)*(X22-E22)/E22
                    console.log(X11,X12,X21,X22);
                    if(X11-E11<0) {local_chi=-local_chi;}
                    fvenn.selectAll("text").data([[c,a,b]]).enter().append("text")
                    fvenn.selectAll("text").data([[c,a,b]]).text(function(d0){
                        return "Overlap:"+d0[0]+" X Group "+d[2]+":"+d0[1]+" Y Group "+d[3]+":"+d0[2]+" Chi:"+Math.round(local_chi*1000)/1000;

                    });
                    callback(d[4]);
                });
    }
 

        $("#reset_gene").on("click",function() {window.json.modules[0].dots.attr("r",2.0);$("#mytextarea").val("")})
	
        $("#highlight_gene").on("click",
                function() {
                    var str = document.getElementById("mytextarea").value
                    var a = str.replace(/\n/g, ",").split(",")
                    var map = {};
                    var samples = nameToIndex(a);
                    $("#gene_source").html("Submitted") ;
                    brushHandler(samples)
                })
        var nameToIndex= function(a) {
            var map = {};
            var samples = [];
            for (var i = 0; i < a.length; i += 1) {

                map[a[i].toUpperCase()] = 1
            }
            window.json.modules[0].dots.each(function (d, i) {
                if (window.data.table.rows[i].c[0].v.toUpperCase() in map) {

                    samples.push(i);
                }
            });
            return samples
        }
 var ct_callback=function(d) {
     iBrushHandler(d);
 }
 var render_ct_func=function() {
           var colA=$("#cx").val()
           var colB=$("#cy").val()
           console.log("in render ct");
           contingency_table("#contingency_table",{"colA":colA,"colB":colB},ct_callback);
       } 
   $("#render_ct").on("click",function(e) {render_ct_func()});
panels.forEach(function(d) {
d3.select("#"+d.id+"_options").append("tr").append("td").append("input").attr("class","button").attr("type","button").attr("value","download").on("click", function()
      {
        var svg_xml= (new XMLSerializer).serializeToString(d3.select("#"+d.id)[0][0])
        var form=$('<form>',{"action":"../cgi-bin/download.py","method":"post"})
        .append($('<input>',{"name":"data","value":svg_xml,"type":"hidden"}))
        .append($('<input>',{"name":"output_format","value":"pdf","type":"hidden"}))
        .append($('<input>',{"name":"width","value":d3.select("#"+d.id).attr("width"),"type":"hidden"}))
        .append($('<input>',{"name":"height","value":d3.select("#"+d.id).attr("height"),"type":"hidden"}))
        .append($('<input>',{"name":"filename","value":"download","type":"hidden"}))
        form.submit()
      })
     } 
  );

    }(snowflake,jQuery))



    // functions need to be integrated.
    function iqr(k) {
        return function(d, i) {
            var q1 = d.quartiles[0],
                    q3 = d.quartiles[2],
                    iqr = (q3 - q1) * k,
                    i = -1,
                    j = d.length;
            while (d[++i] < q1 - iqr);
            while (d[--j] > q3 + iqr);
            return [i, j];
        };
    }




    function bar(ldata,el,cols,genename) {
        var height=200
        var width=200
        var barwidth=20
        var gap=2
        var ld=[]
        ldata.forEach(function(d){ld.push(parseFloat(d))});
        ldata=ld;
        var y=d3.scale.linear()
                .domain([0, d3.max(ldata)])
                .range([0, height]);
       var y2=d3.scale.linear()
                .domain([0, d3.max(ldata)])
                .range([height, 0]);
        var yAxis = d3.svg.axis()
                       .scale(y2)
                       .orient("left");
       
        console.log(y);
        console.log(ldata);
        d3.select(el).selectAll('g').remove();
        var text=d3.select(el).append("g").attr("class","txt").attr("transform","translate(5,10)")
        text.append("text").text(genename)
        var svg = d3.select(el).append("g")
                .attr('width', width)
                .attr('height', height)
                .attr('class', 'chart')
                .attr("transform","translate(5,50)");

        svg.selectAll('.chart')
                .data(ldata)
                .enter().append('rect')
                .attr('class', 'bar')
                .attr('x', function(d, i) { return i * (barwidth+gap) })
                .attr('y', function(d, i) { return height-y(d) })
                .attr('width', function(d) { return barwidth})
                .attr('height', function(d) {  return y(d);})
                .style("fill","blue").append("title").text(function(d,i) {return cols[i]+":"+d});
                ;
         svg.append("g").attr("transform","translate(-5,0)")
      .attr("class", "y axis")
      .call(yAxis)
    .append("text")
      .attr("class", "label")
      .attr("transform", "rotate(-90)")
      .attr("y", 6)
      .attr("dy", ".71em")
          
        var text2=d3.select(el).append("g").attr("class","txt").attr("transform","translate(5,255)")
        text2.selectAll(".marker").data(cols)
        .enter().append("g").attr("transform",function(d, i) { return "translate("+(i * (barwidth+gap)+barwidth/2) +",0)"}).attr("class","marker").append("text").attr("transform","rotate(75)").text(function(d){return d});
    }
 function heatmapFoldChange(ldata,el,names,colnames,raw)
    {
	var psuedo=1.0
        var colorscale=d3.scale.linear().range(["white", "red"]).domain([0,20]);
        var g=d3.select(el).selectAll("g").data(ldata)
        g.selectAll(".tile").data(function(d,i){
                    var a=[]
                    d.forEach(function(d0,i0) {a.push([i,d0])})
                    return a;
                   })
                .style("fill", function(d,i0) { return colorscale((raw[d[0]][i0]+psuedo)/(raw[d[0]][0]+psuedo)); })
                 .on("mouseover",function(d,i0){
                     bar(raw[d[0]],"#barplot1",colnames,names[d[0]]);
                })
                .select("title").text(function(d,i0){console.log("change",names[d[0]]);return names[d[0]]+" "+colnames[i0]+" "+raw[d[0]][i0];})
               ;
      
        g.enter().append("g")
                .attr("transform",function(d,i) {return "translate(0,"+i*5+")"})
                .selectAll(".tile")
                .data(function(d,i){
                    var a=[]
                    d.forEach(function(d0,i0) {a.push([i,d0])})
                    return a;
                   })
                .enter()
                .append("rect")
                .attr("class", "tile")
                .attr("x",function(d,i) {return i*10;})
                .attr("y",0)
                .attr("width", 10)
                .attr("height",5)
                .style("fill", function(d,i0) { return colorscale((raw[d[0]][i0]+psuedo)/(raw[d[0]][0]+psuedo)); })
                .on("mouseover",function(d,i0){
                     bar(raw[d[0]],"#barplot1",colnames,names[d[0]]);
                })
                .append("title").text(function(d,i0){console.log("init",names[d[0]]);return names[d[0]]+" "+colnames[i0]+" "+raw[d[0]][i0];})

         ;
        g.exit().remove()
    }

    function heatmap(ldata,el,names,colnames,raw)
    {
        var colorscale=d3.scale.linear().range(["white", "red"]).domain([0,1]);

        var g=d3.select(el).selectAll("g").data(ldata)
        g.selectAll(".tile").data(function(d,i){
                    var a=[]
                    d.forEach(function(d0,i0) {a.push([i,d0])})
                    return a;
                   })
                .style("fill", function(d) { return colorscale(d[1]); })
                 .on("mouseover",function(d,i0){
                     bar(raw[d[0]],"#barplot1",colnames,names[d[0]]);
                })
                .select("title").text(function(d,i0){console.log("change",names[d[0]]);return names[d[0]]+" "+colnames[i0]+" "+raw[d[0]][i0];})
               ;
      
        g.enter().append("g")
                .attr("transform",function(d,i) {return "translate(0,"+i*5+")"})
                .selectAll(".tile")
                .data(function(d,i){
                    var a=[]
                    d.forEach(function(d0,i0) {a.push([i,d0])})
                    return a;
                   })
                .enter()
                .append("rect")
                .attr("class", "tile")
                .attr("x",function(d,i) {return i*10;})
                .attr("y",0)
                .attr("width", 10)
                .attr("height",5)
                .style("fill", function(d) { return colorscale(d[1]); })
                .on("mouseover",function(d,i0){
                     bar(raw[d[0]],"#barplot1",colnames,names[d[0]]);
                })
                .append("title").text(function(d,i0){console.log("init",names[d[0]]);return names[d[0]]+" "+colnames[i0]+" "+raw[d[0]][i0];})
                

         ;
        g.exit().remove()
    }

    function boxplot(data,el)
    {
        var margin = {top: 10, right: 5, bottom: 10, left: 5},
                width = 40 - margin.left - margin.right,
                height = 300 - margin.top - margin.bottom;
        var chart = d3.box()
                .whiskers(iqr(1.5))
                .width(width)
                .height(height);
        chart.domain([0,1]);
        var newData = data[0].map(function(col, i) {
            return data.map(function(row) {
                return row[i]
            })
        });
        var svg = d3.select(el).selectAll("g")
        svg.data(newData)
                .attr("class", "box")
                .attr("width", width + margin.left + margin.right)
                .attr("height", height + margin.bottom + margin.top)
                .attr("transform", function(d,i) { return "translate(" + ((width+margin.left+margin.right)*i+margin.left) + "," + margin.top + ")"})
                .call(chart);

        svg.data(newData)
                .enter().append("g")
                .attr("class", "box")
                .attr("width", width + margin.left + margin.right)
                .attr("height", height + margin.bottom + margin.top)
                .attr("transform", function(d,i) { return "translate(" + ((width+margin.left+margin.right)*i+margin.left) + "," + margin.top + ")"})
                .call(chart);

    }

    function norm(data)
    {   var sum=0.0
        for(var i in data) {sum+=data[i]}
        var b=[]
        for(var i in data) {b.push(data[i]/sum)}
        return b
    }
