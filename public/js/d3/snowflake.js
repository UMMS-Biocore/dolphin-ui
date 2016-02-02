var snowflake = snowflake || {};
(function(B){
        B.module = (function(o) { 
        for (var key in o) {this[key]=o[key];}
       } );
        B.module.prototype = {
            required_parameters : [],
            optional_parameters : [],
            validate:function(o) {
            for(var i in this.required_parameters) {
                if (!o[this.required_paramters[i]]) {
                    return false;
                }
            }
            return true;
            }, 
            render : function() {
                        var self=this;
                        self[self.type]=self.el.append(self.type);
                        for (var key in self.attrs) {
                        self[self.type].attr(key,self.attrs[key]);
                        }
                        self.render_modules();

                    },
            $el : function() {
                return this[this.type];
            },
            handler: function(data) {
                var self=this;
                self.data=data;
                self.render();
            },

            render_modules: function() {
                        var self=this;
                        var d1;
            
                        if (self.modules)
                       {
                       self.modules.forEach(function(d)
                       {
                        /*
                         if(typeof d.type=="string")
                         {
                         d1=B.init(d);
                         }
                         else
                         {
                             d1=d.type.init(d);
                         }
                         d1.el=self.g;
                         d1.render();
                         */
                        d.render=B[d.type].render;
                        d.render_modules=B[d.type].render_modules;
                        d.el=self.g
                        d.render()
                       });
                
                     }
                    },
             extend: function(option) {
                 return this.init(option);
            },
            init: function(option) {
                function F() {}
                F.prototype = this;
                a = new F();
                for(var key in option) {a[key]=option[key];}
                if (!"data" in a) {a["data"]={}};
                return a;
            }
         
            
          };
        
        
        
        B.panel = new B.module();
        B.panel.render = function() {
            var self=this;
            self.g = self.el.append("g").attr("id",self.id || "noname_panel");
            self.x=self.x || 0;
            self.y=self.y || 0;
            self.height = self.height || 800;
            self.width = self.width || 600;
            self.rotate= self.rotate || 0;
            self.scale=self.scale || 1;
            self.g.attr("transform", "translate("+self.x+ "," +self.y+") rotate("+self.rotate+") scale("+self.scale+")" );
            if (self.border) {  
             self.g.append("g").attr("class","border").append("rect").attr("x",0).attr("y",0).attr("height",self.height).attr("width",self.width).attr("stroke",self.color || "grey").attr("stroke-width",self.border)
            .attr('fill','white').attr('fill-opacity',0.0).attr("rx",self.rx || 0).attr("ry", self.ry || 0);
            
            }
            self.render_modules();
        
          } ;
    
        /*
        B.rect = new B.module({"type":"rect"});
        B.circle = new B.module({"type":"circle"});
        B.line = new B.module({"type":"circle"});
        */
    B.text = new B.module({"type":"text"});
    B.text.render = function() {
     var self=this;
    
     var t=self.el.append("text");
     var x=self.attrs.x || 0;
     var y=self.attrs.y || 0;
     t.attr("transform","translate("+x+","+y+")").text(self.attrs.text);
    };
    
    B.curve = new B.module({"type":"curve"});
    B.curve.render = function() {
    var self=this;
    var  lineFunction = d3.svg.line()
                    .x(function(d) { return d[0]; })
                    .y(function(d) { return d[1]; })
                    .interpolate("basis");
    var curve=self.el.append("path").attr("d",lineFunction(self.points)).attr("stroke",self.attrs.stroke || "black").attr("stroke-width", self.attrs["stroke-width"] || 2).attr("fill",self.attrs.fill || "none");
    };
        
        B.plot_json = function(json) {
            if(json.el === "undefined") {json.el=d3.select("body").append("svg").attr("height",600).attr("width",800);}
            var figure=B[json.type].init(json);
            figure.render();
            return figure;
        };
        B.init = function(d) {
            if (typeof d.type === "string" )
            {
            if (B[d.type])
            {
            return B[d.type].init(d);
            }
            else
            {
                B[d.type]=new B.module({"type":d.type});
                console.log(B[d.type]);
                return B[d.type].init(d);
            }
            }
            else
            {
                return d.type.init(d);
            }
        }
    }
(snowflake));

//---------------- END OF CORE MODULE ---------------------------
//---------------- BEGIN OF HIST MODULE ---------------------------
(function(B){
    B.hist=new B.module({"type":"hist"});
    B.hist.render=function() {
     /*  
     revise from http://http://bl.ocks.org/mbostock/3048450
     */
     var self=this;
     var el=self.el;
     var formatCount = d3.format(",.0f");
     var margin = {top: 10, right: 30, bottom: 30, left: 30},
        width = this.width || 100,
        height = this.height || 100;
        xmin=this.attrs.xmin || Math.min.apply(Math,this.attrs.values);
        xmax=this.attrs.xmax || Math.max.apply(Math,this.attrs.values);
        if(!this.attrs.id) {this.attrs.id="svg_hist";}
var x;
if (self.logscale)
{
    x=d3.scale.log().domain([xmin,xmax]).range([0,width])
}
else
{
x = d3.scale.linear()
    .domain([xmin, xmax])
    .range([0, width]);
}
// Generate a histogram using twenty uniformly-spaced bins.
var data = d3.layout.histogram()
    .bins(x.ticks(this.attrs.binsize))
    (this.attrs.values);
var y = d3.scale.linear()
    .domain([0, d3.max(data, function(d) { return d.y; })])
    .range([height, 0]);

var xAxis = d3.svg.axis()
    .scale(x)
    .orient("bottom");

    var svg = el.append("g").attr("class","hist").attr("id",this.id)
    .attr("width", width + margin.left + margin.right)
    .attr("height", height + margin.top + margin.bottom)
    .append("g")
    .attr("transform", "translate(" + (margin.left+this.x) + "," + (margin.top+this.y) + ")");
    var bar = svg.selectAll(".bar")
    .data(data)
    .enter().append("g")
    .attr("class", "bar")
    .attr("transform", function(d) { return "translate(" + x(d.x) + "," + y(d.y) + ")"; }); 

    var binwidth= x(data[0].dx+data[0].x)-x(data[0].x)-1;
    bar.append("rect")
        .attr("x", 1)
        .attr("width", binwidth )
        .attr("height", function(d) { return height - y(d.y); })
        .attr("fill",self.attrs.color ||"steelblue")
        .attr("shape-rendering","crispEdges");
    if (this.attrs.text)
    {   
    bar.append("text")
        .attr("dy", ".75em")
        .attr("y", 6)
        .attr("x", binwidth / 2)
        .attr("font-size",8)
        .attr("text-anchor", "middle")
        .attr("fill","#fff")
        .text(function(d) { return formatCount(d.y); }); 
    }   
    else
    {   
        bar.append("title").text(function(d) {return d.x+" : "+formatCount(d.y);}); 
    }   
    if (this.attrs.ticks)
    {   
       svg.append("g")
        .attr("class", "x axis")
        .attr("transform", "translate(0," + height + ")")
        .attr("font-size",8)
        .call(xAxis); 
     }   
 };   
  
}(snowflake));
//--------------------- END of HIST MODULE ---------------------------------------



