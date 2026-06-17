// Check Login

const user =
JSON.parse(
    localStorage.getItem("user")
);

if(!user){
    window.location.href =
    "login.html";
}

// Display Name

document.getElementById(
    "userName"
).textContent =
user.full_name || "User";

// Logout

document.getElementById(
    "logoutBtn"
).addEventListener(
    "click",
    () => {

        localStorage.removeItem(
            "user"
        );

        window.location.href =
        "login.html";

    }
);

// Load Dashboard

async function loadDashboard(){

    try{

        const response =
        await fetch(
            `${API_BASE_URL}/items.php`
        );

        const items =
        await response.json();

        let lost = 0;
        let found = 0;
        let returned = 0;

        items.forEach(item=>{

            if(item.item_type==="lost")
                lost++;

            if(item.item_type==="found")
                found++;

            if(item.status==="returned")
                returned++;

        });

        document.getElementById(
            "lostCount"
        ).textContent = lost;

        document.getElementById(
            "foundCount"
        ).textContent = found;

        document.getElementById(
            "returnedCount"
        ).textContent = returned;

        document.getElementById(
            "claimCount"
        ).textContent = 0;

        renderRecentItems(items);

    }

    catch(error){

        console.error(error);

    }

}

function renderRecentItems(items){

    const container =
    document.getElementById(
        "recentItems"
    );

    container.innerHTML = "";

    items.slice(0,5).forEach(item=>{

        container.innerHTML += `

        <div class="item">

            <div class="item-title">
                ${item.item_name}
            </div>

            <div class="item-meta">
                ${item.category}
                •
                ${item.location}
                •
                ${item.status}
            </div>

        </div>

        `;

    });

}

loadDashboard();