/*
* jQuery for creating and managing user.
*/
$(document).ready(function() {



	/* Validate input when creating user. */

	$("input[type='submit']").click(function() {

		var error = false;

		$("input[name='name-internal']").removeAttr('class');
		$("input[name='pwd']").removeAttr('class');
		$("input[name='pwd-again']").removeAttr('class');

		if($("input[name='name-internal']").val() == '') {
			error = true;
			$("input[name='name-internal']").attr('class','error');
		}
		else {
			$("input[name='name-internal']").removeAttr('class');
		}

		if($("input[name='pwd']").val() == '') {
			error = true;
			$("input[name='pwd']").attr('class','error');
		}
		else {
			$("input[name='pwd']").removeAttr('class');

			if($("input[name='pwd-again']").val() != $("input[name='pwd']").val()) {
				error = true;
				$("input[name='pwd-again']").attr('class','error');
			}
			else {
				$("input[name='pwd-again']").removeAttr('class');
			}
		}

		if(error) { return false; }
	});



	/* Descriptions of the roles */

	function explainRole(role) {
		var text = "";
		switch(role) {
			case "admin":
				text = "<strong>Administrator</strong> – No restrictions. The only one able to manage users.";
				break;
			case "author":
				text = "<strong>Author</strong> – Creates and manages categories, comments, pages, polls and posts. Can upload and manage media.";
				break;
			case "guest":
				text = "<strong>Guest</strong> – Creates polls and posts and can upload media. But can only manage his or her own stuff.";
				break;
			case "mechanic":
				text = "<strong>Mechanic</strong> – Helps to make everything work. Changes settings and can fiddle with the themes.";
				break;
		}
		$("#explainrole").hide();
		$("#explainrole").html(text);
		$("#explainrole").fadeIn();
	}

	var role = $("#role option:selected").val();
	if(role == null) {
		role = $("#role #explainrole").text();
	}

	explainRole(role);

	$("#role select").change(function() {
		var selected = $("#role option:selected").val();
		explainRole(selected);
	});



	/* Descriptions of the statuses */

	function explainStatus(status) {
		var text = "";
		switch(status) {
			case "active":
				text = "<strong>Employed</strong> – Everything is normal.";
				break;
			case "suspended":
				text = "<strong>Suspended</strong> – This account cannot be logged in into. May be useful when a person is on vacation for a while.";
				break;
			case "trash":
				text = "<strong>Deletion candidate</strong> – This account may be deleted soon and cannot be logged in into.";
				break;
		}
		$("#explainstatus").hide();
		$("#explainstatus").html(text);
		$("#explainstatus").fadeIn();
	}

	var status = $("#status option:selected").val();
	if(status == null) {
		status = $("#status #explainstatus").text();
	}

	explainStatus(status);

	$("#status select").change(function() {
		var selected = $("#status option:selected").val();
		explainStatus(selected);
	});



});