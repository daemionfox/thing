$(document).ready(function(){
    console.log("Document is loaded");

    $("#vote-submit-button").on('click', function(event){
        event.preventDefault()
        $("#vote_vendor_isSkip").val(0);

        let votes = $("#vote_vendor_Votes");
        if (parseInt(votes.val()) === 0) {
            votes.val(0);
        }
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

    $(".vendor-delete").on('click', function(event){
       event.preventDefault();
       let vid = $(this).data('vendorid');
       let vname = $(this).data('vendname');
       if (confirm("Do you want to delete " + vname + " id of " + vid + "?")) {
           window.location = '/vendor/delete?vendor=' + vid;
       }
    });

    $(".process-check").on('click', function(event){
        event.preventDefault();
        let bottomScore = $(this).data('score');
        let totalVend = 0;
        let totalTable = 0;
        let smallTotal = 0;
        let largeTotal = 0;
        let bodegaTotal = 0;
        let islandTotal = 0;

        let curVend = parseInt($("#approve_vendor_approved").val());
        $(".vote-process").removeClass('process-accept');
        $(".process-check").each(function(indx, item) {
            let voteproc = $(item).parents('.vote-process').first();
            let scorelocal = $(item).data('score');
            let tableLocal = $(item).data('table');

            if (scorelocal >= bottomScore) {
                voteproc.addClass('process-accept');
                totalVend++;
                if (tableLocal === "Large Booth") {
                    largeTotal++;
                } else if (tableLocal === "Small Booth") {
                    smallTotal++;
                } else if (tableLocal === "Island") {
                    islandTotal++;
                } else if (tableLocal === .5 || tableLocal === ".5" ) {
                    bodegaTotal++;
                } else {
                    totalTable += tableLocal;
                }
            }

        });



        $("#approve_vendor_score").val(bottomScore);
        $("#hover-table").html(totalTable);
        $("#hover-lgbooth").html(largeTotal);
        $("#hover-smbooth").html(smallTotal);
        $("#report-window").html(totalVend + curVend);
    });

    $('.vote-process').on('mouseover', function(event){
        event.preventDefault();
        let scoreButton = $(this).find('.process-check').first();
        let bottomScore = $(scoreButton).data('score');
        let totalVend = 0;
        let totalTable = 0;
        let smallTotal = 0;
        let largeTotal = 0;
        let tattooTotal = 0;
        let bodegaTotal = 0;

        $(".process-check").each(function(indx, item) {
            let voteproc = $(item).parents('.vote-process').first();
            let scorelocal = $(item).data('score');
            let tableLocal = $(item).data('table');

            if (scorelocal >= bottomScore) {
                totalVend++;
                if (tableLocal === "Large Booth") {
                    largeTotal++;
                } else if (tableLocal === "Small Booth") {
                    smallTotal++;
                } else if (tableLocal === "Tattoo Booth") {
                    tattooTotal++;
                } else if (tableLocal === .5) {
                    bodegaTotal++;
                } else {
                    totalTable += tableLocal;
                }

            }

        });
        $("#hover-table").html(totalTable);
        $("#hover-lgbooth").html(largeTotal);
        $("#hover-smbooth").html(smallTotal);
        $("#hover-tbooth").html(tattooTotal);
        $("#hover-bodega").html(bodegaTotal);

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
        let currentvote = parseInt($("#current-vote-value").val());


        let va = $("#vote-alert")
        va.html("");

        if (currentvote < cur && remain === 0) {
            $(this).val(currentvote);
            va.html("You do not have any votes remaining");
        } else if (currentvote < cur && cur > remain) {
            let newv = currentvote + remain;
            if (newv > max) { newv = max }
            $(this).val(newv);
            va.html("You only have " + remain + " votes remaining");
        } else if (cur > max) {
            $(this).val(max);
            va.html("You may not spend more than " + max + " votes");
        } else if (cur < min) {
            $(this).val(min);
            va.html("You may not spend less than " + min + " votes");
        }
    });

    $(".note-modal-btn").on('click', function(event){
        event.preventDefault();
        $("#note_modal").modal('show');

    })

});

function loadChart(target)
{
    console.log('Starting chart pull for ' + target);
    let canvas = $("#" + target);
    let dataurl = canvas.data('url');
    let datatype = canvas.data('charttype');
    let status = canvas.data('status');

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
                },
                onClick: function (e, activeEls) {
                    let datasetIndex = activeEls[0].datasetIndex;
                    let dataIndex = activeEls[0]._index;
                    let cate = this.legend.legendItems[dataIndex].text;
                    window.location = "/vendor?filter_status=" + status + "&filter_category=" + cate;
                }
            }
        });


    }).fail(function(data){
        alert("Problem: " + data);
    });



}