//--------------------- BEGIN OF CIRCOS MODULE -----------------------------------

/**
 * VERSION 0.1.3
 * Improvements:
 * 1. remove dependency on underscore
 * 2. add namespace bam2x.circos
 * 3. remove source code redundacy
 *
 */

(function(B){
    B.circos = new B.module({"type":"circos"});
    C=B.circos;
    function default_model()
    {
      return (function(options){
      for (var key in options)
      {
        this[key]=options[key];
      }});
    }
   /**
    * Ideogram Section
    *
    *
    */
    C.IdeogramModel = default_model();
    C.IdeogramView= default_model();
    C.IdeogramView.prototype = {
       render: function(text,ticks_boolean)
           {
               var ideogram=this.el;
               if(this.track_name){
                    ideogram.attr("class",this.track_name);
               }
               if(this.model.id){
               ideogram.attr("id",this.model.id);
               }
               ideogram.selectAll("path").remove();
               var self=this;
               ideogram.attr("transform","translate("+this.cx+","+this.cy+")");
               ideogram.append("path").attr("d", d3.svg.arc().outerRadius(this.outerRadius)
                       .innerRadius(this.innerRadius)
                       .startAngle(this.startAngle)
                       .endAngle(this.endAngle)
                       )
                       .attr("class","symbol")
                       .attr("model",this.model)
                       .attr("id","symbol-"+this.model.id)
                       .style("fill",this.model.color)
                       .style("opacity",0.5)
                       .on("mouseover", function(d,i){
                        d3.select(this).style("opacity",1.0);
                        ideogram.append("path").attr("d",d3.svg.arc().outerRadius(self.cx)
                       .innerRadius(10)
                       .startAngle(self.startAngle)
                       .endAngle(self.endAngle)
                       )
                       .style("fill","yellow")
                       .attr("class","flash")
                       .style("opacity",0.3);
                               }
                           
                          )
                       .on("mouseout",function(d,i)
                               {
                                d3.select(this).style("opacity",0.7);
                                ideogram.selectAll(".flash").remove();
                               }
                               
                          ).append("title").text(this.model.id);
                       
               if(text){
                  var text_content = ideogram.append("svg:text")
                  .attr("x", 10)
                  .attr("dy",-15);
                  text_content.append("svg:textPath")
                  .attr("xlink:href","#symbol-"+this.model.id)
                  .text(self.model.id);
                  }
               if(ticks_boolean) {
                    var el=this.el;
                    var ticks = el.append("g").selectAll("g")
                                .data([self])
                                .enter().append("g").selectAll("g")
                                .data(self.groupTicks)
                                .enter().append("g")
                                .attr("transform", function(d) {
                                return "rotate(" + (d.angle * 180 / Math.PI - 90) + ")"
                                + "translate(" + self.outerRadius + ",0)";
                                });
                   ticks.append("line")
                    .attr("x1", 1)
                    .attr("y1", 0)
                    .attr("x2", 5)
                    .attr("y2", 0)
                    .style("stroke", "#000");

                   ticks.append("text")
                    .attr("x", 8)
                    .attr("dy", ".35em")
                    .attr("transform", function(d) { return d.angle > Math.PI ? "rotate(180)translate(-16)" : null; })
                    .style("text-anchor", function(d) { return d.angle > Math.PI ? "end" : null; })
                    .text(function(d) { return d.label; });
                }
           },
           
           translateBed: function(start,end) //bed format [start,end) 0 index
           {
               var startAngle=start/this.model.length*(this.endAngle-this.startAngle)+this.startAngle;
               var endAngle=end/this.model.length*(this.endAngle-this.startAngle)+this.startAngle;
               return [startAngle,endAngle];
           },
           groupTicks: function(d) {
                var k = (d.endAngle - d.startAngle) / d.model.length;
                var step = Math.round(Math.PI/(k*24*10))*10;
                step = step===0 ? 1:step;
                return d3.range(0, d.model.length, step).map(function(v, i) {
                    return {
                        angle: v * k + d.startAngle,
                        label: i % 5 ? null : v
                        };
                });
            }
    };
    
    C.IdeogramTrack = function(options)
    {
        var self=this;
        for (key in options) { this[key]=options[key]}
        self.hidden=self.hidden || {};
        this.initHidden();
    };
    C.IdeogramTrack.prototype = {
           add: function(ideogram)
           {
               this.collection.push(ideogram);
           }
           ,

           render: function(ticks)
           {

               var self=this;
               self.calculateHidden();
               self.el.selectAll(".ideogram_track").remove()
               var track_el=self.el.append("g").attr("class","ideogram_track");
               var offsetAngle=0;
               var gapAngle=this.gapAngle; //set later
               var totalAngle=3.1415926*2-gapAngle*self.totalGaps;
               var startAngle=offsetAngle;
               self.ideogramViews={};
               this.collection.filter(function(d) {return !self.hidden[d.id]}).forEach( function(i)
                   {
                     var endAngle=startAngle+i.length/self.totalLength*totalAngle;
                     var ideogramView = new C.IdeogramView({"startAngle":startAngle,"endAngle":endAngle,"innerRadius":self.innerRadius,"outerRadius":self.outerRadius,"model":i,"el":track_el.append("g").attr("id",i.id),"cx":self.cx,"cy":self.cy})
                     self.ideogramViews[i.id]=ideogramView;
                     if(ticks){
                     ideogramView.render(true,true);
                     }
                     else
                     {
                     ideogramView.render(true);
                     }
                     startAngle=endAngle+gapAngle;
                   });
           
           },
          calculateHidden: function()
           {
                this.totalLength=0;
                this.totalGaps=0;
                var self=this;
                this.collection.forEach(function(i)
                {
                     if (!self.hidden[i.id]) {
                       self.hidden[i.id]=false;
                       self.totalLength+=i.length;
                       self.totalGaps+=1;
                  } 
                  });
 
           },
           initHidden: function()
           {
                var self=this;
                this.collection.forEach(function(i)
                {
                    if (!self.hidden[i.id])
                    {
                         self.hidden[i.id]=false;
                    }
                });
 
           },
           
           translateBed: function(id,start,end) //bed format [start,end) 0 index
           {
               return this.ideogramViews[id].translateBed(start,end);
               
           }
    };
    /**
     *  BED6
     *
     */
     C.BedModel=default_model();
     C.BedTrack=default_model();
     C.BedTrack.prototype={
           add: function(bed)
           {
               this.collection.add(bed);
           },

           render: function(coordinates)
           {   var self=this;
               self.el.selectAll("g").remove();
               this.collection.filter(function(d) {return !coordinates.hidden[d.chr];}).forEach(function(i)
                       {
                           if(self.color) {i.color=self.color;}
                            var angles=coordinates.translateBed(i.chr,i.start,i.end);
                            var startAngle=angles[0];
                            var endAngle=angles[1];
                            var ideogramView = new C.IdeogramView({"startAngle":startAngle,"endAngle":endAngle,"innerRadius":self.innerRadius,"outerRadius":self.outerRadius,"model":i,"el":self.el.append("g").attr("id",i.id),"cx":self.cx,"cy":self.cy});
                            ideogramView.render();
                       });
           }
     };
     
     /**
      * Link section
      *
      */
     C.LinkModel=default_model();
     C.LinkView=default_model();
     C.LinkView.prototype={
         render: function(coordinates){
                var g=this.el.append("g");
                var self=this;
                var targetAngles=coordinates.translateBed(this.model.target.chr,this.model.target.start,this.model.target.end);
                var sourceAngles=coordinates.translateBed(this.model.source.chr,this.model.source.start,this.model.source.end);
                g.append("path")
                .attr("d",
                        d3.svg.chord()
                            .source(function() { return {startAngle:sourceAngles[0],
                                endAngle:sourceAngles[1]};})
                            .target(function() { return {startAngle:targetAngles[0],
                                endAngle:targetAngles[1]};})
                            .radius(self.radius)


                    ).attr("model",self.model)
                    .attr("class","symbol")
                    .style("fill",  self.model.color)
                    .style("opacity", 0.5)
                    .on("mouseover", function() {
                          d3.select(this).style("opacity",1.0);
                          g.append("path").attr("d",d3.svg.arc().outerRadius(self.cx)
                               .innerRadius(10)
                               .startAngle(sourceAngles[0])
                               .endAngle(sourceAngles[1])
                               ).style("fill","yellow")
                               .attr("class","flash")
                               .style("opacity",0.3);
                          g.append("path").attr("d",d3.svg.arc().outerRadius(self.cx)
                                   .innerRadius(10)
                                   .startAngle(targetAngles[0])
                                   .endAngle(targetAngles[1])
                                   ).style("fill","yellow")
                               .attr("class","flash")
                               .style("opacity",0.3);
                    }
                    )
                    .on("mouseout", function() {
                         d3.select(this).style("opacity",0.5);
                         g.selectAll(".flash").remove();
                    })
                    .append("title").text("1-index\n"+self.model.source.chr+":"+(self.model.source.start+1)+"-"+self.model.source.end+"\nto\n"+self.model.target.chr+":"+(self.model.target.start+1)+"-"+self.model.target.end+"\n"
                    );
            }
     };
     C.LinkTrack=default_model();
     C.LinkTrack.prototype={
      render: function(coordinates)
           {   var self=this;
               this.el.selectAll("g").remove();
               this.el.attr("transform","translate("+this.cx+","+this.cy+")");

               this.collection.forEach(function(i)
                       {
                        if (coordinates.hidden[i.source.chr] || coordinates.hidden[i.target.chr])
                        {}
                        else
                        {
                        if(self.color) {i.color=self.color;}
                        linkView = new C.LinkView({"el":self.el.append("g"),
                                                 "model":i,
                                                 "radius":self.radius,
                                                 "cx":self.cx,
                                                 "cy":self.cy
                                                });
                        linkView.render(coordinates);
                        }
                       });
       }
     };
     
     /**
      * Plot section
      */
      C.PlotModel=default_model();
      C.PlotModel.prototype = {
        length: function(){
            return this.values.length;// return value's length.
        },
        max: function(){
            var max=this.values[0];
            for (var v in this.values)
                {
                 if (max < this.values[v]) {max=this.values[v];}
                }
            return max;
        },
        min: function(){
            var min=this.values[0];
            for (var v in this.values)
                {
                 if (min > this.values[v]) {min=this.values[v];}
                }
            return min;
            
        }
      };
      C.PlotView=default_model();
      C.PlotView.prototype={
        render: function(){
        var self=this;
        var bars=this.el.selectAll("path").data(this.model.values).enter().append("path");
        var len=self.model.length();
        var angle=self.endAngle-self.startAngle;
        if (self.yMin >= 0)
         {
         bars.attr("fill",self.model.color).attr("d",
             d3.svg.arc()
                 .outerRadius(function(d) {return self.translateToHeight(d);})
                 .innerRadius(function(d) { return self.innerRadius;}  )
                 .startAngle(function(d,i) { return self.startAngle+i/len*angle;})
                 .endAngle(function(d,i) {return self.startAngle+(i+1)/len*angle;}));
                    
        
         }
         else
         {
            bars.attr("fill",this.model.color).attr("d",
                 d3.svg.arc()
                 .outerRadius(function(d) {return self.translateToHeight(d);})
                 .innerRadius(function(d) { return self.translateToHeight(0);}  )
                 .startAngle(function(d,i) { return self.startAngle+i/len*angle;})
                 .endAngle(function(d,i) {return self.startAngle+(i+1)/len*angle;}));
                 
            
         }
         bars.style("opacity",0.5)
                 .on("mouseover",function(d,i) {
                            d3.select(this).style("opacity",1.0);
                            self.el.append("path").attr("d",d3.svg.arc().outerRadius(self.cx)
                                       .innerRadius(10)
                                       .startAngle(self.startAngle+i/len*angle)
                                       .endAngle(self.startAngle+(i+1)/len*angle)
                                       ).style("fill","yellow")
                                   .attr("class","flash")
                                   .style("opacity",0.3);
                            })
                 
                .on("mouseout",function() {
                    d3.select(this).style("opacity",0.5);
                    self.el.selectAll(".flash").remove();
                })
                .append("title").text( function(d,i) { return "1-index\n pos: "+(i+1)+"\nvalue:"+d;});
    
    
    },
    
    translateToHeight: function(value)
    {
        return (value-this.yMin)/(this.yMax-this.yMin)*(this.outerRadius-this.innerRadius)+this.innerRadius;
    }
   };
   
     C.PlotTrack=default_model();
     C.PlotTrack.prototype={
        render: function(coordinates)
           {   var self=this;
              self.el.selectAll("g").remove();
               var yMins=[];
              var yMaxs=[];
              for ( var key in this.collection){
                  yMins.push(Math.min.apply(Math,this.collection[key].values));
                  yMaxs.push(Math.max.apply(Math,this.collection[key].values));
              }
              this.yMin=Math.min.apply(Math,yMins);
              this.yMax=Math.max.apply(Math,yMaxs);
              this.el.attr("class","plot");
              this.el.attr("transform","translate("+this.cx+","+this.cy+")");
               this.collection.forEach(function(i)
                       {
                           if (!coordinates.hidden[i.chr]) 
                            {
                            var angles=coordinates.translateBed(i.chr,0,i.length());
                            var startAngle=angles[0];
                            var endAngle=angles[1];
                            var model=self.el.append("g").attr("id",i.chr+"_"+i.id);
                            var plotView = new C.PlotView({"startAngle":startAngle,"endAngle":endAngle,"innerRadius":self.innerRadius,"outerRadius":self.outerRadius,"model":i,"el":model,"cx":self.cx,"cy":self.cy,"yMin":self.yMin,"yMax":self.yMax});
                            plotView.render();
                            }
                       });
       }
     };
    
     /**
      * BedGraph section
      *
      */
      C.BedGraphModel= function(options){
        for (var key in options){
            this[key]=options[key];
        }
        if (options.length && options.start && !options.end){
            this.end=parseInt(options.start)+parseInt(options.length);
        }
        if (options.start && options.end){
            this.length=parseInt(options.end)-parseInt(options.start);
        }
      };
    
       C.BedGraphTrack = default_model();
       C.BedGraphTrack.prototype = {
         max: function(){
            var max=+this.collection[0].value;
            for (var v in this.collection)
                {
                 if (max < +this.collection[v].value) {max=+this.collection[v].value;}
                }
            return max;
        },
        min: function(){
            var min=+this.collection[0].value;
            for (var v in this.collection)
                {
                 if (min > +this.collection[v].value) {min=+this.collection[v].value;}
                }
            return min;
            
        },
         render: function(coordinates) {

        var self=this;
        self.el.selectAll("g").remove();
        var bars=this.el.append("g").selectAll("path").data(this.collection.filter(function(d) {return !coordinates.hidden[d.chr];})).enter().append("path");
        this.el.attr("class","plot");
        this.el.attr("transform","translate("+this.cx+","+this.cy+")");
        this.yMin=this.min();
        this.yMax=this.max();
        if (this.yMin > 0) {this.yMin=0;}
       
      
        if (self.yMin >= 0)
         {
                bars.attr("fill",self.color).attr("d",
                d3.svg.arc()
                 .outerRadius(function(d) {return self.translateToHeight(d.value);})
                 .innerRadius(function(d) { return self.innerRadius;})
                 .startAngle(function(d,i) { return coordinates.translateBed(d.chr,d.start,d.start+1)[0];})
                 .endAngle(function(d,i) {return coordinates.translateBed(d.chr,d.end-1,d.end)[1];})
                 );
                
                      
         }
         else
         {
            bars.attr("fill",this.color).attr("d",
                 d3.svg.arc()
                 .outerRadius(function(d) {return self.translateToHeight(d.value);})
                 .innerRadius(function(d) { return self.translateToHeight(0);}  )
               .startAngle(function(d,i) { return coordinates.translateBed(d.chr,d.start,d.start+1)[0];})
                 .endAngle(function(d,i) {return coordinates.translateBed(d.chr,d.end-1,d.end)[1];})
                 );
            
         }
         bars.style("opacity",0.5)
                          .on("mouseover",function(d) {
                            d3.select(this).style("opacity",1.0);
                            self.el.append("path").attr("d",d3.svg.arc().outerRadius(self.cx)
                                       .innerRadius(10)
                                       .startAngle(coordinates.translateBed(d.chr,d.start,d.start+1)[0])
                                       .endAngle(coordinates.translateBed(d.chr,d.end-1,d.end)[1])
                                       ).style("fill","yellow")
                                   .attr("class","flash")
                                   .style("opacity",0.3);
                            })
                .on("mouseout",function() {
                    d3.select(this).style("opacity",0.5);
                    self.el.selectAll(".flash").remove();
                })
                .append("title").text( function(d,i) { return d.chr + ":" + (+d.start+1) + "-" + d.end + "\n value:" + d.value;});
        
    
    },
    
         translateToHeight: function(value){
        return (value-this.yMin)/(this.yMax-this.yMin)*(this.outerRadius-this.innerRadius)+this.innerRadius;
          }
       };
 
 C.render = function() {
      var self=this;
      var el=self.el;
      console.log(self);
      data=self.data;
      var outerRadius,innerRadius,plotHeight,bedHeight,gapHeight;     
      if (typeof data.config !="undefined")
      {
      outerRadius=data.config.outerRadius || 250;
      innerRadius=data.config.innerRadius || 70;
      plotHeight=data.config.plotHeight || 30;
      bedHeight=data.config.bedHeight || 10;
      gapHeight= data.config.gapHeight || 5;
      }
      else
      {
     outerRadius=250;
      innerRadius=70;
      plotHeight=30;
      bedHeight=10;
      gapHeight=5;

      }
      var nowRadius=outerRadius;
      //var cy = outerRadius + 30;
      var cy = data.config.cy || outerRadius + 50;
      var cx = data.config.cx || outerRadius +50 ;
      if (cy<300) {cy=300}
      //var cx = outerRadius + 30
      var x = data.config.x || 0;
      var y = data.config.y || 0;
      var svg = self.el.append("g").attr("id","circos").attr("height",cy*2).attr("width",cx*2).attr("transform","translate("+x+","+y+")");
      //$("#"+el+" svg").attr("xmlns:xlink","http://www.w3.org/1999/xlink");
      if (data.config.scale) {svg.attr("transform","translate("+x+","+y+") scale("+data.config.scale+")")}
      var collection = [];
      var ideograms = [];
      for (var i in data.ideograms){
          ideograms.push(new C.IdeogramModel(data.ideograms[i]));
      }
      collection=ideograms;
      var ideogramTrack = new C.IdeogramTrack({"collection":collection,"el":svg.append("g"),"cx":cx,"cy":cy,"outerRadius":nowRadius,"innerRadius":nowRadius-bedHeight,"gapAngle":0.02});
      ideogramTrack.render(true);
     
     nowRadius=nowRadius-bedHeight-gapHeight;
     var trackNames=[];
     var tracks=[];
     for( var i in data.tracks)
     {
     track=data.tracks[i];
     trackNames.push(data.tracks[i].name);
     var plots=[];
     if (track.type=="plot")
     {
     for( var j in track.values)
     {
         var model=new C.PlotModel(track.values[j]);
         if (track.color)
         {
             model.color=track.color;
         }
         plots.push(model);

     }
      
      var plotTrack = new C.PlotTrack({"name":track.name,"collection":plots,"el":svg.append("g"),"cx":cx,"cy":cy,'outerRadius':nowRadius,'innerRadius':nowRadius-plotHeight});
      plotTrack.render(ideogramTrack);
      nowRadius-=plotHeight+gapHeight;
      tracks.push(plotTrack);
    }

    var bedGraphTrack,bedTrack;
    if ( track.type=="bedgraph"){
        collection= track.values;
        if (track.color)
        {
        bedGraphTrack = new C.BedGraphTrack({"collection":collection,"el":svg.append("g"),"cx":cx,"cy":cy,"outerRadius":nowRadius,"innerRadius":nowRadius-plotHeight,"color":track.color});
        }
        else
        {
        bedGraphTrack = new C.BedGraphTrack({"collection":collection,"el":svg.append("g"),"cx":cx,"cy":cy,"outerRadius":nowRadius,"innerRadius":nowRadius-plotHeight});
        }
        bedGraphTrack.render(ideogramTrack);
        nowRadius-=plotHeight+gapHeight;
        tracks.push(bedGraphTrack);
        }
    
    if ( track.type=="bed"){
        collection= track.values;
        if (track.color)
        {
        bedTrack = new C.BedTrack({"color":track.color,"collection":collection,"el":svg.append("g"),"cx":cx,"cy":cy,"outerRadius":nowRadius,"innerRadius":nowRadius-bedHeight});
        }
        else
        {
        bedTrack = new C.BedTrack({"collection":collection,"el":svg.append("g"),"cx":cx,"cy":cy,"outerRadius":nowRadius,"innerRadius":nowRadius-bedHeight});
        }
        bedTrack.render(ideogramTrack);
        nowRadius-=bedHeight+gapHeight;
        tracks.push(bedTrack);
        }
    if (track.type=="links")
    {
        var links = [];
        var linkTrack;

        for(var i in track.values){
            links.push(new C.LinkModel(track.values[i]));
         }
        if (track.color) {
        linkTrack = new C.LinkTrack({"collection":links,"el":svg.append("g"),"cx":cx,"cy":cy,'radius':nowRadius,'color':track.color});
        }
        else
        {
        linkTrack = new C.LinkTrack({"collection":links,"el":svg.append("g"),"cx":cx,"cy":cy,'radius':nowRadius});
        }
        linkTrack.render(ideogramTrack);
        tracks.push(linkTrack);
    }
    }

    var legend = svg.append("g").attr("class","legend");
    legend.selectAll("circle").data(trackNames).enter().append("circle").attr("class","circle").attr("cy",function(d,i) {return cy/3 + i*15 -5}).attr("cx",cx+outerRadius+10).attr("fill",function(d,i) {return data.tracks[i].color || "black"}).attr("r",4).style("opacity",0.7)
    .on("mouseover",function(d,i) {
        d3.select(this).style("opacity",1.0).attr("r",5);
        if(data.tracks[i].type!="links")
        {
            svg.append("g").attr("class","flash").attr("transform","translate("+cx+","+cy+")").append("path").attr("d",d3.svg.arc().outerRadius(tracks[i].outerRadius).innerRadius(tracks[i].innerRadius).startAngle(0).endAngle(3.1415926*2)).attr("fill","yellow").style("opacity",0.2);
        }
        else
        {
            svg.append("g").attr("class","flash").attr("transform","translate("+cx+","+cy+")").append("path").attr("d",d3.svg.arc().outerRadius(tracks[i].radius).innerRadius(0).startAngle(0).endAngle(3.1415926*2)).attr("fill","yellow").style("opacity",0.2);
        }

        })
    .on("mouseout",function(d,i) {
        d3.select(this).style("opacity",0.7).attr("r",4);
        svg.selectAll(".flash").remove();
        });
    legend.selectAll("text").data(trackNames).enter().append("text").attr("y",function(d,i) {return cy/3 +i*15}).attr("x",cx+outerRadius+20).text(function(d) {return d});
    
    

    var groups= svg.append("g").attr("class","groups");
    groups.selectAll("circle").data(data.ideograms).enter().append("circle").attr("cy",function(d,i) { return 4*cy/3 +i*15 -5 }).attr("cx",cx+outerRadius+35).attr("r",5).style("fill",function(d,i) { return d.color || "black"} )
    .on("click", function(d,i) {
        ideogramTrack.hidden[d.id]=!ideogramTrack.hidden[d.id];
        if(ideogramTrack.hidden[d.id]) {d3.select(this).style("fill","white").style("stroke",d.color || "black");}
        else {d3.select(this).style("fill",d.color || "black").style("stroke",d.color || "black")}
        ideogramTrack.render(true);
        for (i in tracks)
        {
            tracks[i].render(ideogramTrack);
        }
        
    
        } )
    .append("title").text(function(d) {return d.id});
  };
 }(snowflake));
