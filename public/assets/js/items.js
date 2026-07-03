document.addEventListener("DOMContentLoaded", () => {

    /*
    -----------------------------------------
    Confirm Delete
    -----------------------------------------
    */

    document.querySelectorAll(".danger").forEach(button => {

        button.addEventListener("click", function(e){

            if(!confirm("Are you sure you want to delete this item?")){

                e.preventDefault();

            }

        });

    });

    /*
    -----------------------------------------
    Auto-hide Alerts
    -----------------------------------------
    */

    document.querySelectorAll(".success, .error").forEach(alert => {

        setTimeout(() => {

            alert.style.transition = "opacity .3s";

            alert.style.opacity = "0";

            setTimeout(() => {

                alert.remove();

            },300);

        },4000);

    });

});