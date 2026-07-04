const modal=document.getElementById("editModal");

document.querySelectorAll(".editUser").forEach(btn=>{
btn.onclick=e=>{
e.preventDefault();
id.value=btn.dataset.id;
fullname.value=btn.dataset.fullname;
email.value=btn.dataset.email;
role.value=btn.dataset.role;
if(btn.dataset.id=="<?= $_SESSION['user_id'] ?>"){
role.disabled=true;
}else{
role.disabled=false;
}
modal.classList.add("show");
};
});

function closeModal(){modal.classList.remove("show");}

window.onclick=e=>{if(e.target===modal)closeModal();}

document.querySelectorAll(".success,.error").forEach(a=>{
setTimeout(()=>a.remove(),4000);
});