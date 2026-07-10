const modal = document.getElementById("editModal");
const tableCard = document.querySelector(".table-card");
const currentUserId = tableCard ? tableCard.dataset.currentUserId : "";
const searchForm = document.getElementById("userSearchForm");
const searchInput = document.getElementById("userSearchInput");
const searchMeta = document.getElementById("userSearchMeta");
const noUsersRow = document.getElementById("noUsersRow");
const userRows = Array.from(document.querySelectorAll(".user-row"));
const idInput = document.getElementById("id");
const fullnameInput = document.getElementById("fullname");
const emailInput = document.getElementById("email");
const roleInput = document.getElementById("role");

function bindEditButtons() {
    document.querySelectorAll(".editUser").forEach(btn => {
        btn.onclick = e => {
            e.preventDefault();

            if(!modal || !idInput || !fullnameInput || !emailInput || !roleInput) {
                return;
            }

            idInput.value = btn.dataset.id;
            fullnameInput.value = btn.dataset.fullname;
            emailInput.value = btn.dataset.email;
            roleInput.value = btn.dataset.role;
            roleInput.disabled = btn.dataset.id === currentUserId;
            modal.classList.add("show");
        };
    });
}

function closeModal() {
    if(!modal) {
        return;
    }

    modal.classList.remove("show");
}

function filterUsers() {
    const query = (searchInput.value || "").trim().toLowerCase();
    let visibleCount = 0;

    userRows.forEach(row => {
        const isVisible = row.dataset.search.includes(query);
        row.style.display = isVisible ? "" : "none";

        if(isVisible) {
            visibleCount += 1;
            const numberCell = row.querySelector(".user-row-number");
            if(numberCell) {
                numberCell.textContent = visibleCount;
            }
        }
    });

    if(noUsersRow) {
        noUsersRow.style.display = visibleCount === 0 ? "" : "none";
    }

    if(searchMeta) {
        searchMeta.textContent = `${visibleCount} ${visibleCount === 1 ? "user" : "users"} shown`;
    }
}

if(searchForm) {
    searchForm.addEventListener("submit", e => {
        e.preventDefault();
        filterUsers();
    });
}

if(searchInput) {
    searchInput.addEventListener("input", filterUsers);
    filterUsers();
}

bindEditButtons();

window.onclick = e => {
    if(modal && e.target === modal) {
        closeModal();
    }
};

document.querySelectorAll(".success,.error").forEach(a => {
    setTimeout(() => a.remove(), 4000);
});
