document.addEventListener("DOMContentLoaded", () => {

    const dropdownButton = document.getElementById("userDropdown");
    const dropdownMenu = document.getElementById("userDropdownMenu");
    const chevron = dropdownButton.querySelector(".fa-chevron-down");

    dropdownButton.addEventListener("click", function(e){

        e.stopPropagation();

        dropdownMenu.classList.toggle("show");

        if(dropdownMenu.classList.contains("show")){
            chevron.style.transform = "rotate(180deg)";
        }else{
            chevron.style.transform = "rotate(0deg)";
        }

    });

    document.addEventListener("click", function(){

        dropdownMenu.classList.remove("show");
        chevron.style.transform = "rotate(0deg)";

    });

});