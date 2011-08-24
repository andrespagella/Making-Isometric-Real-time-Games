// A generic sprite class

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
	this.shown = true;
	this.zoomLevel = 1;
	this.shadow = null;

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
	if (src instanceof Image) {
		this.spritesheet = src;
	} else {
		this.spritesheet = new Image();
		this.spritesheet.src = src;	
	}
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

Sprite.prototype.draw = function(c, drawShadow) {
	if (this.shown) {
		if (drawShadow !== undefined && drawShadow) {
			if (this.shadow === null) { // Shadow not created yet
				var sCnv = document.createElement("canvas");
  				var sCtx = sCnv.getContext("2d");

  				sCnv.width = this.width;
  				sCnv.height = this.height;

  				sCtx.drawImage(this.spritesheet, 
					this.offsetX, 
					this.offsetY, 
					this.width,
					this.height, 
					0, 
					0, 
					this.width * this.zoomLevel, 
					this.height * this.zoomLevel);

				var idata = sCtx.getImageData(0, 0, sCnv.width, sCnv.height);

				for (var i = 0, len = idata.data.length; i < len; i += 4) {
			        idata.data[i] = 0; // R
			        idata.data[i + 1] = 0; // G
			        idata.data[i + 2] = 0; // B
			    }

			    sCtx.clearRect(0, 0, sCnv.width, sCnv.height);
				sCtx.putImageData(idata, 0, 0);

				this.shadow = sCtx;
			}

			c.save();
			c.globalAlpha = 0.1;
			var sw = this.width * this.zoomLevel;
			var sh = this.height * this.zoomLevel;
			c.drawImage(this.shadow.canvas, this.posX, this.posY - sh, sw, sh * 2);
			c.restore();
		}

		c.drawImage(this.spritesheet, 
					this.offsetX, 
					this.offsetY, 
					this.width,
					this.height, 
					this.posX, 
					this.posY, 
					this.width * this.zoomLevel, 
					this.height * this.zoomLevel);
	}
}