document.querySelectorAll(".toggle-password").forEach(icon => {

    icon.addEventListener("click", function () {

        const input = document.getElementById(this.dataset.target);

        if (input.type === "password") {

            input.type = "text";
            this.classList.remove("fa-eye");
            this.classList.add("fa-eye-slash");

        } else {

            input.type = "password";
            this.classList.remove("fa-eye-slash");
            this.classList.add("fa-eye");

        }

    });

});

document.querySelectorAll(".success, .error").forEach(alert => {

    setTimeout(() => {

        alert.style.opacity = "0";

        setTimeout(() => {

            alert.remove();

        }, 300);

    }, 4000);

});

