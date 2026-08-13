/* ------------------------------------------------------------------
 | Clinic System - shared browser behaviour
 |
 | The real work (saving, searching, permissions) happens in Laravel on the
 | server. This file only handles small things that live in the browser.
 | -----------------------------------------------------------------*/

document.addEventListener("DOMContentLoaded", function () {

    /* --------------------------------------------------------------
     | 1. Show / hide password
     | -------------------------------------------------------------*/
    document.querySelectorAll(".password-toggle").forEach(function (button) {

        button.addEventListener("click", function () {

            const input = this.parentElement.querySelector("input");

            if (!input) return;

            if (input.type === "password") {
                input.type = "text";
                this.innerHTML = '<i class="bi bi-eye-slash"></i>';
            } else {
                input.type = "password";
                this.innerHTML = '<i class="bi bi-eye"></i>';
            }
        });
    });


    /* --------------------------------------------------------------
     | 2. Ask before logging out
     | -------------------------------------------------------------*/
    document.querySelectorAll(".logout-form").forEach(function (form) {

        form.addEventListener("submit", function (e) {

            if (!confirm("Are you sure you want to logout?")) {
                e.preventDefault();
            }
        });
    });


    /* --------------------------------------------------------------
     | 3. Ask before anything destructive
     |    Any form with  data-confirm="..."  shows that message first.
     | -------------------------------------------------------------*/
    document.querySelectorAll("form[data-confirm]").forEach(function (form) {

        form.addEventListener("submit", function (e) {

            if (!confirm(form.getAttribute("data-confirm"))) {
                e.preventDefault();
            }
        });
    });


    /* --------------------------------------------------------------
     | 4. Flash messages disappear after 5 seconds
     | -------------------------------------------------------------*/
    document.querySelectorAll(".alert-auto-close").forEach(function (alert) {

        setTimeout(function () {
            alert.style.transition = "opacity .4s";
            alert.style.opacity = "0";
            setTimeout(function () { alert.remove(); }, 400);
        }, 5000);
    });


    /* --------------------------------------------------------------
     | 5. Dark mode - remembered in the browser
     | -------------------------------------------------------------*/
    const darkToggle = document.getElementById("darkModeToggle");

    if (localStorage.getItem("clinicDarkMode") === "on") {
        document.body.classList.add("dark-mode");
        if (darkToggle) darkToggle.checked = true;
    }

    if (darkToggle) {
        darkToggle.addEventListener("change", function () {
            document.body.classList.toggle("dark-mode", this.checked);
            localStorage.setItem("clinicDarkMode", this.checked ? "on" : "off");
        });
    }


    /* --------------------------------------------------------------
     | 6. Book Appointment page: show only the doctors that belong to
     |    the department the patient has selected.
     | -------------------------------------------------------------*/
    const departmentSelect = document.getElementById("department");
    const doctorSelect     = document.getElementById("doctor_id");

    if (departmentSelect && doctorSelect && window.clinicDoctors) {

        const refreshDoctors = function () {

            const departmentId = departmentSelect.value;
            const chosen       = doctorSelect.getAttribute("data-selected");

            doctorSelect.innerHTML = '<option value="">Select Doctor</option>';

            window.clinicDoctors
                .filter(function (doctor) {
                    return !departmentId || String(doctor.department_id) === String(departmentId);
                })
                .forEach(function (doctor) {
                    const option = document.createElement("option");
                    option.value = doctor.id;
                    option.textContent = doctor.name + " - " + doctor.specialization;
                    if (String(chosen) === String(doctor.id)) option.selected = true;
                    doctorSelect.appendChild(option);
                });
        };

        departmentSelect.addEventListener("change", refreshDoctors);
        refreshDoctors();
    }


    /* --------------------------------------------------------------
     | 7. Prescription page: "Add Medicine" adds another row
     | -------------------------------------------------------------*/
    const addMedicineBtn = document.getElementById("addMedicineRow");
    const medicineList   = document.getElementById("medicineRows");

    if (addMedicineBtn && medicineList) {

        addMedicineBtn.addEventListener("click", function () {

            const index    = medicineList.querySelectorAll(".medicine-row").length;
            const template = document.getElementById("medicineRowTemplate").innerHTML;

            const wrapper = document.createElement("div");
            wrapper.innerHTML = template.replace(/__INDEX__/g, index);

            medicineList.appendChild(wrapper.firstElementChild);
        });

        // The little "x" button on a row removes it again.
        medicineList.addEventListener("click", function (e) {

            const removeBtn = e.target.closest(".remove-medicine-row");
            if (!removeBtn) return;

            if (medicineList.querySelectorAll(".medicine-row").length > 1) {
                removeBtn.closest(".medicine-row").remove();
            } else {
                alert("A prescription needs at least one medicine.");
            }
        });
    }


    /* --------------------------------------------------------------
     | 8. Any "modal filler" button copies its data-* values into the
     |    matching fields of the modal it opens. This is what makes the
     |    single Edit modal work for every row of a table.
     | -------------------------------------------------------------*/
    document.querySelectorAll("[data-fill-modal]").forEach(function (button) {

        button.addEventListener("click", function () {

            const modalId = this.getAttribute("data-fill-modal");
            const modal   = document.getElementById(modalId);

            if (!modal) return;

            Object.keys(this.dataset).forEach(function (key) {

                if (key === "fillModal") return;

                // data-field-name="value"  ->  the element with name="field_name"
                const field = modal.querySelector('[data-field="' + key + '"]');
                if (field) field.value = button.dataset[key];
            });

            // A form whose action contains __ID__ gets the real id put in.
            const form = modal.querySelector("form[data-action-template]");
            if (form && this.dataset.id) {
                form.action = form.getAttribute("data-action-template")
                                  .replace("__ID__", this.dataset.id);
            }
        });
    });

});
