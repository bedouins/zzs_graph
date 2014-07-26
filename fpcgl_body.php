<html>
	<meta charset="utf-8">
	<style>
		.node {
			stroke: #fff;
			stroke-width: 1.5px;
		}

		.link {
			stroke: #999;
			stroke-opacity: .6;
		}

	</style>
	<body>
		<script src="d3.v3.js"></script>
		<script>

			var width = 1400,
height = 700;

var color = d3.scale.category20();

var force = d3.layout.force()
.charge(-800)
.linkStrength(0.5)
.linkDistance(function(d){      	return  d.distance ; })
.size([width, height]);
var colors = d3.scale.category10() ;
var svg = d3.select("body").append("svg")
.attr("width", width)
.attr("height", height).attr("class", "bubble");

d3.json("testoraclejson_nodes.php<?php print "?node_th=" . $_REQUEST['node_th'] . "&link_th=" . $_REQUEST['link_th'];
	if (isset($_REQUEST['nsrsbh'])) {	print "&nsrlist='" . $_REQUEST['nsrsbh'] . "'";
	}
?>
	",
	function(error, graph) {
	force
	.nodes(graph.nodes)
	.links(graph.links)
	.start();

	var marker = d3.select("svg")
		.append("svg:defs")
		.selectAll("marker")
		.data(graph.nodes) //not edges
		.enter()
		.append("svg:marker");
		
	marker.attr({
			id:function(d){
				return "marker-" + d.id; },
			viewBox:"0 -5 10 10",
			refX:function(d) { 
				return 10; },
			markerWidth:2,
			markerHeight:2,
			orient: "auto"
		})
		.append('svg:path')
		.attr('d',"M0,-5L10,0L0,5")
		.attr("fill",function(d) { 
			return colors((d.group+1)*2); });

var lineX2 = function (d) {
    var length = Math.sqrt(Math.pow(d.target.y - d.source.y, 2) + Math.pow(d.target.x - d.source.x, 2));
    var scale = (length - d.target.r) / length;
    var offset = (d.target.x - d.source.x) - (d.target.x - d.source.x) * scale;
    return d.target.x - offset;
};
var lineY2 = function (d) {
    var length = Math.sqrt(Math.pow(d.target.y - d.source.y, 2) + Math.pow(d.target.x - d.source.x, 2));
    var scale = (length - d.target.r) / length;
    var offset = (d.target.y - d.source.y) - (d.target.y - d.source.y) * scale;
    return d.target.y - offset;
};

	var link = svg.append('g')
			.selectAll(".link")
			.data(graph.links)
			.enter()
			.append("line")
			.attr("class", "link")
			.style("stroke", function(d) { 
				return colors(d.color*3); })
			.style("stroke-width", function(d) { 
				return Math.sqrt(Math.sqrt(d.value/50000)); })
			.attr("marker-end",function(d){
				return "url(#marker-" + (d.target.id) + ")";	})	;

	var node = svg.selectAll(".node")
		.data(graph.nodes)
		.enter().append("g")
		.attr("class", "node")
		.on("click", function(d) {
			if (d3.event.defaultPrevented) {
				return; // ignore drag
			}else{
				window.open("fpcgl_body.php?node_th=100000000&link_th=10000000&nsrsbh="+d.nsrsbh,"_self");
			}})
		.call(force.drag);

	node.append("circle")
		.attr("r", function(d) { return d.r; })
		.style("fill", function(d) { return colors((d.group+1)*2); });

	node.append("title")
		.text(function(d) { return Math.round(d.value/10000)+'万元'; });
	node.append("text")
		.attr("dy", ".35em")
		.attr("stroke","black")
		.attr("text-anchor", "middle")
		.text(function(d) { return d.name });

		force.on("tick", function() {
		    node.attr("cx", function(d) { return d.x = Math.max(d.r, Math.min(width-d.r, d.x)); })
		        .attr("cy", function(d) { return d.y = Math.max(d.r, Math.min(height-d.r, d.y)); })
				.attr("transform", function(d) { 
						return "translate(" + d.x + "," + d.y + ")"; });
						
			link.attr("x1", function(d) { return d.source.x; })
				.attr("y1", function(d) { return d.source.y; })
				.attr("x2", lineX2)
				.attr("y2", lineY2);
//				.attr("x2", function(d) { return d.target.x; })
//				.attr("y2", function(d) { return d.target.y; });
		        
		});
	});

		</script>
	</body>
</html>