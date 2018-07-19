(function(){
    var f = document.querySelectorAll(".ytsl-click_div");
    for (var i = 0; i < f.length; ++i) {
		f[i].onclick = function () {
			this.parentElement.innerHTML = this.getAttribute("data-iframe");
		}
    }
})();
