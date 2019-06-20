
/**
 * Traverses the DOM as specified in the path variable
 * and returns the last node named therein.
 */
function extractNode(node, path)
{
	for(var i = 0; i < path.length; i++) {
		for(var j = 0; j < node.childNodes.length; j++) {
			if(node.childNodes.item(j).nodeName == path[i]) {
				node = node.childNodes.item(j);
				break;
			}
		}
			
		if(node.nodeName != path[i])
			return null;			
	}
	
	return node;
}

/**
 * Returns the textual value of a node.
 */
function extractNodeValue(node, path)
{
	var node = extractNode(node, path);
	
	if(node == null)
		return '';

	if(node.childNodes.length == 0)
		return '';

	return node.childNodes.item(0).data;
}

/**
 * Creates and XMLHttpRequest object and requests an url.
 *
 * @author Apple
 * @license Unknown
 */
function executeXMLHttpRequest(url, callback) 
{
	var req = false;

	// branch for native XMLHttpRequest object
	if (window.XMLHttpRequest) {
		req = new XMLHttpRequest();
		req.onreadystatechange = callback;
		req.open("GET", url, true);
		req.send(null);
	// branch for IE/Windows ActiveX version
	} else if (window.ActiveXObject) {
		req = new ActiveXObject("Microsoft.XMLHTTP");
		if (req) {
			req.onreadystatechange = callback;
			req.open("GET", url, true);
			req.send();
		}
	}

	return req;
}
