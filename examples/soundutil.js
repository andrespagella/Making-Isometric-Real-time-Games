// Maximum number of sound objects allowed in the pool
var MAX_PLAYBACKS = 3;
var globalVolume = 0.3;

function SoundUtil() {
	this.maxPlaybacks = MAX_PLAYBACKS;
	this.audioObjects = []; // Pool of audio objects available for reutilization
}

SoundUtil.prototype.play = function(file, startTime, duration, volume, loop) {

	// Get an audio object from pool
	var audioObject = this.getAudioObject();
	var suObj = this;

	/** 
	 * No audio objects are available on the pool. Don't play anything.
	 * NOTE: This is the approach taken by toy organs, alternatively you
	 * could also add objects into a queue to be played later on
	 */
	if (audioObject !== null) {
		audioObject.obj.loop = loop;
		audioObject.obj.volume = volume;

		for (var i = 0; i < file.length; i++) {
			if (audioObject.obj.canPlayType(file[i][1]) === "probably" ||
				audioObject.obj.canPlayType(file[i][1]) === "maybe") {
				audioObject.obj.src = file[i][0];
				audioObject.obj.type = file[i][1];
				break;
			}
		}

		var playBack = function() {
			// Remove the event listener, otherwise it will keep getting called over and over agian
			audioObject.obj.removeEventListener('canplaythrough', playBack, false);
			audioObject.obj.currentTime = startTime;
			audioObject.obj.play();

			// There's no need to listen if the object has finished playing if it's playing in loop mode
			if (!loop) {
				setTimeout(function() {
					audioObject.obj.pause();
					suObj.freeAudioObject(audioObject);
				}, duration);
			}
		}

		audioObject.obj.addEventListener('canplaythrough', playBack, false);
	}
}

SoundUtil.prototype.getAudioObject = function() {
	if (this.audioObjects.length === 0) {
		var a = new Audio();
		var audioObject = {
			id: 0,
			obj: a,
			busy: true
		}

		this.audioObjects.push (audioObject);

		return audioObject;
	} else {
		for (var i = 0; i < this.audioObjects.length; i++) {
			if (!this.audioObjects[i].busy) {
				this.audioObjects[i].busy = true;
				return this.audioObjects[i];
			}
		}

		// No audio objects are free. Can we create a new one?
		if (this.audioObjects.length <= this.maxPlaybacks) {
			var a = new Audio();
			var audioObject = {
				id: this.audioObjects.length,
				obj: a,
				busy: true
			}

			this.audioObjects.push (audioObject);

			return audioObject;
		} else {
			return null;
		}
	}
}

SoundUtil.prototype.freeAudioObject = function(audioObject) {
	for (var i = 0; i < this.audioObjects.length; i++) {
		if (this.audioObjects[i].id === audioObject.id) {
			this.audioObjects[i].currentTime = 0;
			this.audioObjects[i].busy = false;
		}
	}
}