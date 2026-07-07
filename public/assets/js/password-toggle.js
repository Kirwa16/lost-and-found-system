document.querySelectorAll(".toggle-password").forEach(toggle => {
    const togglePassword = () => {
        const input = document.getElementById(toggle.dataset.target);

        if (!input) {
            return;
        }

        const shouldShow = input.type === "password";

        input.type = shouldShow ? "text" : "password";
        toggle.classList.toggle("fa-eye", !shouldShow);
        toggle.classList.toggle("fa-eye-slash", shouldShow);
        toggle.setAttribute("aria-label", shouldShow ? "Hide password" : "Show password");
    };

    toggle.addEventListener("click", togglePassword);
    toggle.addEventListener("keydown", event => {
        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            togglePassword();
        }
    });
});
