let i= 0
$('#ajout').click(function(e) {
    e.preventDefault()
    let id = 'copy' + i++
   $("#original").clone(true).attr("id",id).appendTo($("#container"));
    
})
function suppression() {
    $(this).parent().parent().html("")

}

$(".supprimer").click(function () {
    $(this).parent().parent().html(null)

})
$("#container").on("click",".supprimer" ,function () {
    $(this).parent().parent().html("")

})