// A* class adjusted for a tileMap with non-contigious indexes

/**
 * @param tileMap: A 2 dimensional matrix with non-contigious indexes
 * @param gridW: Grid width measured in rows  
 * @param gridH: Grid height measured in columns
 * @param src: Source point, an object containing X and Y coordinates representing row/column
 * @param dest: Destination point, an object containing X and Y coordinates representing row/column
 * @param strict: [OPTIONAL] A boolean indicating whether or not traversing through the tileMap should create new indexes (default TRUE)
 */
function aStar(tileMap, gridW, gridH, src, dest, strict) {
	/*
	var openList = [new Node(null, src)];
	var closedList = [];
	var path = [];
	*/
	/*while (var len = openList.length) {

	}
	*/
}

// Heuristic (might want to switch to Manhattan to gain a little more performance)
aStar.prototype.getNext = function(src, dest) {
	return Math.max(Math.abs(src.x - dest.x), abs(src.y - dest.y));
}

function List() {
	
}

function Node(parentNode, src) {
	/*
	parentNode: parentNode,
    this.x: src.x,
    this.y: src.y,
    this.F: 0,
    this.G: 0
    */
}