//---  END OF CIRCOS

//-------------------- BEGIN OF LINCOS MODULE ----------------------------------

(function(D,C) {
   D.lincos=new D.module({"type":"lincos"});
   var L=D.lincos;
   function default_model()
    {
      return (function(options){
      for (var key in options)
      {
        this[key]=options[key];
      }});
    }
    
   
    L.IdeogramView=default_model();
    L.IdeogramView.prototype = {
      render: function(text,ticks){
               var ideogram=this.el;
               if(this.track_name)
                   {
               ideogram.attr("class",this.track_name);
                   }
               if(this.model.id)
               {
               ideogram.attr("id",this.model.id);
               }
               var self=this;
               //ideogram.attr("transform","translate("+this.x+","+this.y+")");
               ideogram.selectAll("rect").data([self]).enter().append("rect")
               .attr("x",function(d) {return d.x})
               .attr("y",function(d) {return d.y})
               .attr("height",function(d) {return d.height})
               .attr("width",function(d) {return d.width})
               .attr("fill",function(d) {return d.color})
               .attr("opacity",0.7)
               .on("mouseover",function(d,i) {d3.select(this).attr("opacity",1.0)})
               .on("mouseout",function(d,i) {d3.select(this).attr("opacity",0.7)})
               .append("title").text(function(d) { return self.model.id });
               if (text) {
                   text = ideogram.append("text").attr("class","chr-name");
                   text.attr("transform","translate("+self.x+","+(self.y-10)+")").attr("x",5).text(self.model.id);
               }
               if (ticks) {
              //TODO
               }
           },
      translateBed: function(start,end) { //bed format [start,end) 0 index
               var self=this;
               var k=(self.width/self.model.length);
               return [self.x+k*start,k*(end-start)] ; //return x,width
           }
    };
    L.IdeogramTrack=default_model();
    L.IdeogramTrack.prototype = {
    totalLength: function()
    {
        var s=0;
        this.collection.forEach(function(i)
        {
             s+=i.length;
        });
        return s;
    },
      render: function(text,ticks)
           {
               this.ideogramViews={};
               var offsetx=this.x;
               var totalLength=this.totalLength();
               var totalGaps=this.collection.length-1;
               var gap=this.gap;
               var totalWidth=this.width-gap*totalGaps;
               var startx=offsetx;
               var self=this;
               this.collection.forEach(function(d,i) {
                   var width=d.length/totalLength*totalWidth;
                   var color=self.color;
                   if (d.color) {color=d.color}
                   var ideogramView = new L.IdeogramView({"model":d,"x":startx,"y":self.y,"height":self.height,"width": width,"color":color,"el":self.el.append("g")} );
                   ideogramView.render(text,ticks);
                   startx=startx+width+gap;
                   self.ideogramViews[d.id]=ideogramView;
               });
           },
           
           translateBed: function(id,start,end) //bed format [start,end) 0 index
           {
               return this.ideogramViews[id].translateBed(start,end);
               
           }
    };
    L.BedTrack=default_model();
    L.BedTrack.prototype = {
      render: function(coordinates)
           {   var self=this;
               this.collection.forEach(function(i)
                       {
                            var xw=coordinates.translateBed(i.chr,i.start,i.end);
                            var x=xw[0];
                            var width=xw[1];
                            var color=self.color;
                            if (i.color) {color=i.color}
                            var ideogramView = new L.IdeogramView({"y":self.y,"x":x,"width":width,"height":self.height,"model":i,"el":self.el.append("g").attr("id",i.id),"color":color});
                            ideogramView.render(false,false);
                            self.bedViews[i.id]=ideogramView;
                       });
       }
    };
    L.PlotView=default_model();
    L.PlotView.prototype = {
      render: function(){
    var self=this;
    self.yMax = self.yMax || Math.max.apply(Math,this.model.values);
    self.yMin = self.yMin || Math.min.apply(Math,this.model.values);
    var len=self.model.length();
    var bars=this.el.selectAll("rect").data(this.model.values).enter().append("rect");
    var x=self.x;
    var width=self.width;
    var k=width/len;
    bars.attr("fill",self.model.color)
    .attr("x",function(d,i) {return x+k*i})
    .attr("y",function(d,i) { if (d<0) {return self.y} else {return self.y - d/self.yMax*self.height}})
    .attr("height",function(d,i){
        return Math.abs(d)/self.yMax*self.height;
        })
    .attr("width",function(d,i) {return k})
    .attr("opacity",0.7)
    .on("mouseover",function(d,i) {d3.select(this).attr("opacity",1.0)})
    .on("mouseout",function(d,i) {d3.select(this).attr("opacity",0.7)})
    .append("title").text(function(d,i) { return self.model.chr+"\npos:"+(i+1)+"\nvalue:"+d });
        
      }
    };
    L.PlotTrack=default_model();
    L.PlotTrack.prototype = {
      
      render: function(coordinates,name){
          var yMins=[];
          var yMaxs=[];
              for ( var key in this.collection){
                  yMins.push(Math.min.apply(Math,this.collection[key].values));
                  yMaxs.push(Math.max.apply(Math,this.collection[key].values));
                 }
          this.yMin=Math.min.apply(Math,yMins);
          this.yMax=Math.max.apply(Math,yMaxs);
          this.el.attr("class","plot");
          var self=this;
          if(name !== 'undefined' && name){
            text = self.el.append("g").append("text");
            text.attr("transform","translate("+self.x+","+(self.y-3)+")").attr("x",5).text(self.name);
          }
          this.collection.forEach(function(i)
                       {
                            var xw=coordinates.translateBed(i.chr,0,i.length());
                            var x=xw[0];
                            var width=xw[1];
                            var model=self.el.append("g").attr("id",i.chr+"_"+i.id);
                            var plotView = new L.PlotView({"height":self.height,"x":x,"y":self.y,"width":width,"model":i,"el":self.el.append("g"),"yMin":self.yMin,"yMax":self.yMax});
                            plotView.render();
                       });
            }

        
    };
    
    L.BedGraphTrack=default_model();
    L.BedGraphTrack.prototype={
      max: function(){
            var max=+this.collection[0].value;
            for (var v in this.collection)
                {
                 if (max < +this.collection[v].value) {max=+this.collection[v].value;}
                }
            return max;
        },
        min: function(){
            var min=this.collection[0].value;
            for (var v in this.collection)
                {
                 if (min > +this.collection[v].value) {min=+this.collection[v].value;}
                }
            return min;
     
        } ,
      render: function(coordinates) {
        var self=this;
        var bars=this.el.selectAll("rect").data(this.collection).enter().append("rect");
        this.el.attr("class","bedgraph");
        //this.el.attr("transform","translate("+this.cx+","+this.cy+")");
        this.yMin=this.yMin || this.min();
        this.yMax=this.yMax || this.max();
        
        if (this.yMin > 0) {this.yMin=0}
        bars.attr("x", function(d,i) {
                       return coordinates.translateBed(d.chr,d.start,d.end)[0];
                    })
                .attr("width", function(d,i) {
                       return coordinates.translateBed(d.chr,d.start,d.end)[1];
                    })
                .attr("fill",function(d,i) { if (d.color) {return d.color} else {return self.color}} )
                .attr("height",function(d,i) { return self.translateToHeight(d.value)})
                .attr("y",function(d,i) {
                        if(d.value >= 0 ){
                            return self.height + self.y - self.translateToHeight(d.value-self.yMin)}
                        if(d.value < 0 ) {
                            return self.height + self.y - self.translateToHeight(0-self.yMin)}
                        }
                    );
         bars.style("opacity",0.5)
                .on("mouseover",function(d) {
                            d3.select(this).style("opacity",1.0);
                            })
                .on("mouseout",function() {
                    d3.select(this).style("opacity",0.5);
                    })
                .append("title").text( function(d,i) { return d.chr + ":" + (+d.start+1) + "-" + d.end + "\n value:" + d.value });
    },

    translateToHeight: function(value)
    {
        return Math.abs(value)/(this.yMax-this.yMin)*(this.height);
    }
    };
    
    
    
    L.render= function() {
     var self = this;
     var data = self.data;
     var x=data.config.x || 0;
     var y=data.config.y || 0;
     var scale=data.config.scale || 1;
     
      
      var plotHeight= data.config.plotHeight || 30;
      var bedHeight=data.config.bedHeight || 10;
      var gapHeight= data.config.gapHeight || 5;
      var width = data.config.width || 1000;
      var height = data.config.height || 800;
     
      var nowY = 20;
      var svg = self.el.append("g").attr("class","lincos").attr("align","center");
      svg.attr("transform","translate("+x+","+y+") scale("+scale+")");
      svg.attr("width",width+50);
      svg.attr("height",height);
     
     
      var ideograms = [];
      for (var i in data.ideograms){
          ideograms.push(new C.IdeogramModel(data.ideograms[i]));
      }
    
      var ideogramView = new L.IdeogramView({});
      var ideogramTrack = new L.IdeogramTrack({"y":nowY,"collection":ideograms,"el":svg.append("g"),"x":25,"width":width,"height":bedHeight,"gap":20});
     ideogramTrack.render(true,true);
     
     nowY=nowY+bedHeight+gapHeight+plotHeight;
     for( var i in data.tracks)
     {
     track=data.tracks[i];
     var plots=[];
     if (track.type=="plot")
     {
     for( var j in track.values)
     {
         var model=new C.PlotModel(track.values[j]);
         if (track.color)
         {
             model.color=track.color;
         }
         plots.push(model);

     }
      var plotsCollection= plots;
      var plotTrack = new L.PlotTrack({"collection":plotsCollection,"el":svg.append("g"),"y":nowY,"height":plotHeight});
      plotTrack.render(ideogramTrack);
      nowY+=plotHeight+gapHeight;
    }
    if (track.type=="bed")
    {
        var collection= new BedsCollection();
        collection.add(track.values);
        var color="black";
        if (track.color) { color=track.color}
        var bedTrack = new L.BedTrack({"collection":collection,"el":svg.append("g"),"y":nowY,"color":color,height:bedHeight});
        bedTrack.render(ideogramTrack);
        nowY+=bedHeight+gapHeight;
    }
    if (track.type=="bedgraph")
    {
        var collection= [];
        collection.push(track.values);
        var color="black";
        if (track.color) { color=track.color}
        var bgTrack = new L.BedGraphTrack({"collection":collection,"el":svg.append("g"),"y":nowY,"color":color,height:plotHeight});
        bgTrack.render(ideogramTrack);
        nowY+=plotHeight+gapHeight;
    }
    }
 
};
}(snowflake,snowflake.circos));


