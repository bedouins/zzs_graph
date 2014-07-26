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

var width = 1800,
height = 900;

var color = d3.scale.category20();

var force = d3.layout.force()
.charge(-800)
.linkStrength(0.5)
.linkDistance(function(d){      	return Math.log(d.distance)*<?php if(isset($_REQUEST['nsrsbh'] )){
	print "50";}else{print "100";}?> ; })
.size([width, height]);

var svg = d3.select("body").append("svg")
.attr("width", width)
.attr("height", height).attr("class", "bubble");


d3.json("testoraclejson_nodes.php<?php print "?node_th=" . $_REQUEST['node_th'] . "&link_th=" . $_REQUEST['link_th'];if(isset($_REQUEST['nsrsbh'] )){	print "&nsrlist='".$_REQUEST['nsrsbh']."'";}?>", 
	function(error, graph) {
	force
	.nodes(graph.nodes)
	.links(graph.links)
	.start();

	var link = svg.selectAll(".link")
	.data(graph.links)
	.enter().append("line")
	.attr("class", "link")
	.style("stroke", function(d) { return color(d.color*3); })
	.attr("marker-end",function(d){  
                    return "url(#marker-" + (d.target) + ")";
                })
	.style("stroke-width", function(d) { return Math.sqrt(Math.sqrt(d.value/50000)); })
	//.linkDistance(function(d){      	return d.distance; })
	;

	var node = svg.selectAll(".node")
	.data(graph.nodes)
	.enter().append("g")
	.attr("class", "node")
	.on("click", function(d) {
	if (d3.event.defaultPrevented) return; // ignore drag
	window.open("fpcgl_body.php?node_th=10000000&link_th=1000000&nsrsbh="+d.nsrsbh,"_self");})
	.call(force.drag);

	node.append("circle")
	.attr("r", function(d) { return Math.sqrt(Math.sqrt(d.value/1000)); })
	.style("fill", function(d) { return color((d.group+1)*2); });

	node.append("title")
	.text(function(d) { return Math.round(d.value/10000)+'万元'; });
	node.append("text")
	.attr("dy", ".35em")
	.attr("stroke","black")
	.attr("text-anchor", "center")
	.text(function(d) { return d.name });

	force.on("tick", function() {
	link.attr("x1", function(d) { return d.source.x; })
	.attr("y1", function(d) { return d.source.y; })
	.attr("x2", function(d) { return d.target.x; })
	.attr("y2", function(d) { return d.target.y; });

	node.attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; });
	});
	});

		</script>
	</body>
</html>