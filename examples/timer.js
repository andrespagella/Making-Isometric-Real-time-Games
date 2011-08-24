/**
 * A simple timer
 */

var Timer = function() {
	this.date = new Date();
}

Timer.prototype.update = function() {
	var d = new Date();
	this.date = d;
}
	
Timer.prototype.getMilliseconds = function() {
	return this.date.getTime();
}
	
Timer.prototype.getSeconds = function() {
	return Math.round(this.date.getTime() / 1000);
}