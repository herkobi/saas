$(document).ready(function(){

    // $("#content-search").on("keyup", function() {
    //     let value = $(this).val().toLowerCase();
    //     $("#content-list a").filter(function() {
    //         $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    //     });
    // });

    $('*[data-href]').on('click', function() {
        window.location = $(this).data("href");
    });

    $(".table-payment").find('tr[data-target]').on('click', function(){
        $('#orderModal').data('orderid', $(this).data('id'));
    });

    $("#tableSearch").on("keyup", function() {
        let value = $(this).val().toLowerCase();
        $("tbody#content-list tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
