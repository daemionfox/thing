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


    $(".process-check").on('click', function(event){
        event.preventDefault();
        let bottomScore = $(this).data('score');
        let totalVend = 0;
        let curVend = parseInt($("#approve_vendor_approved").val());
        $(".vote-process").removeClass('process-accept');
        $(".process-check").each(function(indx, item) {
            let voteproc = $(item).parents('.vote-process').first();
            let scorelocal = $(item).data('score');

            if (scorelocal >= bottomScore) {
                voteproc.addClass('process-accept');
                totalVend++;
            }

        });
        $("#approve_vendor_score").val(bottomScore);
        $("#report-window").html(totalVend + curVend);
    });

    $('.vote-process').on('mouseover', function(event){
        event.preventDefault();
        let scoreButton = $(this).find('.process-check').first();
        let bottomScore = $(scoreButton).data('score');
        let totalVend = 0;

        $(".process-check").each(function(indx, item) {
            let voteproc = $(item).parents('.vote-process').first();
            let scorelocal = $(item).data('score');

            if (scorelocal >= bottomScore) {
                totalVend++;
            }

        });
        $("#report-hover").html(totalVend);
    }).on('mouseout', function(event){
        event.preventDefault();
        $("#report-hover").html("");
    });

    $("#vote_vendor_Votes").on('blur', function(event){
        console.log("Vote check");
        let cur = parseInt($(this).val());
        let max = parseInt($(this).prop('max'));
        let min = parseInt($(this).prop('min'));
        let remain = parseInt($("#total-votes-remaining").val());

        if (remain < max) {
            $(this).prop('max', remain);
            max = remain;
        }

        let va = $("#vote-alert")
        va.html("");
        if (cur > max) {
            $(this).val(max);
            va.html("You may not spend more than " + max + " votes");
        }
        if (cur < min) {
            $(this).val(min);
            va.html("You may not spend less than " + min + " votes");
        }
    });



});

function loadChart(target)
{
    console.log('Starting chart pull for ' + target);
    let canvas = $("#" + target);
    let dataurl = canvas.data('url');
    let datatype = canvas.data('charttype');

    $.get(
        dataurl,
        {
            'type': datatype,
        }
    ).done(function(data){
        let ctx = document.getElementById(target).getContext('2d');

        let myChart = new Chart(ctx, {
            type: datatype,
            data: data,
            options: {
                legend: {
                    position: 'left'
                }
            }
        });
    }).fail(function(data){
        alert("Problem: " + data);
    });



}