//----- END OF LINCOS
//
//----- BEGIN OF SCATTER
(function(D) {
    D.scatter = new D.module({"type":"scatter"});
    D.scatter.brushCb = function() {console.log("TO IMPLEMENT")}
    D.scatter.mouseoverCb = function() {console.log("TO IMPLEMENT")}
    D.scatter.legendMouseoverCb = function(d,i) {console.log(d,i,"TO IMPLEMENT")}
    D.scatter.legendMouseoutCb = function(d,i) {console.log(d,i,"TO IMPLEMENT")}
    D.scatter.legendMouseClickCb = function(d,i) {console.log(d,i,"TO IMPLEMENT")}
    D.scatter.render = function() {
   var self=this;
    var width = self.width || 900,
    height = self.height || 500;
var x,y;
if(self.logscale) {
    x=d3.scale.log().range([0,width])
    y=d3.scale.log().range([height,0])
}
else
{
x = d3.scale.linear()
    .range([0, width]);
y = d3.scale.linear()
    .range([height, 0]);
}
var color = d3.scale.category10();
var xAxis = d3.svg.axis()
    .scale(x)
    .orient("bottom");
var yAxis = d3.svg.axis()
    .scale(y)
    .orient("left");
var svg = self.el.append("g")
    .attr("width", width )
    .attr("height", height )
    .attr("transform", "translate(" + (self.x || 0) + "," + (self.y || 0 )+ ") scale("+(self.scale ||1 )+") rotate("+(self.rotate || 0) +")");
var data=self.data;
  x.domain(d3.extent(data, function(d) { return d.x; })).nice();
  y.domain(d3.extent(data, function(d) { return d.y; })).nice();
  var brush = d3.svg.brush().x(x).y(y).on("brush",brushCb);
  function brushCb() {
        var t = 0;
        var results=[];

            var extent = brush.extent();
            var x0 = extent[0][0],
                y0 = extent[0][1],
                x1 = extent[1][0],
                y1 = extent[1][1];
            self.data.forEach(function (d,i) {
                if (x0 <= d.x && d.x <= x1 && y0 <= d.y && d.y <= y1)
                    { t += 1;
                      results.push(i)}
                    })
            self.brushCb(results);
  }
  svg.call(brush);
  svg.append("g")
      .attr("class", "x axis")
      .attr("transform", "translate(0," + height + ")")
      .call(xAxis)
    .append("text")
      .attr("class", "label")
      .attr("x", width)
      .attr("y", -6)
      .style("text-anchor", "end")
      .text(self.xlabel || "x axis");
  svg.append("g")
      .attr("class", "y axis")
      .call(yAxis)
    .append("text")
      .attr("class", "label")
      .attr("transform", "rotate(-90)")
      .attr("y", 6)
      .attr("dy", ".71em")
      .style("text-anchor", "end")
      .text(self.ylabel || "y axis");
  if (self.zi_type=="value" || self.zi_type=="logvalue") {
      var minz=data[0].z
      var maxz=data[0].z
      data.forEach(function(d0){
          if (minz>d0.z) {minz=d0.z}
          if (maxz<d0.z) {maxz=d0.z}
      })
      
      var colorscale ;
      if(self.zi_type=="value")
      {
      colorscale= d3.scale.linear()
    .domain([minz,  maxz])
    .range(["red", "green"]);
      }
      else
      {
          colorscale= d3.scale.linear()
    .domain([Math.log(minz+1.0),  Math.log(maxz+1.0)])
    .range(["red", "green"]);
      }
      var dots=svg.selectAll(".dot")
      .data(data)
    .enter().append("circle")
      .attr("class", "dot")
      .attr("r", 2.0)
      .attr("i",function(d,i) {return i})
      .attr("cx", function(d) { return x(d.x); })
      .attr("cy", function(d) { return y(d.y); })
      .style("fill", function(d,i) { return colorscale(d.z) || 0; })
      .style("opacity",0.7)
      .on("mouseover",function(d,i) { self.mouseoverCb(i);d3.select(this).attr("r",3.0).style("opacity",1.0);})
      .on("mouseout",function(d) {d3.select(this).attr("r",2.0).style("opacity",0.7);});
  }
  else {
  var z_group={};
  data.forEach(function(d0){
      if (d0.z in z_group) {
          
      }
      else
      {
          z_group[d0.z]=1;
      }
  })
  var gkeys=Object.keys(z_group);
  var z_color={}
  gkeys.sort();
  console.log(gkeys);
  for(var i in gkeys){z_group[gkeys[i]]=[];z_color[gkeys[i]]=i;color(i);}
  console.log(z_group);
  console.log(color.domain())
  data.forEach(function(d,i){
      z_group[d.z].push(i);
  })
  var dots=svg.selectAll(".dot")
      .data(data)
    .enter().append("circle")
      .attr("class", "dot")
      .attr("r", 2.0)
      .attr("i",function(d,i) {return i})
      .attr("cx", function(d) { return x(d.x); })
      .attr("cy", function(d) { return y(d.y); })
      .style("fill", function(d,i) { return color(z_color[d.z] || 0); })
      .style("opacity",0.7)
      .on("mouseover",function(d,i) { self.mouseoverCb(i);d3.select(this).attr("r",3.0).style("opacity",1.0);})
      .on("mouseout",function(d) {d3.select(this).attr("r",2.0).style("opacity",0.7);});
  var legend = svg.selectAll(".legend")
      .data(color.domain())
    .enter().append("g")
      .attr("class", "legend")
      .attr("transform", function(d, i) { return "translate(0," + i * 20 + ")"; });
  console.log(color.domain())
  console.log(z_group);
  legend.append("rect")
      .attr("x", width - 18)
      .attr("width", 18)
      .attr("height", 18)
      .style("fill", color)
      //.on("click",function(d,i) { alert(d)}) //self.legendMouseClickCb(z_group[d],d)})
      .on("mouseover",function(d,i) {d3.select(this).attr("width",22).attr("x",width-20);self.legendMouseoverCb(z_group[gkeys[d]],d)})
      .on("mouseout",function(d,i) {d3.select(this).attr("width",18).attr("x",width-18);self.legendMouseoutCb(z_group[gkeys[d]],d)})
  legend.append("text")
      .attr("x", width - 24)
      .attr("y", 9)
      .attr("dy", ".35em")
      .style("text-anchor", "end")
      .text(function(d) { return gkeys[d]; })
}

 self.dots=dots
 self.legend=legend
 self.svg=svg
 self.xscale=x
 self.yscale=y
    };
}(snowflake));
//--  END OF SCATTER ------


