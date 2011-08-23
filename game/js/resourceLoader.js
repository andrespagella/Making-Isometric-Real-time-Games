// ResourceLoader class

var ResourceType = {
	IMAGE: 0,
	SOUND: 1
}

function ResourceLoader(onPartial, onComplete) {
	this.resources = [];
	this.resourcesLoaded = 0;

	if (onPartial !== undefined && typeof(onPartial) === "function") {
		this.onPartial = onPartial;
	}	

	if (onComplete !== undefined && typeof(onComplete) === "function") {
		this.onComplete = onComplete;
	}	
}

ResourceLoader.prototype.addResource = function(filePath, fileType, resourceType) {
	var res = { 
		filePath: filePath, 
		fileType: fileType,
		resourceType: resourceType
	};

	this.resources.push(res);
}

ResourceLoader.prototype.startPreloading = function() {
	for (var i = 0, len = this.resources.length; i < len; i++) {
		switch(this.resources[i].resourceType) {
			case ResourceType.IMAGE:
				var img = new Image();
				var rl = this;

				img.src = this.resources[i].filePath;
				img.addEventListener('load', function() { rl.onResourceLoaded(); }, false);
				break;
			case ResourceType.SOUND:
				var a = new Audio();

				// Only preload sound files that we can play.
				if (a.canPlayType(this.resources[i].fileType) === "probably") {
					
					a.src = this.resources[i].filePath;
					a.type = this.resources[i].fileType;

					var rl = this;
					a.addEventListener('canplaythrough', function() {
						a.removeEventListener('canplaythrough', arguments.callee, false);
						rl.onResourceLoaded();
        			}, false);
				} else {
					// Can't play the sound. Assume that the resource is loaded.
					this.onResourceLoaded();
				}

				break;
		}
	}
}

ResourceLoader.prototype.onResourceLoaded = function() {
	this.resourcesLoaded++;
	
	if (this.onPartial != undefined) {
		this.onPartial();
	}

	if (this.resourcesLoaded == this.resources.length) {
		if (this.onComplete != undefined) {
			this.onComplete();	
		}
	}

	return;
}

ResourceLoader.prototype.isLoadComplete = function() {
	if (this.resources.length == this.resourcesLoaded) {
		return true;
	}

	return false;
}