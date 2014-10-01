jQuery(document).ready(function() {
    jQuery('.datepicker').datepicker({
        dateFormat : 'yy-mm-dd'
    });
//    jQuery('#addEventButton').hover(function (){
//         		alert("enter an event title please");
// 
//     	if (jQuery("#eventTitle").text() == "enter event title")
//     		alert("enter an event title please");
//     });
});
function ifcrush_validateAddForm() {
	/* Validating date field */
	var x=document.forms["addeventform"]["eventDate"].value;
	if (x=="select date") {
		 alert("Please choose a date");
		 return false;
	}
	/* Validating email field */
	var x=document.forms["addeventform"]["title"].value;
	
	if (x==null || x=="" || x=="enter event title") {
		 alert("Please enter and event title");
		 return false;
	}
	return false; /* change to true later */
}
function ifcrush_validateUpdateForm() {
	/* Validating date field */
	var x=document.forms["updateventform"]["eventDate"].value;
	if (x=="select date") {
		 alert("Please choose a date");
		 return false;
	}
	/* Validating email field */
	var x=document.forms["addeventform"]["title"].value;
	
	if (x==null || x=="" || x=="enter event title") {
		 alert("Please enter a title");
		 return false;
	}
	return true;
}
