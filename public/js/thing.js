$(document).ready(function(){
    console.log("Document is loaded");

    $("#vote-submit-button").on('click', function(event){
        event.preventDefault()
        $("#vote_vendor_isSkip").val(0);
        $("form[name=vote_vendor]").submit();
    });


    $("#vote-skip-button").on('click', function(event){
        event.preventDefault()
        if(confirm("Are you sure you want to skip this vendor?")){
            $("#vote_vendor_isSkip").val(1);
            $("#vote_vendor_Votes").val(0);
            $("form[name=vote_vendor]").submit();
        }
    });

});