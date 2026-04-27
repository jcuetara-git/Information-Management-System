
/* profile icon */
function toggleMenu(){
    var menu = document.getElementById("profileDropdown");

    if(menu.style.display === "block"){
        menu.style.display = "none";
    }else{
        menu.style.display = "block";
    }
}

window.onclick = function(event) { 
    if (!event.target.closest('.profile-menu')) {
        var dropdown = document.getElementById("profileDropdown");
        if (dropdown) {
            dropdown.style.display = "none";
        }
    }
}

/* password icon */
function toggle(fieldId, icon){
    const input = document.getElementById(fieldId);
    const eye = icon.querySelector("i");

    if(input.type === "password"){
        input.type = "text";
        eye.classList.remove("fa-eye");
        eye.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        eye.classList.remove("fa-eye-slash");
        eye.classList.add("fa-eye");
    }
}

/* confirmation message */
function closeSuccess() {
    const overlay = document.querySelector('.success-overlay');
    if (overlay) {
        overlay.classList.add('fade-out');
        setTimeout(() => {
            overlay.remove();
        }, 300);
    }
}

function confirmSave() {
    return confirm("Are you sure you want to save this information?");
}

/* modal */
const modal = document.getElementById("studentModal");
const openBtn = document.getElementById("openModalBtn");
const closeBtn = document.querySelector(".close");
const cancelBtn = document.querySelector(".cancel-btn");

openBtn.onclick = () => modal.style.display = "block";
closeBtn.onclick = () => modal.style.display = "none";
cancelBtn.onclick = () => modal.style.display = "none";

window.onclick = (e) => {
    if (e.target === modal) modal.style.display = "none";
};

/* auto age*/
document.getElementById("dob").addEventListener("change", function () {
    const dob = new Date(this.value);
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();

    if (
        today.getMonth() < dob.getMonth() ||
        (today.getMonth() === dob.getMonth() && today.getDate() < dob.getDate())
    ) {
        age--;
    }

    document.getElementById("age").value = age;
});