//-- START OF PIE ---------
(function(D) {
    D.pie = new D.module({"type":"pie"});
    D.pie.render = function() {
   var self=this;
       
    var w = self.width || 400,
    h = self.height || 400;

var r = h/2;
var color = d3.scale.category20c();
var data=self.data;
var vis = self.el.append("g").attr("transform","translate("+ (self.x || 0 ) +"," +(self.y || 0) + ") scale("+(self.scale||1) +") rotate("+ (self.rotate||0) +")");
vis.data([data]).attr("width", w).attr("height", h).append("g").attr("transform", "translate(" + r + "," + r + ")");
var pie = d3.layout.pie().value(function(d){return d.value;});
// declare an arc generator function
var arc = d3.svg.arc().outerRadius(r);
// select paths, use arc generator to draw
var arcs = vis.selectAll("g.slice").data(pie).enter().append("svg:g").attr("class", "slice");
arcs.append("svg:path")
    .attr("fill", function(d, i){
        return color(i);
    })
    .attr("d", function (d) {
        // log the result of the arc generator to show how cool it is :)
        console.log(arc(d));
        return arc(d);
    });

// add the text
arcs.append("svg:text").attr("transform", function(d){
            d.innerRadius = 0;
            d.outerRadius = r;
    return "translate(" + arc.centroid(d) + ")";}).attr("text-anchor", "middle").text( function(d, i) {
    return data[i].label;}
        );
    }
}(snowflake));

