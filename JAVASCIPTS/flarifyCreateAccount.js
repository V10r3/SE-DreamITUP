document.addEventListener("DOMContentLoaded", () => {
    const emailInput = document.querySelector("input[type='email']");
    const firstNameInput = document.querySelector("input[placeholder='First Name']");
    const lastNameInput = document.querySelector("input[placeholder='Last Name']");
    const usernameInput = document.querySelector("input[type='text']");
    const roleSelect= document.querySelector("select");
    const passwordInput = document.querySelector("input[type='password']");
    const submitButton = document.querySelector(".btn");

    function showError(message) {
        alert(message);
    }

    submitButton.addEventListener("click", (event) => {
        event.preventDefault();

        if (emailInput.ariaValueMax.trim() === ""){
           showError("Please enter your email address.");
           return;
        }

        if (firstNameInpur.value.trim() === ""){
           showError("Please enter your first name.");
           return;
        }

        if (lastNameInput.value.trim() === ""){
           showError("Please enter your last name.");
           return;
        }

         if (roleSelect.value === "") {
             showError("Please select your role.");
             return;
        }

        if (passwordInput.value.length < 8) {
         showError("Password must be at least 8 characters.");
            return;
        }

        console.log("ACCOUNT DATA:");
        console.log("Email:", emailInput.value);
        console.log("First Name:", firstNameInput.value);
        console.log("Last Name:", lastNameInput.value);
        console.log("Role:", roleSelect.value);
        console.log("Password:", passwordInput.value);

        alert("Account details submitted! (Demo only)");
    });
    
});
