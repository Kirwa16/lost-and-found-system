const modal = document.getElementById("editModal");
const tableCard = document.querySelector(".table-card");
const currentUserId = tableCard ? tableCard.dataset.currentUserId : "";
const searchForm = document.getElementById("userSearchForm");
const searchInput = document.getElementById("userSearchInput");
const searchMeta = document.getElementById("userSearchMeta");
const noUsersRow = document.getElementById("noUsersRow");
const userRows = Array.from(document.querySelectorAll(".user-row"));

function bindEditButtons() {
    document.querySelectorAll(".editUser").forEach(btn => {
        btn.onclick = e => {
            e.preventDefault();
            id.value = btn.dataset.id;
            fullname.value = btn.dataset.fullname;
            email.value = btn.dataset.email;
            role.value = btn.dataset.role;
            role.disabled = btn.dataset.id === currentUserId;
            modal.classList.add("show");
        };
    });
}

function closeModal() {
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
    if(e.target === modal) {
        closeModal();
    }
};

document.querySelectorAll(".success,.error").forEach(a => {
    setTimeout(() => a.remove(), 4000);
});
