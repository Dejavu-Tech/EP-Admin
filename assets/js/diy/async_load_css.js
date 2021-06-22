/**
 * 异步加载外部CSS文件，并且回调
 */
function styleOnload(node, callback) {
	// for IE6-9 and Opera
	if (node.attachEvent) {
		node.attachEvent('onload', callback);
		// NOTICE:
		// 1. "onload" will be fired in IE6-9 when the file is 404, but in
		// this situation, Opera does nothing, so fallback to timeout.
		// 2. "onerror" doesn't fire in any browsers!
	}
	// polling for Firefox, Chrome, Safari
	else {
		setTimeout(function () {
			poll(node, callback);
		}, 0); // for cache
	}
}

function poll(node, callback) {
	if (callback.isCalled) {
		return;
	}
	var isLoaded = false;
	if (/webkit/i.test(navigator.userAgent)) {// webkit
		if (node['sheet']) {
			isLoaded = true;
		}
	}
	// for Firefox
	else if (node['sheet']) {
		try {
			if (node['sheet'].cssRules) {
				isLoaded = true;
			}
		} catch (ex) {
			// NS_ERROR_DOM_SECURITY_ERR
			if (ex.code === 1000) {
				isLoaded = true;
			}
		}
	}
	if (isLoaded) {
		// give time to render.
		setTimeout(function () {
			callback();
		}, 1);
	} else {
		setTimeout(function () {
			poll(node, callback);
		}, 1);
	}
}

// 我的动态创建LINK函数
function createLink(cssURL, lnkId, charset, media) {
	var head = document.getElementsByTagName('head')[0], linkTag = null;
	if (!cssURL) {
		return false;
	}
	linkTag = document.createElement('link');
	linkTag.setAttribute('id', (lnkId || 'dynamic-style'));
	linkTag.setAttribute('rel', 'stylesheet');
	linkTag.setAttribute('charset', (charset || 'utf-8'));
	linkTag.setAttribute('media', (media || 'all'));
	linkTag.setAttribute('type', 'text/css');
	linkTag.href = cssURL;
	head.appendChild(linkTag);
	return linkTag;
}
