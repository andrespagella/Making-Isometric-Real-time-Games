// Building classes

var Building = function(instanceId, sprite) {
	this.width = 0;
	this.height = 0;
	this.sprite = (sprite == undefined) ? new Sprite('../img/spritesheet.png', this.width, this.height, 0, 0, 1, 0) : sprite;
}
Building.prototype.setSize = function(w, h) {
	this.width = w;
	this.height = h;
	this.sprite.width = w;
	this.sprite.height = h;
}

var Cinema = function(instanceId, sprite) {
	Building.apply(this, arguments);

	this.buildingTypeId = 3; // It's a cinema
	this.instanceId = null;

	this.sprite.setOffset(259, 1);

	this.setSize(256, 200);

	this.tileWidth = 2;
	this.tileHeight = 2;
}		
Cinema.prototype = new Building();

var Hotel = function(instanceId, sprite) {
	Building.apply(this, arguments);

	this.buildingTypeId = 2; // It's a Hotel
	this.instanceId = null;

	this.sprite.setOffset(639, 1);

	this.setSize(256, 300);

	this.tileWidth = 2;
	this.tileHeight = 2;
}		
Hotel.prototype = new Building();

var IceCreamShop = function() {
	Building.apply(this, arguments);

	this.buildingTypeId = 1; // It's an Ice Cream Shop

	this.sprite.setOffset(1, 92);

	this.setSize(128, 110);

	this.tileWidth = 1;
	this.tileHeight = 1;
}
IceCreamShop.prototype = new Building();

var Tree = function() {
	Building.apply(this, arguments);

	this.buildingTypeId = 4; // It's a tree

	this.sprite.setOffset(130, 1);

	this.setSize(128, 110);

	this.tileWidth = 1;
	this.tileHeight = 1;
}
Tree.prototype = new Building();

var BuildingPortion = function(buildingTypeId, x, y) {
	this.buildingTypeId = buildingTypeId;
	this.x = x;
	this.y = y;
}