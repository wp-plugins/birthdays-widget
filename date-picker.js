jQuery(document).ready(function(){
    jQuery("#birthday_date").datepicker({
        changeMonth: true,
        changeYear: true,
        maxDate: "+0D",
        "dateFormat" : "dd-mm-yy"
    });
    jQuery("#ui-datepicker-div").hide();
});