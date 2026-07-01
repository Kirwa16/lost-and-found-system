document.addEventListener("DOMContentLoaded", () => {

    const toggle = document.getElementById("sidebarToggle");
    const sidebar = document.getElementById("sidebar");
    const main = document.getElementById("main");

    console.log(toggle);
    console.log(sidebar);
    console.log(main);

    if (!toggle || !sidebar || !main) {
        console.error("Sidebar elements not found.");
        return;
    }

    // Restore saved state
    if (localStorage.getItem("sidebar") === "collapsed") {
        sidebar.classList.add("collapsed");
        main.classList.add("expanded");
    }

    toggle.addEventListener("click", () => {

        sidebar.classList.toggle("collapsed");
        main.classList.toggle("expanded");

        localStorage.setItem(
            "sidebar",
            sidebar.classList.contains("collapsed")
                ? "collapsed"
                : "expanded"
        );

    });

});