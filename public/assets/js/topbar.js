document.addEventListener("DOMContentLoaded", () => {

    const dropdownButton = document.getElementById("userDropdown");
    const dropdownMenu = document.getElementById("userDropdownMenu");
    const chevron = dropdownButton ? dropdownButton.querySelector(".fa-chevron-down") : null;
    const notificationButton = document.getElementById("notificationDropdown");
    const notificationMenu = document.getElementById("notificationDropdownMenu");

    if(dropdownButton && dropdownMenu) {
        dropdownButton.addEventListener("click", function(e){

            e.stopPropagation();

            dropdownMenu.classList.toggle("show");
            notificationMenu?.classList.remove("show");

            if(dropdownMenu.classList.contains("show")){
                chevron.style.transform = "rotate(180deg)";
            }else{
                chevron.style.transform = "rotate(0deg)";
            }

        });
    }

    if(notificationButton && notificationMenu) {
        notificationButton.addEventListener("click", function(e){
            e.stopPropagation();

            notificationMenu.classList.toggle("show");
            dropdownMenu?.classList.remove("show");

            if(chevron) {
                chevron.style.transform = "rotate(0deg)";
            }
        });

        notificationMenu.addEventListener("click", function(e){
            e.stopPropagation();
        });
    }

    document.addEventListener("click", function(){

        dropdownMenu?.classList.remove("show");
        notificationMenu?.classList.remove("show");

        if(chevron) {
            chevron.style.transform = "rotate(0deg)";
        }

    });

    document.querySelectorAll(".success, .error").forEach(alert => {
        setTimeout(() => {
            alert.classList.add("flash-hiding");

            setTimeout(() => {
                alert.remove();
            }, 350);
        }, 4500);
    });

    if(window.location.search.includes("success=") || window.location.search.includes("error=")) {
        const url = new URL(window.location.href);
        url.searchParams.delete("success");
        url.searchParams.delete("error");
        window.history.replaceState({}, document.title, url.pathname + url.search + url.hash);
    }

});
