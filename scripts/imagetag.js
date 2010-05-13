function updateForm(){
	$("#width").val($("#drag").attr("offsetWidth"));
	$("#height").val($("#drag").attr("offsetHeight"));
	$("#top").val($("#drag").attr("offsetTop"));
	$("#left").val($("#drag").attr("offsetLeft"));
}
$(document).ready(function(){
	updateForm();
	$("#drag").resizable({
		 stop: function() {
		  	updateForm();
		  }
	});
	$("#drag").draggable({
		  containment: 'parent',
		  stop: function() {
		  	updateForm();
		  }
	});
	$(".tag").hover(
	function () {
	$(this).addClass("tagOn");
	},
	function () {
	$(this).removeClass("tagOn");
	}
	);
});