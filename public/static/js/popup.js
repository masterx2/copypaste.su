function addEvent(obj, evt, fn) {
    if (obj.addEventListener) {
        obj.addEventListener(evt, fn, false);
    }
    else if (obj.attachEvent) {
        obj.attachEvent("on" + evt, fn);
    }
}

addEvent(document, "mouseout", function(e) {
    e = e ? e : window.event;
    var from = e.relatedTarget || e.toElement;
    if (!from || from.nodeName == "HTML") {
    	if (!popup.getCookie('popup_on_exit')) {
        	popup.create();
    	}
    }
});

var popup = {
	getCookie: function(name) {
	    var matches = document.cookie.match(new RegExp(
	        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
	    ));
	    return matches ? decodeURIComponent(matches[1]) : undefined;
	},

	setCookie: function(name, value, options) {
	    options = options || {};
	    var expires = options.expires;
	    if (typeof expires == "number" && expires) {
	        var d = new Date();
	        d.setTime(d.getTime() + expires * 1000);
	        expires = options.expires = d;
	    }
	    if (expires && expires.toUTCString) {
	        options.expires = expires.toUTCString();
	    }
	    value = encodeURIComponent(value);

	    var updatedCookie = name + "=" + value;

	    for (var propName in options) {
	        updatedCookie += "; " + propName;
	        var propValue = options[propName];
	        if (propValue !== true) {
	            updatedCookie += "=" + propValue;
	        }
	    }
	    document.cookie = updatedCookie;
	},

	animate: function(options) {
		var start = new Date;
		function step() {
			requestAnimationFrame(step)
			var progress = (new Date - start) / options.duration;
			if (progress > 1) progress = 1;
			options.step(progress);
			if (progress == 1) return;
		};
		step();
  	},

  	generate: function(options) {
  		return '<div class="message"><h4>' + options.title + '</h4>' + options.text + '</div> \
  					<div class="controls"><input type="text"><button>Позвоните мне</button></div>';
  	},

	create: function(options) {
		var shadowEl = document.createElement('div'),
			popupEl = document.createElement('div');	
		
		// Shadow Style
		shadowEl.style.opacity = 0;
		shadowEl.style.position = 'absolute';
		shadowEl.style.top = 0;
		shadowEl.style.bottom = 0;
		shadowEl.style.left = 0;
		shadowEl.style.right = 0;
		shadowEl.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
		// Popup style
		popupEl.style.opacity = 0;
		popupEl.style.position = 'absolute';
		popupEl.style.width = '500px';
		popupEl.style.height = '300px';
		popupEl.style.left = '50%';
		popupEl.style.top = '50%';
		popupEl.style.marginLeft = '-250px';
		popupEl.style.marginTop = '-150px';
		popupEl.style.borderRadius = '10px';
		popupEl.style.boxShadow = '0 0 10px #414141';
		popupEl.style.border = '1px solid #808080';
		popupEl.style.overflow = 'hidden';


		popupEl.innerHTML = this.generate({
			title: 'Already leaving?',
			text: "<div class=\"text\">Forgive me if I can not surprise you that service, I hope you come back someday. In the meantime, here's a cute cat</div><img src=\"https://126001.selcdn.ru/Storage/giphy.gif\">"
		});

		addEvent(shadowEl, 'click', function(event){

		})

		document.body.appendChild(shadowEl);
		document.body.appendChild(popupEl);

		this.animate({
			step: function(progress){
				popupEl.style.opacity = progress;
				shadowEl.style.opacity = progress;
			},
			duration: 200
		})

		this.setCookie('popup_on_exit', 1, {
			expires: 600
		});
	},

	destroy: function() {
		
	}
};

