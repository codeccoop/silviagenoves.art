console.log("Hello World!");
$(document).on(function () {
	$.find(".filter-link-parent")
		.on("click", function (ev) {
			console.log(ev, this);
		});
});
