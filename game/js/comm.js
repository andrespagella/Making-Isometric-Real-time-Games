// Comm functions

var SERVER_PATH_URL = "http://localhost/~andres/game2/game/async/";

function request(url, callback) {
	var req = false;

	if (window.XMLHttpRequest) {
		try {
			req = new XMLHttpRequest();
		} catch(e) {
			// Do nothing.
		}
	}

	if (req) {
		req.open("GET", url, true);
		req.send(null);
		req.onreadystatechange = function() {
			switch(req.readyState) {
				case 2:
					if (req.status !== 200) {
						callback('ERROR');
						return;
					}
					break;
				case 4:
					callback (req.responseText);
					break;
			}
		}
	} else {
		// Doesn't include support for XMLHttpRequest
		callback('ERROR');
	}
}

// Purchase
function purchase(buildingId, row, col, callback) {
	var url = SERVER_PATH_URL + 'purchase.php';

	url += "?buildingId=" + buildingId;
	url += "&x=" + row;
	url += "&y=" + col;

	request(url, callback);
}

// Demolish
function demolish(row, col, callback) {
	var url = SERVER_PATH_URL + 'demolish.php';

	url += "?x=" + row;
	url += "&y=" + col;

	request(url, callback);
}

// Sync
function sync(callback) {
	var url = SERVER_PATH_URL + 'sync.php';

	request(url, callback);
}