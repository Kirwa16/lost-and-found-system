// LOGIN

const loginForm = document.getElementById("loginForm");

if(loginForm){

    loginForm.addEventListener("submit", async (e)=>{

        e.preventDefault();

        const email = document.getElementById("email").value;
        const password = document.getElementById("password").value;

        try{

            const response = await fetch(
                `${API_BASE_URL}/login.php`,
                {
                    method:"POST",
                    headers:{
                        "Content-Type":"application/json"
                    },
                    body:JSON.stringify({
                        email,
                        password
                    })
                }
            );

            const data = await response.json();

            if(response.ok){

                localStorage.setItem(
                    "user",
                    JSON.stringify(data)
                );

                window.location.href =
                    "dashboard.html";

            }else{

                alert(
                    data.message ||
                    "Login failed"
                );

            }

        }catch(error){

            console.error(error);

            alert(
                "Server connection error"
            );

        }

    });

}

// REGISTER

const registerForm =
document.getElementById("registerForm");

if(registerForm){

    registerForm.addEventListener(
        "submit",
        async (e)=>{

            e.preventDefault();

            const full_name =
                document.getElementById(
                    "full_name"
                ).value;

            const student_staff_id =
                document.getElementById(
                    "student_staff_id"
                ).value;

            const phone_number =
                document.getElementById(
                    "phone_number"
                ).value;

            const email =
                document.getElementById(
                    "email"
                ).value;

            const password =
                document.getElementById(
                    "password"
                ).value;

            try{

                const response =
                await fetch(
                    `${API_BASE_URL}/register.php`,
                    {
                        method:"POST",
                        headers:{
                            "Content-Type":
                            "application/json"
                        },
                        body:JSON.stringify({
                            full_name,
                            student_staff_id,
                            phone_number,
                            email,
                            password
                        })
                    }
                );

                const data =
                await response.json();

                if(response.ok){

                    alert(
                        "Registration successful"
                    );

                    window.location.href =
                        "login.html";

                }else{

                    alert(
                        data.message ||
                        "Registration failed"
                    );

                }

            }catch(error){

                console.error(error);

                alert(
                    "Server connection error"
                );

            }

        }
    );

}