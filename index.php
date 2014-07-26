
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>D3: Force layout</title>
		<script type="text/javascript" src="d3.v3.js"></script>
		<style type="text/css">
			/* No style rules here yet */
		</style>
	</head>
	<body>
		<script type="text/javascript">

			//Width and height
			var w = 500;
			var h = 300;

			//Original data
			var dataset = {
				nodes: [
					{ name: "Adam" , value:"10"},
					{ name: "Bob" , value:"20"},
					{ name: "Carrie" , value:"100"},
					{ name: "Donovan" , value:"10"},
					{ name: "Edward" , value:"20"},
					{ name: "Felicity" , value:"30"},
					{ name: "George" , value:"10"},
					{ name: "Hannah" , value:"20"},
					{ name: "Iris" , value:"30"},
					{ name: "Jerry" , value:"40"}
				],
				edges: [
					{ source: 0, target: 1 ,value:5 },
					{ source: 0, target: 2 ,value:5 },
					{ source: 0, target: 3 ,value:5 },
					{ source: 0, target: 4 ,value:1 },
					{ source: 1, target: 5 ,value:1 },
					{ source: 2, target: 5 ,value:1 },
					{ source: 2, target: 5 ,value:5 },
					{ source: 3, target: 4 ,value:5 },
					{ source: 5, target: 8 ,value:8 },
					{ source: 5, target: 9 ,value:8 },
					{ source: 6, target: 7 ,value:8 },
					{ source: 7, target: 8 ,value:5 },
					{ source: 8, target: 9 ,value:5 }
				]
			};

			//Initialize a default force layout, using the nodes and edges in dataset
			var force = d3.layout.force()
								 .nodes(dataset.nodes)
								 .links(dataset.edges)
								 .size([w, h])
								 .linkDistance([50])
								 .charge([-100])
								 .start();

			var colors = d3.scale.category10();

			//Create SVG element
			var svg = d3.select("body")
						.append("svg")
						.attr("width", w)
						.attr("height", h);
			
			//Create edges as lines
			var edges = svg.selectAll("line")
				.data(dataset.edges)
				.enter()
				.append("line")
				.style("stroke", "#ccc")
				.style("stroke-width",function(d){
					return d.value;
				});
			
			//Create nodes as circles
			var nodes = svg.selectAll("circle")
				.data(dataset.nodes)
				.enter()
				.append("circle")
				.attr("r", function(d){
					return d.value/2;
				})
				.style("fill", function(d, i) {
					return colors(i);
				})
				.call(force.drag);
			
			//Every time the simulation "ticks", this will be called
			force.on("tick", function() {

				edges.attr("x1", function(d) { return d.source.x; })
					 .attr("y1", function(d) { return d.source.y; })
					 .attr("x2", function(d) { return d.target.x; })
					 .attr("y2", function(d) { return d.target.y; });
			
				nodes.attr("cx", function(d) { return d.x; })
					 .attr("cy", function(d) { return d.y; });
	
			});


		</script>
	</body>
</html>