
/* password icon */
function toggle(fieldId, icon){
    const input = document.getElementById(fieldId);
    const eye = icon.querySelector("i");

    if(input && eye) {
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

/* Global Click Handler */
window.onclick = function(event) { 
    // Profile Dropdown
    if (!event.target.closest('.profile-menu')) {
        var dropdown = document.getElementById("profileDropdown");
        if (dropdown) {
            dropdown.style.display = "none";
        }
    }

    // Modal Outside Click
    const modal = document.getElementById("studentModal");
    if (event.target === modal) {
        modal.style.display = "none";
    }
}

/* Modal Logic */
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById("studentModal");
    const openBtn = document.getElementById("openModalBtn");
    const closeBtn = document.querySelector(".close");
    const cancelBtn = document.querySelector(".cancel-btn");

    if(openBtn && modal) {
        openBtn.onclick = () => modal.style.display = "block";
    }
    if(closeBtn && modal) {
        closeBtn.onclick = () => modal.style.display = "none";
    }
    if(cancelBtn && modal) {
        cancelBtn.onclick = () => modal.style.display = "none";
    }

    /* Auto Age Calculation */
    const dobEl = document.getElementById("dob");
    const ageEl = document.getElementById("age");
    
    if(dobEl && ageEl) {
        dobEl.addEventListener("change", function () {
            const dob = new Date(this.value);
            const today = new Date();
            let age = today.getFullYear() - dob.getFullYear();

            if (
                today.getMonth() < dob.getMonth() ||
                (today.getMonth() === dob.getMonth() && today.getDate() < dob.getDate())
            ) {
                age--;
            }
            ageEl.value = age;
        });
    }
});
