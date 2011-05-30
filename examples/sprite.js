// Sprite class

var Sprite = function(src, width, height, offsetX, offsetY, frames, duration) {
	this.spritesheet = null;
	this.offsetX = 0;
	this.offsetY = 0;
	this.width = width;
	this.height = height;
	this.frames = 1;
	this.currentFrame = 0;
	this.duration = 1;
	this.posX = 0;
	this.posY = 0;

	this.setSpritesheet(src);
	this.setOffset(offsetX, offsetY);
	this.setFrames(frames);
	this.setDuration(duration);
	
	var d = new Date();
	if (this.duration > 0 && this.frames > 0) {
		this.ftime = d.getTime() + (this.duration / this.frames);
	} else {
		this.ftime = 0;	
	}
}

Sprite.prototype.setSpritesheet = function(src) {
	this.spritesheet = new Image();
	this.spritesheet.src = src;
}

Sprite.prototype.setPosition = function(x, y) {
	this.posX = x;
	this.posY = y;
}

Sprite.prototype.setOffset = function(x, y) {
	this.offsetX = x;
	this.offsetY = y;
}

Sprite.prototype.setFrames = function(fcount) {
	this.currentFrame = 0;
	this.frames = fcount;
}

Sprite.prototype.setDuration = function(duration) {
	this.duration = duration;
}

Sprite.prototype.animate = function(c, t) {
	if (t.getMilliseconds() > this.ftime) {
		this.nextFrame ();
	}
	
	this.draw(c);
}

Sprite.prototype.nextFrame = function() {	
	if (this.duration > 0) {
		var d = new Date();

		if (this.duration > 0 && this.frames > 0) {
			this.ftime = d.getTime() + (this.duration / this.frames);
		} else {
			this.ftime = 0;	
		}

		this.offsetX = this.width * this.currentFrame;
		
		if (this.currentFrame === (this.frames - 1)) {
			this.currentFrame = 0;
		} else {
			this.currentFrame++;
		}
	}
}

Sprite.prototype.draw = function(c) {
	c.drawImage(this.spritesheet, this.offsetX, this.offsetY, this.width, this.height, this.posX, this.posY, this.width, this.height);
}