//----- END OF PIE MODULE -----
//----- BEGIN OF TABLE --------
/**
 *  Table render the data from google sheet sql
 *  a good demo for google sheet api.
 * 
 */
(function(D) {
        D.table = new D.module().extend()
        D.table.render = function(caption) {
              var self=this;
              
              var s="<table class='snowflake-table'>"
              if (caption) {
                  s+="<caption>"+caption+"</caption>"
              }
              self.data.table.cols.forEach(function(d) {
                if(d.label=="") {s+="<th>"+d.id+"</th>"} else {s+="<th>"+d.label+"</th>"}
              })
              self.data.table.rows.forEach(function(d) {
              s+="<tr>"    
              d.c.forEach(function(d0) {s+="<td>"+d0.v+"</td>"})
              s+="</tr>"    
              })
              s+="</table>"
              self.el.html(s)
          }
}(snowflake));
//------  END OF TABlE --------



//------  BEGIN OF GSHEET ----
snowflake.gsheet={};
(function (G) {
G.jsonp = function(url)
{
    var script = window.document.createElement('script');
    script.async = true;
    script.src = url;
    script.onerror = function()
    {
        alert('Can not access JSONP file.')
    };
    var done = false;
    script.onload = script.onreadystatechange = function()
    {
        if (!done && (!this.readyState || this.readyState === 'loaded' || this.readyState === 'complete'))
        {
            done = true;
            script.onload = script.onreadystatechange = null;
            if (script.parentNode)
            {
                return script.parentNode.removeChild(script);
            }
        }
    };
    window.document.getElementsByTagName('head')[0].appendChild(script);
};
 G.query = function(sql, key,callback)
{
    if (typeof callback == "undefined") {callback="console.log"}
    var url = 'http://spreadsheets.google.com/a/google.com/tq?',
        params = {
            key: key,
            tq: encodeURIComponent(sql),
            tqx: 'responseHandler:' + callback
        },
        qs = [];
    for (var key in params)
    {
        qs.push(key + '=' + params[key]);
    }
    url += qs.join('&');
  G.jsonp(url); // Call JSONP helper functiona
}
})(snowflake.gsheet);
